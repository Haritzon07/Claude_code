<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleBlock;
use App\Models\AcademicPeriod;
use App\Models\TimeBlock;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Services\ScheduleConflictService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $conflictService;

    public function __construct(ScheduleConflictService $conflictService)
    {
        $this->conflictService = $conflictService;
    }

    public function index(Request $request)
    {
        $query = Schedule::with(['academicPeriod']);

        if ($request->has('grade')) {
            $query->where('grade', $request->grade);
        }

        if ($request->has('period_id')) {
            $query->where('academic_period_id', $request->period_id);
        } else {
            $currentPeriod = AcademicPeriod::where('is_active', true)->first();
            if ($currentPeriod) {
                $query->where('academic_period_id', $currentPeriod->id);
            }
        }

        $schedules = $query->paginate(15);
        $periods = AcademicPeriod::orderBy('period_number')->get();

        return view('horarios.index', compact('schedules', 'periods'));
    }

    public function create()
    {
        $periods = AcademicPeriod::orderBy('period_number')->get();
        $grades = [6, 7, 8, 9, 10, 11];
        $groups = [1, 2, 3];

        return view('horarios.create', compact('periods', 'grades', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_period_id' => 'required|exists:academic_periods,id',
            'grade' => 'required|integer|min:6|max:11',
            'group' => 'required|integer|min:1|max:3',
            'name' => 'required|string|max:255',
        ]);

        // Verificar que no exista ya un horario para ese grado y grupo en ese período
        $existing = Schedule::where('academic_period_id', $validated['academic_period_id'])
            ->where('grade', $validated['grade'])
            ->where('group', $validated['group'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Ya existe un horario para ese grado y grupo en el período seleccionado.');
        }

        $schedule = Schedule::create($validated);

        return redirect()->route('schedules.edit', $schedule)
            ->with('success', 'Horario creado exitosamente. Ahora puede agregar bloques de clase.');
    }

    public function show(Schedule $schedule)
    {
        $schedule->load(['scheduleBlocks.timeBlock', 'scheduleBlocks.subject', 'scheduleBlocks.teacher', 'scheduleBlocks.classroom']);

        $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $timeBlocks = TimeBlock::active()->get();

        $conflicts = $schedule->detectConflicts();

        return view('horarios.show', compact('schedule', 'days', 'timeBlocks', 'conflicts'));
    }

    public function edit(Schedule $schedule)
    {
        $schedule->load(['scheduleBlocks.timeBlock', 'scheduleBlocks.subject', 'scheduleBlocks.teacher', 'scheduleBlocks.classroom']);

        $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $timeBlocks = TimeBlock::active()->get();
        $subjects = Subject::active()->forGrade($schedule->grade)->get();
        $teachers = Teacher::active()->with('user')->get();
        $classrooms = Classroom::available()->get();

        return view('horarios.edit', compact('schedule', 'days', 'timeBlocks', 'subjects', 'teachers', 'classrooms'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $schedule->update($validated);

        return back()->with('success', 'Horario actualizado exitosamente.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Horario eliminado exitosamente.');
    }

    public function addBlock(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'time_block_id' => 'required|exists:time_blocks,id',
            'day_of_week' => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'notes' => 'nullable|string',
        ]);

        $validated['schedule_id'] = $schedule->id;

        // Verificar conflictos antes de crear
        $conflicts = $this->conflictService->checkBlockConflicts($validated);

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'message' => 'Se detectaron conflictos',
                'conflicts' => $conflicts
            ], 422);
        }

        $block = ScheduleBlock::create($validated);
        $block->load(['timeBlock', 'subject', 'teacher', 'classroom']);

        return response()->json([
            'success' => true,
            'message' => 'Bloque agregado exitosamente',
            'block' => $block
        ]);
    }

    public function updateBlock(Request $request, ScheduleBlock $block)
    {
        $validated = $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'notes' => 'nullable|string',
        ]);

        // Verificar conflictos
        $blockData = array_merge($block->toArray(), $validated);
        $conflicts = $this->conflictService->checkBlockConflicts($blockData, $block->id);

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'message' => 'Se detectaron conflictos',
                'conflicts' => $conflicts
            ], 422);
        }

        $block->update($validated);
        $block->load(['timeBlock', 'subject', 'teacher', 'classroom']);

        return response()->json([
            'success' => true,
            'message' => 'Bloque actualizado exitosamente',
            'block' => $block
        ]);
    }

    public function deleteBlock(ScheduleBlock $block)
    {
        $block->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bloque eliminado exitosamente'
        ]);
    }

    public function viewByTeacher(Teacher $teacher)
    {
        $currentPeriod = AcademicPeriod::where('is_active', true)->first();

        if (!$currentPeriod) {
            return view('horarios.teacher', ['teacher' => $teacher, 'blocks' => collect()]);
        }

        $blocks = ScheduleBlock::whereHas('schedule', function($q) use ($currentPeriod) {
                $q->where('academic_period_id', $currentPeriod->id);
            })
            ->where('teacher_id', $teacher->id)
            ->with(['schedule', 'timeBlock', 'subject', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('time_block_id')
            ->get();

        $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $timeBlocks = TimeBlock::active()->get();

        return view('horarios.teacher', compact('teacher', 'blocks', 'days', 'timeBlocks'));
    }

    public function viewByClassroom(Classroom $classroom)
    {
        $currentPeriod = AcademicPeriod::where('is_active', true)->first();

        if (!$currentPeriod) {
            return view('horarios.classroom', ['classroom' => $classroom, 'blocks' => collect()]);
        }

        $blocks = ScheduleBlock::whereHas('schedule', function($q) use ($currentPeriod) {
                $q->where('academic_period_id', $currentPeriod->id);
            })
            ->where('classroom_id', $classroom->id)
            ->with(['schedule', 'timeBlock', 'subject', 'teacher'])
            ->orderBy('day_of_week')
            ->orderBy('time_block_id')
            ->get();

        $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $timeBlocks = TimeBlock::active()->get();

        return view('horarios.classroom', compact('classroom', 'blocks', 'days', 'timeBlocks'));
    }
}
