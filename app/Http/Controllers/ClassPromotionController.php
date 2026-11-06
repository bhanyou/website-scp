<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Major;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassPromotionController extends Controller
{
    /**
     * Halaman kerja Kenaikan Kelas & Kelulusan.
     */
    public function index(Request $request)
    {
        $currentYear = Setting::where('key', 'academic_year')->value('value') ?? '2025/2026';
        $currentSemester = Setting::where('key', 'semester')->value('value') ?? 'Ganjil';

        $selectedGrade = $request->query('grade');
        $selectedMajor = $request->query('major');
        $selectedRombel = $request->query('rombel');
        $selectedClassId = $request->query('class_id');

        $classes = SchoolClass::withCount(['students' => function ($q) {
            $q->where('status', 'aktif');
        }])->orderBy('grade')->orderBy('name')->get();

        // Cari kelas yang dipilih (selectedClass)
        $selectedClass = null;

        // 1. Jika class_id dipilih langsung
        if ($selectedClassId) {
            $selectedClass = $classes->firstWhere('id', $selectedClassId);
            if ($selectedClass) {
                if (!$selectedMajor) {
                    $selectedMajor = $selectedClass->major;
                }
                if (!$selectedGrade) {
                    $selectedGrade = match (strtoupper($selectedClass->grade)) {
                        'X' => '10',
                        'XI' => '11',
                        'XII' => '12',
                        default => $selectedClass->grade,
                    };
                }
                if (!$selectedRombel && preg_match('/(\d+)$/', trim($selectedClass->name), $matches)) {
                    $selectedRombel = $matches[1];
                }
            }
        } elseif ($selectedMajor && $selectedGrade && $selectedRombel) {
            // 2. Jika filter kombinasi (jurusan, tingkat, rombel) dipilih dan mengerucut ke 1 rombel
            $gradeNormalized = match (strtoupper($selectedGrade)) {
                '10' => 'X',
                '11' => 'XI',
                '12' => 'XII',
                default => strtoupper($selectedGrade),
            };

            $selectedClass = $classes->first(function ($c) use ($selectedMajor, $gradeNormalized, $selectedRombel) {
                return $c->major === $selectedMajor &&
                       ($c->grade === $gradeNormalized || str_starts_with($c->name, $gradeNormalized . ' ')) &&
                       str_ends_with(trim($c->name), (string) $selectedRombel);
            });

            if ($selectedClass) {
                $selectedClassId = $selectedClass->id;
            }
        }

        // Query siswa aktif
        $studentsQuery = Student::with(['schoolClass', 'previousClass'])
            ->where('status', 'aktif');

        if ($selectedClass) {
            $studentsQuery->where('class_id', $selectedClass->id);
        } else {
            if ($selectedMajor) {
                $studentsQuery->whereHas('schoolClass', function ($q) use ($selectedMajor) {
                    $q->where('major', $selectedMajor);
                });
            }

            if ($selectedGrade) {
                $studentsQuery->whereHas('schoolClass', function ($q) use ($selectedGrade) {
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

            if ($selectedRombel) {
                $studentsQuery->whereHas('schoolClass', function ($q) use ($selectedRombel) {
                    $q->where('name', 'like', '% ' . $selectedRombel)
                      ->orWhere('name', 'like', '%' . $selectedRombel);
                });
            }
        }

        $students = $studentsQuery->orderBy('name')->get();

        // Cari kelas tujuan otomatis untuk kelas yang dipilih (jika ada)
        $targetClass = null;
        if ($selectedClass) {
            $targetClass = $this->resolveTargetClass($selectedClass, $classes);
        }

        // Cek apakah ada data kenaikan kelas terakhir yang bisa di-rollback
        $hasRollbackData = Student::whereNotNull('previous_class_id')->exists();
        $rollbackCount = Student::whereNotNull('previous_class_id')->count();

        // Statistik siswa aktif per tingkat
        $countGrade10 = Student::where('status', 'aktif')
            ->whereHas('schoolClass', fn($q) => $q->whereIn('grade', ['10', 'X']))
            ->count();

        $countGrade11 = Student::where('status', 'aktif')
            ->whereHas('schoolClass', fn($q) => $q->whereIn('grade', ['11', 'XI']))
            ->count();

        $countGrade12 = Student::where('status', 'aktif')
            ->whereHas('schoolClass', fn($q) => $q->whereIn('grade', ['12', 'XII']))
            ->count();

        $countGraduated = Student::where('status', 'lulus')->count();

        // Ambil daftar jurusan untuk dropdown filter
        $majors = Major::orderBy('code')->get();
        if ($majors->isEmpty()) {
            $majors = SchoolClass::whereNotNull('major')
                ->where('major', '!=', '')
                ->distinct()
                ->orderBy('major')
                ->get()
                ->map(fn($c) => (object) ['code' => $c->major, 'name' => $c->major]);
        }

        return view('class_promotions.index', compact(
            'currentYear',
            'currentSemester',
            'classes',
            'majors',
            'selectedClassId',
            'selectedClass',
            'selectedMajor',
            'selectedGrade',
            'selectedRombel',
            'targetClass',
            'students',
            'hasRollbackData',
            'rollbackCount',
            'countGrade10',
            'countGrade11',
            'countGrade12',
            'countGraduated'
        ));
    }

    /**
     * Eksekusi proses kenaikan kelas (per rombel atau seluruh sekolah sekaligus).
     */
    public function process(Request $request)
    {
        $mode = $request->input('mode', 'single_class'); // 'single_class' atau 'all_school'
        $stayStudentIds = $request->input('stay_students', []); // Siswa yang tinggal kelas / tidak lulus

        if (!is_array($stayStudentIds)) {
            $stayStudentIds = [];
        }

        $allClasses = SchoolClass::all();

        DB::beginTransaction();
        try {
            $promotedCount = 0;
            $graduatedCount = 0;
            $stayCount = 0;

            if ($mode === 'single_class') {
                $classId = $request->input('class_id');
                $class = SchoolClass::findOrFail($classId);

                $students = Student::where('class_id', $class->id)
                    ->where('status', 'aktif')
                    ->get();

                $targetClass = $this->resolveTargetClass($class, $allClasses);

                foreach ($students as $student) {
                    // Jika siswa ditandai TINGGAL KELAS / TIDAK LULUS
                    if (in_array($student->id, $stayStudentIds)) {
                        $stayCount++;
                        continue;
                    }

                    // Jika Kelas 12 -> LULUS
                    if (in_array(strtoupper($class->grade), ['12', 'XII'])) {
                        $student->update([
                            'previous_class_id' => $student->class_id,
                            'status' => 'lulus',
                            'class_id' => null,
                        ]);
                        $graduatedCount++;
                    } else {
                        // Kelas 10 / 11 -> Naik ke target class
                        if ($targetClass) {
                            $student->update([
                                'previous_class_id' => $student->class_id,
                                'class_id' => $targetClass->id,
                            ]);
                            $promotedCount++;
                        }
                    }
                }

                $msg = "Kenaikan kelas untuk {$class->name} berhasil diproses! ";
                if ($graduatedCount > 0) $msg .= "{$graduatedCount} siswa dinyatakan lulus. ";
                if ($promotedCount > 0) $msg .= "{$promotedCount} siswa berhasil naik ke {$targetClass?->name}. ";
                if ($stayCount > 0) $msg .= "{$stayCount} siswa tetap di kelas lama (tinggal kelas).";

            } else {
                // Mode Seluruh Sekolah Sekaligus
                $activeStudents = Student::with('schoolClass')
                    ->where('status', 'aktif')
                    ->whereNotNull('class_id')
                    ->get();

                foreach ($activeStudents as $student) {
                    $class = $student->schoolClass;
                    if (!$class) continue;

                    // Jika siswa ditandai tinggal kelas
                    if (in_array($student->id, $stayStudentIds)) {
                        $stayCount++;
                        continue;
                    }

                    if (in_array(strtoupper($class->grade), ['12', 'XII'])) {
                        $student->update([
                            'previous_class_id' => $student->class_id,
                            'status' => 'lulus',
                            'class_id' => null,
                        ]);
                        $graduatedCount++;
                    } else {
                        $targetClass = $this->resolveTargetClass($class, $allClasses);
                        if ($targetClass) {
                            $student->update([
                                'previous_class_id' => $student->class_id,
                                'class_id' => $targetClass->id,
                            ]);
                            $promotedCount++;
                        }
                    }
                }

                $msg = "Kenaikan kelas massal seluruh sekolah berhasil diproses! ";
                $msg .= "{$promotedCount} siswa naik kelas, {$graduatedCount} siswa lulus, dan {$stayCount} siswa tinggal kelas.";
            }

            DB::commit();

            return redirect()
                ->route('class-promotions.index')
                ->with('success', $msg);

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('class-promotions.index')
                ->with('error', 'Gagal memproses kenaikan kelas: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan / Rollback kenaikan kelas terakhir (kembalikan ke kelas semula).
     */
    public function rollback()
    {
        DB::beginTransaction();
        try {
            $affectedStudents = Student::whereNotNull('previous_class_id')->get();

            if ($affectedStudents->isEmpty()) {
                DB::rollBack();
                return redirect()
                    ->route('class-promotions.index')
                    ->with('error', 'Tidak ditemukan riwayat kenaikan kelas yang dapat dibatalkan.');
            }

            $count = 0;
            foreach ($affectedStudents as $student) {
                $student->update([
                    'class_id' => $student->previous_class_id,
                    'status' => 'aktif',
                    'previous_class_id' => null,
                ]);
                $count++;
            }

            DB::commit();

            return redirect()
                ->route('class-promotions.index')
                ->with('success', "Rollback berhasil! Sebanyak {$count} siswa telah dikembalikan ke kelas asalnya.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('class-promotions.index')
                ->with('error', 'Gagal membatalkan kenaikan kelas: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk mencari kelas tujuan berdasarkan pola nama rombel dan jurusan.
     */
    private function resolveTargetClass(SchoolClass $currentClass, $allClasses): ?SchoolClass
    {
        $grade = strtoupper($currentClass->grade);

        if (in_array($grade, ['12', 'XII'])) {
            return null; // Tingkat 12 targetnya adalah Lulus
        }

        preg_match('/(\d+)$/', trim($currentClass->name), $mCurrent);
        $currentRombel = $mCurrent[1] ?? '';

        // Jika tingkat 10 / X -> cari tingkat 11 / XI
        if (in_array($grade, ['10', 'X'])) {
            $expectedName = preg_replace('/^X\b/i', 'XI', $currentClass->name);
            $expectedName = preg_replace('/^10\b/', '11', $expectedName);

            return $allClasses->first(function ($c) use ($expectedName, $currentClass, $currentRombel) {
                return in_array(strtoupper($c->grade), ['11', 'XI']) && (
                    strcasecmp($c->name, $expectedName) === 0 ||
                    ($c->major && $currentClass->major && strcasecmp($c->major, $currentClass->major) === 0 && str_ends_with(trim($c->name), (string)$currentRombel))
                );
            });
        }

        // Jika tingkat 11 / XI -> cari tingkat 12 / XII
        if (in_array($grade, ['11', 'XI'])) {
            $expectedName = preg_replace('/^XI\b/i', 'XII', $currentClass->name);
            $expectedName = preg_replace('/^11\b/', '12', $expectedName);

            return $allClasses->first(function ($c) use ($expectedName, $currentClass, $currentRombel) {
                return in_array(strtoupper($c->grade), ['12', 'XII']) && (
                    strcasecmp($c->name, $expectedName) === 0 ||
                    ($c->major && $currentClass->major && strcasecmp($c->major, $currentClass->major) === 0 && str_ends_with(trim($c->name), (string)$currentRombel))
                );
            });
        }

        return null;
    }
}
