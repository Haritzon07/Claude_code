<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->paginate(10);
        return view('periodos.years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('periodos.years.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|unique:academic_years,year',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
        ]);

        $academicYear = AcademicYear::create($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Año escolar creado exitosamente.');
    }

    public function show(AcademicYear $academicYear)
    {
        $academicYear->load(['periods', 'holidays', 'activities']);
        return view('periodos.years.show', compact('academicYear'));
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('periodos.years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'year' => 'required|integer|unique:academic_years,year,' . $academicYear->id,
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
        ]);

        $academicYear->update($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Año escolar actualizado exitosamente.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_active) {
            return back()->with('error', 'No se puede eliminar un año escolar activo.');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('success', 'Año escolar eliminado exitosamente.');
    }

    public function activate(AcademicYear $academicYear)
    {
        $academicYear->activate();

        return back()->with('success', 'Año escolar activado exitosamente.');
    }
}
