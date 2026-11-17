<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorshipType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_mandatory',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(WorshipSchedule::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WorshipSession::class);
    }
}
