<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceQrToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_session_id',
        'token',
        'created_at_epoch',
        'expires_at_epoch',
        'is_revoked',
    ];

    protected function casts(): array
    {
        return [
            'created_at_epoch' => 'integer',
            'expires_at_epoch' => 'integer',
            'is_revoked' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function isValid(): bool
    {
        return ! $this->is_revoked && time() <= $this->expires_at_epoch;
    }
}
