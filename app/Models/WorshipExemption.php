<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorshipExemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'start_date',
        'end_date',
        'reason_category',
        'notes',
        'approved_by',
        'is_confidential',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_confidential' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeActiveOnDate(Builder $query, Carbon|string $date): Builder
    {
        $dateStr = is_string($date) ? $date : $date->toDateString();

        return $query->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->reason_category) {
            'sharia_halangan' => "Halangan Syar'i",
            'sick' => 'Sakit',
            'emergency' => 'Kondisi Darurat',
            'dispensation' => 'Dispensasi Resmi',
            default => ucfirst($this->reason_category),
        };
    }
}
