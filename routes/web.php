<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Assignments
    Route::resource('assignments', \App\Http\Controllers\AssignmentController::class);
    Route::get('/assignments/{assignment}/download', [\App\Http\Controllers\AssignmentController::class, 'download'])->name('assignments.download');
    
    // Submissions
    Route::post('/assignments/{assignment}/submit', [\App\Http\Controllers\SubmissionController::class, 'store'])->name('submissions.store');
    Route::post('/submissions/{submission}/grade', [\App\Http\Controllers\SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::post('/submissions/{submission}/ai-feedback', [\App\Http\Controllers\SubmissionController::class, 'generateAIFeedback'])->name('submissions.ai-feedback');
    Route::get('/submissions/{submission}/download', [\App\Http\Controllers\SubmissionController::class, 'download'])->name('submissions.download');
    Route::get('/assignments/{assignment}/export', [\App\Http\Controllers\ReportController::class, 'exportAssignmentData'])->name('reports.assignment');
    
    // Quizzes
    Route::resource('quizzes', \App\Http\Controllers\QuizController::class)->except(['edit', 'update']);
    Route::post('/quizzes/{quiz}/take', [\App\Http\Controllers\QuizController::class, 'take'])->name('quizzes.take');
    Route::get('/quizzes/{quiz}/attempt/{attempt}', [\App\Http\Controllers\QuizController::class, 'active'])->name('quizzes.active');
    Route::post('/quizzes/{quiz}/attempt/{attempt}/submit', [\App\Http\Controllers\QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/quizzes/{quiz}/export', [\App\Http\Controllers\ReportController::class, 'exportQuizData'])->name('reports.quiz');
    
    Route::get('/reports/classes/{class}/student/{student}', [\App\Http\Controllers\ReportController::class, 'exportReportCard'])->name('reports.report-card');

    // Announcements Route
    Route::resource('announcements', \App\Http\Controllers\AnnouncementController::class)->only(['index', 'create', 'store', 'destroy']);

    // Attendances Route
    Route::get('attendances', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/create', [\App\Http\Controllers\AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('attendances', [\App\Http\Controllers\AttendanceController::class, 'store'])->name('attendances.store');

    // Notifications Route
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Master Data (Super Admin)
    Route::middleware(['auth', 'role:super_admin'])->prefix('master-data')->group(function () {
        Route::resource('academic-years', \App\Http\Controllers\AcademicYearController::class)->except(['create', 'show', 'edit']);
        Route::resource('subjects', \App\Http\Controllers\SubjectController::class)->except(['create', 'show', 'edit']);
        Route::resource('classes', \App\Http\Controllers\ClassController::class)->except(['create', 'edit']);
        Route::post('classes/{class}/assign-students', [\App\Http\Controllers\ClassController::class, 'assignStudents'])->name('classes.assign-students');
        Route::delete('classes/{class}/remove-student/{student}', [\App\Http\Controllers\ClassController::class, 'removeStudent'])->name('classes.remove-student');
        Route::post('classes/{class}/assign-teacher/{subject}', [\App\Http\Controllers\ClassController::class, 'assignTeacher'])->name('classes.assign-teacher');

        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
        Route::post('users/import', [\App\Http\Controllers\BulkImportController::class, 'importUsers'])->name('users.import');
    });
});

require __DIR__.'/auth.php';
