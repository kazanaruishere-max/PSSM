<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Classes;
use App\Models\Quiz;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return $this->superAdminDashboard();
        }

        if ($user->hasRole('teacher')) {
            return $this->teacherDashboard($user);
        }

        // Default: Student or Class Leader
        return $this->studentDashboard($user);
    }

    private function superAdminDashboard()
    {
        $stats = [
            'total_students' => User::role('student')->count() + User::role('class_leader')->count(),
            'total_teachers' => User::role('teacher')->count(),
            'total_classes'  => Classes::count(),
            'active_assignments' => Assignment::where('deadline', '>', now())->count(),
            'active_quizzes' => Quiz::where('end_time', '>', now())->count(),
        ];

        // For simple chart integration later
        $recentClasses = Classes::withCount('students')->latest()->take(5)->get();

        return view('dashboard.super_admin', compact('stats', 'recentClasses'));
    }

    private function teacherDashboard(User $user)
    {
        // Get classes teacher is assigned to (via class_subject pivot)
        $classIds = DB::table('class_subject')->where('teacher_id', $user->id)->pluck('class_id');
        $classesCount = Classes::whereIn('id', $classIds)->count();

        // Get count of assignments that have ungraded submissions
        $pendingGrading = Submission::whereHas('assignment', function ($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->whereNull('graded_at')->count();

        $activeAssignments = Assignment::where('teacher_id', $user->id)
                                ->where('deadline', '>', now())
                                ->count();
                                
        $activeQuizzes = Quiz::where('teacher_id', $user->id)
                                ->where('start_time', '<=', now())
                                ->where('end_time', '>=', now())
                                ->count();

        $recentAssignments = Assignment::where('teacher_id', $user->id)
                                ->latest()
                                ->take(5)
                                ->get();

        return view('dashboard.teacher', compact(
            'classesCount', 
            'pendingGrading', 
            'activeAssignments', 
            'activeQuizzes',
            'recentAssignments'
        ));
    }

    private function studentDashboard(User $user)
    {
        // Get student's classes
        $classIds = $user->classes()->pluck('classes.id');

        // Upcoming Assignments
        $upcomingAssignments = Assignment::whereIn('class_id', $classIds)
            ->where('deadline', '>', now())
            ->where('is_published', true)
            ->with(['subject', 'teacher'])
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        // Check which ones are already submitted by the student
        $submittedAssignmentIds = Submission::where('student_id', $user->id)
            ->whereIn('assignment_id', $upcomingAssignments->pluck('id'))
            ->pluck('assignment_id')
            ->toArray();

        // Active Quizzes
        $activeQuizzes = Quiz::whereIn('class_id', $classIds)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->with(['subject', 'teacher'])
            ->orderBy('end_time', 'asc')
            ->take(5)
            ->get();

        // Get recent grades
        $recentGrades = Submission::where('student_id', $user->id)
            ->whereNotNull('graded_at')
            ->with('assignment.subject')
            ->latest('graded_at')
            ->take(5)
            ->get();

        return view('dashboard.student', compact(
            'upcomingAssignments', 
            'submittedAssignmentIds', 
            'activeQuizzes',
            'recentGrades'
        ));
    }
}
