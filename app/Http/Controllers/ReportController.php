<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Quiz;
use App\Models\Classes;
use App\Models\User;
use App\Models\Submission;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Export all latest submissions for a specific Assignment to CSV.
     */
    public function exportAssignmentData(Request $request, Assignment $assignment)
    {
        // Must be the teacher of this assignment or admin
        if ($request->user()->cannot('assignments.grade') || ($assignment->teacher_id !== $request->user()->id && !$request->user()->hasRole('super_admin'))) {
            abort(403, 'Unauthorized access to export report.');
        }

        $assignment->load('subject', 'class');
        
        // Get the latest submission for each student
        $submissions = $assignment->submissions()
            ->with('student')
            ->whereIn('version', function($query) use ($assignment) {
                $query->selectRaw('MAX(version)')
                      ->from('submissions')
                      ->where('assignment_id', $assignment->id)
                      ->groupBy('student_id');
            })
            ->get();

        // Fix #10: Sanitize filename to prevent HTTP header injection
        $safeTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $assignment->title);
        $safeClass = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $assignment->class->name);
        $fileName = "Nilai_Tugas_{$safeTitle}_{$safeClass}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($submissions) {
            $file = fopen('php://output', 'w');
            
            // CSV Header row
            fputcsv($file, ['Nama Siswa', 'Waktu Submit', 'Status', 'Nilai', 'Feedback']);

            foreach ($submissions as $sub) {
                $status = $sub->graded_at ? 'Dinilai' : 'Belum Dinilai';
                $score = $sub->score !== null ? $sub->score : '-';
                $feedback = $sub->feedback ?? '';
                $submittedAt = $sub->submitted_at ? $sub->submitted_at->format('Y-m-d H:i:s') : '-';

                fputcsv($file, [
                    $sub->student->name,
                    $submittedAt,
                    $status,
                    $score,
                    $feedback
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export all attempts for a specific Quiz to CSV.
     */
    public function exportQuizData(Request $request, Quiz $quiz)
    {
        // Must be the teacher of this quiz or admin
        if ($request->user()->cannot('quizzes.delete') && $quiz->teacher_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized access to export report.');
        }

        $quiz->load('subject', 'class');
        $attempts = $quiz->attempts()->with('student')->orderBy('student_id')->orderBy('attempt_number')->get();

        // Fix #10: Sanitize filename to prevent HTTP header injection
        $safeTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $quiz->title);
        $safeClass = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $quiz->class->name);
        $fileName = "Nilai_Kuis_{$safeTitle}_{$safeClass}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($attempts) {
            $file = fopen('php://output', 'w');
            
            // CSV Header row
            fputcsv($file, ['Nama Siswa', 'Upaya Ke', 'Waktu Mulai', 'Waktu Selesai', 'Status', 'Nilai']);

            foreach ($attempts as $attempt) {
                $status = $attempt->submitted_at ? 'Selesai' : 'Belum/Terputus';
                $score = $attempt->score !== null ? $attempt->score : '-';
                
                fputcsv($file, [
                    $attempt->student->name,
                    $attempt->attempt_number,
                    $attempt->started_at->format('Y-m-d H:i:s'),
                    $attempt->submitted_at ? $attempt->submitted_at->format('Y-m-d H:i:s') : '-',
                    $status,
                    $score
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export student report card to PDF.
     */
    public function exportReportCard(Request $request, Classes $class, User $student)
    {
        // Only student's teacher, admin, or student themselves can access
        $user = $request->user();
        if (!$user->hasRole('super_admin') && 
            $class->homeroom_teacher_id !== $user->id && 
            $student->id !== $user->id) {
            abort(403);
        }

        $class->load(['academicYear', 'homeroomTeacher', 'subjects']);
        $student->load('studentProfile');

        // Calculate grades for each subject in this class
        $grades = [];
        foreach ($class->subjects as $subject) {
            // Average from assignments
            $assignmentAvg = Submission::where('student_id', $student->id)
                ->whereHas('assignment', function($q) use ($class, $subject) {
                    $q->where('class_id', $class->id)->where('subject_id', $subject->id);
                })
                ->whereNotNull('score')
                ->avg('score');

            // Average from quizzes
            $quizAvg = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->where('quiz_attempts.student_id', $student->id)
                ->where('quizzes.class_id', $class->id)
                ->where('quizzes.subject_id', $subject->id)
                ->whereNotNull('quiz_attempts.score')
                ->avg('quiz_attempts.score');

            $finalScore = 0;
            if ($assignmentAvg && $quizAvg) {
                $finalScore = ($assignmentAvg * 0.6) + ($quizAvg * 0.4); // 60% Tugas, 40% Kuis
            } elseif ($assignmentAvg) {
                $finalScore = $assignmentAvg;
            } elseif ($quizAvg) {
                $finalScore = $quizAvg;
            }

            $finalScore = round($finalScore, 2);
            
            $predicate = 'C';
            if ($finalScore >= 85) $predicate = 'A (Sangat Baik)';
            elseif ($finalScore >= 75) $predicate = 'B (Baik)';
            elseif ($finalScore >= 60) $predicate = 'C (Cukup)';
            else $predicate = 'D (Perlu Bimbingan)';

            $grades[] = [
                'subject_name' => $subject->name,
                'average_score' => $finalScore ?: '-',
                'predicate' => $predicate
            ];
        }

        $pdf = Pdf::loadView('pdf.report_card', compact('student', 'class', 'grades'));
        
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $student->name);
        return $pdf->download("Rapor_{$safeName}.pdf");
    }
}
