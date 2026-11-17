<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentActivityAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_activity_id',
        'date',
        'student_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
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
