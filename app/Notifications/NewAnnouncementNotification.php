<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $authorName = $this->announcement->author->name;
        $title = $this->announcement->title;
        $classId = $this->announcement->class_id;
        
        $message = "Pengumuman Baru: {$title} dari {$authorName}";
        if (!$classId) {
            $message = "Pengumuman Sekolah: {$title}";
        }

        return [
            'title' => $this->announcement->title,
            'message' => $message,
            'url' => route('announcements.index'),
            'type' => 'announcement',
        ];
    }
}
