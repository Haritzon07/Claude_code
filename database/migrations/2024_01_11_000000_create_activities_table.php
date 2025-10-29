<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                'entrega_boletines',
                'reunion_padres',
                'evaluacion',
                'examen',
                'acto_civico',
                'izada_bandera',
                'cultural',
                'deportivo',
                'extracurricular',
                'institucional',
                'otro'
            ])->default('otro');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->foreignId('classroom_id')->nullable()->constrained()->onDelete('set null');
            $table->text('participants')->nullable(); // JSON: grados, grupos, docentes específicos
            $table->string('color')->default('#2d5f3f');
            $table->boolean('is_public')->default(true); // Visible para todos
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
