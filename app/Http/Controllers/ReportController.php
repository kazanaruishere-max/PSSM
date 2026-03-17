<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $fileName = "Nilai_Tugas_{$assignment->title}_{$assignment->class->name}.csv";

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

        $fileName = "Nilai_Kuis_{$quiz->title}_{$quiz->class->name}.csv";

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
}
