<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorshipSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'worship_type_id',
        'academic_year_id',
        'name',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(WorshipType::class, 'worship_type_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(WorshipScheduleGroup::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WorshipSession::class);
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
            default => 'Tidak Diketahui',
        };
    }
}
