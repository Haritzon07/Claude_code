@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 text-primary-institucional">
                <i class="fas fa-home"></i> Dashboard - Cronograma Institucional
            </h1>
            <p class="text-muted">Bienvenido al Sistema de Cronograma Institucional de {{ config('app.institution.name') }}</p>
        </div>
    </div>

    <!-- Información del Período Actual -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-primary-institucional">
                <div class="card-header bg-primary-institucional text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Año Escolar Actual</h5>
                </div>
                <div class="card-body">
                    @if($currentYear)
                        <h4>{{ $currentYear->name }}</h4>
                        <p class="mb-1"><strong>Período:</strong> {{ $currentYear->start_date->format('d/m/Y') }} - {{ $currentYear->end_date->format('d/m/Y') }}</p>
                        @if($currentYear->description)
                            <p class="text-muted mb-0">{{ $currentYear->description }}</p>
                        @endif
                    @else
                        <p class="text-muted">No hay año escolar activo</p>
                        <a href="{{ route('academic-years.create') }}" class="btn btn-primary-institucional">
                            <i class="fas fa-plus"></i> Crear Año Escolar
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-secondary-institucional">
                <div class="card-header bg-secondary-institucional text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-week"></i> Período Académico Actual</h5>
                </div>
                <div class="card-body">
                    @if($currentPeriod)
                        <h4>{{ $currentPeriod->name }}</h4>
                        <p class="mb-1"><strong>Período:</strong> {{ $currentPeriod->start_date->format('d/m/Y') }} - {{ $currentPeriod->end_date->format('d/m/Y') }}</p>
                        @if($currentPeriod->description)
                            <p class="text-muted mb-0">{{ $currentPeriod->description }}</p>
                        @endif
                    @else
                        <p class="text-muted">No hay período académico activo</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary-institucional h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Docentes</h6>
                            <h2 class="mb-0">{{ $stats['total_teachers'] }}</h2>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('teachers.index') }}" class="text-white text-decoration-none">
                        Ver todos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-secondary-institucional h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Materias</h6>
                            <h2 class="mb-0">{{ $stats['total_subjects'] }}</h2>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('subjects.index') }}" class="text-white text-decoration-none">
                        Ver todas <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50 mb-1">Recursos</h6>
                            <h2 class="mb-0">{{ $stats['total_classrooms'] }}</h2>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-door-open"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('classrooms.index') }}" class="text-white text-decoration-none">
                        Ver todos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-accent-institucional h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-dark mb-1">Horarios</h6>
                            <h2 class="mb-0 text-dark">{{ $stats['total_schedules'] }}</h2>
                        </div>
                        <div class="fs-1 opacity-50 text-dark">
                            <i class="fas fa-table"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('schedules.index') }}" class="text-dark text-decoration-none">
                        Ver todos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximas Actividades -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-institucional text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check"></i> Próximas Actividades
                    </h5>
                </div>
                <div class="card-body">
                    @if($upcomingActivities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Fecha/Hora</th>
                                        <th>Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingActivities as $activity)
                                        <tr>
                                            <td>
                                                <span class="badge me-2" style="background-color: {{ $activity->color }}"></span>
                                                <strong>{{ $activity->title }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</span>
                                            </td>
                                            <td>
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $activity->start_datetime->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                @if($activity->location)
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $activity->location }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('activities.index') }}" class="btn btn-primary-institucional">
                                <i class="fas fa-list"></i> Ver Todas las Actividades
                            </a>
                            <a href="{{ route('calendar') }}" class="btn btn-outline-primary-institucional">
                                <i class="fas fa-calendar"></i> Ver Calendario
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay actividades próximas</p>
                            <a href="{{ route('activities.create') }}" class="btn btn-primary-institucional">
                                <i class="fas fa-plus"></i> Crear Actividad
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="row mt-4">
        <div class="col-12">
            <h4 class="mb-3"><i class="fas fa-bolt"></i> Accesos Rápidos</h4>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 text-center hover-shadow">
                <div class="card-body">
                    <i class="fas fa-plus-circle fa-3x text-primary-institucional mb-3"></i>
                    <h5>Crear Horario</h5>
                    <p class="text-muted small">Crear un nuevo horario para un grado</p>
                    <a href="{{ route('schedules.create') }}" class="btn btn-sm btn-primary-institucional">Ir</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 text-center hover-shadow">
                <div class="card-body">
                    <i class="fas fa-calendar-plus fa-3x text-secondary-institucional mb-3"></i>
                    <h5>Nueva Actividad</h5>
                    <p class="text-muted small">Agregar evento o actividad</p>
                    <a href="{{ route('activities.create') }}" class="btn btn-sm btn-secondary-institucional">Ir</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 text-center hover-shadow">
                <div class="card-body">
                    <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                    <h5>Nuevo Docente</h5>
                    <p class="text-muted small">Registrar un nuevo docente</p>
                    <a href="{{ route('teachers.create') }}" class="btn btn-sm btn-success">Ir</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 text-center hover-shadow">
                <div class="card-body">
                    <i class="fas fa-file-alt fa-3x text-info mb-3"></i>
                    <h5>Reportes</h5>
                    <p class="text-muted small">Ver estadísticas y reportes</p>
                    <a href="{{ route('reportes.index') }}" class="btn btn-sm btn-info">Ir</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
