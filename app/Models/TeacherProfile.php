<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id', 'teacher_id_number', 'specialization', 'phone',
    ];

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getPhoneAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
