<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bloques de tiempo (períodos de clase)
        Schema::create('time_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "1° Hora", "2° Hora", "Recreo"
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('order'); // Orden en el día
            $table->enum('type', ['clase', 'recreo', 'almuerzo', 'descanso'])->default('clase');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Horarios (uno por grado y grupo)
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->onDelete('cascade');
            $table->integer('grade'); // 6, 7, 8, 9, 10, 11
            $table->integer('group'); // 1, 2, 3
            $table->string('name'); // "Horario 6-1", "Horario 11-2"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['academic_period_id', 'grade', 'group']);
        });

        // Bloques de clase (asignaciones específicas)
        Schema::create('schedule_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('time_block_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'])->default('lunes');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('classroom_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_blocks');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('time_blocks');
    }
};
