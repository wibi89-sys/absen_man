<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_slogan',
        'npsn',
        'nsm',
        'school_level',
        'principal_name',
        'principal_nip',
        'school_address',
        'school_phone',
        'school_email',
        'school_website',
        'logo_path',
        'favicon_path',
        'enable_demo_mode',
        'gate_welcome_text',
    ];

    protected $casts = [
        'enable_demo_mode' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('school_setting');
        });

        static::deleted(function () {
            Cache::forget('school_setting');
        });
    }

    /**
     * Retrieve current school settings with permanent cache.
     */
    public static function current(): self
    {
        return Cache::rememberForever('school_setting', function () {
            return self::firstOrCreate(
                ['id' => 1],
                [
                    'school_name' => 'MADRASAH ONE',
                    'school_slogan' => 'Integrated Student Monitoring System',
                    'npsn' => '20261101',
                    'nsm' => '131132730001',
                    'school_level' => 'Madrasah Aliyah (MA)',
                    'principal_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
                    'principal_nip' => '197508152002121003',
                    'school_address' => 'Jl. Pendidikan Karakter No. 99, Kota Bandung',
                    'school_phone' => '(022) 7891234',
                    'school_email' => 'info@madrasah-one.sch.id',
                    'school_website' => 'https://absen.laksanatech.com',
                    'enable_demo_mode' => true,
                    'gate_welcome_text' => 'Selamat Datang di Madrasah Tercinta. Awali Hari dengan Senyum dan Semangat!',
                ]
            );
        });
    }

    /**
     * Get URL for the logo or null if not set.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return asset('storage/'.$this->logo_path);
        }

        return null;
    }

    /**
     * Get initials of the school name (e.g. "M1" or "MAN1").
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->school_name));
        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 2));
        }

        $initials = '';
        foreach (array_slice($words, 0, 3) as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
        }

        return $initials ?: 'M1';
    }
}
