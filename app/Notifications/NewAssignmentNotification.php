<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $assignment;

    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Tugas Baru: ' . $this->assignment->title,
            'message' => 'Tugas baru dari ' . $this->assignment->subject->name . ' telah dipublikasikan. Tenggat Waktu: ' . $this->assignment->due_date->translatedFormat('d M Y H:i'),
            'url' => route('assignments.show', $this->assignment->id),
            'type' => 'assignment',
        ];
    }
}
