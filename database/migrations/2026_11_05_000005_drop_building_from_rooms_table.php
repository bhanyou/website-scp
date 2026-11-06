<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('rooms', 'building')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('building');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('rooms', 'building')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('building')->nullable()->after('name');
            });
        }
    }
};

