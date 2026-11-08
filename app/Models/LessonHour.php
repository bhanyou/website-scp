<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonHour extends Model
{
    protected $fillable = [
        'day',
        'period_number',
        'label',
        'start_time',
        'end_time',
        'duration_minutes',
        'day_type',
        'is_active',
    ];

    /**
     * Konfigurasi Standar Alokasi Waktu
     */
    public static function getDefaultData(): array
    {
        return [
            // SENIN - MODE UPACARA (35 Menit)
            'Senin' => [
                'upacara' => [
                    1  => ['start' => '08:00', 'end' => '08:35', 'duration' => 35],
                    2  => ['start' => '08:35', 'end' => '09:10', 'duration' => 35],
                    3  => ['start' => '09:10', 'end' => '09:45', 'duration' => 35],
                    // Istirahat I: 09:45 - 10:00 (15')
                    4  => ['start' => '10:00', 'end' => '10:35', 'duration' => 35],
                    5  => ['start' => '10:35', 'end' => '11:10', 'duration' => 35],
                    6  => ['start' => '11:10', 'end' => '11:45', 'duration' => 35],
                    // Ishoma: 11:45 - 12:45 (60')
                    7  => ['start' => '12:45', 'end' => '13:25', 'duration' => 40],
                    8  => ['start' => '13:25', 'end' => '14:05', 'duration' => 40],
                    9  => ['start' => '14:05', 'end' => '14:45', 'duration' => 40],
                    10 => ['start' => '14:45', 'end' => '15:25', 'duration' => 40],
                ],
                'non_upacara' => [
                    1  => ['start' => '07:15', 'end' => '07:55', 'duration' => 40],
                    2  => ['start' => '07:55', 'end' => '08:35', 'duration' => 40],
                    3  => ['start' => '08:35', 'end' => '09:15', 'duration' => 40],
                    // Istirahat I: 09:15 - 09:30 (15')
                    4  => ['start' => '09:30', 'end' => '10:10', 'duration' => 40],
                    5  => ['start' => '10:10', 'end' => '10:50', 'duration' => 40],
                    6  => ['start' => '10:50', 'end' => '11:30', 'duration' => 40],
                    // Ishoma: 11:30 - 12:30 (60')
                    7  => ['start' => '12:30', 'end' => '13:10', 'duration' => 40],
                    8  => ['start' => '13:10', 'end' => '13:50', 'duration' => 40],
                    9  => ['start' => '13:50', 'end' => '14:30', 'duration' => 40],
                    10 => ['start' => '14:30', 'end' => '15:10', 'duration' => 40],
                ],
            ],

            // SELASA - ADA LITERASI (07:00 - 07:30)
            'Selasa' => [
                'default' => [
                    1  => ['start' => '07:30', 'end' => '08:10', 'duration' => 40],
                    2  => ['start' => '08:10', 'end' => '08:50', 'duration' => 40],
                    3  => ['start' => '08:50', 'end' => '09:30', 'duration' => 40],
                    // Istirahat I: 09:30 - 09:45 (15')
                    4  => ['start' => '09:45', 'end' => '10:25', 'duration' => 40],
                    5  => ['start' => '10:25', 'end' => '11:05', 'duration' => 40],
                    6  => ['start' => '11:05', 'end' => '11:45', 'duration' => 40],
                    // Ishoma: 11:45 - 12:45 (60')
                    7  => ['start' => '12:45', 'end' => '13:25', 'duration' => 40],
                    8  => ['start' => '13:25', 'end' => '14:05', 'duration' => 40],
                    9  => ['start' => '14:05', 'end' => '14:45', 'duration' => 40],
                    10 => ['start' => '14:45', 'end' => '15:25', 'duration' => 40],
                ],
            ],

            // RABU - 40 MENIT (JAM 1 S/D 10)
            'Rabu' => [
                'default' => [
                    1  => ['start' => '07:15', 'end' => '07:55', 'duration' => 40],
                    2  => ['start' => '07:55', 'end' => '08:35', 'duration' => 40],
                    3  => ['start' => '08:35', 'end' => '09:15', 'duration' => 40],
                    // Istirahat I: 09:15 - 09:30 (15')
                    4  => ['start' => '09:30', 'end' => '10:10', 'duration' => 40],
                    5  => ['start' => '10:10', 'end' => '10:50', 'duration' => 40],
                    6  => ['start' => '10:50', 'end' => '11:30', 'duration' => 40],
                    // Ishoma: 11:30 - 12:30 (60')
                    7  => ['start' => '12:30', 'end' => '13:10', 'duration' => 40],
                    8  => ['start' => '13:10', 'end' => '13:50', 'duration' => 40],
                    9  => ['start' => '13:50', 'end' => '14:30', 'duration' => 40],
                    10 => ['start' => '14:30', 'end' => '15:10', 'duration' => 40],
                ],
            ],

            // KAMIS - 40 MENIT (JAM 1 S/D 10)
            'Kamis' => [
                'default' => [
                    1  => ['start' => '07:15', 'end' => '07:55', 'duration' => 40],
                    2  => ['start' => '07:55', 'end' => '08:35', 'duration' => 40],
                    3  => ['start' => '08:35', 'end' => '09:15', 'duration' => 40],
                    // Istirahat I: 09:15 - 09:30 (15')
                    4  => ['start' => '09:30', 'end' => '10:10', 'duration' => 40],
                    5  => ['start' => '10:10', 'end' => '10:50', 'duration' => 40],
                    6  => ['start' => '10:50', 'end' => '11:30', 'duration' => 40],
                    // Ishoma: 11:30 - 12:30 (60')
                    7  => ['start' => '12:30', 'end' => '13:10', 'duration' => 40],
                    8  => ['start' => '13:10', 'end' => '13:50', 'duration' => 40],
                    9  => ['start' => '13:50', 'end' => '14:30', 'duration' => 40],
                    10 => ['start' => '14:30', 'end' => '15:10', 'duration' => 40],
                ],
            ],

            // JUMAT - HANYA JAM 1 S/D 8
            'Jumat' => [
                'default' => [
                    1  => ['start' => '07:15', 'end' => '07:55', 'duration' => 40],
                    2  => ['start' => '07:55', 'end' => '08:35', 'duration' => 40],
                    3  => ['start' => '08:35', 'end' => '09:15', 'duration' => 40],
                    // Istirahat: 09:00 - 09:15 (15')
                    4  => ['start' => '09:30', 'end' => '10:10', 'duration' => 40],
                    5  => ['start' => '10:10', 'end' => '10:50', 'duration' => 40],
                    6  => ['start' => '10:50', 'end' => '11:25', 'duration' => 35],
                    // Shalat Jumat & Istirahat: 11:15 - 12:55
                    7  => ['start' => '12:40', 'end' => '13:15', 'duration' => 35],
                    8  => ['start' => '13:15', 'end' => '13:50', 'duration' => 35],
                ],
            ],
        ];
    }

    /**
     * Seed atau Reset ke Default
     */
    public static function seedDefaults(): void
    {
        $defaults = self::getDefaultData();

        foreach ($defaults as $day => $typeData) {
            foreach ($typeData as $dayType => $periods) {
                foreach ($periods as $periodNum => $time) {
                    self::updateOrCreate(
                        [
                            'day' => $day,
                            'day_type' => $dayType,
                            'period_number' => $periodNum,
                        ],
                        [
                            'label' => "Jam ke-{$periodNum}",
                            'start_time' => $time['start'],
                            'end_time' => $time['end'],
                            'duration_minutes' => $time['duration'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Dapatkan mode hari Senin aktif ('upacara' atau 'non_upacara')
     */
    public static function getMondayMode(): string
    {
        $setting = Setting::where('key', 'monday_schedule_mode')->first();
        return $setting ? $setting->value : 'upacara';
    }

    /**
     * Ambil daftar periode untuk hari tertentu yang siap digunakan form jadwal
     */
    public static function getPeriodsForDay(string $day): array
    {
        // Pastikan database terisi
        if (self::count() === 0) {
            self::seedDefaults();
        }

        $dayType = 'default';
        if ($day === 'Senin') {
            $dayType = self::getMondayMode();
        }

        $records = self::where('day', $day)
            ->where('day_type', $dayType)
            ->where('is_active', true)
            ->orderBy('period_number')
            ->get();

        if ($records->isEmpty() && $day === 'Senin') {
            // Fallback jika mode belum ada data
            $records = self::where('day', $day)
                ->where('is_active', true)
                ->orderBy('period_number')
                ->get();
        }

        if ($records->isEmpty()) {
            // Ambil dari default data
            $defaults = self::getDefaultData();
            $dayDefaults = $defaults[$day][$dayType] ?? ($defaults[$day]['default'] ?? []);
            $result = [];
            foreach ($dayDefaults as $num => $p) {
                $result[$num] = [
                    'label' => "Jam ke-{$num}",
                    'start' => $p['start'],
                    'end' => $p['end'],
                    'duration' => $p['duration'],
                ];
            }
            return $result;
        }

        $result = [];
        foreach ($records as $r) {
            $result[$r->period_number] = [
                'label' => $r->label,
                'start' => substr((string) $r->start_time, 0, 5),
                'end' => substr((string) $r->end_time, 0, 5),
                'duration' => $r->duration_minutes,
            ];
        }

        return $result;
    }
}
