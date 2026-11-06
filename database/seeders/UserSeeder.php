<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Waka Kurikulum',
            'username' => 'waka',
            'email' => 'waka@sch.com',
            'password' => Hash::make('password'),
            'role' => 'waka',
            'phone' => '081234567890',
        ]);

        User::create([
            'name' => 'Guru',
            'username' => 'guru1',
            'email' => 'guru@sch.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'phone' => '081234567891',
        ]);

        User::create([
            'name' => 'Siswa',
            'username' => 'siswa1',
            'email' => 'siswa@sch.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'phone' => '081234567893',
        ]);

        User::create([
            'name' => 'Orang Tua',
            'username' => 'orangtua',
            'email' => 'orangtua@sch.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'phone' => '081234567894',
        ]);
    }
}