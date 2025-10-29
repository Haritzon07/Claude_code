<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with(['user', 'subjects'])->paginate(20);
        return view('docentes.index', compact('teachers'));
    }

    public function create()
    {
        $subjects = Subject::active()->get();
        return view('docentes.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'identification' => 'required|string|unique:users,identification',
            'phone' => 'nullable|string|max:20',
            'code' => 'required|string|unique:teachers,code',
            'specialization' => 'nullable|string|max:255',
            'max_weekly_hours' => 'required|integer|min:1|max:60',
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'identification' => $validated['identification'],
            'phone' => $validated['phone'] ?? null,
            'user_type' => 'docente',
            'password' => Hash::make($validated['identification']), // Password temporal
        ]);

        // Crear teacher
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'code' => $validated['code'],
            'specialization' => $validated['specialization'] ?? null,
            'max_weekly_hours' => $validated['max_weekly_hours'],
        ]);

        // Asignar materias
        if ($request->has('subjects')) {
            $teacher->subjects()->attach($request->subjects);
        }

        return redirect()->route('teachers.index')
            ->with('success', 'Docente creado exitosamente.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'subjects', 'scheduleBlocks.schedule']);
        return view('docentes.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load(['user', 'subjects']);
        $subjects = Subject::active()->get();
        return view('docentes.edit', compact('teacher', 'subjects'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->user_id,
            'identification' => 'required|string|unique:users,identification,' . $teacher->user_id,
            'phone' => 'nullable|string|max:20',
            'code' => 'required|string|unique:teachers,code,' . $teacher->id,
            'specialization' => 'nullable|string|max:255',
            'max_weekly_hours' => 'required|integer|min:1|max:60',
            'is_active' => 'boolean',
        ]);

        // Actualizar usuario
        $teacher->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'identification' => $validated['identification'],
            'phone' => $validated['phone'] ?? null,
        ]);

        // Actualizar teacher
        $teacher->update([
            'code' => $validated['code'],
            'specialization' => $validated['specialization'] ?? null,
            'max_weekly_hours' => $validated['max_weekly_hours'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Actualizar materias
        if ($request->has('subjects')) {
            $teacher->subjects()->sync($request->subjects);
        }

        return redirect()->route('teachers.index')
            ->with('success', 'Docente actualizado exitosamente.');
    }

    public function destroy(Teacher $teacher)
    {
        // Verificar que no tenga clases asignadas
        if ($teacher->scheduleBlocks()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un docente que tiene clases asignadas.');
        }

        $user = $teacher->user;
        $teacher->delete();
        $user->delete();

        return redirect()->route('teachers.index')
            ->with('success', 'Docente eliminado exitosamente.');
    }
}
