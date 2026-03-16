<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'class_id', 'subject_id', 'student_id', 'date',
        'status', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function class_() { return $this->belongsTo(Classes::class, 'class_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }
}
