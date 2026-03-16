<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Assignment extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'teacher_id', 'class_id', 'subject_id', 'title',
        'description', 'attachment_path', 'deadline',
        'max_score', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'max_score' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['title', 'deadline', 'is_published'])->logOnlyDirty();
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class_()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActive($query)
    {
        return $query->where('deadline', '>=', now());
    }

    public function isPastDeadline(): bool
    {
        return $this->deadline->isPast();
    }
}
