<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'default_start_time',
        'default_end_time',
        'late_tolerance_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'late_tolerance_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }
}
