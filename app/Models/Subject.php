<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_subject')
            ->withPivot('teacher_id', 'schedule')
            ->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
