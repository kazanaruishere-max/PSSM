<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Assignment $assignment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'deadline_reminder',
            'title' => 'Pengingat Deadline Tugas',
            'message' => "Tugas '{$this->assignment->title}' untuk mata pelajaran {$this->assignment->subject->name} harus dikumpulkan maksimal besok ({$this->assignment->deadline->format('d M Y H:i')}).",
            'url' => route('assignments.show', $this->assignment->id),
            'icon' => 'clock',
            'color' => 'text-red-500',
            'bg_color' => 'bg-red-50',
            'assignment_id' => $this->assignment->id,
        ];
    }
}
