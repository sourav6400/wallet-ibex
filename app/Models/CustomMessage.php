<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_type',
        'message',
        'user_id',
        'is_active',
        'is_global',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('starts_at')
              ->orWhere('starts_at', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('ends_at')
              ->orWhere('ends_at', '>=', $now);
        });
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}