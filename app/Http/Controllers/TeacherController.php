<?php
namespace App\Http\Controllers;
use App\Models\Teacher;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class TeacherController extends Controller
{
    /**
     * Menampilkan daftar guru.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $homeroomStatus = $request->query('homeroom_status');
        $classId = $request->query('class_id');
        $query = Teacher::with(['user', 'homeroomClasses']);
        // Filter Pencarian (Nama / NIP / Email)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }
        // Filter Status Penugasan Wali Kelas
        if ($homeroomStatus === 'is_homeroom') {
            $query->has('homeroomClasses');
        } elseif ($homeroomStatus === 'not_homeroom') {
            $query->doesntHave('homeroomClasses');
        }
        // Filter Kelas Wali yang diampu
        if ($classId) {
            $query->whereHas('homeroomClasses', function ($q) use ($classId) {
                $q->where('id', $classId);
            });
        }
                $perPage = (int) $request->query('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $teachers = $query->orderBy('name')->paginate($perPage)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();
        return view('teachers.index', compact(
            'teachers',
            'classes',
            'search',
            'homeroomStatus',
            'classId'
        ));
    }

    /**
     * Sinkronisasi otomatis mapel guru ke master data mata pelajaran (tabel subjects).
     */
    private function syncSubjectToDatabase(?string $mapelName): ?Subject
    {
        if (empty($mapelName)) {
            return null;
        }

        $raw = trim($mapelName);
        if ($raw === '' || $raw === '-' || strtolower($raw) === 'null') {
            return null;
        }

        // Tangani jika ada beberapa mapel dipisah koma atau titik koma
        $parts = preg_split('/[,;]+/', $raw);
        $primarySubject = null;

        foreach ($parts as $part) {
            $name = trim($part);
            if ($name === '' || $name === '-') {
                continue;
            }

            // Cari apakah mapel sudah ada di tabel subjects (case-insensitive)
            $existing = Subject::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();
            if (!$existing) {
                // Generate kode unik dari singkatan nama mapel
                $cleanWords = preg_split('/\s+/', strtoupper($name));
                $codeCandidate = '';
                if (count($cleanWords) >= 2) {
                    foreach ($cleanWords as $w) {
                        $cleanW = preg_replace('/[^A-Z0-9]/', '', $w);
                        if ($cleanW !== '') {
                            $codeCandidate .= substr($cleanW, 0, 1);
                        }
                    }
                } else {
                    $cleanW = preg_replace('/[^A-Z0-9]/', '', $cleanWords[0] ?? '');
                    $codeCandidate = substr($cleanW, 0, 4);
                }

                if (strlen($codeCandidate) < 2) {
                    $codeCandidate = 'MPL';
                }

                $uniqueCode = $codeCandidate;
                $counter = 1;
                while (Subject::where('code', $uniqueCode)->exists()) {
                    $uniqueCode = $codeCandidate . '-' . $counter;
                    $counter++;
                }

                $created = Subject::create([
                    'code' => $uniqueCode,
                    'name' => $name,
                    'description' => 'Mata pelajaran otomatis dari penugasan guru',
                ]);

                if (!$primarySubject) {
                    $primarySubject = $created;
                }
            } else {
                if (!$primarySubject) {
                    $primarySubject = $existing;
                }
            }
        }

        return $primarySubject;
    }


    /**
     * Mencari dan menyelesaikan objek SchoolClass untuk penugasan wali kelas dari baris file import.
     * Mendukung:
     * - "X RPL 1", "X-RPL-1", "X.RPL.1", "10 RPL 1", "10-RPL-1"
     * - "Wali Kelas X RPL 1", "Wali Kelas: XII AKL 2", "Wali XI DKV 1"
     * - Kolom wali kelas, tugas tambahan, jabatan, atau sel baris mana pun yang memuat info wali kelas.
     */
    private function resolveHomeroomClass(?string $rawHomeroom, ?string $rawPosition, array $rowCells, array &$normalizedClasses): ?SchoolClass
    {
        $candidates = [];

        if (!empty($rawHomeroom)) {
            $candidates[] = trim($rawHomeroom);
        }
        if (!empty($rawPosition) && (stripos($rawPosition, 'wali') !== false || preg_match('/\b(XII|XI|X|12|11|10)\b/i', $rawPosition))) {
            $candidates[] = trim($rawPosition);
        }

        // Cek sel baris lain jika ada yang memuat kata 'wali' atau pola penugasan kelas
        foreach ($rowCells as $cell) {
            $cellStr = trim((string)$cell);
            if ($cellStr !== '' && stripos($cellStr, 'wali') !== false) {
                if (!in_array($cellStr, $candidates)) {
                    $candidates[] = $cellStr;
                }
            }
        }

        foreach ($candidates as $cand) {
            // 1. Bersihkan awalan kata wali kelas
            $cleaned = preg_replace('/^(wali\s*kelas|wali|walas|tugas\s*tambahan|tugas)\s*[:\-]?\s*/i', '', $cand);
            $cleanKey = strtolower(preg_replace('/[^a-z0-9]/i', '', $cleaned));

            if (isset($normalizedClasses[$cleanKey])) {
                return $normalizedClasses[$cleanKey];
            }

            $akClean = str_replace('ak', 'akl', $cleanKey);
            if (isset($normalizedClasses[$akClean])) {
                return $normalizedClasses[$akClean];
            }

            // 2. Gunakan Regex untuk mengekstrak Tingkat + Jurusan + Rombel
            if (preg_match('/\b(XII|XI|X|12|11|10)[\s\-\._]*([A-Za-z]+)[\s\-\._]*(\d+)?\b/i', $cand, $m)) {
                $grade = strtoupper($m[1]);
                $major = strtoupper($m[2]);
                $rombel = $m[3] ?? '';

                $gradeRoman = match ($grade) {
                    '10' => 'X',
                    '11' => 'XI',
                    '12' => 'XII',
                    default => $grade,
                };

                $targetKey = strtolower(preg_replace('/[^a-z0-9]/i', '', $gradeRoman . $major . $rombel));
                if (isset($normalizedClasses[$targetKey])) {
                    return $normalizedClasses[$targetKey];
                }

                $akTarget = str_replace('ak', 'akl', $targetKey);
                if (isset($normalizedClasses[$akTarget])) {
                    return $normalizedClasses[$akTarget];
                }

                $targetNoAk = str_replace('akl', 'ak', $targetKey);
                if (isset($normalizedClasses[$targetNoAk])) {
                    return $normalizedClasses[$targetNoAk];
                }

                // Cek variasi dengan angka arab jika tersimpan sebagai angka
                $arabicKey = strtolower(preg_replace('/[^a-z0-9]/i', '', match ($gradeRoman) {
                    'X' => '10',
                    'XI' => '11',
                    'XII' => '12',
                    default => $gradeRoman
                } . $major . $rombel));
                if (isset($normalizedClasses[$arabicKey])) {
                    return $normalizedClasses[$arabicKey];
                }

                // 3. Jika kelas belum terdaftar di tabel classes, buatkan otomatis
                $formattedClassName = trim($gradeRoman . ' ' . $major . ($rombel !== '' ? ' ' . $rombel : ''));
                $newClass = SchoolClass::create([
                    'name' => $formattedClassName,
                    'grade' => $gradeRoman,
                    'major' => $major,
                ]);

                $normalizedClasses[$targetKey] = $newClass;
                return $newClass;
            }
        }

        return null;
    }

    /**
     * Membersihkan gelar akademis, tanda baca, dan spasi dari nama untuk pencocokan toleran.
     */
    private function normalizePersonName(string $name): string
    {
        $n = strtolower($name);
        $n = preg_replace('/\b(dra|drs|h|hj|s\.?\s*pd(\.?\s*i|\.?\s*kor)?|m\.?\s*pd|s\.?\s*t|s\.?\s*kom|s\.?\s*e|m\.?\s*m|m\.?\s*acc|m\.?\s*s\.?\s*i|ss|s\.?\s*sos)\b/i', '', $n);
        return preg_replace('/[^a-z0-9]/i', '', $n);
    }

    /**
     * Mencocokkan guru berdasarkan nama (mendukung gelar, variasi spasi, tanda baca, dsb).
     */
    private function findTeacherByName(string $rawName, $teachers): ?Teacher
    {
        $rawTrimmed = trim($rawName);
        if ($rawTrimmed === '') {
            return null;
        }

        // 1. Pencocokan langsung (case-insensitive)
        foreach ($teachers as $t) {
            if (strcasecmp(trim($t->name), $rawTrimmed) === 0) {
                return $t;
            }
        }

        // 2. Pencocokan nama bersih (tanpa gelar akademis & tanda baca)
        $cleanTarget = $this->normalizePersonName($rawTrimmed);
        if ($cleanTarget !== '') {
            foreach ($teachers as $t) {
                if ($this->normalizePersonName($t->name) === $cleanTarget) {
                    return $t;
                }
            }
        }

        // 3. Pencocokan kata inti nama (core words overlap)
        $targetWords = preg_split('/[\s,\.]+/', strtolower($rawTrimmed), -1, PREG_SPLIT_NO_EMPTY);
        $filteredWords = array_filter($targetWords, function ($w) {
            return !in_array($w, [
                'dra', 'drs', 'h', 'hj', 'spd', 'spdi', 'spdkor', 'mpd', 'st',
                'skom', 'se', 'mm', 'macc', 'msi', 'ss', 'ssos', 's', 'pd', 'i', 'kor', 'm', 'acc'
            ]);
        });

        if (count($filteredWords) >= 2) {
            foreach ($teachers as $t) {
                $tLower = strtolower($t->name);
                $allWordsFound = true;
                foreach ($filteredWords as $w) {
                    if (stripos($tLower, $w) === false) {
                        $allWordsFound = false;
                        break;
                    }
                }
                if ($allWordsFound) {
                    return $t;
                }
            }
        }

        return null;
    }

    /**
     * Memproses file penugasan Wali Kelas & Guru BK (mencocokkan nama guru tanpa kewajiban NIP).
     */
    private function importHomeroomList(array $allRows, int $headerRowIndex, array $headerMap)
    {
        $dataRows = array_slice($allRows, $headerRowIndex + 1);
        if (empty($dataRows)) {
            return redirect()
                ->route('teachers.index')
                ->with('error', 'Berkas tidak memuat baris data wali kelas yang dapat diproses.');
        }

        $allClasses = SchoolClass::all();
        $normalizedClasses = [];
        foreach ($allClasses as $cls) {
            $k = strtolower(preg_replace('/[^a-z0-9]/i', '', $cls->name));
            $normalizedClasses[$k] = $cls;
            $akAlias = str_replace('akl', 'ak', $k);
            $normalizedClasses[$akAlias] = $cls;
        }

        $allTeachers = Teacher::with('user')->get();
        $assignedCount = 0;
        $newTeachersCount = 0;

        DB::beginTransaction();
        try {
            foreach ($dataRows as $row) {
                // Lewati baris yang kosong
                $hasData = false;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $hasData = true;
                        break;
                    }
                }
                if (!$hasData) {
                    continue;
                }

                $rawClass = trim((string)($row[$headerMap['kelas']] ?? ''));
                $rawWali  = trim((string)($row[$headerMap['wali']] ?? ''));
                $rawBk    = isset($headerMap['bk']) ? trim((string)($row[$headerMap['bk']] ?? '')) : '';

                // Lewati baris footer atau catatan (seperti 'KETERANGAN', 'Ditetapkan di', dll)
                if (empty($rawClass) || empty($rawWali) || stripos($rawClass, 'keterangan') !== false || stripos($rawClass, 'ditetapkan') !== false || stripos($rawClass, 'semester') !== false || stripos($rawClass, 'daftar') !== false) {
                    continue;
                }

                // 1. Selesaikan Kelas Tujuan
                $targetClass = $this->resolveHomeroomClass($rawClass, null, $row, $normalizedClasses);
                if (!$targetClass) {
                    continue;
                }

                // 2. Cari Guru berdasarkan kecocokan Nama (karena file wali kelas tidak memuat NIP)
                $teacher = $this->findTeacherByName($rawWali, $allTeachers);

                if (!$teacher) {
                    // Jika belum terdaftar di database, buatkan akun guru otomatis
                    $baseUsername = strtolower(preg_replace('/[^a-z0-9]/i', '', explode(',', $rawWali)[0]));
                    if (strlen($baseUsername) < 3) {
                        $baseUsername = 'guru_' . time();
                    }
                    $username = $baseUsername;
                    $c = 1;
                    while (User::where('username', $username)->exists()) {
                        $username = $baseUsername . $c++;
                    }

                    $newUser = User::create([
                        'name' => $rawWali,
                        'username' => $username,
                        'role' => 'guru',
                        'password' => Hash::make('password'),
                    ]);

                    // Generate unique placeholder NIP karena kolom NIP wajib diisi
                    $genNip = 'GURU' . date('Ymd') . rand(1000, 9999);
                    while (Teacher::where('nip', $genNip)->exists()) {
                        $genNip = 'GURU' . date('Ymd') . rand(1000, 9999);
                    }

                    $teacher = Teacher::create([
                        'user_id' => $newUser->id,
                        'name' => $rawWali,
                        'nip' => $genNip,
                        'username' => $username,
                    ]);

                    $allTeachers->push($teacher);
                    $newTeachersCount++;
                }

                // 3. Pasangkan Guru ke Kelas Sebagai Wali Kelas
                SchoolClass::where('homeroom_teacher_id', $teacher->id)
                    ->where('id', '!=', $targetClass->id)
                    ->update(['homeroom_teacher_id' => null]);

                $targetClass->update(['homeroom_teacher_id' => $teacher->id]);
                $assignedCount++;

                // 4. Sinkronisasi Guru BK jika tertera di baris tersebut
                if (!empty($rawBk)) {
                    $bkTeacher = $this->findTeacherByName($rawBk, $allTeachers);
                    if ($bkTeacher) {
                        if (empty($bkTeacher->mapel)) {
                            $bkTeacher->update(['mapel' => 'BIMBINGAN KONSELING']);
                            $this->syncSubjectToDatabase('BIMBINGAN KONSELING');
                        }
                        if ($bkTeacher->user && $bkTeacher->user->role !== 'waka') {
                            $bkTeacher->user->update(['role' => 'bk']);
                        }
                        $targetClass->update(['bk_teacher_id' => $bkTeacher->id]);
                    }
                }
            }

            DB::commit();

            $msg = "Sinkronisasi penugasan wali kelas berhasil! {$assignedCount} kelas berhasil ditugaskan ke guru wali kelas berdasarkan kecocokan nama.";
            if ($newTeachersCount > 0) {
                $msg .= " ({$newTeachersCount} data guru baru ditambahkan otomatis).";
            }

            return redirect()
                ->route('teachers.index')
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('teachers.index')
                ->with('error', 'Terjadi kesalahan saat memproses data wali kelas: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form tambah guru.
     */
    public function create()
    {
        return view('teachers.create');
    }
    /**
     * Menyimpan guru baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', 'unique:teachers,nip'],
            'username' => ['nullable', 'string', 'max:50', 'unique:teachers,username', 'unique:users,username'],
            'mapel' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6'],
            'homeroom_class_id' => ['nullable', 'exists:classes,id'],
        ]);
        DB::transaction(function () use ($validated) {
            // Resolusi username
            $username = !empty($validated['username']) ? trim($validated['username']) : null;
            if (empty($username)) {
                $idx = Teacher::count() + 1;
                while (User::where('username', 'guru' . $idx)->exists() || Teacher::where('username', 'guru' . $idx)->exists()) {
                    $idx++;
                }
                $username = 'guru' . $idx;
            }
            $email = !empty($validated['email']) ? trim($validated['email']) : null;
            $plainPassword = !empty($validated['password']) ? $validated['password'] : 'password';
            $user = User::create([
                'name' => $validated['name'],
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'role' => 'guru',
                'phone' => $validated['phone'] ?? null,
            ]);
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'username' => $username,
                'name' => $validated['name'],
                'mapel' => !empty($validated['mapel']) ? trim($validated['mapel']) : null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
            if (!empty($teacher->mapel)) {
                $this->syncSubjectToDatabase($teacher->mapel);
            }
            if (!empty($validated['homeroom_class_id'])) {
                SchoolClass::where('id', $validated['homeroom_class_id'])
                    ->update(['homeroom_teacher_id' => $teacher->id]);
            }
        });
        return redirect()
            ->route('teachers.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }
    /**
     * Menampilkan detail guru.
     */
    public function show(string $id)
    {
        $teacher = Teacher::with(['user', 'homeroomClasses'])->findOrFail($id);
        return view('teachers.show', compact('teacher'));
    }
    /**
     * Menampilkan form edit guru.
     */
    public function edit(string $id)
    {
        $teacher = Teacher::with(['user', 'homeroomClasses'])->findOrFail($id);
        $classes = SchoolClass::orderBy('name')->get();
        return view('teachers.edit', compact('teacher', 'classes'));
    }
    /**
     * Memperbarui data guru.
     */
    public function update(Request $request, string $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('teachers', 'nip')->ignore($teacher->id),
            ],
            'username' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('teachers', 'username')->ignore($teacher->id),
                Rule::unique('users', 'username')->ignore($teacher->user_id),
            ],
            'mapel' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($teacher->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8'],
            'homeroom_class_id' => ['nullable', 'exists:classes,id'],
        ]);
        DB::transaction(function () use ($teacher, $validated) {
            $username = !empty($validated['username']) ? trim($validated['username']) : $teacher->username;
            $email = !empty($validated['email']) ? trim($validated['email']) : null;
            $teacher->update([
                'name' => $validated['name'],
                'nip' => $validated['nip'],
                'username' => $username,
                'mapel' => !empty($validated['mapel']) ? trim($validated['mapel']) : null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
            if (!empty($teacher->mapel)) {
                $this->syncSubjectToDatabase($teacher->mapel);
            }
            if ($teacher->user) {
                $teacher->user->update([
                    'name' => $validated['name'],
                    'username' => $username,
                    'email' => $email,
                    'phone' => $validated['phone'] ?? null,
                ]);
            }
            if (!empty($validated['password'])) {
                $teacher->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }
            // Update penugasan wali kelas
            SchoolClass::where('homeroom_teacher_id', $teacher->id)
                ->update(['homeroom_teacher_id' => null]);
            if (!empty($validated['homeroom_class_id'])) {
                SchoolClass::where('id', $validated['homeroom_class_id'])
                    ->update(['homeroom_teacher_id' => $teacher->id]);
            }
        });
        return redirect()
            ->route('teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }
    /**
     * Menghapus guru.
     */
    public function destroy(string $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $teacher->delete();
            if ($user) {
                $user->delete();
            }
        });
        return redirect()
            ->route('teachers.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
    /**
     * Download format template CSV/Excel untuk import guru.
     */
    /**
     * Menghapus beberapa data guru yang dipilih sekaligus (Bulk Delete).
     */
    public function bulkDestroy(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'waka') {
            abort(403, 'Hanya Waka Kurikulum yang dapat menghapus data guru.');
        }

        $validated = $request->validate([
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['required', 'integer', 'exists:teachers,id'],
        ], [
            'teacher_ids.required' => 'Pilih minimal satu data guru yang ingin dihapus.',
            'teacher_ids.min' => 'Pilih minimal satu data guru yang ingin dihapus.',
        ]);

        $teacherIds = $validated['teacher_ids'];

        DB::transaction(function () use ($teacherIds, $currentUser) {
            $teachers = Teacher::with('user')->whereIn('id', $teacherIds)->get();
            $userIdsToDelete = [];

            foreach ($teachers as $teacher) {
                if ($teacher->user_id && $teacher->user_id != $currentUser->id) {
                    $userIdsToDelete[] = $teacher->user_id;
                }
                $teacher->delete();
            }

            if (!empty($userIdsToDelete)) {
                \App\Models\User::whereIn('id', $userIdsToDelete)
                    ->where('role', 'guru')
                    ->delete();
            }
        });

        $count = count($teacherIds);

        return redirect()
            ->route('teachers.index')
            ->with('success', "{$count} data guru terpilih dan akun pengguna terkait berhasil dihapus.");
    }

    /**
     * Menghapus seluruh data guru beserta akun pengguna terkait (Kecuali akun Waka).
     */
    public function destroyAll()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'waka') {
            abort(403, 'Hanya Waka Kurikulum yang dapat menghapus seluruh data guru.');
        }
        DB::transaction(function () use ($currentUser) {
            $teachers = Teacher::with('user')
                ->where('user_id', '!=', $currentUser->id)
                ->get();
            $userIdsToDelete = [];
            foreach ($teachers as $teacher) {
                if ($teacher->user_id && $teacher->user_id != $currentUser->id) {
                    $userIdsToDelete[] = $teacher->user_id;
                }
                $teacher->delete();
            }
            if (!empty($userIdsToDelete)) {
                \App\Models\User::whereIn('id', $userIdsToDelete)
                    ->where('role', 'guru')
                    ->delete();
            }
        });
        return redirect()
            ->route('teachers.index')
            ->with('success', 'Seluruh data guru berhasil dihapus.');
    }
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_guru.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        // Contoh nama kelas aktual dari database
        $sampleClass = SchoolClass::orderBy('name')->first()?->name ?? 'X RPL 1';
        $callback = function () use ($sampleClass) {
            $output = fopen('php://output', 'w');
            // UTF-8 BOM untuk kompatibilitas langsung di Microsoft Excel Windows
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            // Header kolom
            fputcsv($output, [
                'nip',
                'username',
                'nama',
                'mapel',
                'jabatan',
                'email',
                'password',
                'wali_kelas',
                'no_telepon',
                'alamat',
            ]);
            // Baris contoh 1
            fputcsv($output, [
                '198501152010011002',
                'guru1',
                'Budi Santoso, S.Kom',
                'Pemrograman Web',
                'Guru Pengajar',
                '',
                'password123',
                $sampleClass,
                '081234567890',
                'Jl. Merdeka No. 10, Yogyakarta',
            ]);
            // Baris contoh 2 (bukan wali kelas)
            fputcsv($output, [
                '198703202012012003',
                'Siti Rahayu, S.Pd',
                'siti.rahayu@sekolah.sch.id',
                'password123',
                '',
                '081298765432',
                'Jl. Mawar No. 4, Sleman',
            ]);
            // Baris contoh 3 (tanpa email & password manual, otomatis dibuatkan sistem)
            fputcsv($output, [
                '199008122018011005',
                'Ahmad Fauzi, M.Pd',
                '',
                '',
                '',
                '081345678901',
                'Jl. Sudirman No. 25, Bantul',
            ]);
            fclose($output);
        };
        return response()->stream($callback, 200, $headers);
    }
    /**
     * Import data guru dan akun login dari file Excel / CSV lokal.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ], [
            'file.required' => 'File CSV/Excel wajib dipilih.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);
        $file = $request->file('file');
        $extension = strtolower(trim((string) $file->getClientOriginalExtension()));
        if (empty($extension)) {
            $extension = strtolower(trim((string) $file->extension()));
        }
        $filePath = $file->getRealPath();
        $allRows = \App\Services\DocumentTableExtractor::extract($filePath, $extension, 'teacher');
        if (empty($allRows)) {
            return redirect()
                ->route('teachers.index')
                ->with('error', 'Gagal membaca tabel data dari file yang diunggah. Pastikan file (.xlsx, .xls, .docx, .pdf, atau .csv) memuat baris data yang valid.');
        }
        // Cari baris header secara cerdas di antara 25 baris pertama
        $homeroomHeaderRowIndex = null;
        $homeroomHeaderMap = [];
        $masterHeaderRowIndex = null;
        $masterHeaderMap = [];

        foreach (array_slice($allRows, 0, 25, true) as $rowIndex => $candidateRow) {
            $tempMap = [];
            foreach ($candidateRow as $colIdx => $col) {
                $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$col)));
                $clean = preg_replace('/[^a-z0-9]/', '', $clean);
                if (empty($clean)) {
                    continue;
                }
                if (!isset($tempMap['nip']) && (str_contains($clean, 'nip') || in_array($clean, ['noinduk', 'nomorinduk', 'idguru']))) {
                    $tempMap['nip'] = $colIdx;
                } elseif (!isset($tempMap['username']) && (str_contains($clean, 'username') || in_array($clean, ['user', 'namauser', 'akun', 'login', 'idpengguna', 'kodeguru', 'inisial']))) {
                    $tempMap['username'] = $colIdx;
                } elseif (!isset($tempMap['wali']) && (str_contains($clean, 'namawalikelas') || str_contains($clean, 'walikelas') || str_contains($clean, 'namawali') || str_contains($clean, 'guruwali') || str_contains($clean, 'wali'))) {
                    $tempMap['wali'] = $colIdx;
                } elseif (!isset($tempMap['bk']) && (str_contains($clean, 'gurubk') || str_contains($clean, 'bk') || str_contains($clean, 'konseling'))) {
                    $tempMap['bk'] = $colIdx;
                } elseif (!isset($tempMap['kelas']) && (in_array($clean, ['kelas', 'kls', 'rombel', 'rombelbinaan', 'kelasbinaan']) || str_contains($clean, 'kelas') || str_contains($clean, 'rombel'))) {
                    $tempMap['kelas'] = $colIdx;
                } elseif (!isset($tempMap['name']) && (str_contains($clean, 'nama') || str_contains($clean, 'name') || str_contains($clean, 'guru') || str_contains($clean, 'pengajar'))) {
                    $tempMap['name'] = $colIdx;
                } elseif (!isset($tempMap['mapel']) && (str_contains($clean, 'mapel') || str_contains($clean, 'matapelajaran') || str_contains($clean, 'subject') || str_contains($clean, 'pelajaran') || str_contains($clean, 'bidangstudi') || str_contains($clean, 'pengampu'))) {
                    $tempMap['mapel'] = $colIdx;
                } elseif (!isset($tempMap['homeroom']) && (str_contains($clean, 'wali') || str_contains($clean, 'homeroom') || str_contains($clean, 'kelaswali') || str_contains($clean, 'walikelas') || str_contains($clean, 'binaan') || str_contains($clean, 'tugastambahan') || str_contains($clean, 'jabatantambahan') || str_contains($clean, 'rombelwali'))) {
                    $tempMap['homeroom'] = $colIdx;
                } elseif (!isset($tempMap['position']) && (str_contains($clean, 'jabatan') || str_contains($clean, 'posisi') || str_contains($clean, 'tugas') || str_contains($clean, 'jenisptk') || str_contains($clean, 'ptk') || str_contains($clean, 'profesi') || str_contains($clean, 'status') || str_contains($clean, 'kategori') || str_contains($clean, 'keterangan') || str_contains($clean, 'role'))) {
                    $tempMap['position'] = $colIdx;
                } elseif (!isset($tempMap['email']) && (str_contains($clean, 'mail') || str_contains($clean, 'surel'))) {
                    $tempMap['email'] = $colIdx;
                } elseif (!isset($tempMap['password']) && (str_contains($clean, 'pass') || str_contains($clean, 'sandi'))) {
                    $tempMap['password'] = $colIdx;
                } elseif (!isset($tempMap['phone']) && (str_contains($clean, 'telp') || str_contains($clean, 'telepon') || str_contains($clean, 'phone') || str_contains($clean, 'hp') || str_contains($clean, 'wa'))) {
                    $tempMap['phone'] = $colIdx;
                } elseif (!isset($tempMap['address']) && (str_contains($clean, 'alamat') || str_contains($clean, 'address') || str_contains($clean, 'domisili'))) {
                    $tempMap['address'] = $colIdx;
                }
            }

            // A. Deteksi Header Daftar Wali Kelas (ada kolom kelas dan ada kolom wali/nama)
            if (isset($tempMap['kelas']) && (isset($tempMap['wali']) || isset($tempMap['name']))) {
                $homeroomHeaderRowIndex = $rowIndex;
                $homeroomHeaderMap = $tempMap;
                if (!isset($homeroomHeaderMap['wali']) && isset($homeroomHeaderMap['name'])) {
                    $homeroomHeaderMap['wali'] = $homeroomHeaderMap['name'];
                }
                break;
            }

            // B. Deteksi Header Master Guru (ada NIP dan ada Nama)
            if (isset($tempMap['nip']) && isset($tempMap['name'])) {
                $masterHeaderRowIndex = $rowIndex;
                $masterHeaderMap = $tempMap;
                break;
            }
        }

        // Jika terdeteksi berkas daftar penugasan wali kelas (tidak wajib NIP)
        if ($homeroomHeaderRowIndex !== null) {
            return $this->importHomeroomList($allRows, $homeroomHeaderRowIndex, $homeroomHeaderMap);
        }

        // Jika berkas data guru, pastikan header NIP dan Nama ditemukan
        if ($masterHeaderRowIndex === null) {
            return redirect()
                ->route('teachers.index')
                ->with('error', 'Header kolom wajib tidak ditemukan di dalam berkas. Untuk data guru pastikan terdapat kolom NIP dan Nama Guru, atau untuk daftar wali kelas pastikan terdapat kolom Kelas dan Nama Wali Kelas.');
        }

        $headerRowIndex = $masterHeaderRowIndex;
        $headerMap = $masterHeaderMap;
        $allRows = array_slice($allRows, $headerRowIndex + 1);
        if (empty($allRows)) {
            return redirect()
                ->route('teachers.index')
                ->with('error', 'Berkas tidak memuat baris data guru yang dapat diproses.');
        }
        // Cache semua kelas dengan berbagai variasi penamaan untuk pencocokan cerdas
        $allClasses = SchoolClass::all();
        $normalizedClasses = [];
        foreach ($allClasses as $cls) {
            $k1 = strtolower(preg_replace('/[^a-z0-9]/i', '', $cls->name));
            $normalizedClasses[$k1] = $cls;

            // Alias angka jika nama kelas memakai angka Romawi (X, XI, XII)
            if (str_starts_with($k1, 'xii')) {
                $normalizedClasses['12' . substr($k1, 3)] = $cls;
            } elseif (str_starts_with($k1, 'xi')) {
                $normalizedClasses['11' . substr($k1, 2)] = $cls;
            } elseif (str_starts_with($k1, 'x')) {
                $normalizedClasses['10' . substr($k1, 1)] = $cls;
            }
        }
        $createdCount = 0;
        $updatedCount = 0;
        $assignedHomeroomCount = 0;
        $skippedNonTeacherCount = 0;
        $skippedRows = [];
        $rowNumber = 1;
        // Cache daftar username yang sudah terpakai
        $existingUsernames = User::whereNotNull('username')->pluck('username')->flip()->toArray();
        $existingTeacherUsernames = Teacher::whereNotNull('username')->pluck('username')->flip()->toArray();
        $nextGuruSeq = 1;
        DB::beginTransaction();
        try {
            foreach ($allRows as $row) {
                $rowNumber++;
                // Lewati baris yang kosong atau hanya berisi spasi
                $hasCellData = false;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $hasCellData = true;
                        break;
                    }
                }
                if (!$hasCellData) {
                    continue;
                }
                $nip = isset($headerMap['nip']) ? trim((string)($row[$headerMap['nip']] ?? '')) : '';
                $usernameVal = isset($headerMap['username']) ? trim((string)($row[$headerMap['username']] ?? '')) : '';
                $name = isset($headerMap['name']) ? trim((string)($row[$headerMap['name']] ?? '')) : '';
                $mapelVal = isset($headerMap['mapel']) ? trim((string)($row[$headerMap['mapel']] ?? '')) : null;
                $rawPosition = isset($headerMap['position']) ? trim((string)($row[$headerMap['position']] ?? '')) : '';
                $rawEmail = isset($headerMap['email']) ? trim((string)($row[$headerMap['email']] ?? '')) : '';
                $email = !empty($rawEmail) ? $rawEmail : null;
                $password = isset($headerMap['password']) ? trim((string)($row[$headerMap['password']] ?? '')) : '';
                $homeroomName = isset($headerMap['homeroom']) ? trim((string)($row[$headerMap['homeroom']] ?? '')) : '';
                $phone = isset($headerMap['phone']) ? trim((string)($row[$headerMap['phone']] ?? '')) : null;
                $address = isset($headerMap['address']) ? trim((string)($row[$headerMap['address']] ?? '')) : null;
                // Jika NIP dan Nama keduanya kosong, ini baris kosong / footer / nomor halaman, abaikan secara hening
                if (empty($nip) && empty($name)) {
                    continue;
                }
                // =========================================================================
                // FILTER KETAT: KHUSUS GURU PENGAJAR
                // Abaikan hening: Tata Usaha, Outsourcing/Outsourching, Sheet 1, Satpam, Pegawai, Staf, Kepsek, dll.
                // =========================================================================
                $rowCombinedText = strtolower(implode(' ', array_map(fn($c) => trim((string)$c), $row)));

                // Filter kata kunci non-guru dengan batas kata (word boundaries) agar nama seperti 'Astuti', 'Suryatun', 'Taslim' tidak salah terfilter
                $nonTeacherRegex = '/\b(tata\s+usaha|tu|tas|tendik|staf|staff|outsourch\w*|outsourc\w*|sheet\s*1|satpam|security|keamanan|penjaga|cleaning|kebersihan|caraka|driver|supir|sopir|pegawai|administrasi|laboran|pustakawan|perpustakaan|operator dapodik|operator|kepala\s+sekolah|kepsek|kepala\s+madrasah|wakil\s+kepala|kustodian|sarana\s+prasarana)\b/i';

                $isNonTeacher = false;
                if (preg_match($nonTeacherRegex, $rowCombinedText)) {
                    // Kecualikan jika ada keterangan eksplisit guru/pengajar
                    if (!preg_match('/\b(guru|pengajar|pendidik)\b/i', $rowCombinedText)) {
                        $isNonTeacher = true;
                    }
                }

                if ($isNonTeacher) {
                    $skippedNonTeacherCount++;
                    continue; // Lewati secara hening tanpa menampilkan pesan error/warning mengganggu
                }

                // Cek apakah baris adalah header berulang atau baris ringkasan
                $summaryHeaders = ['no', 'nomor', 'nip', 'nama', 'nama guru', 'rekap', 'jumlah', 'total', 'catatan', 'keterangan', 'mengetahui', 'l', 'p', 'lk', 'pr', 'laki-laki', 'perempuan', 'gender'];
                if (in_array(strtolower($nip), $summaryHeaders) || in_array(strtolower($name), $summaryHeaders)) {
                    continue;
                }

                // Jika Nama guru kosong atau berupa baris footer rekapitulasi gender (seperti 'L' atau 'P'), abaikan baris ini
                $cleanNameCheck = strtoupper(trim($name));
                if (empty($name) || strlen(trim($name)) <= 2 || in_array($cleanNameCheck, ['L', 'P', 'LK', 'PR', 'TOTAL', 'JUMLAH', 'REKAP', 'LAKI-LAKI', 'PEREMPUAN', 'GENDER'])) {
                    continue;
                }

                // Password default jika kosong
                $plainPassword = !empty($password) ? $password : 'password';

                // Email dikosongkan (null) jika tidak diisi di berkas
                if (!empty($email)) {
                    $existingEmailUser = User::where('email', $email)->first();
                    if ($existingEmailUser && (!$teacher || $teacher->user_id !== $existingEmailUser->id)) {
                        $email = null;
                    }
                }

                // Cari guru berdasarkan NIP jika ada, atau Username, atau kecocokan Nama
                $teacher = null;
                if (!empty($nip)) {
                    $teacher = Teacher::with('user')->where('nip', $nip)->first();
                }
                if (!$teacher && !empty($usernameVal)) {
                    $teacher = Teacher::with('user')->where('username', $usernameVal)->first();
                }
                if (!$teacher) {
                    $teacher = $this->findTeacherByName($name, Teacher::with('user')->get());
                }

                // Jika NIP kosong tetapi guru ditemukan, gunakan NIP guru yang sudah ada
                if (empty($nip) && $teacher) {
                    $nip = $teacher->nip;
                }

                // Pastikan NIP tidak pernah berupa string kosong '' agar tidak melanggar UNIQUE constraint di basis data
                if (empty($nip)) {
                    $nip = 'GURU' . date('Ymd') . rand(1000, 9999);
                    while (Teacher::where('nip', $nip)->exists()) {
                        $nip = 'GURU' . date('Ymd') . rand(1000, 9999);
                    }
                }
                // Resolusi Username berurutan (guru1, guru2, dst) jika belum ada
                $username = !empty($usernameVal) ? trim($usernameVal) : '';
                if (empty($username)) {
                    if ($teacher && !empty($teacher->username)) {
                        $username = $teacher->username;
                    } else {
                        while (isset($existingUsernames['guru' . $nextGuruSeq]) || isset($existingTeacherUsernames['guru' . $nextGuruSeq])) {
                            $nextGuruSeq++;
                        }
                        $username = 'guru' . $nextGuruSeq;
                        $nextGuruSeq++;
                    }
                } else {
                    $username = strtolower(preg_replace('/[^a-zA-Z0-9_.-]/', '', $username));
                    $baseUsername = $username;
                    $uCount = 1;
                    while ((isset($existingUsernames[$username]) || isset($existingTeacherUsernames[$username])) && (!$teacher || $teacher->username !== $username)) {
                        $username = $baseUsername . '_' . $uCount;
                        $uCount++;
                    }
                }
                $existingUsernames[$username] = true;
                $existingTeacherUsernames[$username] = true;
                if ($teacher) {
                    // Update data guru yang sudah ada
                    $teacher->update([
                        'name' => $name,
                        'username' => $username,
                        'mapel' => !empty($mapelVal) ? $mapelVal : $teacher->mapel,
                        'phone' => $phone ?: $teacher->phone,
                        'address' => $address ?: $teacher->address,
                    ]);
                    if (!empty($teacher->mapel)) {
                        $this->syncSubjectToDatabase($teacher->mapel);
                    }
                    if ($teacher->user) {
                        $userUpdate = [
                            'name' => $name,
                            'username' => $username,
                        ];
                        if (!empty($password)) {
                            $userUpdate['password'] = Hash::make($plainPassword);
                        }
                        if ($phone) {
                            $userUpdate['phone'] = $phone;
                        }
                        $teacher->user->update($userUpdate);
                    }
                    $currentTeacherId = $teacher->id;
                    $updatedCount++;
                } else {
                    // Buat Akun Guru Baru
                    $user = User::create([
                        'name' => $name,
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make($plainPassword),
                        'role' => 'guru',
                        'phone' => $phone,
                    ]);
                    $newTeacher = Teacher::create([
                        'user_id' => $user->id,
                        'nip' => $nip,
                        'username' => $username,
                        'name' => $name,
                        'mapel' => !empty($mapelVal) ? $mapelVal : null,
                        'phone' => $phone,
                        'address' => $address,
                    ]);
                    if (!empty($newTeacher->mapel)) {
                        $this->syncSubjectToDatabase($newTeacher->mapel);
                    }
                    $currentTeacherId = $newTeacher->id;
                    $createdCount++;
                }
                // =========================================================================
                // PENUGASAN WALI KELAS OTOMATIS DARI FILE IMPORT
                // Sistem membaca kelas binaan dari kolom wali kelas / tugas tambahan / isi baris
                // =========================================================================
                $targetClass = $this->resolveHomeroomClass($homeroomName, $rawPosition, $row, $normalizedClasses);
                if ($targetClass) {
                    // Lepas penugasan lama jika guru ini sebelumnya tercatat di kelas lain
                    SchoolClass::where('homeroom_teacher_id', $currentTeacherId)
                        ->where('id', '!=', $targetClass->id)
                        ->update(['homeroom_teacher_id' => null]);

                    // Berikan penugasan kelas ini ke guru yang bersangkutan
                    $targetClass->update(['homeroom_teacher_id' => $currentTeacherId]);
                    $assignedHomeroomCount++;
                }
            }
            DB::commit();
            $msg = "Import data guru berhasil. {$createdCount} guru baru dan akun login berhasil ditambahkan";
            if ($updatedCount > 0) {
                $msg .= ", {$updatedCount} data guru diperbarui";
            }
            if ($assignedHomeroomCount > 0) {
                $msg .= ", {$assignedHomeroomCount} guru berhasil ditugaskan sebagai wali kelas";
            }
            $msg .= ".";
            if (!empty($skippedRows)) {
                $warningMsg = " Catatan: " . count($skippedRows) . " catatan: " . implode('; ', array_slice($skippedRows, 0, 4));
                if (count($skippedRows) > 4) {
                    $warningMsg .= " (dan " . (count($skippedRows) - 4) . " lainnya)";
                }
                return redirect()->route('teachers.index')->with('success', $msg)->with('warning', $warningMsg);
            }
            return redirect()->route('teachers.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('teachers.index')
                ->with('error', 'Terjadi kesalahan saat memproses data import: ' . $e->getMessage());
        }
    }
}