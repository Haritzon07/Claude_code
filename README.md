# 📊 Sistema de Cronograma Institucional - I.E. Campo Valdés

Sistema completo de gestión de cronogramas, horarios y actividades para instituciones educativas, desarrollado específicamente para la **Institución Educativa Campo Valdés**.

![Colores Institucionales](https://img.shields.io/badge/Verde-2d5f3f-green)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/License-MIT-yellow)

---

## 🎯 Características Principales

### 1. 📅 Gestión de Períodos Académicos
- ✅ Crear y gestionar años escolares (2024, 2025, etc.)
- ✅ Dividir en períodos académicos (1°, 2°, 3°)
- ✅ Definir fechas de inicio y fin de cada período
- ✅ Gestionar semanas académicas
- ✅ Configurar días hábiles y festivos

### 2. 🕐 Gestión de Horarios
- ✅ Crear bloques de clase por día y hora
- ✅ Asignar materias a cada bloque
- ✅ Asignar docentes a cada materia
- ✅ Asignar aulas/salones
- ✅ Gestionar horarios por grado (6° a 11°) y grupo (1, 2, 3)
- ✅ Horarios especiales (recreos, descansos, almuerzo)
- ✅ Intensidad horaria por materia
- ✅ **Detección automática de conflictos** (mismo docente o salón en dos lugares)

### 3. 🎪 Gestión de Actividades y Eventos
- ✅ Calendario académico anual
- ✅ Fechas de entrega de boletines
- ✅ Semanas de evaluaciones/exámenes
- ✅ Reuniones de padres
- ✅ Actividades extracurriculares
- ✅ Eventos institucionales
- ✅ Días especiales (izadas de bandera, actos cívicos)
- ✅ Actividades culturales y deportivas

### 4. 👁️ Visualización
- ✅ Vista mensual del cronograma
- ✅ Vista semanal detallada
- ✅ Vista diaria por grado/grupo
- ✅ Vista por docente (ver su horario completo)
- ✅ Vista por salón (ocupación de aulas)
- ✅ Calendarios interactivos con FullCalendar

### 5. 🔔 Sistema de Notificaciones
- ✅ Recordatorios de actividades próximas
- ✅ Cambios en el cronograma
- ✅ Notificaciones para docentes sobre sus horarios
- ✅ Alertas de fechas importantes
- ✅ Avisos de eventos institucionales

### 6. 📚 Gestión de Materias y Docentes
- ✅ Catálogo completo de materias
- ✅ Base de datos de docentes
- ✅ Asignación de materias a docentes
- ✅ Carga académica por docente (horas semanales)
- ✅ Materias por grado
- ✅ Control de horas máximas por docente

### 7. 🏫 Gestión de Recursos
- ✅ Salones y aulas disponibles
- ✅ Laboratorios (química, física, informática)
- ✅ Espacios deportivos
- ✅ Auditorios y salas múltiples
- ✅ Biblioteca
- ✅ Control de disponibilidad de espacios

### 8. 📊 Reportes y Estadísticas
- ✅ Reporte de carga académica por docente
- ✅ Ocupación de salones por período
- ✅ Distribución de materias por grado
- ✅ Calendario consolidado del año
- ✅ Exportación a PDF/Excel (próximamente)
- ✅ Gráficos estadísticos

### 9. 🔐 Sistema de Permisos
- ✅ **Administradores**: Control total del cronograma
- ✅ **Coordinadores**: Gestión de horarios y actividades
- ✅ **Docentes**: Ver sus horarios y modificar sus actividades
- ✅ **Estudiantes**: Ver horarios de su grado y actividades
- ✅ **Padres**: Ver horarios de sus hijos y calendario de eventos
- ✅ **Invitados**: Vista limitada del calendario público

### 10. ⚡ Funciones Especiales
- ✅ **Detección de conflictos**: El sistema verifica automáticamente si un docente o salón está ocupado
- ✅ **Sugerencias automáticas**: Sugiere docentes o salones alternativos cuando hay conflictos
- ✅ Plantillas de horarios reutilizables
- ✅ Búsqueda avanzada (por materia, docente, salón, fecha)
- ✅ Filtros múltiples
- ✅ Comparación entre períodos

---

## 🎨 Diseño Institucional

El sistema utiliza los **colores institucionales** de la I.E. Campo Valdés:

- 🟢 **Verde Primario**: `#2d5f3f`
- 🟢 **Verde Secundario**: `#4a8f5f`
- 🟡 **Amarillo Institucional**: `#ffdd1f`

### Diseño Responsive
- 📱 Móvil
- 💻 Tablet
- 🖥️ PC

---

## 🛠️ Tecnologías Utilizadas

### Backend
- **Laravel 10.x** - Framework PHP
- **MySQL** - Base de datos
- **Spatie Laravel Permission** - Sistema de roles y permisos
- **Laravel Sanctum** - Autenticación API

### Frontend
- **Bootstrap 5** - Framework CSS
- **FullCalendar** - Calendarios interactivos
- **Font Awesome** - Iconos
- **Chart.js** - Gráficos estadísticos
- **SweetAlert2** - Alertas y confirmaciones
- **Dragula** - Drag and Drop para horarios
- **Moment.js** - Manejo de fechas

### Herramientas de Desarrollo
- **Vite** - Build tool
- **Composer** - Gestor de dependencias PHP
- **NPM** - Gestor de dependencias JavaScript

---

## 📋 Requisitos del Sistema

- PHP >= 8.1
- Composer >= 2.0
- Node.js >= 16.x
- MySQL >= 5.7 o MariaDB >= 10.3
- Extensiones PHP requeridas:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

---

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/Haritzon07/Claude_code.git
cd Claude_code
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de JavaScript

```bash
npm install
```

### 4. Configurar el archivo de entorno

```bash
cp .env.example .env
```

Editar `.env` y configurar:
- Nombre de la aplicación
- Base de datos
- URL de la aplicación
- Configuración institucional

```env
APP_NAME="Cronograma Campo Valdés"
DB_DATABASE=cronograma_campovaldes
DB_USERNAME=root
DB_PASSWORD=tu_password

INSTITUTION_NAME="I.E. Campo Valdés"
INSTITUTION_COLOR_PRIMARY="#2d5f3f"
INSTITUTION_COLOR_SECONDARY="#4a8f5f"
INSTITUTION_COLOR_ACCENT="#ffdd1f"
```

### 5. Generar clave de aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Esto creará:
- ✅ Estructura de base de datos completa
- ✅ Usuario administrador
- ✅ Usuario coordinador
- ✅ 6 docentes de ejemplo
- ✅ Año escolar 2025 con 3 períodos
- ✅ 10 materias principales
- ✅ 11 salones y laboratorios
- ✅ Bloques de tiempo (horario escolar)
- ✅ Días festivos

### 7. Compilar assets

```bash
npm run dev
```

Para producción:
```bash
npm run build
```

### 8. Iniciar el servidor

```bash
php artisan serve
```

La aplicación estará disponible en: http://localhost:8000

---

## 👤 Usuarios de Ejemplo

Después de ejecutar los seeders, podrás acceder con:

### Administrador
- **Email**: admin@campovaldes.edu.co
- **Password**: admin123
- **Permisos**: Control total del sistema

### Coordinador
- **Email**: coordinador@campovaldes.edu.co
- **Password**: coord123
- **Permisos**: Gestión de horarios y actividades

### Docentes
- **Email**: [nombre.apellido]@campovaldes.edu.co
- **Password**: docente123
- **Permisos**: Ver horarios y gestionar sus actividades

---

## 📚 Estructura del Proyecto

```
cronograma-institucional/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── DashboardController.php
│   │       ├── AcademicYearController.php
│   │       ├── ScheduleController.php
│   │       ├── ActivityController.php
│   │       ├── SubjectController.php
│   │       ├── TeacherController.php
│   │       └── ClassroomController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── AcademicYear.php
│   │   ├── AcademicPeriod.php
│   │   ├── Schedule.php
│   │   ├── ScheduleBlock.php
│   │   ├── Activity.php
│   │   ├── Subject.php
│   │   ├── Teacher.php
│   │   └── Classroom.php
│   └── Services/
│       └── ScheduleConflictService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/
│   │   └── app.css (Estilos institucionales)
│   ├── js/
│   │   └── app.js (Funcionalidad del cliente)
│   └── views/
│       ├── layouts/
│       ├── dashboard/
│       ├── periodos/
│       ├── horarios/
│       ├── materias/
│       ├── docentes/
│       ├── recursos/
│       ├── actividades/
│       └── reportes/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── composer.json
├── package.json
└── README.md
```

---

## 🔧 Uso del Sistema

### Crear un Horario

1. Ir a **Horarios** → **Crear Nuevo**
2. Seleccionar período académico, grado y grupo
3. Agregar bloques de clase arrastrando o haciendo clic
4. Asignar materia, docente y salón
5. El sistema **detecta automáticamente conflictos**
6. Guardar horario

### Gestionar Actividades

1. Ir a **Actividades** → **Nueva Actividad**
2. Ingresar título, tipo de actividad, fecha y hora
3. Seleccionar ubicación (opcional)
4. Definir participantes (grados, docentes específicos)
5. Guardar actividad

### Ver Calendario

1. Ir a **Calendario**
2. Ver vista mensual, semanal o lista
3. Hacer clic en eventos para ver detalles
4. Filtrar por tipo de actividad

### Generar Reportes

1. Ir a **Reportes**
2. Seleccionar tipo de reporte:
   - Carga académica por docente
   - Ocupación de salones
   - Distribución de materias
3. Exportar a PDF o Excel

---

## 🔍 Detección de Conflictos

El sistema incluye un **servicio avanzado de detección de conflictos**:

### Tipos de Conflictos Detectados

1. **Conflicto de Docente**: Un docente asignado a dos clases simultáneas
2. **Conflicto de Salón**: Un salón ocupado por dos grupos al mismo tiempo

### Cómo Funciona

Cuando se intenta asignar un bloque de horario, el sistema:
1. Verifica si el docente está libre en ese horario
2. Verifica si el salón está disponible
3. Si hay conflicto, muestra mensaje de error
4. **Sugiere alternativas**: Docentes o salones disponibles

### Ejemplo de Uso

```php
// El servicio se usa automáticamente al crear bloques
$conflicts = $scheduleConflictService->checkBlockConflicts([
    'teacher_id' => 1,
    'classroom_id' => 5,
    'day_of_week' => 'lunes',
    'time_block_id' => 2,
]);

if (!empty($conflicts)) {
    // Mostrar conflictos al usuario
    // Sugerir alternativas
}
```

---

## 📊 Base de Datos

### Tablas Principales

- `users` - Usuarios del sistema
- `roles` / `permissions` - Sistema de roles y permisos
- `academic_years` - Años escolares
- `academic_periods` - Períodos académicos
- `academic_weeks` - Semanas académicas
- `holidays` - Días festivos
- `subjects` - Materias
- `grade_subjects` - Materias por grado
- `teachers` - Docentes
- `teacher_subjects` - Materias que dicta cada docente
- `classrooms` - Salones y recursos
- `time_blocks` - Bloques de tiempo
- `schedules` - Horarios por grado
- `schedule_blocks` - Bloques específicos de clase
- `activities` - Actividades y eventos
- `notifications` - Notificaciones
- `schedule_templates` - Plantillas de horarios

---

## 🎯 Roadmap / Próximas Funcionalidades

- [ ] Exportación a PDF/Excel de horarios
- [ ] Sistema de notificaciones por email
- [ ] App móvil (React Native)
- [ ] Gestión de estudiantes y grupos
- [ ] Asistencia de docentes y estudiantes
- [ ] Integración con plataforma de evaluación
- [ ] Copiar cronograma de año anterior
- [ ] Gráfico de Gantt para proyectos institucionales
- [ ] Comparación entre períodos
- [ ] API REST completa

---

## 🤝 Contribuir

Las contribuciones son bienvenidas! Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/NuevaFuncionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/NuevaFuncionalidad`)
5. Abre un Pull Request

---

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

---

## 👥 Créditos

**Desarrollado para:**
- I.E. Campo Valdés - Medellín, Colombia

**Tecnologías:**
- Laravel Framework
- Bootstrap
- FullCalendar
- Font Awesome
- Chart.js

---

## 📞 Soporte

Para soporte técnico o preguntas:
- 📧 Email: soporte@campovaldes.edu.co
- 🌐 Web: www.campovaldes.edu.co
- 📱 Teléfono: (604) XXX-XXXX

---

## ⭐ Agradecimientos

Agradecemos a toda la comunidad de Campo Valdés por su apoyo en el desarrollo de este sistema.

---

<div align="center">

**Hecho con ❤️ para la I.E. Campo Valdés**

[⬆ Volver arriba](#-sistema-de-cronograma-institucional---ie-campo-valdés)

</div>
