<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignmentRequest;
use App\Models\Assignment;
use App\Models\Classes;
use App\Models\Subject;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // If super admin, see all
        if ($user->hasRole('super_admin')) {
            $assignments = Assignment::with(['teacher', 'class', 'subject'])->latest()->paginate(15);
        } 
        // If teacher, see assignments they created
        elseif ($user->hasRole('teacher')) {
            $assignments = Assignment::where('teacher_id', $user->id)
                ->with(['class', 'subject'])
                ->latest()
                ->paginate(15);
        } 
        // If student, see published assignments for their classes
        else {
            $classIds = $user->classes()->pluck('classes.id');
            $assignments = Assignment::whereIn('class_id', $classIds)
                ->published()
                ->with(['teacher', 'subject'])
                ->latest()
                ->paginate(15);
        }

        return view('assignments.index', compact('assignments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Must be teacher or super_admin
        if (!$request->user()->can('assignments.create')) {
            abort(403, 'Unauthorized action.');
        }

        $classes = Classes::all(); // Alternatively, filter by teacher's classes
        $subjects = Subject::all();

        return view('assignments.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssignmentRequest $request)
    {
        $data = $request->validated();
        $data['teacher_id'] = $request->user()->id;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('assignments/attachments', 'private');
            $data['attachment_path'] = $path;
        }

        $data['is_published'] = $request->has('is_published');

        $assignment = Assignment::create($data);
        
        // Notify students if published immediately
        if ($assignment->is_published) {
            $class = Classes::with('students')->find($assignment->class_id);
            if ($class && $class->students->isNotEmpty()) {
                Notification::send($class->students, new NewAssignmentNotification($assignment));
            }
        }

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Tugas berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Assignment $assignment)
    {
        $user = $request->user();

        // Security check: If student, ensure they belong to the class and it's published
        if ($user->hasRole('student') || $user->hasRole('class_leader')) {
            if (!$assignment->is_published || !$user->classes()->where('classes.id', $assignment->class_id)->exists()) {
                abort(403, 'Unauthorized access to this assignment.');
            }
        }

        $assignment->load(['teacher', 'class', 'subject']);
        
        // Load submissions depending on role
        if ($user->can('assignments.grade')) {
            // Teachers see all latest submissions
            $submissions = $assignment->submissions()
                ->with('student')
                ->whereIn('version', function($query) use ($assignment) {
                    $query->selectRaw('MAX(version)')
                          ->from('submissions')
                          ->where('assignment_id', $assignment->id)
                          ->groupBy('student_id');
                })
                ->get();
        } else {
            // Students see only their own submission history
            $submissions = $assignment->submissions()
                ->where('student_id', $user->id)
                ->orderByDesc('version')
                ->get();
        }

        return view('assignments.show', compact('assignment', 'submissions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Assignment $assignment)
    {
        if ($request->user()->cannot('assignments.edit') && $assignment->teacher_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            abort(403);
        }

        $classes = Classes::all();
        $subjects = Subject::all();

        return view('assignments.edit', compact('assignment', 'classes', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAssignmentRequest $request, Assignment $assignment)
    {
        if ($request->user()->cannot('assignments.edit') && $assignment->teacher_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            abort(403);
        }

        $data = $request->validated();
        $data['is_published'] = $request->has('is_published');

        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($assignment->attachment_path) {
                Storage::disk('private')->delete($assignment->attachment_path);
            }
            $path = $request->file('attachment')->store('assignments/attachments', 'private');
            $data['attachment_path'] = $path;
        }

        $assignment->update($data);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Tugas berhasil diubah.');
    }

    /**
     * Download the attachment for an assignment.
     */
    public function download(Request $request, Assignment $assignment)
    {
        $user = $request->user();

        // If student, check if they belong to the class
        if ($user->hasRole('student') || $user->hasRole('class_leader')) {
            if (!$user->classes()->where('classes.id', $assignment->class_id)->exists()) {
                abort(403);
            }
        }

        if (!$assignment->attachment_path || !Storage::disk('private')->exists($assignment->attachment_path)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        return Storage::disk('private')->download($assignment->attachment_path);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Assignment $assignment)
    {
        if ($request->user()->cannot('assignments.delete') && $assignment->teacher_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            abort(403);
        }

        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('success', 'Tugas berhasil dihapus.');
    }
}
