<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')->constrained()->onDelete('cascade');
            $table->integer('week_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_exam_week')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_weeks');
    }
};
