<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $classes = Classes::with(['academicYear', 'homeroomTeacher', 'subjects'])->orderBy('grade_level')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('year')->get();
        $teachers = User::role('teacher')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.classes.index', compact('classes', 'academicYears', 'teachers', 'subjects'));
    }

    public function store(Request $request)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'grade_level' => 'required|integer|min:1|max:12',
            'academic_year_id' => 'required|exists:academic_years,id',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        DB::beginTransaction();
        try {
            $class = Classes::create([
                'name' => $validated['name'],
                'grade_level' => $validated['grade_level'],
                'academic_year_id' => $validated['academic_year_id'],
                'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?? null,
            ]);

            if (!empty($validated['subjects'])) {
                // Attach subjects to pivot table
                $class->subjects()->attach($validated['subjects']);
            }

            DB::commit();
            return redirect()->route('classes.index')->with('success', 'Kelas berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Classes $class)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'grade_level' => 'required|integer|min:1|max:12',
            'academic_year_id' => 'required|exists:academic_years,id',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        DB::beginTransaction();
        try {
            $class->update([
                'name' => $validated['name'],
                'grade_level' => $validated['grade_level'],
                'academic_year_id' => $validated['academic_year_id'],
                'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?? null,
            ]);

            if (isset($validated['subjects'])) {
                $class->subjects()->sync($validated['subjects']);
            } else {
                $class->subjects()->detach();
            }

            DB::commit();
            return redirect()->route('classes.index')->with('success', 'Kelas berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Request $request, Classes $class)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $class->load(['academicYear', 'homeroomTeacher', 'subjects', 'students.studentProfile']);
        
        // Get all active students not already in this class
        $enrolledStudentIds = $class->students->pluck('id')->toArray();
        $availableStudents = User::role('student')
            ->whereNotIn('id', $enrolledStudentIds)
            ->where('is_active', true)
            ->with('studentProfile')
            ->orderBy('name')
            ->get();

        // Get all active teachers
        $availableTeachers = User::role('teacher')
            ->where('is_active', true)
            ->with('teacherProfile')
            ->orderBy('name')
            ->get();

        return view('admin.classes.show', compact('class', 'availableStudents', 'availableTeachers'));
    }

    public function assignStudents(Request $request, Classes $class)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        $class->students()->syncWithoutDetaching($validated['student_ids']);

        return back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function removeStudent(Request $request, Classes $class, User $student)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $class->students()->detach($student->id);

        return back()->with('success', 'Siswa berhasil dihapus dari kelas.');
    }

    public function assignTeacher(Request $request, Classes $class, Subject $subject)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $validated = $request->validate([
            'teacher_id' => 'nullable|exists:users,id'
        ]);

        // Update the pivot table for this class and subject
        if ($validated['teacher_id']) {
            DB::table('class_subject')
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->update(['teacher_id' => $validated['teacher_id']]);
        } else {
             DB::table('class_subject')
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->update(['teacher_id' => null]);
        }

        return back()->with('success', 'Wali / Guru Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy(Request $request, Classes $class)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        // Check for dependencies, like students assigned to this class
        if ($class->students()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus kelas karena masih ada siswa yang terdaftar.');
        }

        $class->subjects()->detach();
        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
