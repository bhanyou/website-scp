<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            $requests = PermissionRequest::with([
                'student',
                'parent',
            ])
                ->where('student_id', $student->id)
                ->latest()
                ->get();

            return view('permission-requests.index', compact(
                'requests',
                'student'
            ));
        }

        $parent = SchoolParent::where('user_id', $user->id)->first();

        if (!$parent) {
            abort(403, 'Akun tidak terhubung dengan data siswa atau orang tua.');
        }

        $studentIds = $parent->students()
            ->pluck('id');

        $requests = PermissionRequest::with([
            'student',
            'parent',
        ])
            ->whereIn('student_id', $studentIds)
            ->latest()
            ->get();

        return view('permission-requests.index', compact(
            'requests'
        ));
    }

    public function create()
    {
        $user = Auth::user();

        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            return view('permission-requests.create', compact(
                'student'
            ));
        }

        $parent = SchoolParent::where('user_id', $user->id)->first();

        if (!$parent) {
            abort(403, 'Akun tidak terhubung dengan data siswa atau orang tua.');
        }

        $students = $parent->students()
            ->orderBy('name')
            ->get();

        return view('permission-requests.create', compact(
            'students'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $student = Student::where('user_id', $user->id)->first();

        $parent = SchoolParent::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
            ],
            'permission_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:permission_date',
            ],
            'time_scope' => [
                'nullable',
                'in:full_day,partial_day',
            ],
            'start_hour' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],
            'end_hour' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
                'gte:start_hour',
            ],
            'type' => [
                'required',
                'in:Izin,Sakit',
            ],
            'reason' => [
                'required',
                'string',
            ],
            'document' => [
                'nullable',
                'file',
            ],
        ], [
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai izin.',
            'end_hour.gte' => 'Jam selesai tidak boleh lebih awal dari jam mulai izin.',
        ]);

        /*
         * Siswa hanya boleh mengajukan untuk dirinya sendiri.
         */
        if ($student) {
            if ((int) $validated['student_id'] !== (int) $student->id) {
                abort(403, 'Siswa tidak dapat mengajukan izin untuk siswa lain.');
            }

            $parentId = null;
        } else {
            /*
             * Orang tua hanya boleh mengajukan untuk anak
             * yang terhubung pada akun orang tua tersebut.
             */
            if (!$parent) {
                abort(403, 'Akun tidak terhubung dengan data orang tua.');
            }

            $isChild = $parent->students()
                ->where('students.id', $validated['student_id'])
                ->exists();

            if (!$isChild) {
                abort(403, 'Orang tua tidak dapat mengajukan izin untuk siswa lain.');
            }

            $parentId = $parent->id;
        }

        /*
         * Dokumen wajib sesuai alur UKK.
         * Format file belum dibatasi di sini karena dokumen UKK
         * yang kita gunakan tidak mencantumkan daftar ekstensi
         * maupun batas ukuran secara spesifik.
         */
        $documentPath = $request->hasFile('document')
            ? $request->file('document')->store('permission-requests', 'public')
            : null;

        $timeScope = $validated['time_scope'] ?? 'full_day';
        $startHour = $timeScope === 'partial_day' ? ($validated['start_hour'] ?? null) : null;
        $endHour = $timeScope === 'partial_day' ? ($validated['end_hour'] ?? null) : null;
        $startTime = null;
        $endTime = null;

        if ($timeScope === 'partial_day' && $startHour && $endHour) {
            $endDate = $validated['permission_date'];
            $startPeriod = \App\Models\LessonHour::where('period_number', $startHour)->first();
            $endPeriod = \App\Models\LessonHour::where('period_number', $endHour)->first();

            $startTime = $startPeriod ? $startPeriod->start_time : null;
            $endTime = $endPeriod ? $endPeriod->end_time : null;
        } else {
            $endDate = $validated['end_date'] ?? $validated['permission_date'];
        }

        PermissionRequest::create([
            'student_id' => $validated['student_id'],
            'parent_id' => $parentId,
            'permission_date' => $validated['permission_date'],
            'end_date' => $endDate,
            'time_scope' => $timeScope,
            'start_hour' => $startHour,
            'end_hour' => $endHour,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'document_path' => $documentPath,
            'status' => 'Pending',
        ]);

        return redirect()
            ->route('permission-requests.index')
            ->with(
                'success',
                'Pengajuan izin berhasil dikirim dan menunggu verifikasi.'
            );
    }

    public function show(string $id)
    {
        $user = Auth::user();

        $permissionRequest = PermissionRequest::with([
            'student',
            'parent',
        ])->findOrFail($id);

        // Jika akun Waka Kurikulum, izinkan melihat seluruh detail pengajuan dan verifikasi
        if ($user->role === 'waka') {
            $canVerify = true;
            return view('permission-requests.show', compact('permissionRequest', 'canVerify'));
        }

        // Jika akun Guru BK / Kesiswaan
        if (in_array($user->role, ['bk', 'kesiswaan'], true) || ($user->teacher && $user->teacher->isBkTeacher())) {
            $teacher = $user->teacher;
            $managedClassIds = $teacher ? $teacher->bkClasses->pluck('id') : collect();
            if ($teacher && $teacher->homeroomClasses->isNotEmpty()) {
                $managedClassIds = $managedClassIds->merge($teacher->homeroomClasses->pluck('id'))->unique();
            }
            $isMyClassStudent = $permissionRequest->student && $managedClassIds->contains($permissionRequest->student->class_id);

            if ($isMyClassStudent) {
                $canVerify = true; // Kesiswaan / BK dapat memverifikasi permohonan izin untuk kelas binaannya
                return view('permission-requests.show', compact('permissionRequest', 'canVerify'));
            }

            abort(403, 'Akses Ditolak: Bukan kelas binaan/ampuan Anda.');
        }

        // Jika akun Guru / Wali Kelas
        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            $homeroomClassIds = $teacher
                ? $teacher->homeroomClasses->pluck('id')
                : collect();

            $isMyClassStudent = $permissionRequest->student && $homeroomClassIds->contains($permissionRequest->student->class_id);

            if ($isMyClassStudent) {
                $canVerify = true;
                return view('permission-requests.show', compact('permissionRequest', 'canVerify'));
            }

            abort(403, 'Anda hanya dapat melihat permohonan izin siswa di kelas binaan Anda.');
        }

        // Cek apakah akun adalah siswa
        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            // Siswa hanya boleh melihat pengajuannya sendiri
            if ((int) $permissionRequest->student_id !== (int) $student->id) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
            }

            $canVerify = false;
            return view(
                'permission-requests.show',
                compact('permissionRequest', 'canVerify')
            );
        }

        // Cek apakah akun adalah orang tua
        $parent = SchoolParent::where('user_id', $user->id)->first();

        if (!$parent) {
            abort(403, 'Akun tidak terhubung dengan data siswa atau orang tua.');
        }

        // Orang tua hanya boleh melihat pengajuan anaknya
        $isChild = $parent->students()
            ->where('students.id', $permissionRequest->student_id)
            ->exists();

        if (!$isChild) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $canVerify = false;
        return view(
            'permission-requests.show',
            compact('permissionRequest', 'canVerify')
        );
    }

    /**
     * Menampilkan monitoring seluruh pengajuan izin siswa untuk Waka Kurikulum dan Guru BK.
     */
    public function verificationIndex(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $isBkOrKesiswaan = in_array($user->role, ['bk', 'kesiswaan'], true) || ($teacher && $teacher->isBkTeacher());

        if (!in_array($user->role, ['waka'], true) && !$isBkOrKesiswaan) {
            abort(403, 'Hanya Waka Kurikulum atau Guru BK / Kesiswaan yang memiliki akses ke halaman monitoring izin ini.');
        }

        $selectedGrade = $request->query('grade');
        $selectedMajor = $request->query('major');
        $selectedStatus = $request->query('status');
        $selectedType = $request->query('type');
        $search = $request->query('search');

        $query = PermissionRequest::with([
            'student.schoolClass',
            'parent',
            'verifiedBy',
        ]);

        // Scope kelas binaan khusus Guru BK / Kesiswaan (bukan waka)
        if ($user->role !== 'waka') {
            $managedClassIds = collect();
            if ($teacher) {
                if ($teacher->bkClasses->isNotEmpty()) {
                    $managedClassIds = $managedClassIds->merge($teacher->bkClasses->pluck('id'));
                }
                if ($teacher->homeroomClasses->isNotEmpty()) {
                    $managedClassIds = $managedClassIds->merge($teacher->homeroomClasses->pluck('id'));
                }
            }

            if ($managedClassIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('student', function ($s) use ($managedClassIds) {
                    $s->whereIn('class_id', $managedClassIds);
                });
            }
        }

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        if ($selectedType) {
            $query->where('type', $selectedType);
        }

        if ($selectedMajor) {
            $query->whereHas('student.schoolClass', function ($q) use ($selectedMajor) {
                $q->where('major', $selectedMajor);
            });
        }

        if ($selectedGrade) {
            $query->whereHas('student.schoolClass', function ($q) use ($selectedGrade) {
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

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest('permission_date')
            ->latest('id')
            ->get();

        $majors = \App\Models\SchoolClass::whereNotNull('major')
            ->where('major', '!=', '')
            ->distinct()
            ->orderBy('major')
            ->pluck('major');

        return view('permission-requests.verification', compact(
            'requests',
            'majors',
            'selectedGrade',
            'selectedMajor',
            'selectedStatus',
            'selectedType',
            'search'
        ));
    }

    /**
     * Menyetujui permohonan izin siswa (Wali Kelas, Kesiswaan / Guru BK, atau Waka Kurikulum).
     */
    public function approve(string $id)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $isBkOrKesiswaan = in_array($user->role, ['bk', 'kesiswaan'], true) || ($teacher && $teacher->isBkTeacher());

        if (!in_array($user->role, ['guru', 'waka', 'bk', 'kesiswaan'], true) && !$isBkOrKesiswaan) {
            abort(403, 'Anda tidak memiliki hak untuk memverifikasi izin siswa.');
        }

        $permissionRequest = PermissionRequest::with('student')->findOrFail($id);

        if ($user->role !== 'waka') {
            $allowedClassIds = collect();
            if ($teacher) {
                if ($teacher->homeroomClasses->isNotEmpty()) {
                    $allowedClassIds = $allowedClassIds->merge($teacher->homeroomClasses->pluck('id'));
                }
                if ($teacher->bkClasses->isNotEmpty()) {
                    $allowedClassIds = $allowedClassIds->merge($teacher->bkClasses->pluck('id'));
                }
            }

            if (!$permissionRequest->student || !$allowedClassIds->contains($permissionRequest->student->class_id)) {
                abort(403, 'Anda hanya dapat memverifikasi permohonan izin untuk siswa di kelas binaan/ampuan Anda.');
            }
        }

        $fallbackRoute = ($user->role === 'waka' || $isBkOrKesiswaan)
            ? route('permission-requests.verification')
            : route('homeroom.permissions.index');

        if ($permissionRequest->status !== 'Pending') {
            return redirect()
                ->back(fallback: $fallbackRoute)
                ->with('error', 'Pengajuan ini sudah diverifikasi sebelumnya.');
        }

        $permissionRequest->update([
            'status' => 'Approved',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_note' => null,
        ]);

        $statusType = $permissionRequest->type === 'Sakit' ? 'Sakit' : 'Izin';
        $student = $permissionRequest->student;
        $startDate = Carbon::parse($permissionRequest->permission_date)->startOfDay();
        $endDate = Carbon::parse($permissionRequest->end_date ?? $permissionRequest->permission_date)->startOfDay();

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        // Loop untuk setiap hari dalam rentang tanggal izin yang disetujui
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $permDate = $currentDate->toDateString();
            $dayName = $days[$currentDate->dayOfWeek] ?? null;

            if ($permissionRequest->isPartialDay()) {
                // Izin memotong jam pelajaran (hanya untuk jadwal yang beririsan dengan rentang jam izin)
                $schedules = ($student && $student->class_id && $dayName)
                    ? Schedule::where('class_id', $student->class_id)->where('day', $dayName)->get()
                    : collect();

                foreach ($schedules as $sched) {
                    if ($permissionRequest->appliesToSchedule($sched)) {
                        Attendance::updateOrCreate(
                            [
                                'student_id'  => $student->id,
                                'schedule_id' => $sched->id,
                                'date'        => $permDate,
                            ],
                            [
                                'status'   => $statusType,
                                'check_in' => null,
                                'note'     => "Izin Jam ke-{$permissionRequest->start_hour} - {$permissionRequest->end_hour}: {$permissionRequest->reason}",
                            ]
                        );
                    }
                }
            } else {
                // 1. Update presensi yang sudah ada pada tanggal tersebut
                $updatedCount = Attendance::where('student_id', $permissionRequest->student_id)
                    ->whereDate('date', $permDate)
                    ->update(['status' => $statusType]);

                // 2. Jika belum ada catatan presensi pada tanggal tersebut, buatkan presensi otomatis
                if ($updatedCount === 0 && $student) {
                    $schedules = ($student->class_id && $dayName)
                        ? Schedule::where('class_id', $student->class_id)->where('day', $dayName)->get()
                        : collect();

                    if ($schedules->isNotEmpty()) {
                        foreach ($schedules as $sched) {
                            Attendance::updateOrCreate(
                                [
                                    'student_id'  => $student->id,
                                    'schedule_id' => $sched->id,
                                    'date'        => $permDate,
                                ],
                                [
                                    'status'   => $statusType,
                                    'check_in' => null,
                                ]
                            );
                        }
                    } else {
                        Attendance::updateOrCreate(
                            [
                                'student_id'  => $student->id,
                                'schedule_id' => null,
                                'date'        => $permDate,
                            ],
                            [
                                'status'   => $statusType,
                                'check_in' => null,
                            ]
                        );
                    }
                }
            }

            $currentDate->addDay();
        }

        return redirect()
            ->back(fallback: $fallbackRoute)
            ->with('success', 'Permohonan izin siswa ' . ($student ? $student->name : '') . ' berhasil disetujui.');
    }

    /**
     * Menolak permohonan izin siswa (Wali Kelas, Kesiswaan / Guru BK, atau Waka Kurikulum).
     */
    public function reject(Request $request, string $id)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $isBkOrKesiswaan = in_array($user->role, ['bk', 'kesiswaan'], true) || ($teacher && $teacher->isBkTeacher());

        if (!in_array($user->role, ['guru', 'waka', 'bk', 'kesiswaan'], true) && !$isBkOrKesiswaan) {
            abort(403, 'Anda tidak memiliki hak untuk menolak izin siswa.');
        }

        $permissionRequest = PermissionRequest::with('student')->findOrFail($id);

        if ($user->role !== 'waka') {
            $allowedClassIds = collect();
            if ($teacher) {
                if ($teacher->homeroomClasses->isNotEmpty()) {
                    $allowedClassIds = $allowedClassIds->merge($teacher->homeroomClasses->pluck('id'));
                }
                if ($teacher->bkClasses->isNotEmpty()) {
                    $allowedClassIds = $allowedClassIds->merge($teacher->bkClasses->pluck('id'));
                }
            }

            if (!$permissionRequest->student || !$allowedClassIds->contains($permissionRequest->student->class_id)) {
                abort(403, 'Anda hanya dapat menolak permohonan izin untuk siswa di kelas binaan/ampuan Anda.');
            }
        }

        $fallbackRoute = ($user->role === 'waka' || $isBkOrKesiswaan)
            ? route('permission-requests.verification')
            : route('homeroom.permissions.index');

        if ($permissionRequest->status !== 'Pending') {
            return redirect()
                ->back(fallback: $fallbackRoute)
                ->with('error', 'Pengajuan ini sudah diverifikasi sebelumnya.');
        }

        $validated = $request->validate([
            'verification_note' => ['nullable', 'string'],
        ]);

        $permissionRequest->update([
            'status' => 'Rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_note' => $validated['verification_note'] ?? null,
        ]);

        // Karena izin ditolak oleh sekolah, ketidakhadiran siswa pada rentang hari tersebut dicatat sebagai ALPA
        $student = $permissionRequest->student;
        $startDate = Carbon::parse($permissionRequest->permission_date)->startOfDay();
        $endDate = Carbon::parse($permissionRequest->end_date ?? $permissionRequest->permission_date)->startOfDay();

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $permDate = $currentDate->toDateString();
            $dayName = $days[$currentDate->dayOfWeek] ?? null;

            if ($permissionRequest->isPartialDay()) {
                $schedules = ($student && $student->class_id && $dayName)
                    ? Schedule::where('class_id', $student->class_id)->where('day', $dayName)->get()
                    : collect();

                foreach ($schedules as $sched) {
                    if ($permissionRequest->appliesToSchedule($sched)) {
                        Attendance::updateOrCreate(
                            [
                                'student_id'  => $student->id,
                                'schedule_id' => $sched->id,
                                'date'        => $permDate,
                            ],
                            [
                                'status'   => 'Alpa',
                                'check_in' => null,
                                'note'     => "Ditolak - Alpa Jam ke-{$permissionRequest->start_hour} - {$permissionRequest->end_hour}",
                            ]
                        );
                    }
                }
            } else {
                $updatedCount = Attendance::where('student_id', $permissionRequest->student_id)
                    ->whereDate('date', $permDate)
                    ->update(['status' => 'Alpa']);

                if ($updatedCount === 0 && $student) {
                    $schedules = ($student->class_id && $dayName)
                        ? Schedule::where('class_id', $student->class_id)->where('day', $dayName)->get()
                        : collect();

                    if ($schedules->isNotEmpty()) {
                        foreach ($schedules as $sched) {
                            Attendance::updateOrCreate(
                                [
                                    'student_id'  => $student->id,
                                    'schedule_id' => $sched->id,
                                    'date'        => $permDate,
                                ],
                                [
                                    'status'   => 'Alpa',
                                    'check_in' => null,
                                ]
                            );
                        }
                    } else {
                        Attendance::updateOrCreate(
                            [
                                'student_id'  => $student->id,
                                'schedule_id' => null,
                                'date'        => $permDate,
                            ],
                            [
                                'status'   => 'Alpa',
                                'check_in' => null,
                            ]
                        );
                    }
                }
            }

            $currentDate->addDay();
        }

        return redirect()
            ->back(fallback: $fallbackRoute)
            ->with('success', 'Pengajuan izin siswa ' . ($student ? $student->name : '') . ' ditolak dan ketidakhadiran dicatat sebagai Alpa.');
    }

        /**
     * Menampilkan daftar izin siswa kelas binaan untuk Wali Kelas.
     */
    public function homeroomIndex()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher || $teacher->homeroomClasses->isEmpty()) {
            if (in_array($user->role, ['bk', 'kesiswaan'], true) || ($teacher && $teacher->isBkTeacher())) {
                return redirect()->route('permission-requests.verification');
            }
            abort(403, 'Anda belum ditugaskan sebagai wali kelas.');
        }

        $classes = $teacher->homeroomClasses;
        $classIds = $classes->pluck('id');

        $requests = PermissionRequest::with(['student.schoolClass', 'parent'])
            ->whereHas('student', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds);
            })
            ->latest('permission_date')
            ->latest('id')
            ->get();

        return view('homeroom.permissions', compact('requests', 'classes'));
    }

}