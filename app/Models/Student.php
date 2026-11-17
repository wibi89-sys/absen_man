<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'rfid_uid',
        'qr_token',
        'full_name',
        'nickname',
        'gender',
        'current_class_id',
        'status',
        'enrollment_date',
        'photo',
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            if (empty($student->qr_token)) {
                $student->qr_token = hash('sha256', "STUDENT_{$student->nis}_".config('app.key'));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
        ];
    }

    public function getEffectiveQrTokenAttribute(): string
    {
        return $this->qr_token ?: hash('sha256', "STUDENT_{$this->nis}_".config('app.key'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'current_class_id');
    }

    public function classHistories(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentModel::class, 'student_parent', 'student_id', 'parent_id')
            ->withPivot(['is_primary_contact', 'is_verified', 'verified_by', 'verified_at'])
            ->withTimestamps();
    }

    public function verifiedParents(): BelongsToMany
    {
        return $this->parents()->wherePivot('is_verified', true);
    }

    public function parentMessages(): HasMany
    {
        return $this->hasMany(ParentMessage::class);
    }

    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class);
    }

    public function worshipParticipants(): HasMany
    {
        return $this->hasMany(WorshipSessionParticipant::class);
    }

    public function worshipExemptions(): HasMany
    {
        return $this->hasMany(WorshipExemption::class);
    }
}
