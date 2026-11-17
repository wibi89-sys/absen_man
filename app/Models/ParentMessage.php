<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'parent_id',
        'sender_id',
        'title',
        'message',
        'category',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function getCategoryBadgeAttribute(): string
    {
        return match ($this->category) {
            'academic' => 'bg-blue-100 text-blue-800',
            'attendance' => 'bg-amber-100 text-amber-800',
            'worship' => 'bg-emerald-100 text-emerald-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'academic' => 'Perkembangan Akademik',
            'attendance' => 'Kehadiran Sekolah',
            'worship' => 'Monitoring Ibadah',
            default => 'Komunikasi Umum',
        };
    }
}
