<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\GradeSubject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('gradeSubjects')->paginate(20);
        return view('materias.index', compact('subjects'));
    }

    public function create()
    {
        $grades = [6, 7, 8, 9, 10, 11];
        return view('materias.create', compact('grades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:subjects,code|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'weekly_hours' => 'required|integer|min:1',
        ]);

        $subject = Subject::create($validated);

        // Asignar a grados
        if ($request->has('grades')) {
            foreach ($request->grades as $grade => $data) {
                if (isset($data['selected']) && $data['selected']) {
                    GradeSubject::create([
                        'subject_id' => $subject->id,
                        'grade' => $grade,
                        'weekly_hours' => $data['hours'] ?? $validated['weekly_hours'],
                        'is_required' => $data['required'] ?? true,
                    ]);
                }
            }
        }

        return redirect()->route('subjects.index')
            ->with('success', 'Materia creada exitosamente.');
    }

    public function show(Subject $subject)
    {
        $subject->load(['gradeSubjects', 'teachers']);
        return view('materias.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        $subject->load('gradeSubjects');
        $grades = [6, 7, 8, 9, 10, 11];
        return view('materias.edit', compact('subject', 'grades'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'weekly_hours' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.index')
            ->with('success', 'Materia actualizada exitosamente.');
    }

    public function destroy(Subject $subject)
    {
        // Verificar que no esté en uso en horarios
        if ($subject->scheduleBlocks()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una materia que está en uso en horarios.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Materia eliminada exitosamente.');
    }
}
