<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'target_role',
        'school_class_id',
        'is_pinned',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('is_pinned')
            ->latest('published_at');
    }

    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->whereIn('target_role', ['all', $role]);
    }

    public function getCategoryBadgeAttribute(): string
    {
        return match ($this->category) {
            'academic' => 'bg-blue-100 text-blue-800',
            'attendance' => 'bg-amber-100 text-amber-800',
            'worship' => 'bg-emerald-100 text-emerald-800',
            'emergency' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'academic' => 'Akademik',
            'attendance' => 'Presensi',
            'worship' => 'Ibadah',
            'emergency' => 'Penting / Darurat',
            default => 'Umum',
        };
    }
}
