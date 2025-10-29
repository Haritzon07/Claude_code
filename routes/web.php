<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClassroomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistema de Cronograma Institucional
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Gestión de Períodos Académicos
Route::prefix('periodos')->group(function () {
    // Años escolares
    Route::resource('academic-years', AcademicYearController::class);
    Route::post('academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])
        ->name('academic-years.activate');
});

// Gestión de Horarios
Route::prefix('horarios')->group(function () {
    Route::resource('schedules', ScheduleController::class);

    // Bloques de horario
    Route::post('schedules/{schedule}/blocks', [ScheduleController::class, 'addBlock'])
        ->name('schedules.blocks.add');
    Route::put('blocks/{block}', [ScheduleController::class, 'updateBlock'])
        ->name('schedules.blocks.update');
    Route::delete('blocks/{block}', [ScheduleController::class, 'deleteBlock'])
        ->name('schedules.blocks.delete');

    // Vistas especiales
    Route::get('teacher/{teacher}', [ScheduleController::class, 'viewByTeacher'])
        ->name('schedules.teacher');
    Route::get('classroom/{classroom}', [ScheduleController::class, 'viewByClassroom'])
        ->name('schedules.classroom');
});

// Gestión de Materias
Route::resource('subjects', SubjectController::class)->names('subjects');

// Gestión de Docentes
Route::resource('teachers', TeacherController::class)->names('teachers');

// Gestión de Recursos (Salones, Laboratorios, etc.)
Route::resource('classrooms', ClassroomController::class)->names('classrooms');

// Gestión de Actividades y Eventos
Route::prefix('actividades')->group(function () {
    Route::resource('activities', ActivityController::class);
    Route::get('calendar/events', [ActivityController::class, 'calendar'])
        ->name('activities.calendar.events');
});

// Calendario General
Route::get('/calendario', function () {
    return view('actividades.calendar');
})->name('calendar');

// Reportes
Route::prefix('reportes')->group(function () {
    Route::get('/', function () {
        return view('reportes.index');
    })->name('reportes.index');

    Route::get('/carga-docente', function () {
        return view('reportes.carga-docente');
    })->name('reportes.carga-docente');

    Route::get('/ocupacion-salones', function () {
        return view('reportes.ocupacion-salones');
    })->name('reportes.ocupacion-salones');
});
