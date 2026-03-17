<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class QuizService
{
    /**
     * Start a quiz attempt securely with anti-cheat timers.
     */
    public function startAttempt(Quiz $quiz, User $student): QuizAttempt
    {
        // Check maximum attempts
        $existingAttempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->count();

        if ($existingAttempts >= $quiz->max_attempts) {
            throw ValidationException::withMessages([
                'quiz' => "Maksimum {$quiz->max_attempts} percobaan telah tercapai.",
            ]);
        }

        // Check availability timeframe
        if (now()->lt($quiz->start_time) || now()->gt($quiz->end_time)) {
            throw ValidationException::withMessages([
                'quiz' => 'Kuis saat ini tidak tersedia.',
            ]);
        }

        return QuizAttempt::create([
            'quiz_id'        => $quiz->id,
            'student_id'     => $student->id,
            'attempt_number' => $existingAttempts + 1,
            'started_at'     => now(),
        ]);
    }

    /**
     * Submit an attempt, enforcing the timer limit and auto-grading choices.
     */
    public function submitAttempt(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        if ($attempt->submitted_at) {
            throw ValidationException::withMessages([
                'quiz' => 'Kuis ini sudah dikumpulkan.',
            ]);
        }

        // Timer validation (Anti-cheat)
        $elapsed = $attempt->started_at->diffInSeconds(now());
        $maxSeconds = ($attempt->quiz->duration_minutes * 60) + 30; // 30s grace period

        if ($elapsed > $maxSeconds) {
            throw ValidationException::withMessages([
                'quiz' => 'Waktu pengerjaan telah habis. Pengumpulan ditolak.',
            ]);
        }

        // Auto Grade
        $score = $this->autoGrade($attempt->quiz, $answers);

        $attempt->update([
            'submitted_at'       => now(),
            'answers'            => $answers, // Auto-casted to JSON in Model
            'score'              => $score,
            'time_taken_seconds' => $elapsed,
        ]);

        return $attempt;
    }

    /**
     * Privately check hashes to prevent cheating.
     */
    private function autoGrade(Quiz $quiz, array $answers): int
    {
        $questions = $quiz->questions()->get();
        $correctCount = 0;
        $totalQuestions = $questions->count();

        foreach ($questions as $q) {
            $studentAnswer = $answers[$q->id] ?? null;
            
            // Check hashed answers
            if ($studentAnswer && Hash::check($studentAnswer, $q->correct_answer_hash)) {
                $correctCount++;
            }
        }

        if ($totalQuestions === 0) {
            return 0;
        }

        return (int) round(($correctCount / $totalQuestions) * $quiz->max_score);
    }
}
