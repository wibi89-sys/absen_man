<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_subject_id',
        'assessment_name',
        'score',
        'notes',
        'is_published',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'is_published' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
