<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeSubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use App\Services\AIService;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SubmissionController extends Controller
{
    public function __construct(
        private SubmissionService $submissionService,
        private AIService $aiService
    ) {
    }

    /**
     * Store a new submission from a student.
     */
    public function store(Request $request, Assignment $assignment)
    {
        $request->validate([
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:20480', // 20MB
        ]);

        try {
            $this->submissionService->submit($assignment, $request->user(), $request->all());
            
            return redirect()->route('assignments.show', $assignment)
                ->with('success', 'Tugas berhasil dikumpulkan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Grade a student's submission.
     */
    public function grade(GradeSubmissionRequest $request, Submission $submission)
    {
        // Fix #2: IDOR protection — ensure teacher owns this assignment
        $user = $request->user();
        if (!$user->hasRole('super_admin') && $submission->assignment->teacher_id !== $user->id) {
            abort(403, 'Anda tidak berhak menilai tugas ini.');
        }

        $data = $request->validated();
        
        $submission->update([
            'score' => $data['score'],
            'teacher_feedback' => $data['feedback'], // Encrypted by Model cast
            'graded_by' => $request->user()->id,
            'graded_at' => now(),
        ]);

        return redirect()->route('assignments.show', $submission->assignment_id)
            ->with('success', 'Nilai berhasil disimpan.');
    }

    /**
     * Generate AI-powered feedback for an essay submission.
     */
    public function generateAIFeedback(Request $request, Submission $submission)
    {
        $user = $request->user();
        
        // Security check: Only teacher of this assignment or admin
        if (!$user->hasRole('super_admin') && $submission->assignment->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if (empty($submission->content)) {
            return back()->with('error', 'Tidak ada teks essay untuk dianalisis AI.');
        }

        try {
            $feedback = $this->aiService->generateEssayFeedback(
                $submission->content,
                $submission->assignment->title,
                $submission->assignment->description ?? ''
            );

            $submission->update(['ai_feedback' => $feedback]);

            return back()->with('success', 'Feedback AI berhasil dihasilkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memanggil AI: ' . $e->getMessage());
        }
    }

    /**
     * Download a submission file.
     */
    public function download(Request $request, Submission $submission)
    {
        $user = $request->user();

        // Only allow student who owns it, or teacher/admin
        if ($user->cannot('assignments.grade') && $submission->student_id !== $user->id) {
            abort(403, 'Unauthorized to view this file.');
        }

        if (!$submission->file_path || !Storage::disk('private')->exists($submission->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('private')->download($submission->file_path);
    }
}
