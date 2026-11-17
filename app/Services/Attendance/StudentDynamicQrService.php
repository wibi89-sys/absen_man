<?php

namespace App\Services\Attendance;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class StudentDynamicQrService
{
    /**
     * Durasi jendela waktu token dinamis dalam detik (rotasi setiap 20 detik).
     */
    public const WINDOW_SECONDS = 20;

    /**
     * Hasilkan token QR dinamis untuk Kartu OSIS Digital siswa.
     * Menggunakan time-sliced HMAC SHA-256 (seperti sistem TOTP / Google Authenticator).
     */
    public function generateToken(Student $student): array
    {
        $nowEpoch = time();
        $timeWindow = (int) floor($nowEpoch / self::WINDOW_SECONDS);
        $remainingSeconds = self::WINDOW_SECONDS - ($nowEpoch % self::WINDOW_SECONDS);

        $payload = "STUDENT_{$student->id}_{$student->nis}_{$timeWindow}";
        $signature = hash_hmac('sha256', $payload, config('app.key'));
        $shortHash = substr($signature, 0, 16);

        // Format token: STDYN.<student_id>.<time_window>.<shortHash>
        $token = "STDYN.{$student->id}.{$timeWindow}.{$shortHash}";

        return [
            'token' => $token,
            'expires_in' => $remainingSeconds,
            'time_window' => $timeWindow,
            'student_id' => $student->id,
            'timestamp' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Validasi keabsahan token QR Kartu OSIS Digital yang dipindai di gerbang.
     * Mencegah screenshot lama / titip absen via chat.
     */
    public function validateToken(string $tokenString): ?Student
    {
        if (! str_starts_with($tokenString, 'STDYN.')) {
            return null;
        }

        $parts = explode('.', $tokenString);
        if (count($parts) !== 4) {
            return null;
        }

        [, $studentId, $tokenWindow, $providedHash] = $parts;
        $studentId = (int) $studentId;
        $tokenWindow = (int) $tokenWindow;

        $student = Student::with('currentClass')->find($studentId);
        if (! $student || ! in_array($student->status, ['active', 'aktif'])) {
            return null;
        }

        $currentWindow = (int) floor(time() / self::WINDOW_SECONDS);

        // Toleransi jendela waktu: jendela saat ini atau 1 jendela sebelumnya (toleransi ~20 detik)
        if ($tokenWindow < ($currentWindow - 1) || $tokenWindow > $currentWindow) {
            return null; // Kedaluwarsa atau jam tidak sinkron
        }

        // Verifikasi integritas tanda tangan HMAC kriptografis
        $expectedPayload = "STUDENT_{$student->id}_{$student->nis}_{$tokenWindow}";
        $expectedSignature = hash_hmac('sha256', $expectedPayload, config('app.key'));
        $expectedShortHash = substr($expectedSignature, 0, 16);

        if (! hash_equals($expectedShortHash, $providedHash)) {
            return null; // Tanda tangan palsu / manipulasi
        }

        // Anti-Replay: Mencegah token yang sama digunakan berulang kali dalam jendela yang sama
        $replayCacheKey = "dyn_qr_used:{$tokenString}";
        if (Cache::has($replayCacheKey)) {
            return null; // Token ini sudah pernah discan dalam detik ini
        }
        Cache::put($replayCacheKey, true, self::WINDOW_SECONDS * 2);

        return $student;
    }
}
