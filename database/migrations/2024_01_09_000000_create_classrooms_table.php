<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // "S101", "LAB1"
            $table->string('name'); // "Salón 101", "Laboratorio de Química"
            $table->enum('type', ['aula', 'laboratorio', 'auditorio', 'deportivo', 'biblioteca', 'sala_multiple'])->default('aula');
            $table->integer('capacity')->default(40);
            $table->string('building')->nullable(); // Edificio o bloque
            $table->string('floor')->nullable();
            $table->text('equipment')->nullable(); // Equipamiento disponible
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
