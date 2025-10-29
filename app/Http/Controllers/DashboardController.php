<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Classroom;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $currentYear = AcademicYear::where('is_active', true)->first();
        $currentPeriod = AcademicPeriod::where('is_active', true)->first();

        $upcomingActivities = Activity::upcoming()
            ->where('is_public', true)
            ->take(10)
            ->get();

        $stats = [
            'total_teachers' => Teacher::active()->count(),
            'total_subjects' => Subject::active()->count(),
            'total_classrooms' => Classroom::available()->count(),
            'total_schedules' => $currentPeriod ? Schedule::where('academic_period_id', $currentPeriod->id)->count() : 0,
            'upcoming_activities' => $upcomingActivities->count(),
        ];

        return view('dashboard.index', compact('currentYear', 'currentPeriod', 'upcomingActivities', 'stats'));
    }
}
