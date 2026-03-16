<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id', 'student_id_number', 'date_of_birth',
        'parent_name', 'parent_phone', 'parent_email',
        'address', 'enrollment_year',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'enrollment_year' => 'integer',
        ];
    }

    // ── Encrypted Attributes (UU PDP Compliance) ──

    public function setParentPhoneAttribute($value)
    {
        $this->attributes['parent_phone'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getParentPhoneAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setParentEmailAttribute($value)
    {
        $this->attributes['parent_email'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getParentEmailAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAddressAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
