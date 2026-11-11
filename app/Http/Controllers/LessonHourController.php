<?php

namespace App\Http\Controllers;

use App\Models\LessonHour;
use App\Models\Schedule;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonHourController extends Controller
{
    private function ensureWaka(): void
    {
        if (Auth::user()->role !== 'waka') {
            abort(403, 'Akses khusus Waka Kurikulum.');
        }
    }

    /**
     * Menampilkan konfigurasi alokasi waktu sesi jam pelajaran.
     */
    public function index(Request $request)
    {
        $this->ensureWaka();

        // Pastikan data awal tersedia
        if (LessonHour::count() === 0) {
            LessonHour::seedDefaults();
        }

        $availableDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $selectedDay = $request->query('day', 'Senin');
        if (!in_array($selectedDay, $availableDays)) {
            $selectedDay = 'Senin';
        }

        $mondayMode = LessonHour::getMondayMode();
        $selectedDayType = $request->query('day_type');

        if ($selectedDay === 'Senin') {
            $selectedDayType = $selectedDayType ?: $mondayMode;
        } else {
            $selectedDayType = 'default';
        }

        $lessonHours = LessonHour::where('day', $selectedDay)
            ->where('day_type', $selectedDayType)
            ->orderBy('period_number')
            ->get();

        // Jika data untuk day_type ini masih kosong, sinkronkan dari default
        if ($lessonHours->isEmpty()) {
            LessonHour::seedDefaults();
            $lessonHours = LessonHour::where('day', $selectedDay)
                ->where('day_type', $selectedDayType)
                ->orderBy('period_number')
                ->get();
        }

        return view('lesson_hours.index', compact(
            'availableDays',
            'selectedDay',
            'selectedDayType',
            'mondayMode',
            'lessonHours'
        ));
    }

    /**
     * Memperbarui jam sesi pelajaran.
     */
    public function update(Request $request)
    {
        $this->ensureWaka();

        $validated = $request->validate([
            'day' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat'],
            'day_type' => ['required', 'string', 'in:default,upacara,non_upacara'],
            'periods' => ['required', 'array'],
            'periods.*.start_time' => ['required', 'string'],
            'periods.*.end_time' => ['required', 'string'],
        ]);

        foreach ($validated['periods'] as $periodNumber => $times) {
            $start = trim($times['start_time']);
            $end = trim($times['end_time']);

            // Hitung durasi dalam menit
            $startMinutes = (int) substr($start, 0, 2) * 60 + (int) substr($start, 3, 2);
            $endMinutes = (int) substr($end, 0, 2) * 60 + (int) substr($end, 3, 2);
            $duration = max(0, $endMinutes - $startMinutes);

            LessonHour::updateOrCreate(
                [
                    'day' => $validated['day'],
                    'day_type' => $validated['day_type'],
                    'period_number' => (int) $periodNumber,
                ],
                [
                    'label' => "Jam ke-{$periodNumber}",
                    'start_time' => $start,
                    'end_time' => $end,
                    'duration_minutes' => $duration ?: 40,
                    'is_active' => true,
                ]
            );
        }

        // Otomatis sinkronkan seluruh jadwal pelajaran di hari ini agar jam mulai & selesai langsung ikut berubah
        $this->syncSchedulesWithLessonHours($validated['day']);

        return redirect()
            ->route('lesson-hours.index', [
                'day' => $validated['day'],
                'day_type' => $validated['day_type']
            ])
            ->with('success', "Alokasi jam pelajaran hari {$validated['day']} berhasil diperbarui dan jadwal pelajaran telah disinkronkan otomatis.");
    }

    /**
     * Beralih mode hari Senin (Upacara 35 menit vs Normal 40 menit).
     */
    public function toggleMondayMode(Request $request)
    {
        $this->ensureWaka();

        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:upacara,non_upacara'],
        ]);

        Setting::updateOrCreate(
            ['key' => 'monday_schedule_mode'],
            ['value' => $validated['mode']]
        );

        $labelMode = $validated['mode'] === 'upacara' ? 'Upacara (Durasi 35 Menit)' : 'Normal / Tanpa Upacara (Durasi 40 Menit)';

        // Sinkronkan jadwal hari Senin sesuai mode baru
        $this->syncSchedulesWithLessonHours('Senin');

        return redirect()
            ->route('lesson-hours.index', [
                'day' => 'Senin',
                'day_type' => $validated['mode']
            ])
            ->with('success', "Mode Hari Senin aktif berhasil diubah menjadi: {$labelMode} dan seluruh jadwal hari Senin telah disinkronkan.");
    }

    /**
     * Kembalikan alokasi waktu ke default resmi SMKN 1 Bantul.
     */
    public function resetDefaults(Request $request)
    {
        $this->ensureWaka();

        $day = $request->input('day');
        LessonHour::seedDefaults();

        // Sinkronkan kembali jadwal setelah reset
        $this->syncSchedulesWithLessonHours($day ?: null);

        return redirect()
            ->route('lesson-hours.index', ['day' => $day ?: 'Senin'])
            ->with('success', 'Alokasi waktu kegiatan pembelajaran berhasil direset dan jadwal telah disinkronkan kembali.');
    }

    /**
     * Otomatis sinkronkan start_time dan end_time seluruh jadwal pelajaran yang terikat sesi jam ini.
     */
    private function syncSchedulesWithLessonHours(?string $day = null): void
    {
        $days = $day ? [$day] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        foreach ($days as $d) {
            $dayPeriods = LessonHour::getPeriodsForDay($d);
            if (empty($dayPeriods)) continue;

            $schedules = Schedule::where('day', $d)
                ->whereNotNull('start_period')
                ->whereNotNull('end_period')
                ->get();

            foreach ($schedules as $sched) {
                if (isset($dayPeriods[$sched->start_period]) && isset($dayPeriods[$sched->end_period])) {
                    $newStart = $dayPeriods[$sched->start_period]['start'];
                    $newEnd = $dayPeriods[$sched->end_period]['end'];
                    if ($sched->start_time !== $newStart || $sched->end_time !== $newEnd) {
                        $sched->update([
                            'start_time' => $newStart,
                            'end_time'   => $newEnd,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * API untuk mengambil sesi berdasarkan hari.
     */
    public function getPeriodsApi(string $day)
    {
        $periods = LessonHour::getPeriodsForDay($day);
        return response()->json([
            'day' => $day,
            'periods' => $periods,
        ]);
    }
}
