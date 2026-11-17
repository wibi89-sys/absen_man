<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplineRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'category_id',
        'date',
        'description',
        'action_taken',
        'reported_by',
        'status',
        'points_awarded',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'points_awarded' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DisciplineCategory::class, 'category_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'bg-amber-100 text-amber-800',
            'processed' => 'bg-blue-100 text-blue-800',
            'resolved' => 'bg-emerald-100 text-emerald-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'reported' => 'Dilaporkan',
            'processed' => 'Dalam Penanganan',
            'resolved' => 'Terselesaikan',
            default => ucfirst($this->status),
        };
    }
}
