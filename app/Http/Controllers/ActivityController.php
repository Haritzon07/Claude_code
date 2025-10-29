<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['academicYear', 'classroom', 'creator']);

        if ($request->has('type') && $request->type != 'todos') {
            $query->where('type', $request->type);
        }

        if ($request->has('year_id')) {
            $query->where('academic_year_id', $request->year_id);
        }

        $activities = $query->orderBy('start_datetime', 'desc')->paginate(20);
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        $activityTypes = [
            'entrega_boletines' => 'Entrega de Boletines',
            'reunion_padres' => 'Reunión de Padres',
            'evaluacion' => 'Evaluación',
            'examen' => 'Examen',
            'acto_civico' => 'Acto Cívico',
            'izada_bandera' => 'Izada de Bandera',
            'cultural' => 'Cultural',
            'deportivo' => 'Deportivo',
            'extracurricular' => 'Extracurricular',
            'institucional' => 'Institucional',
            'otro' => 'Otro',
        ];

        return view('actividades.index', compact('activities', 'academicYears', 'activityTypes'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $classrooms = Classroom::available()->get();

        return view('actividades.create', compact('academicYears', 'classrooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'participants' => 'nullable|json',
            'color' => 'nullable|string|max:7',
            'is_public' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        if (isset($validated['participants'])) {
            $validated['participants'] = json_decode($validated['participants'], true);
        }

        $activity = Activity::create($validated);

        return redirect()->route('activities.index')
            ->with('success', 'Actividad creada exitosamente.');
    }

    public function show(Activity $activity)
    {
        $activity->load(['academicYear', 'classroom', 'creator']);
        return view('actividades.show', compact('activity'));
    }

    public function edit(Activity $activity)
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $classrooms = Classroom::available()->get();

        return view('actividades.edit', compact('activity', 'academicYears', 'classrooms'));
    }

    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'participants' => 'nullable|json',
            'color' => 'nullable|string|max:7',
            'is_public' => 'boolean',
        ]);

        if (isset($validated['participants'])) {
            $validated['participants'] = json_decode($validated['participants'], true);
        }

        $activity->update($validated);

        return redirect()->route('activities.index')
            ->with('success', 'Actividad actualizada exitosamente.');
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('activities.index')
            ->with('success', 'Actividad eliminada exitosamente.');
    }

    public function calendar(Request $request)
    {
        $startDate = $request->input('start', now()->startOfMonth());
        $endDate = $request->input('end', now()->endOfMonth());

        $activities = Activity::betweenDates($startDate, $endDate)
            ->where('is_public', true)
            ->with(['classroom'])
            ->get();

        // Formatear para FullCalendar
        $events = $activities->map(function($activity) {
            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'start' => $activity->start_datetime->toIso8601String(),
                'end' => $activity->end_datetime ? $activity->end_datetime->toIso8601String() : null,
                'allDay' => $activity->all_day,
                'color' => $activity->color,
                'extendedProps' => [
                    'type' => $activity->type,
                    'location' => $activity->location,
                    'description' => $activity->description,
                ],
            ];
        });

        return response()->json($events);
    }
}
