<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlockCycle;

class BlockCycleSeeder extends Seeder
{
    public function run(): void
    {
        BlockCycle::create([
            'cycle' => '1',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
            'is_locked' => false,
            'locked_by' => null,
            'locked_at' => null,
        ]);

        BlockCycle::create([
            'cycle' => '2',
            'start_date' => '2027-01-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
            'is_locked' => false,
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }
}