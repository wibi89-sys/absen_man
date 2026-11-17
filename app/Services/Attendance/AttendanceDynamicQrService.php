<?php

namespace App\Services\Attendance;

use App\Models\AttendanceQrToken;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceDynamicQrService
{
    /**
     * Hasilkan token QR baru yang terenkripsi waktu (HMAC SHA-256) berotasi setiap 20-30 detik.
     */
    public function generateToken(AttendanceSession $session, int $ttlSeconds = 25): AttendanceQrToken
    {
        return DB::transaction(function () use ($session, $ttlSeconds) {
            // Nonaktifkan token aktif sebelumnya pada sesi ini
            AttendanceQrToken::where('attendance_session_id', $session->id)
                ->where('is_revoked', false)
                ->update(['is_revoked' => true]);

            $nowEpoch = time();
            $expiresEpoch = $nowEpoch + $ttlSeconds;

            // Generate payload kriptografis unik
            $rawPayload = "GATE_ATT_{$session->id}_{$nowEpoch}_".Str::random(32);
            $tokenHash = hash_hmac('sha256', $rawPayload, config('app.key'));

            return AttendanceQrToken::create([
                'attendance_session_id' => $session->id,
                'token' => $tokenHash,
                'created_at_epoch' => $nowEpoch,
                'expires_at_epoch' => $expiresEpoch,
                'is_revoked' => false,
            ]);
        });
    }

    /**
     * Validasi keabsahan token QR dari pemindaian kamera ponsel siswa.
     */
    public function validateToken(AttendanceSession $session, string $tokenString): ?AttendanceQrToken
    {
        $qrToken = AttendanceQrToken::where('attendance_session_id', $session->id)
            ->where('token', $tokenString)
            ->where('is_revoked', false)
            ->first();

        if (! $qrToken || ! $qrToken->isValid()) {
            return null;
        }

        return $qrToken;
    }
}
