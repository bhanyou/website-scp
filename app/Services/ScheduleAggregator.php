<?php

namespace App\Services;

use App\Models\LessonHour;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleAggregator
{
    /**
     * Memeriksa apakah penggabungan dua periode melintasi batas jam istirahat.
     * Batas Istirahat Resmi:
     * - Istirahat I: Antara Jam 3 dan Jam 4 (Senin 09:45-10:00, hari lain 09:15-09:30)
     * - Istirahat II: Antara Jam 6 dan Jam 7 (Ishoma 11:30-12:30 / Jumat 11:25-12:40)
     */
    public static function crossesBreakBoundary(int $periodA, int $periodB): bool
    {
        $min = min($periodA, $periodB);
        $max = max($periodA, $periodB);

        // Melintasi Istirahat I (antara jam 3 dan jam 4)
        if ($min <= 3 && $max >= 4) {
            return true;
        }

        // Melintasi Istirahat II (antara jam 6 dan jam 7)
        if ($min <= 6 && $max >= 7) {
            return true;
        }

        return false;
    }

    /**
     * Menilai apakah dua slot jam KBM dapat digabungkan menjadi satu sesi KBM.
     * TIGA KONDISI HARUS TERPENUHI SEKALIGUS:
     * 1. Guru pengajar sama persis
     * 2. Mata pelajaran sama persis
     * 3. KODE RUANGAN SAMA PERSIS (ruang_id / room_id)
     * DAN TIDAK MELINTASI JAM ISTIRAHAT
     */
    public static function canMergeSlots(array $slotA, array $slotB): bool
    {
        // Harus jam berurutan
        if ((int)$slotB['period'] !== ((int)$slotA['end_period'] + 1)) {
            return false;
        }

        // 1. Guru pengajar sama
        if ((int)$slotA['teacher_id'] !== (int)$slotB['teacher_id']) {
            return false;
        }

        // 2. Mata pelajaran sama
        if ((int)$slotA['subject_id'] !== (int)$slotB['subject_id']) {
            return false;
        }

        // 3. KODE RUANGAN SAMA PERSIS (ruang_id / room_id)
        if ((int)($slotA['room_id'] ?? 0) !== (int)($slotB['room_id'] ?? 0)) {
            return false;
        }

        return true;
    }

    /**
     * Menggabungkan slot 1-jam menjadi sesi-sesi KBM dinamis berdasarkan kriteria valid.
     * 
     * @param array $slots Slot jam [period => 1..10, teacher_id, subject_id, room_id, ...]
     * @param string $day Nama hari (Senin, Selasa, dll.)
     * @return array Sesi-sesi KBM yang telah teragregasi secara tepat
     */
    public static function aggregateSlots(array $slots, string $day): array
    {
        if (empty($slots)) {
            return [];
        }

        // Urutkan slot berdasarkan nomor jam (1 s.d. 10)
        usort($slots, fn($a, $b) => (int)$a['period'] <=> (int)$b['period']);

        $lessonPeriods = LessonHour::getPeriodsForDay($day);

        $sessions = [];
        $current = null;

        foreach ($slots as $slot) {
            if (!$current) {
                $current = [
                    'class_id' => $slot['class_id'],
                    'day' => $day,
                    'start_period' => (int)$slot['period'],
                    'end_period' => (int)$slot['period'],
                    'teacher_id' => $slot['teacher_id'],
                    'subject_id' => $slot['subject_id'],
                    'room_id' => $slot['room_id'] ?? null,
                    'block_cycle' => $slot['block_cycle'] ?? 1,
                    'block_cycle_id' => $slot['block_cycle_id'] ?? null,
                ];
                continue;
            }

            if (self::canMergeSlots($current, $slot)) {
                $current['end_period'] = (int)$slot['period'];
            } else {
                $sessions[] = self::finalizeSessionTimes($current, $lessonPeriods);
                $current = [
                    'class_id' => $slot['class_id'],
                    'day' => $day,
                    'start_period' => (int)$slot['period'],
                    'end_period' => (int)$slot['period'],
                    'teacher_id' => $slot['teacher_id'],
                    'subject_id' => $slot['subject_id'],
                    'room_id' => $slot['room_id'] ?? null,
                    'block_cycle' => $slot['block_cycle'] ?? 1,
                    'block_cycle_id' => $slot['block_cycle_id'] ?? null,
                ];
            }
        }

        if ($current) {
            $sessions[] = self::finalizeSessionTimes($current, $lessonPeriods);
        }

        return $sessions;
    }

    /**
     * Hitung start_time dan end_time presisi berdasarkan tabel LessonHour
     */
    protected static function finalizeSessionTimes(array $session, array $lessonPeriods): array
    {
        $startP = $session['start_period'];
        $endP = $session['end_period'];

        $startTime = $lessonPeriods[$startP]['start'] ?? '07:15';
        $endTime = $lessonPeriods[$endP]['end'] ?? '15:10';

        if (strlen($startTime) === 5) $startTime .= ':00';
        if (strlen($endTime) === 5) $endTime .= ':00';

        $session['start_time'] = $startTime;
        $session['end_time'] = $endTime;

        return $session;
    }

    /**
     * Sinkronisasi dan agregasi dinamis jadwal untuk SEMUA KELAS pada siklus tertentu
     * berdasarkan matriks denah ruangan resmi (PDF/Array) dan pemisahan batas istirahat.
     */
    public static function syncAllClassesWithMatrix(int $cycleNumber, array $matrixMap): int
    {
        $allClasses = SchoolClass::all();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $blockCycleRecord = \App\Models\BlockCycle::where('cycle', $cycleNumber)->first()
            ?: \App\Models\BlockCycle::find($cycleNumber);
        $blockCycleId = $blockCycleRecord?->id ?? $cycleNumber;

        $totalAggregatedSessions = 0;

        DB::transaction(function () use ($allClasses, $days, $cycleNumber, $blockCycleId, $matrixMap, &$totalAggregatedSessions) {
            foreach ($allClasses as $cls) {
                foreach ($days as $day) {
                    $existingSchedules = Schedule::where('class_id', $cls->id)
                        ->where('block_cycle', $cycleNumber)
                        ->where('day', $day)
                        ->orderBy('start_period')
                        ->get();

                    if ($existingSchedules->isEmpty()) {
                        continue;
                    }

                    // Pecah ke 1-period slots
                    $slots = [];
                    foreach ($existingSchedules as $sched) {
                        for ($p = (int)$sched->start_period; $p <= (int)$sched->end_period; $p++) {
                            $roomObj = $matrixMap[$cls->id][$day][$p] ?? null;
                            $roomId = $roomObj ? $roomObj->id : $sched->room_id;

                            $teacherId = $sched->teacher_id;
                            $subjectId = $sched->subject_id;

                            // Jika ruangan jam ini berganti ke Lab (L.XX) sementara jadwal saat ini adalah teori,
                            // sesuaikan mapel & guru ke sesi praktik lab yang bersangkutan.
                            if ($roomObj && str_starts_with($roomObj->name, 'L.')) {
                                foreach ($existingSchedules as $otherSched) {
                                    if ($otherSched->id !== $sched->id && ($otherSched->room_id === $roomObj->id || ($otherSched->room && $otherSched->room->name === $roomObj->name))) {
                                        $teacherId = $otherSched->teacher_id;
                                        $subjectId = $otherSched->subject_id;
                                        break;
                                    }
                                }
                            }

                            $slots[] = [
                                'class_id' => $cls->id,
                                'period' => $p,
                                'teacher_id' => $teacherId,
                                'subject_id' => $subjectId,
                                'room_id' => $roomId,
                                'block_cycle' => $cycleNumber,
                                'block_cycle_id' => $blockCycleId,
                            ];
                        }
                    }

                    // Lakukan agregasi dinamis dengan aturan:
                    // 1. Guru sama
                    // 2. Mapel sama
                    // 3. Ruangan sama persis
                    // 4. TIDAK melintasi istirahat I (3-4) atau istirahat II (6-7)
                    $aggregated = self::aggregateSlots($slots, $day);

                    // Hapus jadwal lama untuk rombel, siklus, dan hari ini
                    Schedule::where('class_id', $cls->id)
                        ->where('block_cycle', $cycleNumber)
                        ->where('day', $day)
                        ->delete();

                    // Simpan sesi-sesi hasil agregasi presisi
                    foreach ($aggregated as $sess) {
                        Schedule::create([
                            'class_id' => $sess['class_id'],
                            'teacher_id' => $sess['teacher_id'],
                            'subject_id' => $sess['subject_id'],
                            'room_id' => $sess['room_id'],
                            'day' => $sess['day'],
                            'block_cycle' => $sess['block_cycle'],
                            'block_cycle_id' => $sess['block_cycle_id'],
                            'start_period' => $sess['start_period'],
                            'end_period' => $sess['end_period'],
                            'start_time' => $sess['start_time'],
                            'end_time' => $sess['end_time'],
                        ]);
                        $totalAggregatedSessions++;
                    }
                }
            }
        });

        return $totalAggregatedSessions;
    }
}
