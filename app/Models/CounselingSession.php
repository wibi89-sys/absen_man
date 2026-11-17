<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'counselor_id',
        'session_date',
        'category',
        'confidentiality_level',
        'status',
        'case_background',
        'action_plan',
        'recommendations',
        'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'follow_up_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'counselor_id');
    }

    /**
     * Strict privacy rule: Only Guru BK and Super Admin can view confidential details.
     */
    public function canViewDetails(?User $user = null): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['guru_bk', 'super_admin']);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'pribadi' => 'Konseling Pribadi',
            'sosial' => 'Hubungan Sosial / Teman',
            'belajar' => 'Kesulitan Belajar / Akademik',
            'karir' => 'Bimbingan Karir & Studi Lanjut',
            'kedisiplinan' => 'Kasus Perilaku & Kedisiplinan',
            default => ucfirst($this->category),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'bg-slate-100 text-slate-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-emerald-100 text-emerald-800',
            'follow_up_needed' => 'bg-amber-100 text-amber-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Dijadwalkan',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'follow_up_needed' => 'Perlu Tindak Lanjut',
            default => ucfirst($this->status),
        };
    }
}
