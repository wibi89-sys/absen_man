<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorshipSessionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'worship_session_id',
        'student_id',
        'school_class_id',
        'status',
        'scan_record_id',
        'verification_notes',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorshipSession::class, 'worship_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function scanRecord(): BelongsTo
    {
        return $this->belongsTo(WorshipAttendance::class, 'scan_record_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending_verification' => 'bg-amber-100 text-amber-800',
            'present' => 'bg-emerald-100 text-emerald-800',
            'late' => 'bg-orange-100 text-orange-800',
            'absent' => 'bg-rose-100 text-rose-800',
            'excused' => 'bg-blue-100 text-blue-800',
            'exempt' => 'bg-purple-100 text-purple-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_verification' => 'Menunggu Verifikasi',
            'present' => 'Hadir Berjamaah',
            'late' => 'Masbuk / Terlambat',
            'absent' => 'Tidak Hadir',
            'excused' => 'Izin',
            'exempt' => 'Dispensasi',
            default => ucfirst($this->status),
        };
    }

    /**
     * Safely format notes to prevent privacy leakage of female health / sharia exemptions.
     */
    public function getSafeNotesForUser(?User $user = null): string
    {
        if ($this->status === 'exempt') {
            if ($user && $user->hasAnyRole(['petugas_keagamaan', 'super_admin', 'admin', 'guru_bk'])) {
                return $this->verification_notes ?? 'Dispensasi Terdaftar';
            }

            return 'Dispensasi';
        }

        return $this->verification_notes ?? '-';
    }
}
