<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Akun hasil impor Excel memakai kata sandi default yang sama untuk semua
 * orang. Penanda ini memaksa pengguna mengganti sandi saat login pertama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });

        // Akun guru/wali yang sudah ada belum pernah dipaksa ganti sandi.
        DB::table('users')
            ->whereIn('role', ['teacher', 'parent'])
            ->update(['must_change_password' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
