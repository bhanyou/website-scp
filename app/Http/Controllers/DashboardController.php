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
                ->where('block_cycle_id', $activeCycle->id)
                ->count();

            $schedules = Schedule::with([
                'subject',
                'schoolClass',
                'room',
                'blockCycle',
            ])
                ->where('teacher_id', $teacher->id)
                ->where('block_cycle_id', $activeCycle->id)
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
                ->where('block_cycle_id', $activeCycle->id)
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
            'majorStats'
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
                    ->where('block_cycle_id', $activeCycle->id)
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