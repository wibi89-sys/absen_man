<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorshipAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'worship_session_id',
        'student_id',
        'scanned_at',
        'qr_token_id',
        'device_fingerprint',
        'ip_address',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
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

    public function qrToken(): BelongsTo
    {
        return $this->belongsTo(WorshipQrToken::class, 'qr_token_id');
    }
}
