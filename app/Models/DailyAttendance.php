<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'date',
        'status',
        'check_in_time',
        'check_out_time',
        'minutes_late',
        'recorded_by',
        'source',
        'latitude',
        'longitude',
        'distance_meters',
        'device_info',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'minutes_late' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'distance_meters' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'present' => 'bg-emerald-100 text-emerald-800',
            'late' => 'bg-amber-100 text-amber-800',
            'sick' => 'bg-blue-100 text-blue-800',
            'excused' => 'bg-purple-100 text-purple-800',
            'absent' => 'bg-rose-100 text-rose-800',
            'early_departure' => 'bg-orange-100 text-orange-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Hadir',
            'late' => "Terlambat ({$this->minutes_late} mnt)",
            'sick' => 'Sakit',
            'excused' => 'Izin',
            'absent' => 'Alpa (Tanpa Keterangan)',
            'early_departure' => 'Pulang Awal',
            default => ucfirst($this->status),
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'manual_piket' => 'Guru Piket',
            'manual_homeroom' => 'Wali Kelas',
            'self_mobile_gps' => 'GPS Mandiri Siswa',
            'dynamic_student_qr_card' => 'Kartu OSIS Dinamis (Anti-Screenshot)',
            'student_qr_scan' => 'Scan Kartu OSIS (Pos Gerbang)',
            'dynamic_qr_scan' => 'Scan QR Gerbang/Kelas',
            'nfc_tap' => 'Tap Kartu NFC/RFID',
            'machine_lan' => 'Mesin Fingerprint LAN',
            default => ucfirst(str_replace('_', ' ', $this->source)),
        };
    }
}
