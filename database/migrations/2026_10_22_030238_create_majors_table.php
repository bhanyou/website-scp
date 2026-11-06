<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $initial = [
            ['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak'],
            ['code' => 'DKV', 'name' => 'Desain Komunikasi Visual'],
            ['code' => 'PPLG', 'name' => 'Pengembangan Perangkat Lunak dan Gim'],
            ['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan'],
            ['code' => 'AKL', 'name' => 'Akuntansi dan Keuangan Lembaga'],
            ['code' => 'MPLB', 'name' => 'Manajemen Perkantoran dan Layanan Bisnis'],
        ];

        foreach ($initial as $item) {
            DB::table('majors')->insertOrIgnore([
                'code' => $item['code'],
                'name' => $item['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majors');
    }
};
