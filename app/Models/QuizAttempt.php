<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id', 'student_id', 'attempt_number', 'started_at',
        'submitted_at', 'score', 'answers', 'time_taken_seconds',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'answers' => 'array',
            'score' => 'integer',
            'time_taken_seconds' => 'integer',
            'attempt_number' => 'integer',
        ];
    }

    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }

    public function isExpired(): bool
    {
        if (!$this->started_at || !$this->quiz) return false;
        return now()->gt($this->started_at->addMinutes($this->quiz->duration_minutes));
    }
}
