<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Subject;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isStudent()) {
            $attendances = Attendance::with(['class_', 'subject', 'recorder'])
                ->where('student_id', $user->id)
                ->orderBy('date', 'desc')
                ->paginate(20);
            return view('attendances.student_index', compact('attendances'));
        }

        // For Teachers or Super Admins: Show filtering form to select class & subject
        $classes = collect();
        if ($user->isAdmin()) {
            $classes = Classes::with('academicYear')->get();
        } elseif ($user->isTeacher()) {
            $classIds = $user->taughtSubjects()->pluck('class_id')->unique();
            $classes = Classes::with('academicYear')->whereIn('id', $classIds)->get();
        }

        return view('attendances.index', compact('classes'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        if ($user->isStudent()) {
            abort(403);
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
        ]);

        $class = Classes::with('students')->findOrFail($request->class_id);
        $subject = Subject::findOrFail($request->subject_id);
        
        // Ensure teacher can teach this subject in this class
        if ($user->isTeacher()) {
            $canTeach = $user->taughtSubjects()
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->exists();
                
            if (!$canTeach) {
                abort(403, 'Anda tidak mengajar mata pelajaran ini di kelas tersebut.');
            }
        }

        // Fetch existing records if any
        $existingAttendances = Attendance::where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->whereDate('date', $request->date)
            ->get()
            ->keyBy('student_id');

        return view('attendances.create', [
            'class' => $class,
            'subject' => $subject,
            'date' => $request->date,
            'students' => $class->students,
            'existingAttendances' => $existingAttendances,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->isStudent()) {
            abort(403);
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.notes' => 'nullable|string|max:255',
        ]);

        foreach ($request->attendances as $studentId => $data) {
            Attendance::updateOrCreate(
                [
                    'class_id' => $request->class_id,
                    'subject_id' => $request->subject_id,
                    'student_id' => $studentId,
                    'date' => $request->date,
                ],
                [
                    'status' => $data['status'],
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => $user->id,
                ]
            );
        }

        return redirect()->route('attendances.index')->with('success', 'Kehadiran berhasil disimpan.');
    }
}
