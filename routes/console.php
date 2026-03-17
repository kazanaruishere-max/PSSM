<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Assignment;
use App\Notifications\DeadlineReminderNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PSSM Maintenance Tasks (Phase 7)
Schedule::command('dashboard:refresh')->everyFiveMinutes();
Schedule::command('backup:run')->dailyAt('02:00');
Schedule::command('backup:verify')->weeklyOn(0, '03:00');  // Sunday 3 AM
Schedule::command('telescope:prune --hours=48')->daily();
Schedule::command('activitylog:clean --days=90')->weekly();
Schedule::command('queue:prune-failed --hours=72')->daily();

// Deadline Reminders - Runs daily at 07:00 AM
Schedule::call(function () {
    $assignments = Assignment::where('deadline', '>', now())
        ->where('deadline', '<', now()->addDay())
        ->with('class.students')
        ->get();

    foreach ($assignments as $assignment) {
        $studentsWithoutSubmission = $assignment->class->students
            ->filter(fn($s) => !$assignment->submissions()->where('student_id', $s->id)->exists());

        foreach ($studentsWithoutSubmission as $student) {
            $student->notify(new DeadlineReminderNotification($assignment));
        }
    }
})->dailyAt('07:00')->timezone('Asia/Jakarta');
