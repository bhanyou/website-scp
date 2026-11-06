<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolParent;

class SchoolParentSeeder extends Seeder
{
    public function run(): void
    {
        $orangTua = User::where('email', 'orangtua@sch.com')->first();

        SchoolParent::create([
            'user_id' => $orangTua->id,
            'name' => 'Orang Tua',
            'phone' => '081234567894',
            'address' => 'Alamat Orang Tua',
        ]);
    }
}