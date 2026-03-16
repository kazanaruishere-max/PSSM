<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'author_id', 'class_id', 'title', 'content',
        'priority', 'published_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function class_() { return $this->belongsTo(Classes::class, 'class_id'); }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
