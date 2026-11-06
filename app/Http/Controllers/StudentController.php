<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\SchoolParent;
use App\Models\User;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = $user ? $user->teacher : null;
        $isBk = $user && ($user->role === 'bk' || ($teacher && $teacher->isBkTeacher()));

        $bkClassIds = ($isBk && !in_array($user->role, ['waka'], true) && $teacher)
            ? $teacher->bkClasses()->pluck('id')
            : collect();

        $selectedGrade = $request->query('grade');
        $selectedMajor = $request->query('major');
        $selectedRombel = $request->query('rombel');
        $selectedClassId = $request->query('class_id');
        $search = $request->query('search');

        $query = Student::with([
            'user',
            'schoolClass.homeroomTeacher',
            'parent.user',
        ]);

        // Scoping data siswa khusus untuk role Guru BK
        if ($isBk && $user->role !== 'waka') {
            $managedClassIds = $teacher ? $teacher->bkClasses()->pluck('id') : collect();
            $query->whereIn('class_id', $managedClassIds);

            // Jika guru BK mencoba filter kelas di luar binaannya melalui URL
            if ($selectedClassId && !$managedClassIds->contains((int) $selectedClassId)) {
                abort(403, 'Akses Ditolak: Bukan kelas binaan Anda');
            }
        }

        // Filter Kelas Spesifik
        if ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
        }

        // Filter Jurusan
        if ($selectedMajor) {
            $query->whereHas('schoolClass', function ($q) use ($selectedMajor) {
                $q->where('major', $selectedMajor);
            });
        }

        // Filter Tingkat Kelas (10 / X, 11 / XI, 12 / XII)
        if ($selectedGrade) {
            $query->whereHas('schoolClass', function ($q) use ($selectedGrade) {
                $grades = match (strtoupper($selectedGrade)) {
                    '10', 'X' => ['10', 'X'],
                    '11', 'XI' => ['11', 'XI'],
                    '12', 'XII' => ['12', 'XII'],
                    default => [$selectedGrade],
                };

                $q->where(function ($sub) use ($grades, $selectedGrade) {
                    $sub->whereIn('grade', $grades)
                        ->orWhere('name', 'like', $selectedGrade . ' %');
                });
            });
        }

        // Filter Rombel (1, 2, 3, 4)
        if ($selectedRombel) {
            $query->whereHas('schoolClass', function ($q) use ($selectedRombel) {
                $q->where('name', 'like', '% ' . $selectedRombel)
                  ->orWhere('name', 'like', '%' . $selectedRombel);
            });
        }

        // Pencarian Nama atau NIS
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

                $perPage = (int) $request->query('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $students = $query->orderBy('name')->paginate($perPage)->withQueryString();

        if ($bkClassIds->isNotEmpty()) {
            $classes = SchoolClass::with('homeroomTeacher')
                ->whereIn('id', $bkClassIds)
                ->orderBy('grade')
                ->orderBy('name')
                ->get();
        } else {
            $classes = SchoolClass::with('homeroomTeacher')
                ->orderBy('grade')
                ->orderBy('name')
                ->get();
        }

        // Cari informasi kelas jika ada filter kelas atau kombinasi spesifik
        $activeFilteredClass = null;
        if ($selectedClassId) {
            $activeFilteredClass = $classes->firstWhere('id', $selectedClassId);
        } elseif ($selectedMajor && $selectedGrade && $selectedRombel) {
            $gradeNormalized = match (strtoupper($selectedGrade)) {
                '10' => 'X',
                '11' => 'XI',
                '12' => 'XII',
                default => $selectedGrade,
            };
            $activeFilteredClass = $classes->first(function ($c) use ($selectedMajor, $gradeNormalized, $selectedRombel) {
                return $c->major === $selectedMajor &&
                       ($c->grade === $gradeNormalized || str_starts_with($c->name, $gradeNormalized . ' ')) &&
                       str_ends_with($c->name, (string) $selectedRombel);
            });
        }

        $parents = SchoolParent::orderBy('name')->get();

        // Daftar jurusan dari tabel majors
        if ($bkClassIds->isNotEmpty()) {
            $majors = SchoolClass::whereIn('id', $bkClassIds)
                ->whereNotNull('major')
                ->where('major', '!=', '')
                ->distinct()
                ->orderBy('major')
                ->get()
                ->map(fn($c) => (object) ['code' => $c->major, 'name' => $c->major]);
        } else {
            $majors = Major::orderBy('code')->get();
            if ($majors->isEmpty()) {
                $majors = SchoolClass::whereNotNull('major')
                    ->where('major', '!=', '')
                    ->distinct()
                    ->orderBy('major')
                    ->get()
                    ->map(fn($c) => (object) ['code' => $c->major, 'name' => $c->major]);
            }
        }

        return view('students.index', compact(
            'students',
            'classes',
            'parents',
            'majors',
            'activeFilteredClass',
            'selectedGrade',
            'selectedMajor',
            'selectedRombel',
            'selectedClassId',
            'search'
        ));
    }

    public function create()
    {
        if (Auth::user()->role !== 'waka') {
            abort(403, 'Hanya Waka Kurikulum yang berhak menambah siswa.');
        }
        // Hanya akun siswa yang belum memiliki data student dan bukan akun orang tua
        $users = User::where('role', 'siswa')
            ->whereDoesntHave('student')
            ->whereDoesntHave('parent')
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::orderBy('grade')
            ->orderBy('name')
            ->get();

        $parents = SchoolParent::orderBy('name')
            ->get();

        return view('students.create', compact(
            'users',
            'classes',
            'parents'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'class_id' => [
                'required',
                'exists:classes,id',
            ],
            'parent_id' => [
                'nullable',
                'exists:parents,id',
            ],
            'nis' => [
                'required',
                'string',
                'max:50',
                'unique:students,nis',
            ],
            'username' => [
                'nullable',
                'string',
                'max:50',
                'unique:students,username',
                'unique:users,username',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'address' => [
                'nullable',
                'string',
            ],
        ];

        $isExistingUser = $request->filled('user_id') && $request->user_id !== 'new';

        if ($isExistingUser) {
            $rules['user_id'] = [
                'required',
                'exists:users,id',
                'unique:students,user_id',
            ];
        } else {
            $rules['email'] = [
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
            ];
            $rules['password'] = [
                'nullable',
                'string',
                'min:6',
            ];
        }

        $validated = $request->validate($rules, [
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'nis.required' => 'Nomor Induk Siswa (NIS) wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar untuk siswa lain.',
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        DB::transaction(function () use ($validated, $isExistingUser) {
            if (!empty($validated['username'])) {
                $username = trim($validated['username']);
            } else {
                $c = 1;
                while (User::where('username', 'siswa' . $c)->exists() || Student::where('username', 'siswa' . $c)->exists()) {
                    $c++;
                }
                $username = 'siswa' . $c;
            }

            $userEmail = !empty($validated['email']) ? trim($validated['email']) : null;
            $plainPassword = !empty($validated['password']) ? $validated['password'] : 'password';

            if ($isExistingUser) {
                $userId = $validated['user_id'];
                $existingUser = User::find($userId);
                if ($existingUser && empty($existingUser->username)) {
                    $existingUser->update(['username' => $username]);
                }
            } else {
                $user = User::create([
                    'name' => $validated['name'],
                    'username' => $username,
                    'email' => $userEmail,
                    'password' => Hash::make($plainPassword),
                    'role' => 'siswa',
                    'phone' => $validated['phone'] ?? null,
                ]);
                $userId = $user->id;
            }

            // Buat Akun Orang Tua (1 Siswa 1 Orang Tua include)
            // Username ortu: 'p' + username siswa (contoh p12345 / psiswa1)
            $parentId = $validated['parent_id'] ?? null;
            if (!$parentId) {
                $parentUsername = 'p' . $username;
                $parentUser = User::where('username', $parentUsername)->first();
                if (!$parentUser) {
                    $parentUser = User::create([
                        'name' => 'Orang Tua ' . $validated['name'],
                        'username' => $parentUsername,
                        'email' => null,
                        'password' => Hash::make($plainPassword),
                        'role' => 'ortu',
                        'phone' => $validated['phone'] ?? null,
                    ]);
                }
                $parentRecord = SchoolParent::where('user_id', $parentUser->id)->first();
                if (!$parentRecord) {
                    $parentRecord = SchoolParent::create([
                        'user_id' => $parentUser->id,
                        'name' => 'Orang Tua ' . $validated['name'],
                        'phone' => $validated['phone'] ?? null,
                        'address' => $validated['address'] ?? null,
                    ]);
                }
                $parentId = $parentRecord->id;
            }

            Student::create([
                'user_id' => $userId,
                'class_id' => $validated['class_id'],
                'parent_id' => $parentId,
                'nis' => $validated['nis'],
                'username' => $username,
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Data siswa dan akun login berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $user = Auth::user();
        $teacher = $user ? $user->teacher : null;
        $isBk = $user && ($user->role === 'bk' || ($teacher && $teacher->isBkTeacher()));

        $student = Student::with([
            'user',
            'schoolClass',
            'parent',
        ])->findOrFail($id);

        // Jika guru BK mengakses siswa di luar kelas binaannya melalui URL
        if ($isBk && $user->role !== 'waka') {
            $managedClassIds = $teacher ? $teacher->bkClasses()->pluck('id') : collect();
            if (!$managedClassIds->contains((int) $student->class_id)) {
                abort(403, 'Akses Ditolak: Bukan kelas binaan Anda');
            }
        }

        return view('students.show', compact('student'));
    }

    public function edit(string $id)
    {
        if (Auth::user()->role !== 'waka') {
            abort(403, 'Hanya Waka Kurikulum yang berhak mengubah data siswa.');
        }
        $student = Student::with('user')->findOrFail($id);

        // Tampilkan user siswa yang belum dipakai
        // + user milik student yang sedang diedit
        $users = User::where('role', 'siswa')
            ->where(function ($query) use ($student) {
                $query
                    ->where(function ($q) {
                        $q->whereDoesntHave('student')
                          ->whereDoesntHave('parent');
                    })
                    ->orWhere('id', $student->user_id);
            })
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::orderBy('grade')
            ->orderBy('name')
            ->get();

        $parents = SchoolParent::orderBy('name')
            ->get();

        return view('students.edit', compact(
            'student',
            'users',
            'classes',
            'parents'
        ));
    }

    public function update(Request $request, string $id)
    {
        $student = Student::with('user')->findOrFail($id);

        $rules = [
            'class_id' => [
                'required',
                'exists:classes,id',
            ],
            'parent_id' => [
                'nullable',
                'exists:parents,id',
            ],
            'nis' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'nis')
                    ->ignore($student->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'address' => [
                'nullable',
                'string',
            ],
        ];

        if ($request->filled('user_id')) {
            $rules['user_id'] = [
                'required',
                'exists:users,id',
                Rule::unique('students', 'user_id')->ignore($student->id),
            ];
        }

        if ($request->has('email') && $student->user) {
            $rules['email'] = [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($student->user_id),
            ];
        }

        if ($request->filled('password')) {
            $rules['password'] = [
                'nullable',
                'string',
                'min:6',
            ];
        }

        $validated = $request->validate($rules, [
            'class_id.required' => 'Kelas wajib dipilih.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan siswa lain.',
            'name.required' => 'Nama siswa wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        DB::transaction(function () use ($student, $validated, $request) {
            $usernameVal = !empty($validated['username']) ? trim($validated['username']) : null;

            // Update data user akun jika ada
            if ($student->user) {
                $userUpdates = [
                    'name' => $validated['name'],
                    'phone' => $validated['phone'] ?? null,
                ];

                if ($usernameVal !== null) {
                    $userUpdates['username'] = $usernameVal;
                }

                if ($request->has('email')) {
                    $userUpdates['email'] = !empty($validated['email']) ? trim($validated['email']) : null;
                }

                if (!empty($validated['password'])) {
                    $userUpdates['password'] = Hash::make($validated['password']);
                }

                $student->user->update($userUpdates);
            }

            $studentUpdates = [
                'class_id' => $validated['class_id'],
                'parent_id' => $validated['parent_id'] ?? null,
                'nis' => $validated['nis'],
                'username' => $usernameVal,
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ];

            if (!empty($validated['user_id'])) {
                $studentUpdates['user_id'] = $validated['user_id'];
            }

            $student->update($studentUpdates);

            // Perbarui juga data akun orang tua jika ada
            if ($student->parent) {
                $parentUserUpdates = ['name' => 'Orang Tua ' . $validated['name']];
                if ($usernameVal !== null) {
                    $parentUserUpdates['username'] = 'p' . $usernameVal;
                }
                $student->parent->user?->update($parentUserUpdates);
                $student->parent->update([
                    'name' => 'Orang Tua ' . $validated['name'],
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $student = Student::with(['user', 'parent.user'])->findOrFail($id);

        DB::transaction(function () use ($student) {
            $user = $student->user;
            $parent = $student->parent;
            $parentUser = $parent?->user;

            $student->delete();

            if ($parent) {
                $parent->delete();
            }

            if ($parentUser) {
                $parentUser->delete();
            }

            if ($user) {
                $user->delete();
            }
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Data siswa dan akun orang tua berhasil dihapus.');
    }

    /**
     * Menghapus beberapa data siswa yang dipilih sekaligus (Bulk Delete).
     */
    public function bulkDestroy(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'waka') {
            abort(403, 'Hanya Waka Kurikulum yang dapat menghapus data siswa.');
        }

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
        ], [
            'student_ids.required' => 'Pilih minimal satu siswa yang ingin dihapus.',
            'student_ids.min' => 'Pilih minimal satu siswa yang ingin dihapus.',
        ]);

        $studentIds = $validated['student_ids'];

        DB::transaction(function () use ($studentIds) {
            $students = Student::with(['user', 'parent.user'])->whereIn('id', $studentIds)->get();
            $userIdsToDelete = [];
            $parentIdsToDelete = [];

            foreach ($students as $student) {
                if ($student->user_id) {
                    $userIdsToDelete[] = $student->user_id;
                }
                if ($student->parent && $student->parent->user_id) {
                    $userIdsToDelete[] = $student->parent->user_id;
                }
                if ($student->parent_id) {
                    $parentIdsToDelete[] = $student->parent_id;
                }
                $student->delete();
            }

            if (!empty($parentIdsToDelete)) {
                SchoolParent::whereIn('id', $parentIdsToDelete)->delete();
            }

            if (!empty($userIdsToDelete)) {
                User::whereIn('id', $userIdsToDelete)->delete();
            }
        });

        $count = count($studentIds);
        return redirect()
            ->route('students.index')
            ->with('success', "Sebanyak {$count} data siswa dan akun orang tua terpilih berhasil dihapus.");
    }

    /**
     * Download format template CSV/Excel untuk import siswa.
     */

    /**
     * Menghapus seluruh data siswa beserta akun pengguna siswa terkait.
     */
    public function destroyAll()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'waka') {
            abort(403, 'Hanya Waka Kurikulum yang dapat menghapus seluruh data siswa.');
        }

        DB::transaction(function () {
            $students = Student::with(['user', 'parent.user'])->get();
            $userIdsToDelete = [];
            $parentIdsToDelete = [];

            foreach ($students as $student) {
                if ($student->user_id) {
                    $userIdsToDelete[] = $student->user_id;
                }
                if ($student->parent && $student->parent->user_id) {
                    $userIdsToDelete[] = $student->parent->user_id;
                }
                if ($student->parent_id) {
                    $parentIdsToDelete[] = $student->parent_id;
                }
                $student->delete();
            }

            if (!empty($parentIdsToDelete)) {
                SchoolParent::whereIn('id', $parentIdsToDelete)->delete();
            }

            if (!empty($userIdsToDelete)) {
                User::whereIn('id', $userIdsToDelete)->delete();
            }
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Seluruh data siswa berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_siswa.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Ambil beberapa contoh nama kelas aktual dari database
        $sampleClasses = SchoolClass::orderBy('name')->take(3)->pluck('name')->toArray();
        $class1 = $sampleClasses[0] ?? 'X RPL 1';
        $class2 = $sampleClasses[1] ?? 'XII RPL 2';
        $class3 = $sampleClasses[2] ?? 'XI TKJ 1';

        $callback = function () use ($class1, $class2, $class3) {
            $output = fopen('php://output', 'w');
            
            // UTF-8 BOM untuk kompatibilitas langsung di Microsoft Excel Windows
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header kolom
            fputcsv($output, [
                'nis',
                'username',
                'nama',
                'kelas',
                'email',
                'password',
                'no_telepon',
                'alamat',
            ]);

            // Baris contoh 1
            fputcsv($output, [
                '20241001',
                'ahmad_dahlan',
                'Ahmad Dahlan',
                $class1,
                'ahmad@sekolah.sch.id',
                'password123',
                '081234567890',
                'Jl. Malioboro No. 12, Yogyakarta',
            ]);

            // Baris contoh 2 (tanpa email, password & username manual, otomatis digenerate sistem)
            fputcsv($output, [
                '20241002',
                '',
                'Siti Aminah',
                $class2,
                '',
                '',
                '081298765432',
                'Jl. Kaliurang Km. 5, Sleman',
            ]);

            // Baris contoh 3
            fputcsv($output, [
                '20241003',
                'Budi Santoso',
                $class3,
                'budi@sekolah.sch.id',
                'password123',
                '081345678901',
                'Jl. Solo Km. 8, Bantul',
            ]);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import data siswa dan akun login dari file Excel / CSV lokal.
     */
    public function import(Request $request)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ], [
            'file.required' => 'File CSV/Excel wajib dipilih.',
            'file.max' => 'Ukuran file maksimal 20MB.',
        ]);

        $file = $request->file('file');
        $extension = strtolower(trim((string) $file->getClientOriginalExtension()));
        if (empty($extension)) {
            $extension = strtolower(trim((string) $file->extension()));
        }

        $filePath = $file->getRealPath();
        $allRows = \App\Services\DocumentTableExtractor::extract($filePath, $extension);

        if (empty($allRows)) {
            return redirect()
                ->route('students.index')
                ->with('error', 'Gagal membaca isi berkas. Pastikan berkas memiliki isi data tabel yang dapat dibaca.');
        }

        // Cache semua kelas dalam bentuk lowercase untuk pencocokan cepat
        $allClasses = SchoolClass::all()->keyBy(function ($c) {
            return strtolower(preg_replace('/\s+/', '', $c->name));
        });

        // =================================================================
        // METODE 1: Deteksi Cerdas Format "Daftar Hadir Siswa" (Multi-Kelas)
        // =================================================================
        $currentClassName = null;
        $dataStudents = [];

        foreach ($allRows as $r) {
            $fullRow = implode(' ', array_filter(array_map('trim', $r)));

            // 1. Deteksi judul kelas di dalam baris (misal: "KELAS : X RPL 1", "KELAS : XII DKV 2", dll)
            if (preg_match('/\bKELAS\b/i', $fullRow) || preg_match('/\b(XII|XI|X)\s+[A-Za-z]+(\s+\d+)?\b/i', $fullRow)) {
                if (preg_match('/\b(XII|XI|X|\d+)\s+([A-Za-z]+)(\s+\d+)?\b/i', $fullRow, $cm)) {
                    $currentClassName = strtoupper(trim(preg_replace('/\s+/', ' ', $cm[0])));
                    continue;
                }
            }

            // 2. Deteksi baris siswa (mencari sel NIS: angka 4-12 digit)
            $nisColIdx = null;
            $nisVal = null;
            foreach ($r as $idx => $val) {
                $cleanVal = trim((string)$val);
                $cleanVal = trim($cleanVal, "'\"\t ");
                if (preg_match('/^[0-9]{4,12}$/', $cleanVal)) {
                    $nisColIdx = $idx;
                    $nisVal = $cleanVal;
                    break;
                }
            }

            // Jika ada NIS dan kelas aktif terdeteksi
            if ($nisColIdx !== null && $currentClassName) {
                // Cari nama siswa di kolom setelah NIS
                $name = '';
                for ($idx = $nisColIdx + 1; $idx < count($r); $idx++) {
                    $cand = trim((string)$r[$idx]);
                    $cand = trim($cand, "'\"\t ");
                    if (strlen($cand) >= 2 && !preg_match('/^[0-9]+$/', $cand) && !in_array(strtoupper($cand), ['L', 'P', 'S', 'I', 'A'])) {
                        $name = $cand;
                        break;
                    }
                }

                if (!empty($name)) {
                    $dataStudents[] = [
                        'nis' => $nisVal,
                        'name' => $name,
                        'class' => $currentClassName,
                        'username' => $nisVal,
                        'email' => '',
                        'password' => '',
                        'phone' => null,
                        'address' => null,
                    ];
                }
            }
        }

        // =================================================================
        // METODE 2: Format Tabel Datar (Flat Table dengan header kolom)
        // =================================================================
        if (empty($dataStudents)) {
            $headerRowIndex = null;
            $headerMap = [];

            foreach (array_slice($allRows, 0, 10, true) as $rowIndex => $candidateRow) {
                $tempMap = [];
                foreach ($candidateRow as $colIdx => $col) {
                    $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$col)));
                    $clean = preg_replace('/[^a-z0-9]/', '', $clean);

                    if (empty($clean)) continue;

                    if (!isset($tempMap['nis']) && (str_contains($clean, 'nis') || str_contains($clean, 'noinduk') || str_contains($clean, 'nomorinduk'))) {
                        $tempMap['nis'] = $colIdx;
                    } elseif (!isset($tempMap['username']) && (str_contains($clean, 'username') || in_array($clean, ['user', 'namauser', 'akun', 'login']))) {
                        $tempMap['username'] = $colIdx;
                    } elseif (!isset($tempMap['name']) && (str_contains($clean, 'nama') || str_contains($clean, 'name') || str_contains($clean, 'pesertadidik') || str_contains($clean, 'siswa') || str_contains($clean, 'murid'))) {
                        $tempMap['name'] = $colIdx;
                    } elseif (!isset($tempMap['class']) && (str_contains($clean, 'kelas') || str_contains($clean, 'rombel') || str_contains($clean, 'class') || str_contains($clean, 'tingkat'))) {
                        $tempMap['class'] = $colIdx;
                    } elseif (!isset($tempMap['email']) && (str_contains($clean, 'mail') || str_contains($clean, 'surel'))) {
                        $tempMap['email'] = $colIdx;
                    } elseif (!isset($tempMap['password']) && (str_contains($clean, 'pass') || str_contains($clean, 'sandi') || str_contains($clean, 'pwd'))) {
                        $tempMap['password'] = $colIdx;
                    } elseif (!isset($tempMap['phone']) && (str_contains($clean, 'telp') || str_contains($clean, 'telepon') || str_contains($clean, 'phone') || str_contains($clean, 'hp') || str_contains($clean, 'wa') || str_contains($clean, 'kontak'))) {
                        $tempMap['phone'] = $colIdx;
                    } elseif (!isset($tempMap['address']) && (str_contains($clean, 'alamat') || str_contains($clean, 'address') || str_contains($clean, 'domisili') || str_contains($clean, 'tinggal'))) {
                        $tempMap['address'] = $colIdx;
                    }
                }

                if (isset($tempMap['nis']) && (isset($tempMap['name']) || isset($tempMap['class']))) {
                    $headerRowIndex = $rowIndex;
                    $headerMap = $tempMap;
                    break;
                } elseif (isset($tempMap['name']) && isset($tempMap['class'])) {
                    $headerRowIndex = $rowIndex;
                    $headerMap = $tempMap;
                    break;
                }
            }

            if ($headerRowIndex === null) {
                return redirect()
                    ->route('students.index')
                    ->with('error', 'Format data tidak dikenali. Pastikan berkas memuat judul kolom NIS, Nama, dan Kelas, atau format Daftar Hadir Siswa per kelas.');
            }

            $rawRows = array_slice($allRows, $headerRowIndex + 1);
            foreach ($rawRows as $row) {
                if (empty(array_filter($row))) continue;
                $nis = isset($headerMap['nis']) ? trim($row[$headerMap['nis']] ?? '') : '';
                $name = isset($headerMap['name']) ? trim($row[$headerMap['name']] ?? '') : '';
                $className = isset($headerMap['class']) ? trim($row[$headerMap['class']] ?? '') : '';
                if (empty($nis) || empty($name)) continue;

                $dataStudents[] = [
                    'nis' => $nis,
                    'name' => $name,
                    'class' => $className,
                    'username' => isset($headerMap['username']) && !empty(trim($row[$headerMap['username']] ?? '')) ? trim($row[$headerMap['username']]) : $nis,
                    'email' => isset($headerMap['email']) ? trim($row[$headerMap['email']] ?? '') : '',
                    'password' => isset($headerMap['password']) ? trim($row[$headerMap['password']] ?? '') : '',
                    'phone' => isset($headerMap['phone']) ? trim($row[$headerMap['phone']] ?? '') : null,
                    'address' => isset($headerMap['address']) ? trim($row[$headerMap['address']] ?? '') : null,
                ];
            }
        }

        if (empty($dataStudents)) {
            return redirect()
                ->route('students.index')
                ->with('error', 'Tidak ada baris data siswa yang berhasil terbaca dari berkas.');
        }

        $createdCount = 0;
        $updatedCount = 0;

        // Optimasi performa untuk dataset besar (ratusan/ribuan siswa)
        $defaultHashedPassword = Hash::make('password');
        $existingStudentsByNis = Student::with('user')->get()->keyBy('nis');
        $existingEmails = User::pluck('email')->filter()->flip()->toArray();
        $existingUsernames = User::pluck('username')->filter()->flip()->toArray();

        DB::beginTransaction();
        try {
            $rowCounter = 0;
            foreach ($dataStudents as $item) {
                $rowCounter++;
                $nis = $item['nis'];
                $name = $item['name'];
                $className = $item['class'];
                $email = !empty($item['email']) ? trim($item['email']) : null;
                $password = $item['password'];
                $phone = $item['phone'];
                $address = $item['address'];

                // Resolusi Username: Gunakan username dari berkas, atau buat siswa1, siswa2, dst.
                if (!empty($item['username'])) {
                    $candUsername = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $item['username']));
                } else {
                    $candUsername = 'siswa' . $rowCounter;
                }

                $username = $candUsername;
                $suffix = 1;
                while (isset($existingUsernames[$username])) {
                    $username = 'siswa' . ($rowCounter + $suffix);
                    $suffix++;
                }
                $existingUsernames[$username] = true;

                // Resolusi Email: Dikosongkan jika tidak ada di berkas (dapat diisi Waka nantinya)
                if ($email !== null) {
                    if (isset($existingEmails[$email])) {
                        $email = null;
                    } else {
                        $existingEmails[$email] = true;
                    }
                }

                // Resolusi Kelas
                $cleanClassName = strtolower(preg_replace('/\s+/', '', $className));
                $schoolClass = $allClasses->get($cleanClassName);

                // Cek variasi nama kelas (misal: "X AK 1" -> "X AKL 1")
                if (!$schoolClass && str_contains($cleanClassName, 'ak') && !str_contains($cleanClassName, 'akl')) {
                    $altName = preg_replace('/ak([0-9]+)/', 'akl$1', $cleanClassName);
                    $schoolClass = $allClasses->get($altName);
                }

                // Jika kelas belum ada di database, buat otomatis
                if (!$schoolClass) {
                    $grade = 'X';
                    if (preg_match('/^(XII|XI|X|\d+)/i', $className, $gm)) {
                        $grade = strtoupper($gm[1]);
                    }
                    $major = 'UMUM';
                    if (preg_match('/\b(RPL|TKJ|DKV|AKL|AK|MP|BR|BD|LPS)\b/i', $className, $mm)) {
                        $major = strtoupper($mm[1]);
                    }
                    $schoolClass = SchoolClass::create([
                        'name' => $className,
                        'grade' => $grade,
                        'major' => $major,
                    ]);
                    $allClasses->put($cleanClassName, $schoolClass);
                }

                $hashedPassword = !empty($password) ? Hash::make($password) : $defaultHashedPassword;

                // Cari siswa berdasarkan NIS dari cache
                $student = $existingStudentsByNis->get($nis);

                if ($student) {
                    $finalUsername = !empty($student->username) ? $student->username : $username;
                    $student->update([
                        'name' => $name,
                        'username' => $finalUsername,
                        'class_id' => $schoolClass->id,
                        'phone' => $phone ?: $student->phone,
                        'address' => $address ?: $student->address,
                    ]);

                    if ($student->user) {
                        $userUpdate = ['name' => $name, 'username' => $finalUsername];
                        if (!empty($password)) {
                            $userUpdate['password'] = $hashedPassword;
                        }
                        if ($email !== null) {
                            $userUpdate['email'] = $email;
                        }
                        if ($phone) {
                            $userUpdate['phone'] = $phone;
                        }
                        $student->user->update($userUpdate);
                    }

                    // Pastikan siswa yang diperbarui memiliki akun orang tua
                    if (!$student->parent_id) {
                        $parentUsername = 'p' . $finalUsername;
                        $parentUser = User::where('username', $parentUsername)->first();
                        if (!$parentUser) {
                            $parentUser = User::create([
                                'name' => 'Orang Tua ' . $name,
                                'username' => $parentUsername,
                                'email' => null,
                                'password' => $hashedPassword,
                                'role' => 'ortu',
                                'phone' => $phone ?: $student->phone,
                            ]);
                        }
                        $parentRecord = SchoolParent::where('user_id', $parentUser->id)->first();
                        if (!$parentRecord) {
                            $parentRecord = SchoolParent::create([
                                'user_id' => $parentUser->id,
                                'name' => 'Orang Tua ' . $name,
                                'phone' => $phone ?: $student->phone,
                                'address' => $address ?: $student->address,
                            ]);
                        }
                        $student->update(['parent_id' => $parentRecord->id]);
                    }

                    $updatedCount++;
                } else {
                    $user = User::create([
                        'name' => $name,
                        'username' => $username,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'role' => 'siswa',
                        'phone' => $phone,
                    ]);

                    // Buat Akun Orang Tua (Include 1 Siswa 1 Orang Tua)
                    // Username ortu: 'p' + username siswa (contoh p12345 / psiswa1)
                    $parentUsername = 'p' . $username;
                    $parentUser = User::where('username', $parentUsername)->first();
                    if (!$parentUser) {
                        $parentUser = User::create([
                            'name' => 'Orang Tua ' . $name,
                            'username' => $parentUsername,
                            'email' => null,
                            'password' => $hashedPassword,
                            'role' => 'ortu',
                            'phone' => $phone,
                        ]);
                    }
                    $parentRecord = SchoolParent::where('user_id', $parentUser->id)->first();
                    if (!$parentRecord) {
                        $parentRecord = SchoolParent::create([
                            'user_id' => $parentUser->id,
                            'name' => 'Orang Tua ' . $name,
                            'phone' => $phone,
                            'address' => $address,
                        ]);
                    }

                    $newStudent = Student::create([
                        'user_id' => $user->id,
                        'class_id' => $schoolClass->id,
                        'parent_id' => $parentRecord->id,
                        'nis' => $nis,
                        'username' => $username,
                        'name' => $name,
                        'phone' => $phone,
                        'address' => $address,
                    ]);
                    $existingStudentsByNis->put($nis, $newStudent);
                    $createdCount++;
                }
            }

            DB::commit();

            $msg = "Import data siswa berhasil. {$createdCount} siswa baru ditambahkan";
            if ($updatedCount > 0) {
                $msg .= ", {$updatedCount} data siswa diperbarui.";
            } else {
                $msg .= ".";
            }

            return redirect()
                ->route('students.index')
                ->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('students.index')
                ->with('error', 'Terjadi kesalahan saat menyimpan data siswa: ' . $e->getMessage());
        }
    }
}