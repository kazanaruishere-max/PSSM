<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id', 'question_type', 'question_text', 'options',
        'correct_answer_hash', 'explanation', 'points', 'order_number',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'points' => 'integer',
            'order_number' => 'integer',
        ];
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function setCorrectAnswer(string $answer): void
    {
        $this->correct_answer_hash = Hash::make($answer);
    }

    public function checkAnswer(string $answer): bool
    {
        return Hash::check($answer, $this->correct_answer_hash);
    }
}
