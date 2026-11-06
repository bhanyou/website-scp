<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolParent;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
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
            $attendances = Attendance::with([
                'student',
                'schedule.subject',
                'schedule.schoolClass',
                'schedule.blockCycle',
            ])
                ->where('student_id', $student->id)
                ->latest('date')
                ->latest('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data rekap kehadiran berhasil diambil.',
                'student' => [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'class' => $student->schoolClass?->name,
                ],
                'data' => $attendances->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'tanggal' => $attendance->date,
                        'mata_pelajaran' => $attendance->schedule?->subject?->name,
                        'kelas' => $attendance->schedule?->schoolClass?->name,
                        'jam' => $attendance->schedule
                            ? $attendance->schedule->start_time . ' - ' . $attendance->schedule->end_time
                            : null,
                        'status' => $attendance->status,
                        'check_in' => $attendance->check_in,
                        'catatan' => $attendance->note,
                    ];
                }),
            ]);
        }

        // Cek apakah akun adalah Waka Kurikulum, Guru (Wali Kelas), atau BK
        $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
        $isHomeroom = $teacher && \App\Models\SchoolClass::where('homeroom_teacher_id', $teacher->id)->exists();
        $isBk = $teacher && $teacher->isBkTeacher();

        if ($user->role === 'waka' || $isHomeroom || $isBk) {
            $homeroomClassIds = $teacher
                ? \App\Models\SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id')
                : collect();

            if ($isBk && $user->role !== 'waka') {
                $availableClasses = $teacher->bkClasses()->orderBy('name')->get();
                $baseClassIds = $availableClasses->pluck('id');
            } elseif ($isHomeroom && $user->role !== 'waka' && $homeroomClassIds->isNotEmpty()) {
                $availableClasses = \App\Models\SchoolClass::whereIn('id', $homeroomClassIds)->orderBy('name')->get();
                $baseClassIds = $homeroomClassIds;
            } else {
                $availableClasses = \App\Models\SchoolClass::orderBy('name')->get();
                $baseClassIds = $availableClasses->pluck('id');
            }

            // Filter parameter
            $selectedClassId = $request->get('class_id');
            $range = $request->get('range');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $search = $request->get('search');

            $today = \Carbon\Carbon::today();

            if ($range === 'today') {
                $startDate = $today->toDateString();
                $endDate = $today->toDateString();
            } elseif ($range === '7_days' || $range === '1_week') {
                $startDate = $today->copy()->subDays(6)->toDateString();
                $endDate = $today->toDateString();
                $range = '7_days';
            } elseif ($range === 'this_week') {
                $startDate = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
                $endDate = $today->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->toDateString();
            } elseif ($range === '30_days' || $range === '1_month') {
                $startDate = $today->copy()->subDays(29)->toDateString();
                $endDate = $today->toDateString();
                $range = '30_days';
            } elseif ($range === 'this_month') {
                $startDate = $today->copy()->startOfMonth()->toDateString();
                $endDate = $today->copy()->endOfMonth()->toDateString();
            } elseif ($range === 'all') {
                $startDate = null;
                $endDate = null;
            } else {
                if (!$startDate && !$endDate) {
                    $range = 'all';
                }
            }

            // Query Siswa
            $studentsQuery = Student::with('schoolClass');
            if ($selectedClassId) {
                $studentsQuery->where('class_id', $selectedClassId);
            } else {
                $studentsQuery->whereIn('class_id', $baseClassIds);
            }

            if ($search) {
                $studentsQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            }

            $students = $studentsQuery->orderBy('name')->get();
            $studentIds = $students->pluck('id');

            // Query Data Presensi
            $attendancesQuery = Attendance::with([
                'student.schoolClass',
                'schedule.subject',
                'schedule.schoolClass',
                'schedule.blockCycle',
            ])->whereIn('student_id', $studentIds);

            if ($startDate) {
                $attendancesQuery->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $attendancesQuery->whereDate('date', '<=', $endDate);
            }

            $attendances = $attendancesQuery->latest('date')->latest('id')->get();

            // Rekap per siswa
            $studentsRecap = $students->map(function ($std) use ($attendances) {
                $studentAtts = $attendances->where('student_id', $std->id);
                $hadir = $studentAtts->where('status', 'Hadir')->count();
                $sakit = $studentAtts->where('status', 'Sakit')->count();
                $izin = $studentAtts->where('status', 'Izin')->count();
                $alpa = $studentAtts->where('status', 'Alpa')->count();
                $total = $hadir + $sakit + $izin + $alpa;
                $percentage = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

                return [
                    'id' => $std->id,
                    'nis' => $std->nis,
                    'name' => $std->name,
                    'class_name' => $std->schoolClass?->name ?? '-',
                    'class_id' => $std->class_id,
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'total' => $total,
                    'percentage' => $percentage,
                ];
            });

            $summary = [
                'hadir' => $attendances->where('status', 'Hadir')->count(),
                'sakit' => $attendances->where('status', 'Sakit')->count(),
                'izin' => $attendances->where('status', 'Izin')->count(),
                'alpa' => $attendances->where('status', 'Alpa')->count(),
                'total' => $attendances->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data rekap presensi kelas berhasil diambil.',
                'summary' => $summary,
                'available_classes' => $availableClasses->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                'selected_class_id' => $selectedClassId ? (int)$selectedClassId : null,
                'range' => $range,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'data' => $studentsRecap,
            ]);
        }

        // Cek apakah akun adalah orang tua
        $parent = SchoolParent::where('user_id', $user->id)->first();

        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data siswa atau orang tua.',
            ], 403);
        }

        $students = $parent->students()
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        $studentIds = $students->pluck('id');

        $attendances = Attendance::with([
            'student',
            'schedule.subject',
            'schedule.schoolClass',
            'schedule.blockCycle',
        ])
            ->whereIn('student_id', $studentIds)
            ->latest('date')
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data rekap kehadiran anak berhasil diambil.',
            'students' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'class' => $student->schoolClass?->name,
                ];
            }),
            'data' => $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'siswa' => $attendance->student?->name,
                    'nis' => $attendance->student?->nis,
                    'tanggal' => $attendance->date,
                    'mata_pelajaran' => $attendance->schedule?->subject?->name,
                    'kelas' => $attendance->schedule?->schoolClass?->name,
                    'jam' => $attendance->schedule
                        ? $attendance->schedule->start_time . ' - ' . $attendance->schedule->end_time
                        : null,
                    'status' => $attendance->status,
                    'check_in' => $attendance->check_in,
                    'catatan' => $attendance->note,
                ];
            }),
        ]);
    }
}