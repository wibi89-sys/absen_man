<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorshipSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'worship_type_id',
        'worship_schedule_id',
        'date',
        'wave_number',
        'location_id',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'wave_number' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(WorshipType::class, 'worship_type_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorshipSchedule::class, 'worship_schedule_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(SchoolLocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(WorshipSessionParticipant::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(WorshipAttendance::class);
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(WorshipQrToken::class);
    }

    public function activeQrToken(): HasOne
    {
        return $this->hasOne(WorshipQrToken::class)
            ->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->latest('valid_from');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'bg-slate-100 text-slate-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-amber-100 text-amber-800',
            'verified' => 'bg-emerald-100 text-emerald-800',
            'cancelled' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Dijadwalkan',
            'in_progress' => 'Sedang Berlangsung',
            'completed' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi Final',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
