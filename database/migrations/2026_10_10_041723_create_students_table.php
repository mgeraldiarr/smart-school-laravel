<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique(); // Login Orang Tua via NIS
            $table->string('full_name');
            $table->date('birth_date'); // Tanggal lahir verifikasi (YYYY-MM-DD)
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->string('photo_url')->nullable(); // Foto master
            $table->text('face_embedding')->nullable(); // Vektor biometrik AI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
