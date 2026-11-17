<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'port',
        'comm_key',
        'protocol',
        'location_name',
        'is_active',
        'last_sync_at',
        'last_status',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'is_active' => 'boolean',
            'last_sync_at' => 'datetime',
        ];
    }
}
