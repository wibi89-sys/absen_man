<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'nik',
        'full_name',
        'relationship_type',
        'occupation',
        'phone',
        'address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_parent', 'parent_id', 'student_id')
            ->withPivot(['is_primary_contact', 'is_verified', 'verified_by', 'verified_at'])
            ->withTimestamps();
    }

    public function verifiedStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('is_verified', true);
    }

    public function unverifiedStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('is_verified', false);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ParentMessage::class, 'parent_id')->latest();
    }

    /**
     * Check if this parent has legal/verified link to a student (Strict IDOR guard).
     */
    public function hasVerifiedChild(int|Student $student): bool
    {
        $studentId = $student instanceof Student ? $student->id : $student;

        return $this->verifiedStudents()->where('students.id', $studentId)->exists();
    }
}
