<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Room;
use App\Models\Schedule;

class RoomSeeder extends Seeder
{
    /**
     * Jalankan seeder master data seluruh ruangan resmi SMKN 1 Bantul:
     * 1. Ruang Praktik/Khusus: L.1 s/d L.22, L.IPA, L.PB, OR, HALL
     * 2. Ruang Teori: R. 1 s/d R. 34
     */
    public function run(): void
    {
        $rooms = [];

        // 2. Ruang Praktik / Khusus (Resmi SMKN 1 Bantul)
        // a. L.1 s/d L.22 (Laboratorium Komputer 1 - 22)
        for ($i = 1; $i <= 22; $i++) {
            $rooms[] = [
                'name' => "L.{$i}",
                'type' => 'Praktik',
                'capacity' => 36,
            ];
        }

        // b. L.IPA (Laboratorium IPA)
        $rooms[] = [
            'name' => 'L.IPA',
            'type' => 'Praktik',
            'capacity' => 36,
        ];

        // c. L.PB (Laboratorium Penataan Barang)
        $rooms[] = [
            'name' => 'L.PB',
            'type' => 'Praktik',
            'capacity' => 36,
        ];

        // d. OR (Lapangan Olahraga) beserta slot OR1, OR2, OR3
        foreach (['OR', 'OR1', 'OR2', 'OR3'] as $orName) {
            $rooms[] = [
                'name' => $orName,
                'type' => 'Olahraga',
                'capacity' => 72,
            ];
        }

        // e. HALL (Ruang Hall)
        $rooms[] = [
            'name' => 'HALL',
            'type' => 'Praktik',
            'capacity' => 50,
        ];

        // 3. Ruang Teori: R. 1 s/d R. 48 (format "R. [nomor]")
        for ($i = 1; $i <= 48; $i++) {
            $rooms[] = [
                'name' => "R. {$i}",
                'type' => 'Teori',
                'capacity' => 36,
            ];
        }

        // Simpan seluruh data ke database tanpa merusak data relasi yang sudah ada
        foreach ($rooms as $item) {
            Room::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
