<?php

namespace App\Services\Worship;

use App\Models\Student;
use App\Models\WorshipAttendance;
use App\Models\WorshipQrToken;
use App\Models\WorshipSession;
use App\Models\WorshipSessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorshipQrService
{
    /**
     * Generate a new short-lived, cryptographically secure dynamic QR token for the session.
     */
    public function generateToken(WorshipSession $session, int $ttlSeconds = 30): WorshipQrToken
    {
        return DB::transaction(function () use ($session, $ttlSeconds) {
            // Deactivate any currently active tokens for this session
            WorshipQrToken::where('worship_session_id', $session->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $validFrom = now();
            $validUntil = (clone $validFrom)->addSeconds($ttlSeconds);

            // Generate signed token hash
            $rawToken = Str::random(40).'_'.$session->id.'_'.$validFrom->timestamp;
            $tokenHash = hash_hmac('sha256', $rawToken, config('app.key'));

            return WorshipQrToken::create([
                'worship_session_id' => $session->id,
                'token' => $tokenHash,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'is_active' => true,
            ]);
        });
    }

    /**
     * Validate an incoming token string.
     */
    public function validateToken(string $tokenString): ?WorshipQrToken
    {
        $qrToken = WorshipQrToken::with('session')
            ->where('token', $tokenString)
            ->where('is_active', true)
            ->first();

        if (! $qrToken || ! $qrToken->isValid()) {
            return null;
        }

        return $qrToken;
    }

    /**
     * Process an incoming student scan.
     *
     * @return array{success: bool, message: string, data?: array}
     */
    public function recordScan(
        WorshipSession $session,
        Student $student,
        WorshipQrToken $token,
        string $source = 'qr_student_camera',
        ?string $fingerprint = null,
        ?string $ip = null
    ): array {
        // Validate token matches session
        if ($token->worship_session_id !== $session->id || ! $token->isValid()) {
            return [
                'success' => false,
                'message' => 'Token QR tidak valid atau sudah kedaluwarsa. Silakan scan ulang QR terbaru.',
            ];
        }

        // Validate session is not completed or cancelled
        if (in_array($session->status, ['completed', 'verified', 'cancelled'], true)) {
            return [
                'success' => false,
                'message' => 'Sesi ibadah ini sudah ditutup atau dibatalkan.',
            ];
        }

        return DB::transaction(function () use ($session, $student, $token, $source, $fingerprint, $ip) {
            // Check locked denominator: is the student an eligible participant of this session?
            $participant = WorshipSessionParticipant::where('worship_session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();

            if (! $participant) {
                return [
                    'success' => false,
                    'message' => 'Siswa '.$student->full_name.' ('.$student->nis.') tidak terdaftar dalam rombel sesi gelombang ini.',
                ];
            }

            // Check if already scanned (anti-replay)
            $existingScan = WorshipAttendance::where('worship_session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existingScan) {
                return [
                    'success' => false,
                    'message' => 'Siswa sudah melakukan presensi pada sesi ini pada pukul '.$existingScan->scanned_at->format('H:i:s').'.',
                ];
            }

            // Check if currently exempt
            if ($participant->status === 'exempt') {
                return [
                    'success' => false,
                    'message' => 'Siswa berstatus dispensasi pada sesi ini.',
                ];
            }

            // Create initial scan log
            $attendance = WorshipAttendance::create([
                'worship_session_id' => $session->id,
                'student_id' => $student->id,
                'scanned_at' => now(),
                'qr_token_id' => $token->id,
                'device_fingerprint' => $fingerprint,
                'ip_address' => $ip,
                'source' => $source,
            ]);

            // Auto-transition session to 'in_progress' if currently 'scheduled'
            if ($session->status === 'scheduled') {
                $session->update(['status' => 'in_progress']);
            }

            // Update participant record (pending final verification by wali kelas / petugas)
            $participant->update([
                'scan_record_id' => $attendance->id,
                'status' => 'present',
                'verification_notes' => 'Scan mandiri awal ('.now()->format('H:i:s').')',
            ]);

            return [
                'success' => true,
                'message' => 'Presensi berhasil dicatat untuk '.$student->full_name.'.',
                'data' => [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'nis' => $student->nis,
                    'class' => $student->currentClass?->name,
                    'scanned_at' => $attendance->scanned_at->format('H:i:s'),
                ],
            ];
        });
    }
}
