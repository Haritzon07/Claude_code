<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::orderBy('type')->orderBy('code')->paginate(20);
        return view('recursos.index', compact('classrooms'));
    }

    public function create()
    {
        return view('recursos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:classrooms,code|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:aula,laboratorio,auditorio,deportivo,biblioteca,sala_multiple',
            'capacity' => 'required|integer|min:1',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'equipment' => 'nullable|string',
        ]);

        Classroom::create($validated);

        return redirect()->route('classrooms.index')
            ->with('success', 'Salón/Recurso creado exitosamente.');
    }

    public function show(Classroom $classroom)
    {
        $classroom->load('scheduleBlocks.schedule');
        return view('recursos.show', compact('classroom'));
    }

    public function edit(Classroom $classroom)
    {
        return view('recursos.edit', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:classrooms,code,' . $classroom->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:aula,laboratorio,auditorio,deportivo,biblioteca,sala_multiple',
            'capacity' => 'required|integer|min:1',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'equipment' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        $classroom->update($validated);

        return redirect()->route('classrooms.index')
            ->with('success', 'Salón/Recurso actualizado exitosamente.');
    }

    public function destroy(Classroom $classroom)
    {
        if ($classroom->scheduleBlocks()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un salón que está en uso.');
        }

        $classroom->delete();

        return redirect()->route('classrooms.index')
            ->with('success', 'Salón/Recurso eliminado exitosamente.');
    }
}
