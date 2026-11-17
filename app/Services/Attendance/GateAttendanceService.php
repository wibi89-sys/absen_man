<?php

namespace App\Services\Attendance;

use App\Models\AttendanceSession;
use App\Models\DailyAttendance;
use App\Models\Student;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GateAttendanceService
{
    /**
     * Catat presensi masuk harian siswa dari berbagai sumber multi-metode:
     * - self_mobile_gps: Siswa tekan tombol di HP dengan verifikasi radius GPS
     * - student_qr_scan: Petugas/kiosk scan QR pada kartu OSIS siswa
     * - dynamic_qr_scan: Siswa scan QR dinamis di layar gerbang/kelas
     * - nfc_tap: Siswa tap kartu pelajar RFID/NFC
     * - machine_lan: Sinkronisasi log mesin absensi Solution X100C via LAN
     */
    public function recordAttendance(
        Student $student,
        AttendanceSession $session,
        string $source,
        ?float $latitude = null,
        ?float $longitude = null,
        ?int $distanceMeters = null,
        ?string $deviceInfo = null,
        ?int $userId = null
    ): array {
        return DB::transaction(function () use (
            $student,
            $session,
            $source,
            $latitude,
            $longitude,
            $distanceMeters,
            $deviceInfo,
            $userId
        ) {
            $now = Carbon::now();
            $checkInTime = $now->format('H:i:s');
            $date = $session->date instanceof Carbon ? $session->date->format('Y-m-d') : (string) $session->date;

            // Cek apakah siswa sudah memiliki catatan absensi pada sesi ini
            $existing = DailyAttendance::where('attendance_session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                return [
                    'success' => true,
                    'is_duplicate' => true,
                    'status' => $existing->status,
                    'attendance' => $existing,
                    'student' => $student,
                    'message' => "Presensi untuk {$student->full_name} sudah tercatat sebelumnya pada pukul {$existing->check_in_time} WIB.",
                ];
            }

            // Hitung status Hadir atau Terlambat berdasarkan jadwal tipe absensi
            $type = $session->attendanceType;
            $endTimeStr = $type?->default_end_time ?? '07:15:00';
            $scheduledEndTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$endTimeStr}");

            $status = 'present';
            $minutesLate = 0;

            if ($now->greaterThan($scheduledEndTime)) {
                $minutesLate = $scheduledEndTime->diffInMinutes($now);
                $status = 'late';
            }

            $attendance = DailyAttendance::create([
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
                'date' => $date,
                'status' => $status,
                'check_in_time' => $checkInTime,
                'minutes_late' => $minutesLate,
                'recorded_by' => $userId ?? auth()->id(),
                'source' => $source,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'distance_meters' => $distanceMeters,
                'device_info' => $deviceInfo,
                'notes' => match ($source) {
                    'self_mobile_gps' => "Presensi GPS Mandiri (Jarak: {$distanceMeters}m)",
                    'student_qr_scan' => 'Pindai Kartu OSIS di Pos Gerbang',
                    'dynamic_qr_scan' => 'Pindai Layar QR Dinamis',
                    'nfc_tap' => 'Tap Kartu NFC/RFID',
                    'machine_lan' => 'Log Mesin Biometrik Solution X100C',
                    default => 'Presensi Mandiri Terverifikasi',
                },
            ]);

            AuditService::log(
                event: 'daily_attendance_record',
                auditableType: DailyAttendance::class,
                auditableId: $attendance->id,
                oldValues: null,
                newValues: [
                    'student_id' => $student->id,
                    'status' => $status,
                    'source' => $source,
                    'check_in_time' => $checkInTime,
                    'distance' => $distanceMeters,
                ],
                userId: $userId ?? auth()->id()
            );

            return [
                'success' => true,
                'is_duplicate' => false,
                'status' => $status,
                'attendance' => $attendance,
                'student' => $student,
                'message' => "Presensi {$student->full_name} berhasil dicatat ({$attendance->status_label}).",
            ];
        });
    }
}
