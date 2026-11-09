<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BlockCycle;
use App\Models\Journal;
use App\Models\PermissionRequest;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class JournalController extends Controller
{
    /**
     * Menampilkan jadwal mengajar milik Guru pada siklus aktif untuk pengisian jurnal hari ini.
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Akun Guru belum memiliki data guru.');
        }

        $today = Carbon::today();
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

        $activeCycle = BlockCycle::where('is_active', true)->first();
        $activeCycleNumber = (int) ($activeCycle?->cycle ?? 1);

        $schedules = Schedule::with([
            'blockCycle',
            'subject',
            'schoolClass',
            'room',
        ])
            ->where('teacher_id', $teacher->id)
            ->where(function ($query) use ($activeCycle, $activeCycleNumber) {
                if ($activeCycle) {
                    $query->where('block_cycle_id', $activeCycle->id)
                          ->orWhere('block_cycle', $activeCycleNumber);
                } else {
                    $query->where('block_cycle', 1);
                }
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

        // Riwayat Jurnal Mengajar Khusus Hari Ini (Read-Only & Hilang/Bersih di Hari Esoknya)
        $todayJournals = Journal::with(['schedule.subject', 'schedule.schoolClass', 'schedule.room', 'schedule.blockCycle'])
            ->where('teacher_id', $teacher->id)
            ->whereDate('journal_date', $today)
            ->latest('submitted_at')
            ->latest('id')
            ->get();

        // ID jadwal yang sudah diisi jurnal hari ini
        $filledScheduleIds = $todayJournals->pluck('schedule_id')->toArray();

        // Jadwal Mengajar Hari Ini yang BELUM diisi jurnal (Antrean Tugas)
        $todayPendingSchedules = $schedules->filter(function ($sshedule) use ($todayName, $filledScheduleIds) {
            return $sshedule->day === $todayName && !in_array($sshedule->id, $filledScheduleIds);
        });

        return view('journals.index', compact(
            'schedules',
            'todayPendingSchedules',
            'todayJournals',
            'todayName',
            'todayDayIndex',
            'dayOrder',
            'today'
        ));
    }

    /**
     * Menampilkan formulir jurnal untuk jadwal pada siklus aktif.
     */
    public function create(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Akun Guru belum memiliki data guru.');
        }

        $scheduleId = $request->query('schedule_id');

        if (!$scheduleId) {
            return redirect()
                ->route('journals.index')
                ->with('error', 'Silakan pilih jadwal terlebih dahulu.');
        }

        $schedule = Schedule::with([
            'subject',
            'schoolClass.students',
            'room',
            'blockCycle',
        ])
            ->where('teacher_id', $teacher->id)
            ->findOrFail($scheduleId);

        if (!$schedule->blockCycle || !$schedule->blockCycle->is_active) {
            abort(403, 'Jurnal hanya dapat dibuat untuk jadwal pada siklus yang sedang aktif.');
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
            return redirect()
                ->route('journals.index')
                ->with('error', "Pengisian jurnal untuk jadwal hari {$schedule->day} hanya dapat dilakukan pada hari tersebut. Hari ini adalah hari {$todayName}.");
        }

        // Jika sudah ada jurnal yang diisi untuk jadwal ini hari ini, arahkan ke detail jurnal
        $existingJournal = Journal::where('schedule_id', $schedule->id)
            ->whereDate('journal_date', $today)
            ->first();

        if ($existingJournal) {
            return redirect()
                ->route('journals.show', $existingJournal->id)
                ->with('info', 'Anda sudah mengisi jurnal untuk jadwal ini hari ini.');
        }

        $students = $schedule->schoolClass->students()
            ->orderBy('name')
            ->get();

        $todayDate = now()->toDateString();
        $approvedPermissions = PermissionRequest::whereIn('student_id', $students->pluck('id'))
            ->where('status', 'Approved')
            ->whereDate('permission_date', '<=', $todayDate)
            ->where(function ($q) use ($todayDate) {
                $q->whereDate('end_date', '>=', $todayDate)
                  ->orWhereNull('end_date');
            })
            ->get()
            ->keyBy('student_id');

        return view('journals.create', compact('schedule', 'students', 'approvedPermissions'));
    }

    /**
     * Menyimpan jurnal dan presensi siswa.
     */
    public function store(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Akun Guru belum memiliki data guru.');
        }

        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'journal_date' => ['required', 'date'],
            'material' => ['required', 'string'],
            'activity' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'in:Hadir,Sakit,Izin,Alpa'],
        ]);

        $schedule = Schedule::with(['schoolClass', 'blockCycle'])
            ->where('teacher_id', $teacher->id)
            ->findOrFail($validated['schedule_id']);

        if (!$schedule->blockCycle || !$schedule->blockCycle->is_active) {
            abort(403, 'Jurnal hanya dapat dibuat untuk jadwal pada siklus yang sedang aktif.');
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
            return redirect()
                ->route('journals.index')
                ->with('error', "Pengisian jurnal untuk jadwal hari {$schedule->day} hanya dapat dilakukan pada hari tersebut. Hari ini adalah hari {$todayName}.");
        }

        // Jika sudah ada jurnal yang diisi untuk jadwal ini hari ini, arahkan ke detail jurnal
        $existingJournal = Journal::where('schedule_id', $schedule->id)
            ->whereDate('journal_date', $today)
            ->first();

        if ($existingJournal) {
            return redirect()
                ->route('journals.show', $existingJournal->id)
                ->with('info', 'Anda sudah mengisi jurnal untuk jadwal ini hari ini.');
        }

        if (!$schedule->schoolClass) {
            abort(404, 'Kelas pada jadwal tidak ditemukan.');
        }

        $students = $schedule->schoolClass->students()->get();
        $studentIds = $students->pluck('id')->map(fn ($id) => (string) $id)->all();

        foreach (array_keys($validated['attendance']) as $studentId) {
            if (!in_array((string) $studentId, $studentIds, true)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'attendance' => 'Data presensi memuat siswa yang bukan anggota kelas pada jadwal ini.',
                    ]);
            }
        }

        $existingJournal = Journal::where('schedule_id', $schedule->id)
            ->whereDate('journal_date', $validated['journal_date'])
            ->exists();

        if ($existingJournal) {
            return back()
                ->withInput()
                ->withErrors([
                    'journal' => 'Jurnal untuk jadwal ini pada tanggal tersebut sudah dibuat.',
                ]);
        }

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('journals', 'public')
            : null;

        try {
            DB::transaction(function () use ($validated, $schedule, $students, $photoPath) {
                Journal::create([
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
                    $status = $validated['attendance'][$student->id] ?? 'Alpa';

                    $approvedPermission = PermissionRequest::where('student_id', $student->id)
                        ->where('status', 'Approved')
                        ->whereDate('permission_date', '<=', $validated['journal_date'])
                        ->where(function ($q) use ($validated) {
                            $q->whereDate('end_date', '>=', $validated['journal_date'])
                              ->orWhereNull('end_date');
                        })
                        ->latest('id')
                        ->first();

                    if ($approvedPermission) {
                        $status = $approvedPermission->type === 'Sakit' ? 'Sakit' : 'Izin';
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
            });
        } catch (\Throwable $e) {
            if ($photoPath) { Storage::disk('public')->delete($photoPath); }
            report($e);

            return back()
                ->withInput()
                ->withErrors(['journal' => 'Jurnal dan presensi gagal disimpan.']);
        }

        return redirect()
            ->route('journals.index')
            ->with('success', 'Jurnal dan presensi berhasil disimpan.');
    }

    /**
     * Menampilkan detail jurnal. Riwayat jurnal dari siklus sebelumnya tetap dapat dilihat.
     */
    public function show(string $id)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Akun Guru belum memiliki data guru.');
        }

        $journal = Journal::with([
            'schedule.subject',
            'schedule.schoolClass',
            'schedule.room',
            'schedule.blockCycle',
            'teacher',
        ])
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);

        $attendances = Attendance::with('student')
            ->where('schedule_id', $journal->schedule_id)
            ->whereDate('date', $journal->journal_date)
            ->get();

        return view('journals.show', compact('journal', 'attendances'));
    }


    /**
     * Menampilkan formulir edit hanya untuk jurnal pada siklus aktif.
     */
    public function edit(string $id)
    {
        return redirect()->route('journals.show', $id)->with('info', 'Jurnal bersifat Read-Only dan tidak dapat diubah kembali.');
    }

    /**
     * Memperbarui jurnal dan presensi siswa.
     */
    public function update(Request $request, string $id)
    {
        return redirect()->route('journals.show', $id)->with('error', 'Jurnal bersifat Read-Only dan tidak dapat diubah.');
    }

    /**
     * Menghapus jurnal dan presensi terkait hanya jika siklusnya aktif.
     */
    public function destroy(string $id)
    {
        return redirect()->route('journals.index')->with('error', 'Jurnal bersifat Read-Only dan tidak dapat dihapus.');
    }

    /**
     * Menampilkan daftar monitoring seluruh jadwal dan pengisian jurnal guru harian untuk Waka Kurikulum.
     */
    public function wakaIndex(Request $request)
    {
        $today = \Carbon\Carbon::today();
        $todayDayName = ucfirst(\Carbon\Carbon::now()->locale('id')->isoFormat('dddd'));
        $todayDateFormatted = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y');

        $selectedGrade = $request->query('grade');
        $selectedMajor = $request->query('major');
        $selectedTeacherId = $request->query('teacher_id');
        $selectedStatus = $request->query('status', 'all');
        $search = $request->query('search');

        // Query jadwal hari ini pada siklus blok aktif
        $query = Schedule::with([
            'teacher',
            'subject',
            'schoolClass',
            'room',
            'blockCycle',
            'journals' => function ($q) use ($today) {
                $q->whereDate('journal_date', $today);
            }
        ])
        ->where('day', $todayDayName)
        ->whereHas('blockCycle', function ($q) {
            $q->where('is_active', true);
        });

        // Filter Guru Pengajar
        if ($selectedTeacherId) {
            $query->where('teacher_id', $selectedTeacherId);
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

        // Filter Jurusan
        if ($selectedMajor) {
            $query->whereHas('schoolClass', function ($q) use ($selectedMajor) {
                $q->where('major', $selectedMajor);
            });
        }

        // Filter Status Pengisian Jurnal (sudah / belum)
        if ($selectedStatus === 'sudah') {
            $query->whereHas('journals', function ($q) use ($today) {
                $q->whereDate('journal_date', $today);
            });
        } elseif ($selectedStatus === 'belum') {
            $query->whereDoesntHave('journals', function ($q) use ($today) {
                $q->whereDate('journal_date', $today);
            });
        }

        // Pencarian Cepat (Guru, Mapel, Kelas, Ruang)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher', fn($t) => $t->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subject', fn($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('schoolClass', fn($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('room', fn($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        // Statistik Hari Ini (overview keseluruhan jadwal hari aktif)
        $baseTodaySchedules = Schedule::where('day', $todayDayName)
            ->whereHas('blockCycle', fn($q) => $q->where('is_active', true));

        $totalSchedulesToday = (clone $baseTodaySchedules)->count();

        $filledScheduleIds = Journal::whereDate('journal_date', $today)
            ->whereIn('schedule_id', (clone $baseTodaySchedules)->select('id'))
            ->pluck('schedule_id')
            ->unique();

        $totalFilledToday = $filledScheduleIds->count();
        $totalPendingToday = max(0, $totalSchedulesToday - $totalFilledToday);
        $completionPercentage = $totalSchedulesToday > 0 
            ? round(($totalFilledToday / $totalSchedulesToday) * 100) 
            : 0;

        $schedules = $query->orderBy('start_time')
            ->orderBy('class_id')
            ->get();

        $teachers = \App\Models\Teacher::orderBy('name')->get();

        $majors = \App\Models\SchoolClass::whereNotNull('major')
            ->where('major', '!=', '')
            ->distinct()
            ->orderBy('major')
            ->pluck('major');

        return view('waka.journals.index', compact(
            'schedules',
            'teachers',
            'majors',
            'selectedGrade',
            'selectedMajor',
            'selectedTeacherId',
            'selectedStatus',
            'search',
            'todayDayName',
            'todayDateFormatted',
            'totalSchedulesToday',
            'totalFilledToday',
            'totalPendingToday',
            'completionPercentage'
        ));
    }

    /**
     * Menampilkan detail jurnal dan daftar presensi siswa untuk Waka Kurikulum.
     */
    public function wakaShow(string $id)
    {
        $journal = Journal::with([
            'teacher',
            'schedule.subject',
            'schedule.schoolClass',
            'schedule.room',
            'schedule.blockCycle',
        ])->findOrFail($id);

        $attendances = Attendance::with('student')
            ->where('schedule_id', $journal->schedule_id)
            ->whereDate('date', $journal->journal_date)
            ->get();

        return view('waka.journals.show', compact('journal', 'attendances'));
    }

    /**
     * Lembar cetak laporan resmi rekapitulasi jurnal mengajar untuk Waka Kurikulum (PDF / Print).
     */
    public function wakaPrint(Request $request)
    {
        $selectedGrade = $request->query('grade');
        $selectedMajor = $request->query('major');
        $selectedRombel = $request->query('rombel');
        $date = $request->query('date');
        $period = $request->query('period', 'all');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $teacherId = $request->query('teacher_id');

        $query = Journal::with([
            'teacher',
            'schedule.subject',
            'schedule.schoolClass',
            'schedule.room',
            'schedule.blockCycle',
        ]);

        $today = now()->toDateString();

        switch ($period) {
            case 'today':
                $query->whereDate('journal_date', $today);
                $startDate = $today;
                $endDate = $today;
                break;
            case '7days':
                $startDate = now()->subDays(6)->toDateString();
                $endDate = $today;
                $query->whereBetween('journal_date', [$startDate, $endDate]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('journal_date', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->whereDate('journal_date', '>=', $startDate);
                } elseif ($endDate) {
                    $query->whereDate('journal_date', '<=', $endDate);
                }
                break;
            default:
                if ($request->filled('date')) {
                    $query->whereDate('journal_date', $date);
                }
                break;
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($selectedGrade) {
            $query->whereHas('schedule.schoolClass', function ($q) use ($selectedGrade) {
                $grades = match (strtoupper($selectedGrade)) {
                    '10', 'X' => ['10', 'X'],
                    '11', 'XI' => ['11', 'XI'],
                    '12', 'XII' => ['12', 'XII'],
                    default => [$selectedGrade],
                };
                $q->where(function ($sub) use ($grades, $selectedGrade) {
                    $sub->whereIn('grade', $grades)->orWhere('name', 'like', $selectedGrade . ' %');
                });
            });
        }

        if ($selectedMajor) {
            $query->whereHas('schedule.schoolClass', function ($q) use ($selectedMajor) {
                $q->where('major', $selectedMajor);
            });
        }

        if ($selectedRombel) {
            $query->whereHas('schedule.schoolClass', function ($q) use ($selectedRombel) {
                $q->where('name', 'like', '% ' . $selectedRombel)->orWhere('name', 'like', '%' . $selectedRombel);
            });
        }

        $journals = $query->oldest('journal_date')->oldest('schedule_id')->get();

        // Hitung kehadiran per jurnal
        $journalIds = $journals->pluck('id');
        $scheduleIds = $journals->pluck('schedule_id')->unique();
        $allAttendances = Attendance::whereIn('schedule_id', $scheduleIds)->get();

        $journalsWithStats = $journals->map(function ($j) use ($allAttendances) {
            $atts = $allAttendances->where('schedule_id', $j->schedule_id)->where('date', $j->journal_date);
            $j->hadir_count = $atts->where('status', 'Hadir')->count();
            $j->sakit_count = $atts->where('status', 'Sakit')->count();
            $j->izin_count = $atts->where('status', 'Izin')->count();
            $j->alpa_count = $atts->where('status', 'Alpa')->count();
            $j->total_students = $atts->count();
            return $j;
        });

        $filterTeacher = $teacherId ? \App\Models\Teacher::find($teacherId) : null;
        $wakaUser = Auth::user();

        return view('waka.journals.print', compact(
            'journalsWithStats',
            'filterTeacher',
            'selectedGrade',
            'selectedMajor',
            'startDate',
            'endDate',
            'period',
            'wakaUser'
        ));
    }

    /**
     * Lembar cetak laporan resmi rekapitulasi jurnal mengajar untuk Guru Pengampu Mapel (PDF / Print).
     */
    public function teacherPrint(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Akun Guru belum memiliki data guru terdaftar.');
        }

        $period = $request->query('period', 'all');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $classId = $request->query('class_id');

        $query = Journal::with([
            'teacher',
            'schedule.subject',
            'schedule.schoolClass',
            'schedule.room',
            'schedule.blockCycle',
        ])->where('teacher_id', $teacher->id);

        $today = now()->toDateString();

        switch ($period) {
            case 'today':
                $query->whereDate('journal_date', $today);
                $startDate = $today;
                $endDate = $today;
                break;
            case '7days':
                $startDate = now()->subDays(6)->toDateString();
                $endDate = $today;
                $query->whereBetween('journal_date', [$startDate, $endDate]);
                break;
            case 'this_month':
                $startDate = now()->startOfMonth()->toDateString();
                $endDate = now()->endOfMonth()->toDateString();
                $query->whereBetween('journal_date', [$startDate, $endDate]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('journal_date', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->whereDate('journal_date', '>=', $startDate);
                } elseif ($endDate) {
                    $query->whereDate('journal_date', '<=', $endDate);
                }
                break;
            default:
                if ($request->filled('date')) {
                    $query->whereDate('journal_date', $request->query('date'));
                }
                break;
        }

        if ($classId) {
            $query->whereHas('schedule', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $journals = $query->oldest('journal_date')->oldest('schedule_id')->get();

        // Hitung statistik presensi
        $scheduleIds = $journals->pluck('schedule_id')->unique();
        $allAttendances = Attendance::whereIn('schedule_id', $scheduleIds)->get();

        $journalsWithStats = $journals->map(function ($j) use ($allAttendances) {
            $atts = $allAttendances->where('schedule_id', $j->schedule_id)->where('date', $j->journal_date);
            $j->hadir_count = $atts->where('status', 'Hadir')->count();
            $j->sakit_count = $atts->where('status', 'Sakit')->count();
            $j->izin_count = $atts->where('status', 'Izin')->count();
            $j->alpa_count = $atts->where('status', 'Alpa')->count();
            $j->total_students = $atts->count();
            return $j;
        });

        $selectedClass = $classId ? \App\Models\SchoolClass::find($classId) : null;

        return view('journals.print', compact(
            'journalsWithStats',
            'teacher',
            'selectedClass',
            'startDate',
            'endDate',
            'period'
        ));
    }

    /**
     * Halaman Khusus Monitoring Keterlambatan Pengisian Jurnal Mengajar (Waka Kurikulum / Admin).
     */
    public function lateMonitoring(Request $request)
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $targetDate = $request->filled('date')
            ? Carbon::parse($request->query('date'))->startOfDay()
            : Carbon::today();

        $dayName = $days[$targetDate->dayOfWeek];
        $isToday = $targetDate->isToday();
        $dateFormatted = $targetDate->locale('id')->isoFormat('dddd, D MMMM Y');

        $activeCycle = BlockCycle::where('is_active', true)->first();

        $selectedGrade = $request->query('grade');
        $selectedMajor = $request->query('major');
        $selectedTeacherId = $request->query('teacher_id');
        $selectedDelayLevel = $request->query('delay_level', 'all'); // all, warning (30-60m), medium (60-120m), critical (>120m)
        $search = $request->query('search');

        // Query master jadwal pada hari target
        $query = Schedule::with([
            'teacher',
            'subject',
            'schoolClass',
            'room',
            'blockCycle',
        ])
        ->where('day', $dayName);

        if ($activeCycle) {
            $query->where('block_cycle_id', $activeCycle->id);
        }

        if ($selectedTeacherId) {
            $query->where('teacher_id', $selectedTeacherId);
        }

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

        if ($selectedMajor) {
            $query->whereHas('schoolClass', function ($q) use ($selectedMajor) {
                $q->where('major', $selectedMajor);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher', fn($t) => $t->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subject', fn($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('schoolClass', fn($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('room', fn($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $allDaySchedules = $query->orderBy('start_time')->get();

        $totalSchedulesCount = $allDaySchedules->count();
        $filledScheduleIds = Journal::whereDate('journal_date', $targetDate)
            ->whereIn('schedule_id', $allDaySchedules->pluck('id'))
            ->pluck('schedule_id')
            ->unique();
        $totalFilledCount = $filledScheduleIds->count();

        // Kumpulkan data keterlambatan
        $lateItems = collect();
        $now = now();

        foreach ($allDaySchedules as $schedule) {
            // Jika sudah terisi jurnal pada tanggal tsb, maka tidak terlambat
            if ($filledScheduleIds->contains($schedule->id)) {
                continue;
            }

            // Hitung menit keterlambatan
            $endDateTime = Carbon::parse($targetDate->format('Y-m-d') . ' ' . $schedule->end_time);

            if ($isToday) {
                $diffMinutes = $endDateTime->diffInMinutes($now, false);
                // Hanya dianggap terlambat jika jadwal sudah usai lebih dari 30 menit
                if ($diffMinutes <= 30) {
                    continue;
                }
                $minutesLate = (int) $diffMinutes;
            } else {
                // Hari lampau dan belum terisi jurnal: otomatis terlambat
                if ($targetDate->isPast()) {
                    $minutesLate = max(31, (int) $endDateTime->diffInMinutes(now(), false));
                } else {
                    continue; // Hari depan
                }
            }

            // Tentukan level keterlambatan
            if ($minutesLate > 120) {
                $level = 'critical';
                $levelLabel = 'Kritis (>2 Jam)';
                $badgeClass = 'badge-danger';
            } elseif ($minutesLate > 60) {
                $level = 'medium';
                $levelLabel = 'Sedang (1-2 Jam)';
                $badgeClass = 'badge-warning';
            } else {
                $level = 'warning';
                $levelLabel = 'Perhatian (>30 Mnt)';
                $badgeClass = 'badge-info';
            }

            // Filter delay level jika user memilih
            if ($selectedDelayLevel !== 'all' && $selectedDelayLevel !== $level) {
                continue;
            }

            // Format lama keterlambatan
            $hours = floor($minutesLate / 60);
            $mins = $minutesLate % 60;
            if ($hours > 0) {
                $formattedLate = "{$hours} jam {$mins} mnt";
            } else {
                $formattedLate = "{$mins} menit";
            }

            $lateItems->push([
                'schedule' => $schedule,
                'minutes_late' => $minutesLate,
                'formatted_late' => $formattedLate,
                'level' => $level,
                'level_label' => $levelLabel,
                'badge_class' => $badgeClass,
                'end_time_formatted' => substr($schedule->end_time, 0, 5),
                'time_range' => substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5),
            ]);
        }

        // Urutkan dari yang paling terlambat ke terendah
        $lateItems = $lateItems->sortByDesc('minutes_late')->values();

        $totalLateCount = $lateItems->count();
        $criticalCount = $lateItems->where('level', 'critical')->count();
        $mediumCount = $lateItems->where('level', 'medium')->count();
        $warningCount = $lateItems->where('level', 'warning')->count();
        $complianceRate = $totalSchedulesCount > 0
            ? round(($totalFilledCount / $totalSchedulesCount) * 100, 1)
            : 100;

        $teachers = Teacher::orderBy('name')->get();
        $majors = SchoolClass::whereNotNull('major')
            ->where('major', '!=', '')
            ->distinct()
            ->orderBy('major')
            ->pluck('major');

        return view('waka.journals.late_monitoring', compact(
            'lateItems',
            'teachers',
            'majors',
            'targetDate',
            'dayName',
            'dateFormatted',
            'isToday',
            'selectedGrade',
            'selectedMajor',
            'selectedTeacherId',
            'selectedDelayLevel',
            'search',
            'totalSchedulesCount',
            'totalFilledCount',
            'totalLateCount',
            'criticalCount',
            'mediumCount',
            'warningCount',
            'complianceRate'
        ));
    }
}
