<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\PermissionRequest;
use App\Models\Schedule;
use App\Models\SchoolParent;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PermissionRequestApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Cek apakah akun adalah siswa atau orang tua
        $student = Student::with('schoolClass')
            ->where('user_id', $user->id)
            ->first();

        if (!$student && $user->parent) {
            $student = $user->parent->students()->with('schoolClass')->first();
        }

        if ($student) {
            $requests = PermissionRequest::with([
                'student.schoolClass',
                'parent',
            ])
                ->where('student_id', $student->id)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pengajuan izin/sakit berhasil diambil.',
                'student' => [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'class' => $student->schoolClass?->name,
                ],
                'data' => $requests->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'siswa' => $req->student?->name,
                        'nis' => $req->student?->nis,
                        'kelas' => $req->student?->schoolClass?->name,
                        'tanggal' => $req->permission_date,
                        'tanggal_selesai' => $req->end_date ?? $req->permission_date,
                        'durasi_hari' => $req->duration_days,
                        'rentang_tanggal' => $req->date_range_label,
                        'jenis' => $req->type,
                        'alasan' => $req->reason,
                        'dokumen' => $req->document_path,
                        'status' => $req->status,
                        'diverifikasi_oleh' => $req->verified_by,
                        'waktu_verifikasi' => $req->verified_at,
                        'catatan_verifikasi' => $req->verification_note,
                    ];
                }),
            ]);
        }

        // Cek apakah akun adalah Guru (Wali Kelas)
        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            $homeroomClasses = $teacher ? $teacher->homeroomClasses : collect();
            $homeroomClassIds = $homeroomClasses->pluck('id');

            if ($homeroomClassIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum ditugaskan sebagai wali kelas.',
                    'data' => [],
                ], 403);
            }

            $requests = PermissionRequest::with([
                'student.schoolClass',
                'parent',
            ])
                ->whereHas('student', function ($q) use ($homeroomClassIds) {
                    $q->whereIn('class_id', $homeroomClassIds);
                })
                ->latest('permission_date')
                ->latest('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pengajuan izin siswa kelas binaan berhasil diambil.',
                'classes' => $homeroomClasses->pluck('name'),
                'data' => $requests->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'siswa' => $req->student?->name,
                        'nis' => $req->student?->nis,
                        'kelas' => $req->student?->schoolClass?->name,
                        'tanggal' => $req->permission_date,
                        'tanggal_selesai' => $req->end_date ?? $req->permission_date,
                        'durasi_hari' => $req->duration_days,
                        'rentang_tanggal' => $req->date_range_label,
                        'jenis' => $req->type,
                        'alasan' => $req->reason,
                        'dokumen' => $req->document_path,
                        'status' => $req->status,
                        'diverifikasi_oleh' => $req->verified_by,
                        'waktu_verifikasi' => $req->verified_at,
                        'catatan_verifikasi' => $req->verification_note,
                    ];
                }),
            ]);
        }

        // Cek apakah akun adalah Waka Kurikulum atau Guru BK
        $teacher = $user->teacher;
        $isBk = $user->role === 'bk' || ($teacher && $teacher->isBkTeacher());
        if ($user->role === 'waka' || $isBk) {
            $query = PermissionRequest::with([
                'student.schoolClass',
                'parent',
            ]);

            if ($isBk && $user->role !== 'waka') {
                $bkClassIds = $teacher ? $teacher->bkClasses()->pluck('id') : collect();
                $query->whereHas('student', fn($q) => $q->whereIn('class_id', $bkClassIds));
            }

            $requests = $query->latest('permission_date')
                ->latest('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Semua data pengajuan izin siswa berhasil diambil.',
                'data' => $requests->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'siswa' => $req->student?->name,
                        'nis' => $req->student?->nis,
                        'kelas' => $req->student?->schoolClass?->name,
                        'tanggal' => $req->permission_date,
                        'tanggal_selesai' => $req->end_date ?? $req->permission_date,
                        'durasi_hari' => $req->duration_days,
                        'rentang_tanggal' => $req->date_range_label,
                        'jenis' => $req->type,
                        'alasan' => $req->reason,
                        'dokumen' => $req->document_path,
                        'status' => $req->status,
                        'diverifikasi_oleh' => $req->verified_by,
                        'waktu_verifikasi' => $req->verified_at,
                        'catatan_verifikasi' => $req->verification_note,
                    ];
                }),
            ]);
        }

        // Cek apakah akun adalah orang tua
        $parent = SchoolParent::where('user_id', $user->id)->first();

        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak memiliki akses ke pengajuan izin.',
            ], 403);
        }

        $students = $parent->students()
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        $studentIds = $students->pluck('id');

        $requests = PermissionRequest::with([
            'student.schoolClass',
            'parent',
        ])
            ->whereIn('student_id', $studentIds)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data pengajuan izin/sakit anak berhasil diambil.',
            'students' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'class' => $student->schoolClass?->name,
                ];
            }),
            'data' => $requests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'siswa' => $req->student?->name,
                    'nis' => $req->student?->nis,
                    'kelas' => $req->student?->schoolClass?->name,
                    'tanggal' => $req->permission_date,
                    'tanggal_selesai' => $req->end_date ?? $req->permission_date,
                    'durasi_hari' => $req->duration_days,
                    'rentang_tanggal' => $req->date_range_label,
                    'jenis' => $req->type,
                    'alasan' => $req->reason,
                    'dokumen' => $req->document_path,
                    'status' => $req->status,
                    'diverifikasi_oleh' => $req->verified_by,
                    'waktu_verifikasi' => $req->verified_at,
                    'catatan_verifikasi' => $req->verification_note,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'permission_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:permission_date'],
            'type' => ['required', 'in:Izin,Sakit'],
            'reason' => ['required', 'string'],
            'document' => ['required', 'file'],
        ], [
            'end_date.required' => 'Tanggal selesai izin wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai izin.',
        ]);

        // Tentukan apakah yang mengajukan adalah siswa atau orang tua
        $student = Student::where('user_id', $user->id)->first();
        $parent = SchoolParent::where('user_id', $user->id)->first();

        if ($student) {
            // Siswa hanya boleh mengajukan untuk dirinya sendiri
            if ((int) $validated['student_id'] !== (int) $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa hanya dapat mengajukan izin/sakit untuk dirinya sendiri.',
                ], 403);
            }

            $parentId = $student->parent_id;
        } elseif ($parent) {
            // Orang tua hanya boleh mengajukan untuk anaknya
            $isChild = $parent->students()
                ->where('students.id', $validated['student_id'])
                ->exists();

            if (!$isChild) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orang tua hanya dapat mengajukan izin/sakit untuk anaknya.',
                ], 403);
            }

            $parentId = $parent->id;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data siswa atau orang tua.',
            ], 403);
        }

        // Simpan dokumen
        $documentPath = $request->file('document')
            ->store('permission-requests', 'public');

        // Simpan pengajuan dengan status awal Pending
        $permissionRequest = PermissionRequest::create([
            'student_id' => $validated['student_id'],
            'parent_id' => $parentId,
            'permission_date' => $validated['permission_date'],
            'end_date' => $validated['end_date'] ?? $validated['permission_date'],
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'document_path' => $documentPath,
            'status' => 'Pending',
        ]);

        $permissionRequest->load([
            'student',
            'parent',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin/sakit berhasil dibuat.',
            'data' => [
                'id' => $permissionRequest->id,
                'student_id' => $permissionRequest->student_id,
                'student' => $permissionRequest->student?->name,
                'tanggal' => $permissionRequest->permission_date,
                'tanggal_selesai' => $permissionRequest->end_date,
                'durasi_hari' => $permissionRequest->duration_days,
                'rentang_tanggal' => $permissionRequest->date_range_label,
                'jenis' => $permissionRequest->type,
                'alasan' => $permissionRequest->reason,
                'dokumen' => $permissionRequest->document_path,
                'status' => $permissionRequest->status,
            ],
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $permissionRequest = PermissionRequest::with([
            'student.schoolClass',
            'parent',
        ])->findOrFail($id);

        if ($user->role === 'waka') {
            // Waka Kurikulum memiliki akses penuh
        } elseif ($user->role === 'bk' || ($user->teacher && $user->teacher->isBkTeacher())) {
            $teacher = $user->teacher;
            $managedClassIds = $teacher ? $teacher->bkClasses->pluck('id') : collect();
            if (!$permissionRequest->student || !$managedClassIds->contains($permissionRequest->student->class_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak: Bukan kelas binaan Anda.',
                ], 403);
            }
        } elseif ($user->role === 'guru') {
            $teacher = $user->teacher;
            $homeroomClassIds = $teacher ? $teacher->homeroomClasses->pluck('id') : collect();
            if (!$permissionRequest->student || !$homeroomClassIds->contains($permissionRequest->student->class_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat melihat permohonan izin siswa di kelas binaan Anda.',
                ], 403);
            }
        } else {
            // Cek apakah akun adalah siswa
            $student = Student::where('user_id', $user->id)->first();

            if ($student) {
                if ((int) $permissionRequest->student_id !== (int) $student->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kamu tidak memiliki akses ke pengajuan ini.',
                    ], 403);
                }
            } else {
                // Cek apakah akun adalah orang tua
                $parent = SchoolParent::where('user_id', $user->id)->first();

                if (!$parent) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun tidak memiliki akses ke pengajuan ini.',
                    ], 403);
                }

                $isChild = $parent->students()
                    ->where('students.id', $permissionRequest->student_id)
                    ->exists();

                if (!$isChild) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kamu tidak memiliki akses ke pengajuan ini.',
                    ], 403);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pengajuan izin/sakit berhasil diambil.',
            'data' => [
                'id' => $permissionRequest->id,
                'student_id' => $permissionRequest->student_id,
                'student' => $permissionRequest->student?->name,
                'nis' => $permissionRequest->student?->nis,
                'kelas' => $permissionRequest->student?->schoolClass?->name,
                'tanggal' => $permissionRequest->permission_date,
                'tanggal_selesai' => $permissionRequest->end_date ?? $permissionRequest->permission_date,
                'durasi_hari' => $permissionRequest->duration_days,
                'rentang_tanggal' => $permissionRequest->date_range_label,
                'jenis' => $permissionRequest->type,
                'alasan' => $permissionRequest->reason,
                'dokumen' => $permissionRequest->document_path,
                'status' => $permissionRequest->status,
                'diverifikasi_oleh' => $permissionRequest->verified_by,
                'waktu_verifikasi' => $permissionRequest->verified_at,
                'catatan_verifikasi' => $permissionRequest->verification_note,
            ],
        ]);
    }

    /**
     * Menyetujui pengajuan izin/sakit.
     * Hanya akun Waka Kurikulum atau Wali Kelas siswa yang bersangkutan.
     */
    public function approve(Request $request, string $id)
    {
        $user = $request->user();

        $permissionRequest = PermissionRequest::with('student')->find($id);

        if (!$permissionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan izin/sakit tidak ditemukan.',
            ], 404);
        }

        // Cek Otorisasi: Waka Kurikulum atau Guru Wali Kelas
        $isAuthorized = false;
        if ($user->role === 'waka') {
            $isAuthorized = true;
        } elseif ($user->role === 'guru') {
            $teacher = $user->teacher;
            $homeroomClassIds = $teacher ? $teacher->homeroomClasses->pluck('id') : collect();
            if ($permissionRequest->student && $homeroomClassIds->contains($permissionRequest->student->class_id)) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Waka Kurikulum atau Wali Kelas siswa yang bersangkutan yang dapat menyetujui pengajuan.',
            ], 403);
        }

        if ($permissionRequest->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ini sudah diverifikasi.',
            ], 422);
        }

        $permissionRequest->update([
            'status' => 'Approved',
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verification_note' => null,
        ]);

        // Sinkronkan ke presensi siswa untuk seluruh rentang tanggal izin
        $student = $permissionRequest->student;
        $statusType = $permissionRequest->type === 'Sakit' ? 'Sakit' : 'Izin';
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

            $updatedCount = Attendance::where('student_id', $permissionRequest->student_id)
                ->whereDate('date', $permDate)
                ->update(['status' => $statusType]);

            if ($updatedCount === 0 && $student) {
                $dayName = $days[$currentDate->dayOfWeek] ?? null;

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

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin/sakit berhasil disetujui dan data presensi telah disinkronkan.',
            'data' => [
                'id' => $permissionRequest->id,
                'student_id' => $permissionRequest->student_id,
                'tanggal' => $permissionRequest->permission_date,
                'tanggal_selesai' => $permissionRequest->end_date ?? $permissionRequest->permission_date,
                'durasi_hari' => $permissionRequest->duration_days,
                'rentang_tanggal' => $permissionRequest->date_range_label,
                'jenis' => $permissionRequest->type,
                'status' => $permissionRequest->status,
                'diverifikasi_oleh' => $permissionRequest->verified_by,
                'waktu_verifikasi' => $permissionRequest->verified_at,
                'catatan_verifikasi' => $permissionRequest->verification_note,
            ],
        ]);
    }

    public function reject(Request $request, string $id)
    {
        $user = $request->user();

        $validated = $request->validate([
            'verification_note' => ['nullable', 'string'],
        ]);

        $permissionRequest = PermissionRequest::with('student')->find($id);

        if (!$permissionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan izin/sakit tidak ditemukan.',
            ], 404);
        }

        // Cek Otorisasi: Waka Kurikulum atau Guru Wali Kelas
        $isAuthorized = false;
        if ($user->role === 'waka') {
            $isAuthorized = true;
        } elseif ($user->role === 'guru') {
            $teacher = $user->teacher;
            $homeroomClassIds = $teacher ? $teacher->homeroomClasses->pluck('id') : collect();
            if ($permissionRequest->student && $homeroomClassIds->contains($permissionRequest->student->class_id)) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Waka Kurikulum atau Wali Kelas siswa yang bersangkutan yang dapat menolak pengajuan.',
            ], 403);
        }

        if ($permissionRequest->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ini sudah diverifikasi.',
            ], 422);
        }

        $permissionRequest->update([
            'status' => 'Rejected',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'verification_note' => $validated['verification_note'] ?? null,
        ]);

        // Sinkronkan ke presensi sebagai Alpa untuk seluruh rentang tanggal
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

            $updatedCount = Attendance::where('student_id', $permissionRequest->student_id)
                ->whereDate('date', $permDate)
                ->update(['status' => 'Alpa']);

            if ($updatedCount === 0 && $student) {
                $dayName = $days[$currentDate->dayOfWeek] ?? null;

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

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin/sakit berhasil ditolak dan status ketidakhadiran siswa dicatat sebagai Alpa.',
            'data' => [
                'id' => $permissionRequest->id,
                'student_id' => $permissionRequest->student_id,
                'tanggal' => $permissionRequest->permission_date,
                'tanggal_selesai' => $permissionRequest->end_date ?? $permissionRequest->permission_date,
                'durasi_hari' => $permissionRequest->duration_days,
                'rentang_tanggal' => $permissionRequest->date_range_label,
                'jenis' => $permissionRequest->type,
                'status' => $permissionRequest->status,
                'diverifikasi_oleh' => $permissionRequest->verified_by,
                'waktu_verifikasi' => $permissionRequest->verified_at,
                'catatan_verifikasi' => $permissionRequest->verification_note,
            ],
        ]);
    }
}