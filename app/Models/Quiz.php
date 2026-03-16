<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id', 'class_id', 'subject_id', 'title', 'description',
        'duration_minutes', 'start_time', 'end_time', 'max_score',
        'max_attempts', 'is_published', 'is_ai_generated',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'duration_minutes' => 'integer',
            'max_score' => 'integer',
            'max_attempts' => 'integer',
            'is_published' => 'boolean',
            'is_ai_generated' => 'boolean',
        ];
    }

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function class_() { return $this->belongsTo(Classes::class, 'class_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function questions() { return $this->hasMany(QuizQuestion::class); }
    public function attempts() { return $this->hasMany(QuizAttempt::class); }

    public function isAvailable(): bool
    {
        $now = now();
        return $this->is_published
            && (!$this->start_time || $now->gte($this->start_time))
            && (!$this->end_time || $now->lte($this->end_time));
    }
}
