<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentActivityMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_activity_id',
        'student_id',
        'role',
        'joined_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'joined_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(StudentActivity::class, 'student_activity_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
