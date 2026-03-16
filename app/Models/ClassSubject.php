<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    protected $table = 'class_subject';

    protected $fillable = ['class_id', 'subject_id', 'teacher_id', 'schedule'];

    protected function casts(): array
    {
        return ['schedule' => 'array'];
    }

    public function class_()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
