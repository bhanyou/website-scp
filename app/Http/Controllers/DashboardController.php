<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BlockCycle;
use App\Models\Journal;
use App\Models\PermissionRequest;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Major;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function getIndonesianDays(): array
    {
        return [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
    }

    public function guru()
    {
        $user = Auth::user();

        $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            $teacher = Teacher::where('name', $user->name)
                ->orWhere('username', $user->username)
                ->first();
            if ($teacher && empty($teacher->user_id)) {
                $teacher->update(['user_id' => $user->id]);
            }
        }

        if (!$teacher) {
            // Otomatis buat profil guru/BK jika belum ada agar semua role langsung dapat masuk dashboard tanpa error 403
            $teacher = Teacher::create([
                'user_id'  => $user->id,
                'nip'      => ($user->role === 'bk' ? 'BK' : 'GURU') . str_pad((string)$user->id, 6, '0', STR_PAD_LEFT),
                'username' => $user->username ?: 'guru' . $user->id,
                'name'     => $user->name,
                'mapel'    => $user->role === 'bk' ? 'BIMBINGAN KONSELING' : null,
            ]);
        }

        $teacher->load('homeroomClasses.students');
        $activeCycle = BlockCycle::where('is_active', true)->first();
        $today = Carbon::today();
        $days = $this->getIndonesianDays();
        $todayName = $days[$today->dayOfWeek];

        $schedules = collect();
        $journalAlerts = collect();
        $totalMySchedules = 0;

        if ($activeCycle) {
            $totalMySchedules = Schedule::where('teacher_id', $teacher->id)
                ->where(function ($q) use ($activeCycle) {
                    $q->where('block_cycle_id', $activeCycle->id)
                      ->orWhere('block_cycle', $activeCycle->cycle);
                })
                ->count();

            $schedules = Schedule::with([
                'subject',
                'schoolClass',
                'room',
                'blockCycle',
            ])
                ->where('teacher_id', $teacher->id)
                ->where(function ($q) use ($activeCycle) {
                    $q->where('block_cycle_id', $activeCycle->id)
                      ->orWhere('block_cycle', $activeCycle->cycle);
                })
                ->where('day', $todayName)
                ->orderBy('start_time')
                ->get();

            foreach ($schedules as $schedule) {
                $endTime = Carbon::parse($today->format('Y-m-d') . ' ' . $schedule->end_time);
                $minutesLate = $endTime->diffInMinutes(now(), false);

                if ($minutesLate > 30) {
                    $hasJournal = Journal::where('schedule_id', $schedule->id)
                        ->whereDate('journal_date', $today)
                        ->exists();

                    if (!$hasJournal) {
                        $journalAlerts->push([
                            'schedule' => $schedule,
                            'minutes_late' => $minutesLate,
                        ]);
                    }
                }
            }
        }

        $totalMyJournals = Journal::where('teacher_id', $teacher->id)->count();

        $recentMyJournals = Journal::with(['schedule.subject', 'schedule.schoolClass'])
            ->where('teacher_id', $teacher->id)
            ->latest('journal_date')
            ->latest('submitted_at')
            ->take(5)
            ->get();

        return view('dashboard.guru', compact(
            'teacher',
            'activeCycle',
            'todayName',
            'today',
            'totalMySchedules',
            'totalMyJournals',
            'schedules',
            'journalAlerts',
            'recentMyJournals'
        ));
    }

    public function waka()
    {
        $activeCycle = BlockCycle::where('is_active', true)->first();
        $today = Carbon::today();
        $days = $this->getIndonesianDays();
        $todayName = $days[$today->dayOfWeek];

        $totalTeachers = Teacher::count();
        $totalStudents = Student::count();
        $totalMajors = Major::count();
        $totalClasses = SchoolClass::count();
        $totalSubjects = Subject::count();
        $totalRooms = Room::count();

        $todaySchedulesCount = 0;
        $todayJournalsCount = Journal::whereDate('journal_date', $today)->count();
        $journalAlerts = collect();

        if ($activeCycle) {
            $schedules = Schedule::with([
                'teacher',
                'subject',
                'schoolClass',
                'room',
                'blockCycle',
            ])
                ->where(function ($q) use ($activeCycle) {
                    $q->where('block_cycle_id', $activeCycle->id)
                      ->orWhere('block_cycle', $activeCycle->cycle);
                })
                ->where('day', $todayName)
                ->orderBy('start_time')
                ->get();

            $todaySchedulesCount = $schedules->count();

            foreach ($schedules as $schedule) {
                $endTime = Carbon::parse($today->format('Y-m-d') . ' ' . $schedule->end_time);
                $minutesLate = $endTime->diffInMinutes(now(), false);

                if ($minutesLate > 30) {
                    $hasJournal = Journal::where('schedule_id', $schedule->id)
                        ->whereDate('journal_date', $today)
                        ->exists();

                    if (!$hasJournal) {
                        $journalAlerts->push([
                            'schedule' => $schedule,
                            'minutes_late' => $minutesLate,
                        ]);
                    }
                }
            }
        }

        $recentJournals = Journal::with(['teacher', 'schedule.subject', 'schedule.schoolClass'])
            ->latest('journal_date')
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $majorStats = Major::with(['classes' => function ($q) {
            $q->withCount('students');
        }])->orderBy('code')->get()->map(function ($major) {
            $maxRombel = 0;
            foreach ($major->classes as $c) {
                if (preg_match('/(\d+)$/', trim($c->name), $matches)) {
                    $maxRombel = max($maxRombel, (int)$matches[1]);
                }
            }
            $rombelCount = $maxRombel ?: ($major->classes->groupBy('grade')->map->count()->max() ?: 1);

            return (object) [
                'code' => $major->code,
                'name' => $major->name,
                'raw_major' => $major->code,
                'rombel_count' => $rombelCount,
                'total_classes' => $major->classes->count(),
                'total_students' => $major->classes->sum('students_count'),
            ];
        });

        // Akumulasi Presensi Keseluruhan
        $allTimeHadir = Attendance::where('status', 'Hadir')->count();
        $allTimeSakit = Attendance::where('status', 'Sakit')->count();
        $allTimeIzin  = Attendance::where('status', 'Izin')->count();
        $allTimeAlpa  = Attendance::where('status', 'Alpa')->count();
        $allTimeTotal = $allTimeHadir + $allTimeSakit + $allTimeIzin + $allTimeAlpa;
        $allTimeRate  = $allTimeTotal > 0 ? round(($allTimeHadir / $allTimeTotal) * 100, 1) : 0;

        // Rekap Kehadiran per Kelas Hari Ini (Konsolidasi Pilihan B: 1 baris per kelas unik)
        $todayRawAttendances = Attendance::with([
            'student.schoolClass',
            'schedule.subject',
            'schedule.schoolClass',
            'schedule.teacher'
        ])
            ->whereDate('date', $today)
            ->get();

        $todayClassAttendances = $todayRawAttendances
            ->groupBy(function ($att) {
                return $att->schedule?->class_id ?? ($att->student?->class_id ?? 0);
            })
            ->map(function ($items, $classId) {
                $first = $items->first();
                $className = $first?->schedule?->schoolClass?->name ?? ($first?->student?->schoolClass?->name ?? 'Tanpa Kelas');

                // Urutkan jadwal yang berlangsung di kelas ini hari ini
                $sortedSchedules = $items->pluck('schedule')->filter()->unique('id')->sortByDesc('start_time')->values();
                $latestSchedule = $sortedSchedules->first();
                $subjectName = $latestSchedule?->subject?->name ?? ($first?->schedule?->subject?->name ?? '-');
                $teacherName = $latestSchedule?->teacher?->name ?? ($first?->schedule?->teacher?->name ?? '-');

                // Info semua mata pelajaran yang selesai di kelas ini hari ini
                $uniqueSubjects = $sortedSchedules->pluck('subject.name')->filter()->unique()->values();
                $totalSessions = $sortedSchedules->count();
                $allSubjectsStr = $uniqueSubjects->implode(', ');

                // Konsolidasi unik per siswa pada hari ini
                $studentsGrouped = $items->groupBy('student_id');

                $studentsList = [];
                $hadir = 0;
                $sakit = 0;
                $izin = 0;
                $alpa = 0;

                foreach ($studentsGrouped as $studentId => $studentAtts) {
                    $firstAtt = $studentAtts->first();
                    $student = $firstAtt?->student;

                    // Tentukan status harian siswa:
                    // Prioritas: Alpa > Sakit > Izin > Hadir
                    $status = 'Hadir';
                    if ($studentAtts->contains('status', 'Alpa')) {
                        $status = 'Alpa';
                    } elseif ($studentAtts->contains('status', 'Sakit')) {
                        $status = 'Sakit';
                    } elseif ($studentAtts->contains('status', 'Izin')) {
                        $status = 'Izin';
                    }

                    // Kumpulkan catatan dari setiap sesi hari ini (jika ada)
                    $notes = $studentAtts->pluck('note')
                        ->filter()
                        ->map(fn($n) => trim($n))
                        ->filter(fn($n) => $n !== '')
                        ->unique()
                        ->implode('; ');

                    if ($status === 'Hadir') $hadir++;
                    elseif ($status === 'Sakit') $sakit++;
                    elseif ($status === 'Izin') $izin++;
                    elseif ($status === 'Alpa') $alpa++;

                    $studentsList[] = [
                        'nis' => $student?->nis ?? '-',
                        'name' => $student?->name ?? 'Tanpa Nama',
                        'status' => $status,
                        'note' => $notes ?: '',
                    ];
                }

                // Urutkan siswa berdasarkan nama
                usort($studentsList, fn($a, $b) => strcmp($a['name'], $b['name']));

                $total = $hadir + $sakit + $izin + $alpa;
                $rate  = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

                return (object) [
                    'class_id' => (int) $classId,
                    'class_name' => $className,
                    'subject_name' => $subjectName,
                    'teacher_name' => $teacherName,
                    'total_sessions' => $totalSessions,
                    'all_subjects' => $allSubjectsStr,
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'total' => $total,
                    'rate' => $rate,
                    'students' => $studentsList,
                ];
            })
            ->sortBy('class_name')
            ->values();

        // Statistik Presensi Siswa Hari Ini (Unik per siswa yang tercatat hari ini)
        $todayHadir = $todayClassAttendances->sum('hadir');
        $todaySakit = $todayClassAttendances->sum('sakit');
        $todayIzin  = $todayClassAttendances->sum('izin');
        $todayAlpa  = $todayClassAttendances->sum('alpa');
        $todayTotalAbsen = $todayHadir + $todaySakit + $todayIzin + $todayAlpa;
        $todayAttendanceRate = $todayTotalAbsen > 0 ? round(($todayHadir / $todayTotalAbsen) * 100, 1) : 0;

        return view('dashboard.waka', compact(
            'activeCycle',
            'todayName',
            'today',
            'totalTeachers',
            'totalStudents',
            'totalMajors',
            'totalClasses',
            'totalSubjects',
            'totalRooms',
            'todaySchedulesCount',
            'todayJournalsCount',
            'journalAlerts',
            'recentJournals',
            'majorStats',
            'todayHadir',
            'todaySakit',
            'todayIzin',
            'todayAlpa',
            'todayTotalAbsen',
            'todayAttendanceRate',
            'allTimeHadir',
            'allTimeSakit',
            'allTimeIzin',
            'allTimeAlpa',
            'allTimeTotal',
            'allTimeRate',
            'todayClassAttendances'
        ));
    }

    public function siswa()
    {
        $user = Auth::user();
        $student = $user->student;
        if (!$student && $user->parent) {
            $student = $user->parent->students()->first();
        }
        if (!$student && $user->role === 'siswa') {
            $student = Student::where('username', $user->username)
                ->orWhere('name', $user->name)
                ->first();
            if ($student && empty($student->user_id)) {
                $student->update(['user_id' => $user->id]);
            }
        }

        $activeCycle = BlockCycle::where('is_active', true)->first();
        $today = Carbon::today();
        $days = $this->getIndonesianDays();
        $todayName = $days[$today->dayOfWeek];

        $todaySchedules = collect();
        $recentPermissions = collect();
        $attendanceStats = [
            'total' => 0,
            'hadir' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpa' => 0,
            'percent' => 0,
        ];

        if ($student) {
            $student->load(['schoolClass', 'parent']);

            $total = Attendance::where('student_id', $student->id)->count();
            $hadir = Attendance::where('student_id', $student->id)->where('status', 'Hadir')->count();
            $sakit = Attendance::where('student_id', $student->id)->where('status', 'Sakit')->count();
            $izin = Attendance::where('student_id', $student->id)->where('status', 'Izin')->count();
            $alpa = Attendance::where('student_id', $student->id)->where('status', 'Alpa')->count();
            $percent = $total > 0 ? round(($hadir / $total) * 100) : 0;

            $attendanceStats = [
                'total' => $total,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpa' => $alpa,
                'percent' => $percent,
            ];

            if ($activeCycle && $student->class_id) {
                $todaySchedules = Schedule::with(['subject', 'teacher', 'room'])
                    ->where('class_id', $student->class_id)
                    ->where(function ($q) use ($activeCycle) {
                        $q->where('block_cycle_id', $activeCycle->id)
                          ->orWhere('block_cycle', $activeCycle->cycle);
                    })
                    ->where('day', $todayName)
                    ->orderBy('start_time')
                    ->get();
            }

            $recentPermissions = PermissionRequest::where('student_id', $student->id)
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard.siswa', compact(
            'student',
            'activeCycle',
            'todayName',
            'today',
            'todaySchedules',
            'recentPermissions',
            'attendanceStats'
        ));
    }
}