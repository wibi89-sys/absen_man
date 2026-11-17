<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'supervisor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'supervisor_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(StudentActivityMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->where('is_active', true);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentActivityAttendance::class);
    }

    public function getDayNameAttribute(): string
    {
        return match ($this->day_of_week) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Ahad',
            default => 'Fleksibel / Sesuai Jadwal',
        };
    }
}
