<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolParent;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        $isHomeroom = $teacher && SchoolClass::where('homeroom_teacher_id', $teacher->id)->exists();
        $isBk = $user->role === 'bk' || ($teacher && $teacher->isBkTeacher());

        // Siswa, Orang Tua, Waka Kurikulum, Guru BK, atau Guru Wali Kelas boleh melihat rekap.
        if (!in_array($user->role, ['siswa', 'ortu', 'waka', 'bk'], true) && !$isHomeroom && !$isBk) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // =========================================================================
        // 1. REKAP WAKA KURIKULUM, GURU BK, DAN WALI KELAS
        // =========================================================================
        if ($user->role === 'waka' || $isBk || $isHomeroom) {
            $homeroomClassIds = $teacher
                ? SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id')
                : collect();

            $bkClassIds = ($teacher && $isBk)
                ? $teacher->bkClasses()->pluck('id')
                : collect();

            if ($isBk && $user->role !== 'waka' && $bkClassIds->isNotEmpty()) {
                // Guru BK HANYA dapat memantau kelas-kelas binaannya
                $availableClasses = SchoolClass::whereIn('id', $bkClassIds)->orderBy('name')->get();
                $baseClassIds = $bkClassIds;
            } elseif ($isHomeroom && !$isBk && $user->role !== 'waka' && $homeroomClassIds->isNotEmpty()) {
                // Wali Kelas biasa HANYA dapat memantau kelas walinya
                $availableClasses = SchoolClass::whereIn('id', $homeroomClassIds)->orderBy('name')->get();
                $baseClassIds = $homeroomClassIds;
            } else {
                // Waka Kurikulum memiliki akses pantau seluruh 48 kelas
                $availableClasses = SchoolClass::orderBy('name')->get();
                $baseClassIds = $availableClasses->pluck('id');
            }

            // Filter parameter
            $selectedClassId = $request->get('class_id');
            if ($isBk && $user->role !== 'waka' && $selectedClassId) {
                if (!$baseClassIds->contains((int) $selectedClassId)) {
                    abort(403, 'Akses Ditolak: Bukan kelas binaan Anda');
                }
            }
            $selectedClass = $selectedClassId ? SchoolClass::with('homeroomTeacher')->find($selectedClassId) : null;
            $range = $request->get('range');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $search = $request->get('search');

            $today = Carbon::today();

            // Logika Preset Periode Tanggal
            if ($range === 'today') {
                $startDate = $today->toDateString();
                $endDate = $today->toDateString();
            } elseif ($range === '7_days' || $range === '1_week') {
                $startDate = $today->copy()->subDays(6)->toDateString();
                $endDate = $today->toDateString();
                $range = '7_days';
            } elseif ($range === 'this_week') {
                $startDate = $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
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
                // Evaluasi jika parameter start_date dan end_date diisi langsung
                if ($startDate && $endDate) {
                    if ($startDate === $today->toDateString() && $endDate === $today->toDateString()) {
                        $range = 'today';
                    } elseif ($startDate === $today->copy()->subDays(6)->toDateString() && $endDate === $today->toDateString()) {
                        $range = '7_days';
                    } elseif ($startDate === $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString() && $endDate === $today->copy()->endOfWeek(Carbon::SUNDAY)->toDateString()) {
                        $range = 'this_week';
                    } elseif ($startDate === $today->copy()->subDays(29)->toDateString() && $endDate === $today->toDateString()) {
                        $range = '30_days';
                    } elseif ($startDate === $today->copy()->startOfMonth()->toDateString() && $endDate === $today->copy()->endOfMonth()->toDateString()) {
                        $range = 'this_month';
                    } else {
                        $range = 'custom';
                    }
                } elseif ($startDate || $endDate) {
                    $range = 'custom';
                } else {
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

            // Summary Total untuk 4 Stat Cards dihitung dari seluruh siswa sesuai filter (bukan per halaman)
            $allStudentIds = (clone $studentsQuery)->pluck('id');
            $summaryQuery = Attendance::whereIn('student_id', $allStudentIds);
            if ($startDate) {
                $summaryQuery->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $summaryQuery->whereDate('date', '<=', $endDate);
            }

            $statusCounts = (clone $summaryQuery)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $summary = [
                'hadir' => (int) ($statusCounts['Hadir'] ?? 0),
                'sakit' => (int) ($statusCounts['Sakit'] ?? 0),
                'izin' => (int) ($statusCounts['Izin'] ?? 0),
                'alpa' => (int) ($statusCounts['Alpa'] ?? 0),
                'total' => (int) $statusCounts->sum(),
            ];

            // Pagination parameter sama seperti Data Siswa & Guru (10, 15, 25, 50, 100)
            $perPage = (int) $request->query('per_page', 15);
            if (!in_array($perPage, [10, 15, 25, 50, 100])) {
                $perPage = 15;
            }

            // Paginate data siswa
            $students = $studentsQuery->orderBy('name')->paginate($perPage)->withQueryString();
            $pageStudentIds = $students->pluck('id');

            // Query Data Presensi untuk siswa pada halaman saat ini
            $attendancesQuery = Attendance::with([
                'student.schoolClass',
                'schedule.subject',
                'schedule.schoolClass',
                'schedule.blockCycle',
            ])->whereIn('student_id', $pageStudentIds);

            if ($startDate) {
                $attendancesQuery->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $attendancesQuery->whereDate('date', '<=', $endDate);
            }

            $attendances = $attendancesQuery->latest('date')->latest('id')->get();

            // Hitung Rekapitulasi per Siswa pada Halaman Aktif (Nama, Kelas, Hadir, Sakit, Izin, Alpa, Total)
            $recapCollection = $students->getCollection()->map(function ($std) use ($attendances) {
                $studentAtts = $attendances->where('student_id', $std->id);
                $hadir = $studentAtts->where('status', 'Hadir')->count();
                $sakit = $studentAtts->where('status', 'Sakit')->count();
                $izin = $studentAtts->where('status', 'Izin')->count();
                $alpa = $studentAtts->where('status', 'Alpa')->count();
                $total = $hadir + $sakit + $izin + $alpa;

                return (object) [
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
                ];
            });

            $studentsRecap = new LengthAwarePaginator(
                $recapCollection,
                $students->total(),
                $students->perPage(),
                $students->currentPage(),
                ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
            );

            return view('attendances.index', compact(
                'attendances',
                'students',
                'studentsRecap',
                'summary',
                'availableClasses',
                'selectedClassId',
                'selectedClass',
                'startDate',
                'endDate',
                'range',
                'search'
            ));
        }

        // =========================================================================
        // 2. REKAP SISWA & ORANG TUA
        // =========================================================================
        $student = Student::with('schoolClass')->where('user_id', $user->id)->first();
        if (!$student && $user->parent) {
            $student = $user->parent->students()->with('schoolClass')->first();
        }

        if ($student) {
            $range = $request->get('range');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $today = Carbon::today();

            if ($range === 'today') {
                $startDate = $today->toDateString();
                $endDate = $today->toDateString();
            } elseif ($range === '7_days' || $range === '1_week') {
                $startDate = $today->copy()->subDays(6)->toDateString();
                $endDate = $today->toDateString();
                $range = '7_days';
            } elseif ($range === 'this_week') {
                $startDate = $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
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
                if ($startDate || $endDate) {
                    $range = 'custom';
                } else {
                    $range = 'all';
                }
            }

            $attendancesQuery = Attendance::with([
                'student',
                'schedule.subject',
                'schedule.schoolClass',
                'schedule.blockCycle',
            ])->where('student_id', $student->id);

            if ($startDate) {
                $attendancesQuery->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $attendancesQuery->whereDate('date', '<=', $endDate);
            }

            $attendances = $attendancesQuery->latest('date')->latest('id')->get();

            $summary = [
                'hadir' => $attendances->where('status', 'Hadir')->count(),
                'sakit' => $attendances->where('status', 'Sakit')->count(),
                'izin' => $attendances->where('status', 'Izin')->count(),
                'alpa' => $attendances->where('status', 'Alpa')->count(),
                'total' => $attendances->count(),
            ];

            return view('attendances.index', compact('attendances', 'student', 'summary', 'startDate', 'endDate', 'range'));
        }

        // =========================================================================
        // 3. REKAP ORANG TUA
        // =========================================================================
        $parent = SchoolParent::where('user_id', $user->id)->first();

        if (!$parent) {
            abort(403, 'Akun tidak terhubung dengan data siswa atau orang tua.');
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

        $summary = [
            'hadir' => $attendances->where('status', 'Hadir')->count(),
            'sakit' => $attendances->where('status', 'Sakit')->count(),
            'izin' => $attendances->where('status', 'Izin')->count(),
            'alpa' => $attendances->where('status', 'Alpa')->count(),
            'total' => $attendances->count(),
        ];

        return view('attendances.index', compact('attendances', 'students', 'summary'));
    }

    /**
     * Menampilkan lembar cetak laporan resmi rekapitulasi presensi siswa (Format Cetak / PDF).
     */
    public function print(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        $isHomeroom = $teacher && SchoolClass::where('homeroom_teacher_id', $teacher->id)->exists();
        $isBk = $user->role === 'bk' || ($teacher && $teacher->isBkTeacher());

        // Otorisasi: Waka, Guru BK, atau Wali Kelas
        if (!in_array($user->role, ['waka', 'bk'], true) && !$isHomeroom && !$isBk) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak laporan rekap presensi ini.');
        }

        $homeroomClassIds = $teacher
            ? SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id')
            : collect();

        $bkClassIds = ($teacher && $isBk)
            ? $teacher->bkClasses()->pluck('id')
            : collect();

        if ($isBk && $user->role !== 'waka' && $bkClassIds->isNotEmpty()) {
            $baseClassIds = $bkClassIds;
        } elseif ($isHomeroom && !$isBk && $user->role !== 'waka' && $homeroomClassIds->isNotEmpty()) {
            $baseClassIds = $homeroomClassIds;
        } else {
            $baseClassIds = SchoolClass::pluck('id');
        }

        $selectedClassId = $request->get('class_id');
        if ($isBk && $user->role !== 'waka' && $selectedClassId) {
            if (!$baseClassIds->contains((int) $selectedClassId)) {
                abort(403, 'Akses Ditolak: Bukan kelas binaan Anda');
            }
        }
        $range = $request->get('range', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $today = Carbon::today();

        if ($range === 'today') {
            $startDate = $today->toDateString();
            $endDate = $today->toDateString();
        } elseif ($range === '7_days' || $range === '1_week') {
            $startDate = $today->copy()->subDays(6)->toDateString();
            $endDate = $today->toDateString();
        } elseif ($range === 'this_week') {
            $startDate = $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        } elseif ($range === '30_days' || $range === '1_month') {
            $startDate = $today->copy()->subDays(29)->toDateString();
            $endDate = $today->toDateString();
        } elseif ($range === 'this_month') {
            $startDate = $today->copy()->startOfMonth()->toDateString();
            $endDate = $today->copy()->endOfMonth()->toDateString();
        }

        $selectedClass = $selectedClassId ? SchoolClass::with('homeroomTeacher')->find($selectedClassId) : null;

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

        $attendancesQuery = Attendance::whereIn('student_id', $studentIds);
        if ($startDate) {
            $attendancesQuery->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $attendancesQuery->whereDate('date', '<=', $endDate);
        }
        $attendances = $attendancesQuery->get();

        $studentsRecap = $students->map(function ($std) use ($attendances) {
            $studentAtts = $attendances->where('student_id', $std->id);
            $hadir = $studentAtts->where('status', 'Hadir')->count();
            $sakit = $studentAtts->where('status', 'Sakit')->count();
            $izin = $studentAtts->where('status', 'Izin')->count();
            $alpa = $studentAtts->where('status', 'Alpa')->count();
            $total = $hadir + $sakit + $izin + $alpa;
            $percentage = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

            return (object) [
                'id' => $std->id,
                'nis' => $std->nis,
                'name' => $std->name,
                'gender' => $std->gender ?? '-',
                'class_name' => $std->schoolClass?->name ?? '-',
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
            'total_students' => $students->count(),
            'avg_percentage' => $studentsRecap->count() > 0 ? round($studentsRecap->avg('percentage'), 1) : 0,
        ];

        // Ambil data Waka Kurikulum jika ada
        $wakaUser = \App\Models\User::where('role', 'waka')->first();

        return view('attendances.print', compact(
            'studentsRecap',
            'summary',
            'selectedClass',
            'startDate',
            'endDate',
            'range',
            'today',
            'wakaUser'
        ));
    }
}

