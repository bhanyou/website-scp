<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Journal;
use App\Models\PermissionRequest;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JournalApiController extends Controller
{
    /**
     * GET /api/journals
     * Mengambil jadwal mengajar milik Guru pada siklus yang aktif.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Guru belum memiliki data guru.',
            ], 403);
        }

        $today = \Carbon\Carbon::today();
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $todayName = $days[$today->dayOfWeek];

        $dayOrder = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 7,
        ];
        $todayDayIndex = $dayOrder[$todayName] ?? 0;

        $schedules = Schedule::with([
            'blockCycle',
            'subject',
            'schoolClass.students',
            'room',
        ])
            ->where('teacher_id', $teacher->id)
            ->whereHas('blockCycle', function ($query) {
                $query->where('is_active', true);
            })
            ->orderByRaw("
                CASE day
                    WHEN 'Senin' THEN 1
                    WHEN 'Selasa' THEN 2
                    WHEN 'Rabu' THEN 3
                    WHEN 'Kamis' THEN 4
                    WHEN 'Jumat' THEN 5
                    WHEN 'Sabtu' THEN 6
                    ELSE 7
                END
            ")
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal mengajar untuk jurnal berhasil diambil.',
            'data' => $schedules->map(function ($schedule) use ($today, $todayName, $todayDayIndex, $dayOrder) {
                $journal = Journal::where('schedule_id', $schedule->id)
                    ->latest('journal_date')
                    ->latest('id')
                    ->first();

                $schedDayOrder = $dayOrder[$schedule->day] ?? 0;
                $isToday = ($schedule->day === $todayName);
                $isPassed = ($schedDayOrder < $todayDayIndex);
                $hasJournal = $journal ? true : false;
                $canFill = !$hasJournal && $isToday;

                $statusLabel = 'Belum Dibuka';
                if ($hasJournal) {
                    $statusLabel = 'Sudah Diisi';
                } elseif ($isToday) {
                    $statusLabel = 'Buka Kelas';
                } elseif ($isPassed) {
                    $statusLabel = 'Terlewat';
                } else {
                    $statusLabel = 'Jadwal ' . $schedule->day;
                }

                return [
                    'id' => $schedule->id,
                    'hari' => $schedule->day,
                    'jam_mulai' => $schedule->start_time,
                    'jam_selesai' => $schedule->end_time,
                    'mata_pelajaran' => $schedule->subject?->name,
                    'kelas' => $schedule->schoolClass?->name,
                    'ruang' => $schedule->room?->name,
                    'siklus' => $schedule->blockCycle?->cycle,
                    'can_fill' => $canFill,
                    'has_journal' => $hasJournal,
                    'journal_id' => $journal?->id,
                    'is_today' => $isToday,
                    'is_passed' => $isPassed,
                    'status_label' => $statusLabel,
                    'students' => $schedule->schoolClass?->students->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->name,
                            'nis' => $student->nisn ?? $student->nis ?? '',
                        ];
                    })->values() ?? [],
                ];
            })->values(),
        ]);
    }

    /**
     * GET /api/journals/history
     * Mengambil riwayat jurnal yang pernah diisi oleh guru.
     */
    public function history(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Guru belum memiliki data guru.',
            ], 403);
        }

        $journals = Journal::with([
            'schedule.subject',
            'schedule.schoolClass',
            'schedule.room',
            'schedule.blockCycle',
        ])
            ->where('teacher_id', $teacher->id)
            ->latest('journal_date')
            ->latest('submitted_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat jurnal berhasil diambil.',
            'data' => $journals->map(function ($j) {
                return [
                    'id' => $j->id,
                    'schedule_id' => $j->schedule_id,
                    'tanggal' => $j->journal_date ? $j->journal_date->format('Y-m-d') : null,
                    'materi' => $j->material,
                    'aktivitas' => $j->activity,
                    'catatan' => $j->notes,
                    'foto' => $j->photo_path,
                    'waktu_submit' => $j->submitted_at ? $j->submitted_at->toDateTimeString() : null,
                    'mata_pelajaran' => $j->schedule?->subject?->name ?? 'Mata Pelajaran',
                    'kelas' => $j->schedule?->schoolClass?->name ?? '-',
                    'ruang' => $j->schedule?->room?->name ?? '-',
                    'hari' => $j->schedule?->day ?? '-',
                    'jam_mulai' => $j->schedule?->start_time ?? '-',
                    'jam_selesai' => $j->schedule?->end_time ?? '-',
                    'siklus' => $j->schedule?->blockCycle?->cycle,
                ];
            })->values(),
        ]);
    }

    /**
     * POST /api/journals
     * Menyimpan jurnal dan presensi siswa.
     */
    public function store(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Guru belum memiliki data guru.',
            ], 403);
        }

        $validated = $request->validate([
            'schedule_id' => [
                'required',
                'exists:schedules,id',
            ],
            'journal_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'material' => [
                'required',
                'string',
            ],
            'activity' => [
                'required',
                'string',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
            ],
            'attendance' => [
                'required',
                'array',
            ],
            'attendance.*' => [
                'required',
                'in:Hadir,Sakit,Izin,Alpa',
            ],
        ]);

        // Pastikan jadwal milik Guru yang sedang login.
        $schedule = Schedule::with([
            'schoolClass',
            'blockCycle',
        ])
            ->where('teacher_id', $teacher->id)
            ->find($validated['schedule_id']);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan atau bukan milik Guru yang sedang login.',
            ], 404);
        }

        // Jurnal hanya dapat dibuat untuk siklus yang sedang aktif.
        if (!$schedule->blockCycle || !$schedule->blockCycle->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal hanya dapat dibuat untuk jadwal pada siklus yang sedang aktif.',
            ], 403);
        }

        $today = \Carbon\Carbon::today();
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $todayName = $days[$today->dayOfWeek];

        // Validasi: Pengisian jurnal hanya dapat dilakukan pada hari jadwal mengajar berlangsung
        if ($schedule->day !== $todayName) {
            return response()->json([
                'success' => false,
                'message' => "Pengisian jurnal untuk jadwal hari {$schedule->day} hanya dapat dilakukan pada hari tersebut. Hari ini adalah hari {$todayName}.",
            ], 422);
        }

        // Jika sudah ada jurnal yang diisi untuk jadwal ini hari ini
        $existingJournalToday = Journal::where('schedule_id', $schedule->id)
            ->whereDate('journal_date', $today)
            ->first();

        if ($existingJournalToday) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengisi jurnal untuk jadwal ini hari ini.',
                'journal_id' => $existingJournalToday->id,
            ], 422);
        }

        if (!$schedule->schoolClass) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas pada jadwal tidak ditemukan.',
            ], 404);
        }

        // Mengambil siswa dari kelas pada jadwal.
        $students = $schedule->schoolClass
            ->students()
            ->get();

        $studentIds = $students
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        // Pastikan ID presensi berasal dari kelas yang benar.
        foreach (array_keys($validated['attendance']) as $studentId) {
            if (!in_array((string) $studentId, $studentIds, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data presensi memuat siswa yang bukan anggota kelas pada jadwal ini.',
                ], 422);
            }
        }

        // Mencegah jurnal ganda pada jadwal dan tanggal yang sama.
        $existingJournal = Journal::where('schedule_id', $schedule->id)
            ->where('journal_date', $validated['journal_date'])
            ->exists();

        if ($existingJournal) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal untuk jadwal ini pada tanggal tersebut sudah dibuat.',
            ], 422);
        }

        $photoPath = null;

        try {
            // Simpan foto pada disk public.
            $photoPath = $request->file('photo')->store('journals', 'public');

            if (!$photoPath) {
                throw new \RuntimeException('Foto jurnal gagal disimpan.');
            }

            // Simpan jurnal dan presensi dalam satu transaksi.
            $journal = DB::transaction(function () use (
                $validated,
                $schedule,
                $students,
                $photoPath
            ) {
                $journal = Journal::create([
                    'schedule_id' => $schedule->id,
                    'teacher_id' => $schedule->teacher_id,
                    'journal_date' => $validated['journal_date'],
                    'material' => $validated['material'],
                    'activity' => $validated['activity'],
                    'notes' => $validated['notes'] ?? null,
                    'photo_path' => $photoPath,
                    'submitted_at' => now(),
                ]);

                foreach ($students as $student) {
                    // Status default mengikuti controller web saat ini.
                    $status = $validated['attendance'][$student->id] ?? 'Alpa';

                    // Cek pengajuan izin/sakit yang sudah Approved.
                    $approvedPermission = PermissionRequest::where('student_id', $student->id)
                        ->where('permission_date', $validated['journal_date'])
                        ->where('status', 'Approved')
                        ->latest('id')
                        ->first();

                    if ($approvedPermission) {
                        $status = $approvedPermission->type === 'Sakit'
                            ? 'Sakit'
                            : 'Izin';
                    }

                    Attendance::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'schedule_id' => $schedule->id,
                            'date' => $validated['journal_date'],
                        ],
                        [
                            'status' => $status,
                            'check_in' => null,
                            'note' => null,
                        ]
                    );
                }

                return $journal;
            });

            return response()->json([
                'success' => true,
                'message' => 'Jurnal dan presensi berhasil disimpan.',
                'data' => [
                    'journal_id' => $journal->id,
                    'schedule_id' => $journal->schedule_id,
                    'teacher_id' => $journal->teacher_id,
                    'journal_date' => $journal->journal_date,
                    'material' => $journal->material,
                    'activity' => $journal->activity,
                    'notes' => $journal->notes,
                    'photo_path' => $journal->photo_path,
                ],
            ], 201);
        } catch (\Throwable $e) {
            // Bersihkan foto jika penyimpanan database gagal.
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Jurnal dan presensi gagal disimpan.',
            ], 500);
        }
    }

    /**
     * GET /api/journals/{id}
     * Mengambil detail jurnal dan presensi.
     */
    public function show(Request $request, string $id)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Guru belum memiliki data guru.',
            ], 403);
        }

        // Guru hanya dapat melihat jurnal miliknya.
        $journal = Journal::with([
            'schedule.blockCycle',
            'schedule.subject',
            'schedule.schoolClass',
            'schedule.room',
            'teacher',
        ])
            ->where('teacher_id', $teacher->id)
            ->find($id);

        if (!$journal) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal tidak ditemukan.',
            ], 404);
        }

        $attendances = Attendance::with('student')
            ->where('schedule_id', $journal->schedule_id)
            ->whereDate('date', $journal->journal_date)
            ->orderBy('student_id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Detail jurnal berhasil diambil.',
            'data' => [
                'id' => $journal->id,
                'schedule_id' => $journal->schedule_id,
                'teacher_id' => $journal->teacher_id,
                'journal_date' => $journal->journal_date,
                'material' => $journal->material,
                'activity' => $journal->activity,
                'notes' => $journal->notes,
                'photo_path' => $journal->photo_path,
                'submitted_at' => $journal->submitted_at,
                'schedule' => [
                    'hari' => $journal->schedule?->day,
                    'jam_mulai' => $journal->schedule?->start_time,
                    'jam_selesai' => $journal->schedule?->end_time,
                    'mata_pelajaran' => $journal->schedule?->subject?->name,
                    'kelas' => $journal->schedule?->schoolClass?->name,
                    'ruang' => $journal->schedule?->room?->name,
                    'siklus' => $journal->schedule?->blockCycle?->cycle,
                ],
                'presensi' => $attendances->map(function ($attendance) {
                    return [
                        'student_id' => $attendance->student_id,
                        'nama_siswa' => $attendance->student?->name,
                        'nis' => $attendance->student?->nis,
                        'status' => $attendance->status,
                        'check_in' => $attendance->check_in,
                        'note' => $attendance->note,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * PUT/PATCH /api/journals/{id}
     * Memperbarui jurnal dan presensi siswa.
     */
    public function update(Request $request, string $id)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Guru belum memiliki data guru.',
            ], 403);
        }

        $journal = Journal::where('teacher_id', $teacher->id)->find($id);

        if (!$journal) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'material' => [
                'required',
                'string',
            ],
            'activity' => [
                'required',
                'string',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
            ],
            'attendance' => [
                'required',
                'array',
            ],
            'attendance.*' => [
                'required',
                'in:Hadir,Sakit,Izin,Alpa',
            ],
        ]);

        $schedule = Schedule::with(['schoolClass', 'blockCycle'])
            ->find($journal->schedule_id);

        if (!$schedule || !$schedule->schoolClass) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal atau kelas pada jurnal tidak ditemukan.',
            ], 404);
        }

        // Pembaruan jurnal hanya diperbolehkan pada siklus yang sedang aktif.
        if (!$schedule->blockCycle || !$schedule->blockCycle->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal hanya dapat diperbarui untuk jadwal pada siklus yang sedang aktif.',
            ], 403);
        }

        $studentIds = $schedule->schoolClass
            ->students()
            ->get()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        foreach (array_keys($validated['attendance']) as $studentId) {
            if (!in_array((string) $studentId, $studentIds, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data presensi memuat siswa yang bukan anggota kelas pada jadwal ini.',
                ], 422);
            }
        }

        $oldPhotoPath = $journal->photo_path;
        $newPhotoPath = null;

        try {
            if ($request->hasFile('photo')) {
                $newPhotoPath = $request->file('photo')->store('journals', 'public');

                if (!$newPhotoPath) {
                    throw new \RuntimeException('Foto jurnal gagal disimpan.');
                }
            }

            $photoPath = $newPhotoPath ?? $oldPhotoPath;

            DB::transaction(function () use ($journal, $validated, $photoPath) {
                $journal->update([
                    'material' => $validated['material'],
                    'activity' => $validated['activity'],
                    'notes' => $validated['notes'] ?? null,
                    'photo_path' => $photoPath,
                    'submitted_at' => now(),
                ]);

                foreach ($validated['attendance'] as $studentId => $status) {
                    $approvedPermission = PermissionRequest::where('student_id', $studentId)
                        ->where('permission_date', $journal->journal_date)
                        ->where('status', 'Approved')
                        ->latest('id')
                        ->first();

                    if ($approvedPermission) {
                        $status = $approvedPermission->type === 'Sakit'
                            ? 'Sakit'
                            : 'Izin';
                    }

                    Attendance::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'schedule_id' => $journal->schedule_id,
                            'date' => $journal->journal_date,
                        ],
                        [
                            'status' => $status,
                            'check_in' => null,
                            'note' => null,
                        ]
                    );
                }
            });

            // Hapus foto lama jika foto berhasil diganti.
            if ($newPhotoPath && $oldPhotoPath) {
                Storage::disk('public')->delete($oldPhotoPath);
            }

            $journal->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Jurnal dan presensi berhasil diperbarui.',
                'data' => [
                    'journal_id' => $journal->id,
                    'schedule_id' => $journal->schedule_id,
                    'teacher_id' => $journal->teacher_id,
                    'journal_date' => $journal->journal_date,
                    'material' => $journal->material,
                    'activity' => $journal->activity,
                    'notes' => $journal->notes,
                    'photo_path' => $journal->photo_path,
                    'submitted_at' => $journal->submitted_at,
                ],
            ]);
        } catch (\Throwable $e) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Jurnal dan presensi gagal diperbarui.',
            ], 500);
        }
    }

    /**
     * DELETE /api/journals/{id}
     * Menghapus jurnal, presensi terkait, dan foto.
     */
    public function destroy(Request $request, string $id)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Guru belum memiliki data guru.',
            ], 403);
        }

        $journal = Journal::where('teacher_id', $teacher->id)->find($id);

        if (!$journal) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal tidak ditemukan.',
            ], 404);
        }

        // Ambil siklus jadwal sebelum menghapus jurnal.
        $schedule = Schedule::with('blockCycle')->find($journal->schedule_id);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal pada jurnal tidak ditemukan.',
            ], 404);
        }

        // Jurnal hanya dapat dihapus jika jadwalnya berada pada siklus aktif.
        if (!$schedule->blockCycle || !$schedule->blockCycle->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal hanya dapat dihapus untuk jadwal pada siklus yang sedang aktif.',
            ], 403);
        }

        $photoPath = $journal->photo_path;

        try {
            DB::transaction(function () use ($journal) {
                Attendance::where('schedule_id', $journal->schedule_id)
                    ->whereDate('date', $journal->journal_date)
                    ->delete();

                $journal->delete();
            });

            // Hapus foto setelah penghapusan database berhasil.
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Jurnal dan presensi berhasil dihapus.',
                'data' => [
                    'journal_id' => (int) $id,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Jurnal dan presensi gagal dihapus.',
            ], 500);
        }
    }
}