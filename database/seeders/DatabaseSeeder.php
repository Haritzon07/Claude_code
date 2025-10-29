<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\GradeSubject;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\TimeBlock;
use App\Models\Holiday;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Roles
        $adminRole = Role::create(['name' => 'Administrador']);
        $coordinadorRole = Role::create(['name' => 'Coordinador']);
        $docenteRole = Role::create(['name' => 'Docente']);
        $estudianteRole = Role::create(['name' => 'Estudiante']);
        $padreRole = Role::create(['name' => 'Padre']);

        // Crear usuario administrador
        $admin = User::create([
            'name' => 'Administrador Campo Valdés',
            'email' => 'admin@campovaldes.edu.co',
            'identification' => '1234567890',
            'user_type' => 'admin',
            'password' => Hash::make('admin123'),
            'active' => true,
        ]);
        $admin->assignRole($adminRole);

        // Crear usuario coordinador
        $coordinador = User::create([
            'name' => 'Coordinador Académico',
            'email' => 'coordinador@campovaldes.edu.co',
            'identification' => '1234567891',
            'user_type' => 'coordinador',
            'password' => Hash::make('coord123'),
            'active' => true,
        ]);
        $coordinador->assignRole($coordinadorRole);

        // Crear Año Escolar 2025
        $year2025 = AcademicYear::create([
            'year' => 2025,
            'name' => 'Año Escolar 2025',
            'start_date' => '2025-01-22',
            'end_date' => '2025-11-28',
            'is_active' => true,
            'description' => 'Año académico 2025 - I.E. Campo Valdés',
        ]);

        // Crear Períodos Académicos
        $periodo1 = AcademicPeriod::create([
            'academic_year_id' => $year2025->id,
            'period_number' => 1,
            'name' => 'Primer Período',
            'start_date' => '2025-01-22',
            'end_date' => '2025-04-18',
            'is_active' => true,
        ]);

        $periodo2 = AcademicPeriod::create([
            'academic_year_id' => $year2025->id,
            'period_number' => 2,
            'name' => 'Segundo Período',
            'start_date' => '2025-04-21',
            'end_date' => '2025-07-25',
            'is_active' => false,
        ]);

        $periodo3 = AcademicPeriod::create([
            'academic_year_id' => $year2025->id,
            'period_number' => 3,
            'name' => 'Tercer Período',
            'start_date' => '2025-08-04',
            'end_date' => '2025-11-28',
            'is_active' => false,
        ]);

        // Crear Festivos y Vacaciones
        Holiday::create([
            'academic_year_id' => $year2025->id,
            'name' => 'Día de Reyes',
            'date' => '2025-01-06',
            'type' => 'festivo',
        ]);

        Holiday::create([
            'academic_year_id' => $year2025->id,
            'name' => 'Día del Trabajo',
            'date' => '2025-05-01',
            'type' => 'festivo',
        ]);

        Holiday::create([
            'academic_year_id' => $year2025->id,
            'name' => 'Día de la Independencia',
            'date' => '2025-07-20',
            'type' => 'festivo',
        ]);

        // Crear Materias
        $materias = [
            ['code' => 'MAT', 'name' => 'Matemáticas', 'color' => '#2196F3', 'weekly_hours' => 5],
            ['code' => 'ESP', 'name' => 'Español', 'color' => '#4CAF50', 'weekly_hours' => 5],
            ['code' => 'ING', 'name' => 'Inglés', 'color' => '#FF9800', 'weekly_hours' => 3],
            ['code' => 'SOC', 'name' => 'Ciencias Sociales', 'color' => '#9C27B0', 'weekly_hours' => 4],
            ['code' => 'NAT', 'name' => 'Ciencias Naturales', 'color' => '#00BCD4', 'weekly_hours' => 4],
            ['code' => 'EDF', 'name' => 'Educación Física', 'color' => '#FF5722', 'weekly_hours' => 2],
            ['code' => 'ART', 'name' => 'Educación Artística', 'color' => '#E91E63', 'weekly_hours' => 2],
            ['code' => 'TEC', 'name' => 'Tecnología e Informática', 'color' => '#607D8B', 'weekly_hours' => 2],
            ['code' => 'ETI', 'name' => 'Ética y Valores', 'color' => '#795548', 'weekly_hours' => 1],
            ['code' => 'REL', 'name' => 'Religión', 'color' => '#9E9E9E', 'weekly_hours' => 1],
        ];

        foreach ($materias as $materia) {
            $subject = Subject::create($materia);

            // Asignar materias a todos los grados
            for ($grade = 6; $grade <= 11; $grade++) {
                GradeSubject::create([
                    'subject_id' => $subject->id,
                    'grade' => $grade,
                    'weekly_hours' => $materia['weekly_hours'],
                    'is_required' => true,
                ]);
            }
        }

        // Crear Docentes
        $docentes = [
            ['name' => 'María Elena González', 'email' => 'maria.gonzalez@campovaldes.edu.co', 'specialization' => 'Matemáticas', 'subjects' => ['MAT']],
            ['name' => 'Carlos Alberto Pérez', 'email' => 'carlos.perez@campovaldes.edu.co', 'specialization' => 'Español', 'subjects' => ['ESP']],
            ['name' => 'Ana María Rodríguez', 'email' => 'ana.rodriguez@campovaldes.edu.co', 'specialization' => 'Inglés', 'subjects' => ['ING']],
            ['name' => 'Luis Fernando Martínez', 'email' => 'luis.martinez@campovaldes.edu.co', 'specialization' => 'Ciencias Sociales', 'subjects' => ['SOC']],
            ['name' => 'Sandra Patricia López', 'email' => 'sandra.lopez@campovaldes.edu.co', 'specialization' => 'Ciencias Naturales', 'subjects' => ['NAT']],
            ['name' => 'Jorge Andrés Gómez', 'email' => 'jorge.gomez@campovaldes.edu.co', 'specialization' => 'Educación Física', 'subjects' => ['EDF']],
        ];

        foreach ($docentes as $index => $docenteData) {
            $user = User::create([
                'name' => $docenteData['name'],
                'email' => $docenteData['email'],
                'identification' => '100000000' . ($index + 1),
                'user_type' => 'docente',
                'password' => Hash::make('docente123'),
                'active' => true,
            ]);
            $user->assignRole($docenteRole);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'code' => 'DOC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'specialization' => $docenteData['specialization'],
                'max_weekly_hours' => 40,
            ]);

            // Asignar materias al docente
            foreach ($docenteData['subjects'] as $subjectCode) {
                $subject = Subject::where('code', $subjectCode)->first();
                if ($subject) {
                    $teacher->subjects()->attach($subject->id, ['is_primary' => true]);
                }
            }
        }

        // Crear Salones
        $salones = [
            ['code' => '6-1', 'name' => 'Salón 6-1', 'type' => 'aula', 'capacity' => 40, 'building' => 'Bloque A', 'floor' => '1'],
            ['code' => '7-1', 'name' => 'Salón 7-1', 'type' => 'aula', 'capacity' => 40, 'building' => 'Bloque A', 'floor' => '1'],
            ['code' => '8-1', 'name' => 'Salón 8-1', 'type' => 'aula', 'capacity' => 40, 'building' => 'Bloque A', 'floor' => '2'],
            ['code' => '9-1', 'name' => 'Salón 9-1', 'type' => 'aula', 'capacity' => 40, 'building' => 'Bloque B', 'floor' => '1'],
            ['code' => '10-1', 'name' => 'Salón 10-1', 'type' => 'aula', 'capacity' => 40, 'building' => 'Bloque B', 'floor' => '2'],
            ['code' => '11-1', 'name' => 'Salón 11-1', 'type' => 'aula', 'capacity' => 40, 'building' => 'Bloque B', 'floor' => '2'],
            ['code' => 'LAB-QUI', 'name' => 'Laboratorio de Química', 'type' => 'laboratorio', 'capacity' => 30, 'building' => 'Bloque C', 'floor' => '1'],
            ['code' => 'LAB-FIS', 'name' => 'Laboratorio de Física', 'type' => 'laboratorio', 'capacity' => 30, 'building' => 'Bloque C', 'floor' => '1'],
            ['code' => 'LAB-INF', 'name' => 'Sala de Informática', 'type' => 'laboratorio', 'capacity' => 35, 'building' => 'Bloque C', 'floor' => '2'],
            ['code' => 'AUD', 'name' => 'Auditorio Principal', 'type' => 'auditorio', 'capacity' => 200, 'building' => 'Bloque Principal', 'floor' => '1'],
            ['code' => 'BIB', 'name' => 'Biblioteca', 'type' => 'biblioteca', 'capacity' => 60, 'building' => 'Bloque Principal', 'floor' => '2'],
        ];

        foreach ($salones as $salon) {
            Classroom::create($salon);
        }

        // Crear Bloques de Tiempo (Horario Escolar)
        $bloques = [
            ['name' => '1° Hora', 'start_time' => '06:30', 'end_time' => '07:20', 'order' => 1, 'type' => 'clase'],
            ['name' => '2° Hora', 'start_time' => '07:20', 'end_time' => '08:10', 'order' => 2, 'type' => 'clase'],
            ['name' => '3° Hora', 'start_time' => '08:10', 'end_time' => '09:00', 'order' => 3, 'type' => 'clase'],
            ['name' => 'Recreo', 'start_time' => '09:00', 'end_time' => '09:20', 'order' => 4, 'type' => 'recreo'],
            ['name' => '4° Hora', 'start_time' => '09:20', 'end_time' => '10:10', 'order' => 5, 'type' => 'clase'],
            ['name' => '5° Hora', 'start_time' => '10:10', 'end_time' => '11:00', 'order' => 6, 'type' => 'clase'],
            ['name' => 'Almuerzo', 'start_time' => '11:00', 'end_time' => '11:40', 'order' => 7, 'type' => 'almuerzo'],
            ['name' => '6° Hora', 'start_time' => '11:40', 'end_time' => '12:30', 'order' => 8, 'type' => 'clase'],
        ];

        foreach ($bloques as $bloque) {
            TimeBlock::create($bloque);
        }

        echo "✅ Base de datos poblada exitosamente!\n";
        echo "📧 Usuario Admin: admin@campovaldes.edu.co / admin123\n";
        echo "📧 Usuario Coordinador: coordinador@campovaldes.edu.co / coord123\n";
        echo "📧 Usuarios Docentes: [email]@campovaldes.edu.co / docente123\n";
    }
}
