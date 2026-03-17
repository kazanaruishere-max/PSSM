<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Classes;
use App\Models\User;
use App\Notifications\NewAnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Announcement::with(['author', 'class_'])->orderBy('created_at', 'desc');

        if ($user->isAdmin()) {
            // Super Admin melihat semua
        } elseif ($user->isTeacher()) {
            // Guru melihat pengumuman global dan kelas yang ia ajar
            $teacherClassIds = $user->taughtSubjects()->pluck('class_id')->unique();
            $query->where(function ($q) use ($teacherClassIds) {
                $q->whereNull('class_id')
                  ->orWhereIn('class_id', $teacherClassIds);
            });
        } else {
            // Siswa melihat pengumuman global dan kelas tempat ia terdaftar
            $studentClassIds = $user->classes()->pluck('classes.id');
            $query->active()->where(function ($q) use ($studentClassIds) {
                $q->whereNull('class_id')
                  ->orWhereIn('class_id', $studentClassIds);
            });
        }

        $announcements = $query->paginate(20);
        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->isStudent()) {
            abort(403, 'Akses ditolak.');
        }

        $classes = collect();
        if ($user->isAdmin()) {
            $classes = Classes::with('academicYear')->get();
        } else if ($user->isTeacher()) {
            $classIds = $user->taughtSubjects()->pluck('class_id')->unique();
            $classes = Classes::with('academicYear')->whereIn('id', $classIds)->get();
        }

        return view('announcements.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->isStudent()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'class_id' => 'nullable|exists:classes,id',
            'priority' => 'required|in:low,normal,high',
            'expires_at' => 'nullable|date|after:today',
        ]);

        // Teachers cannot send global announcements easily
        if ($user->isTeacher() && empty($validated['class_id'])) {
            return back()->withErrors(['class_id' => 'Guru harus memilih kelas tujuan spesifik.'])->withInput();
        }
        
        // Ensure teacher can only announce to their assigned classes
        if ($user->isTeacher() && !empty($validated['class_id'])) {
            $teacherClassIds = $user->taughtSubjects()->pluck('class_id')->unique()->toArray();
            if (!in_array($validated['class_id'], $teacherClassIds)) {
                abort(403, 'Anda tidak mengajar di kelas ini.');
            }
        }

        $validated['author_id'] = $user->id;
        $validated['published_at'] = now();

        $announcement = Announcement::create($validated);

        // Dispatch Notification
        if (empty($validated['class_id'])) {
            // Global: notify all users
            Notification::send(User::where('is_active', true)->get(), new NewAnnouncementNotification($announcement));
        } else {
            // Class specific: notify students in that class
            $class = Classes::with('students')->find($validated['class_id']);
            if ($class && $class->students->isNotEmpty()) {
                Notification::send($class->students, new NewAnnouncementNotification($announcement));
            }
        }

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        
        if ($user->isAdmin() || ($user->isTeacher() && $announcement->author_id === $user->id)) {
            $announcement->delete();
            return back()->with('success', 'Pengumuman telah dihapus.');
        }

        abort(403, 'Anda tidak berhak menghapus pengumuman ini.');
    }
}
