<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorshipQrToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'worship_session_id',
        'token',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorshipSession::class, 'worship_session_id');
    }

    public function isValid(?Carbon $time = null): bool
    {
        $time = $time ?? now();

        return $this->is_active && $time->greaterThanOrEqualTo($this->valid_from) && $time->lessThanOrEqualTo($this->valid_until);
    }
}
