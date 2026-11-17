<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplineCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'points',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(DisciplineRecord::class, 'category_id');
    }

    public function getTypeBadgeAttribute(): string
    {
        return $this->type === 'achievement'
            ? 'bg-emerald-100 text-emerald-800'
            : 'bg-rose-100 text-rose-800';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'achievement' ? 'Prestasi / Apresiasi' : 'Pelanggaran Disiplin';
    }
}
