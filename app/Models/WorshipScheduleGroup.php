<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorshipScheduleGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'worship_schedule_id',
        'wave_number',
        'school_class_id',
        'location_id',
    ];

    protected function casts(): array
    {
        return [
            'wave_number' => 'integer',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorshipSchedule::class, 'worship_schedule_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(SchoolLocation::class);
    }
}
