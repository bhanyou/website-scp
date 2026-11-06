<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BkClassAssignmentSeeder extends Seeder
{
    /**
     * Petakan guru BK ke kelas masing-masing dan buatkan akun login mandiri bagi setiap Guru BK (tanpa email).
     */
    public function run(): void
    {
        $bkTeachers = [
            [
                'name' => 'CANDRA RAHADIAN, S.Pd',
                'username' => 'candra',
                'nip' => '199610112024211003',
                'password' => 'password',
                'classes' => [
                    'X AK 1', 'X AK 2', 'X AK 3', 'X TKJ 2',
                    'XI AK 3', 'XI LPS 2', 'XI TKJ 2',
                    'XII AK 4', 'XII LPS', 'XII TKJ 2',
                ],
            ],
            [
                'name' => 'NURHIDAYAT SULISTIAWAN, S.Pd',
                'username' => 'nurhidayat',
                'nip' => '198610062022211003',
                'password' => 'password',
                'classes' => [
                    'X LPS 1', 'X TKJ 1', 'X DKV 1', 'X DKV 2',
                    'XI TKJ 1', 'XI DKV 1', 'XI DKV 2',
                    'XII TKJ 1', 'XII DKV 1', 'XII DKV 2',
                ],
            ],
            [
                'name' => 'AISYAH WULANDARI, S.Pd',
                'username' => 'aisyah',
                'nip' => '197210221995122001',
                'password' => 'password',
                'classes' => [
                    'X MP 1', 'X MP 2',
                    'XI AK 1', 'XI AK 2', 'XI LPS 1', 'XI MP 1', 'XI MP 2',
                    'XII AK 3', 'XII MP 1', 'XII MP 2',
                ],
            ],
            [
                'name' => 'RYAWIDYANINGTYAS, S.Pd.',
                'username' => 'ryawidyaningtyas',
                'nip' => '199503272022212001',
                'password' => 'password',
                'classes' => [
                    'X LPS 2', 'X RPL 1', 'X RPL 2',
                    'XI RPL 1', 'XI RPL 2',
                    'XII AK 1', 'XII AK 2', 'XII RPL 1', 'XII RPL 2',
                ],
            ],
            [
                'name' => 'ALIVI SAZKIA HAWIE, S.Pd',
                'username' => 'alivi',
                'nip' => '199903052024212007',
                'password' => 'password',
                'classes' => [
                    'X BR 1', 'X BR 2', 'X BD',
                    'XI BR 1', 'XI BR 2', 'XI BD',
                    'XII BR 1', 'XII BR 2', 'XII BD',
                ],
            ],
        ];

        DB::transaction(function () use ($bkTeachers) {
            foreach ($bkTeachers as $item) {
                $cleanName = trim(explode(',', $item['name'])[0]);
                
                // Cari akun User atau Guru yang sudah ada berdasarkan username atau NIP atau nama
                $user = User::where('username', $item['username'])
                    ->orWhere('name', 'like', "%{$cleanName}%")
                    ->first();

                if ($user) {
                    $user->update([
                        'name' => $item['name'],
                        'username' => $item['username'],
                        'email' => null,
                        'role' => 'bk',
                        'password' => Hash::make($item['password']),
                    ]);
                } else {
                    $user = User::create([
                        'name' => $item['name'],
                        'username' => $item['username'],
                        'email' => null,
                        'role' => 'bk',
                        'password' => Hash::make($item['password']),
                    ]);
                }

                $teacher = Teacher::where('user_id', $user->id)
                    ->orWhere('nip', $item['nip'])
                    ->orWhere('name', 'like', "%{$cleanName}%")
                    ->first();

                if ($teacher) {
                    $teacher->update([
                        'user_id' => $user->id,
                        'name' => $item['name'],
                        'username' => $item['username'],
                        'nip' => $item['nip'],
                        'mapel' => 'BIMBINGAN KONSELING',
                    ]);
                } else {
                    $teacher = Teacher::create([
                        'user_id' => $user->id,
                        'name' => $item['name'],
                        'username' => $item['username'],
                        'nip' => $item['nip'],
                        'mapel' => 'BIMBINGAN KONSELING',
                    ]);
                }

                // Petakan kelas binaan guru BK
                foreach ($item['classes'] as $className) {
                    $class = $this->resolveClass($className);
                    if ($class) {
                        $class->update(['bk_teacher_id' => $teacher->id]);
                    } else {
                        $this->command?->warn("Kelas '{$className}' tidak ditemukan.");
                    }
                }

                $this->command?->info("Akun Guru BK '{$teacher->name}' siap: Username: '{$item['username']}' | NIP: '{$item['nip']}' (Tanpa Email).");
            }
        });
    }

    /**
     * Resolusi nama kelas fleksibel (menangani variasi penamaan AK vs AKL).
     */
    private function resolveClass(string $name): ?SchoolClass
    {
        $class = SchoolClass::where('name', $name)->first();
        if ($class) {
            return $class;
        }

        $altName = preg_replace('/\bAK\b/', 'AKL', $name);
        $class = SchoolClass::where('name', $altName)->first();
        if ($class) {
            return $class;
        }

        $altName2 = preg_replace('/\bAKL\b/', 'AK', $name);
        return SchoolClass::where('name', $altName2)->first();
    }
}
