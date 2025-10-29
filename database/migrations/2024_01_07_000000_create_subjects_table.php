<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#2d5f3f'); // Color para visualización en calendario
            $table->integer('weekly_hours')->default(1); // Horas semanales
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Materias por grado
        Schema::create('grade_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->integer('grade'); // 6, 7, 8, 9, 10, 11
            $table->integer('weekly_hours'); // Puede variar por grado
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->unique(['subject_id', 'grade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_subjects');
        Schema::dropIfExists('subjects');
    }
};
