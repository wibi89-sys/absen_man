<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

    /**
     * In-memory cache for the current request lifecycle.
     */
    protected static ?self $runtimeInstance = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::$runtimeInstance = null;
            Cache::forget('school_setting_attributes');
            Cache::forget('school_setting');
        });

        static::deleted(function () {
            static::$runtimeInstance = null;
            Cache::forget('school_setting_attributes');
            Cache::forget('school_setting');
        });
    }

    /**
     * Ensure the school_settings table exists in database.
     */
    public static function ensureTableExists(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        try {
            if (! Schema::hasTable('school_settings')) {
                try {
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Throwable $migEx) {
                    Log::warning('Artisan migrate call failed: '.$migEx->getMessage());
                }

                if (! Schema::hasTable('school_settings')) {
                    Schema::create('school_settings', function (Blueprint $table) {
                        $table->id();
                        $table->string('school_name')->default('MADRASAH ONE');
                        $table->string('school_slogan')->nullable()->default('Integrated Student Monitoring System');
                        $table->string('npsn', 20)->nullable()->default('20261101');
                        $table->string('nsm', 30)->nullable()->default('131132730001');
                        $table->string('school_level', 50)->default('Madrasah Aliyah (MA)');
                        $table->string('principal_name')->default('Dr. H. Ahmad Fauzi, M.Pd.');
                        $table->string('principal_nip', 30)->nullable()->default('197508152002121003');
                        $table->text('school_address')->nullable();
                        $table->string('school_phone', 30)->nullable()->default('(022) 7891234');
                        $table->string('school_email')->nullable()->default('info@madrasah-one.sch.id');
                        $table->string('school_website')->nullable()->default('https://absen.laksanatech.com');
                        $table->string('logo_path')->nullable();
                        $table->string('favicon_path')->nullable();
                        $table->boolean('enable_demo_mode')->default(false);
                        $table->string('gate_welcome_text')->nullable()->default('Selamat Datang di Madrasah Tercinta. Awali Hari dengan Senyum dan Semangat!');
                        $table->timestamps();
                    });
                }
            }
        } catch (\Throwable $e) {
            Log::error('SchoolSetting::ensureTableExists failed: '.$e->getMessage());
        }
    }

    /**
     * Retrieve current school settings safely without model serialization bugs.
     */
    public static function current(): self
    {
        if (static::$runtimeInstance !== null) {
            return static::$runtimeInstance;
        }

        self::ensureTableExists();

        // Clear legacy corrupted cache if present
        if (Cache::has('school_setting')) {
            $legacy = Cache::get('school_setting');
            if (! ($legacy instanceof self)) {
                Cache::forget('school_setting');
            }
        }

        $attributes = Cache::rememberForever('school_setting_attributes', function () {
            try {
                if (Schema::hasTable('school_settings')) {
                    $setting = self::firstOrCreate(
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
                            'enable_demo_mode' => false,
                            'gate_welcome_text' => 'Selamat Datang di Madrasah Tercinta. Awali Hari dengan Senyum dan Semangat!',
                        ]
                    );

                    return $setting->toArray();
                }
            } catch (\Throwable $e) {
                Log::error('SchoolSetting::current query failed: '.$e->getMessage());
            }

            return [
                'id' => 1,
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
                'enable_demo_mode' => false,
                'gate_welcome_text' => 'Selamat Datang di Madrasah Tercinta. Awali Hari dengan Senyum dan Semangat!',
            ];
        });

        if (! is_array($attributes)) {
            Cache::forget('school_setting_attributes');
            $attributes = [];
        }

        $model = new self;
        $model->forceFill($attributes);
        $model->id = $attributes['id'] ?? 1;
        $model->exists = isset($attributes['created_at']);

        static::$runtimeInstance = $model;

        return $model;
    }

    /**
     * Clear all setting caches.
     */
    public static function clearCache(): void
    {
        static::$runtimeInstance = null;
        Cache::forget('school_setting_attributes');
        Cache::forget('school_setting');
    }

    /**
     * Get URL for the logo or null if not set.
     */
    public function getLogoUrlAttribute(): ?string
    {
        try {
            if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
                return asset('storage/'.$this->logo_path);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Get URL for the favicon or fallback to school logo or default vector favicon.
     */
    public function getFaviconUrlAttribute(): string
    {
        try {
            if ($this->favicon_path && Storage::disk('public')->exists($this->favicon_path)) {
                return asset('storage/'.$this->favicon_path).'?v='.($this->updated_at?->timestamp ?? time());
            }
            if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
                return asset('storage/'.$this->logo_path).'?v='.($this->updated_at?->timestamp ?? time());
            }
        } catch (\Throwable) {
            // Fallback
        }

        return asset('favicon.svg');
    }

    /**
     * Determine if a custom or converted favicon exists.
     */
    public function getHasCustomFaviconAttribute(): bool
    {
        try {
            return ! empty($this->favicon_path) && Storage::disk('public')->exists($this->favicon_path);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get initials of the school name (e.g. "M1" or "MAN1").
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->school_name ?? 'MADRASAH ONE');
        if (empty($name)) {
            return 'M1';
        }

        $words = preg_split('/\s+/', $name);
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
