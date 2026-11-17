<?php

namespace App\Services\Worship;

use App\Models\Student;
use App\Models\WorshipExemption;
use App\Models\WorshipSession;
use App\Models\WorshipSessionParticipant;
use App\Models\WorshipType;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorshipSessionService
{
    public function __construct(
        protected WorshipQrService $qrService,
        protected AuditService $auditService
    ) {}

    /**
     * Create a new worship session with locked denominator participants.
     *
     * @param  array<int>  $classIds
     */
    public function createSession(
        int $worshipTypeId,
        string $date,
        int $waveNumber,
        ?int $locationId,
        array $classIds,
        int $userId,
        ?int $scheduleId = null,
        ?string $notes = null
    ): WorshipSession {
        return DB::transaction(function () use (
            $worshipTypeId,
            $date,
            $waveNumber,
            $locationId,
            $classIds,
            $userId,
            $scheduleId,
            $notes
        ) {
            $session = WorshipSession::create([
                'worship_type_id' => $worshipTypeId,
                'worship_schedule_id' => $scheduleId,
                'date' => $date,
                'wave_number' => $waveNumber,
                'location_id' => $locationId,
                'status' => 'scheduled',
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            // Query all active students in the scheduled classes
            $students = Student::whereIn('current_class_id', $classIds)
                ->whereIn('status', ['active', 'aktif'])
                ->get();

            // Find all active exemptions on this date for these students
            $exemptions = WorshipExemption::whereIn('student_id', $students->pluck('id'))
                ->activeOnDate($date)
                ->get()
                ->keyBy('student_id');

            // Bulk lock participants into the session (deterministic denominator)
            $participantRecords = [];
            $now = now();

            foreach ($students as $student) {
                $hasExemption = $exemptions->get($student->id);

                $participantRecords[] = [
                    'worship_session_id' => $session->id,
                    'student_id' => $student->id,
                    'school_class_id' => $student->current_class_id,
                    'status' => $hasExemption ? 'exempt' : 'pending_verification',
                    'scan_record_id' => null,
                    'verification_notes' => $hasExemption ? ('Dispensasi: '.$hasExemption->category_label) : null,
                    'verified_by' => $hasExemption ? $hasExemption->approved_by : null,
                    'verified_at' => $hasExemption ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($participantRecords)) {
                WorshipSessionParticipant::insert($participantRecords);
            }

            // Generate initial QR token
            $this->qrService->generateToken($session);

            AuditService::log(
                'create',
                'worship_session',
                $session->id,
                null,
                [
                    'worship_type_id' => $worshipTypeId,
                    'date' => $date,
                    'wave' => $waveNumber,
                    'classes_count' => count($classIds),
                    'total_participants' => count($participantRecords),
                ],
                $userId
            );

            return $session;
        });
    }

    /**
     * Create an Asar session specifically for eligible students (activities / staying back).
     *
     * @param  array<int>  $eligibleStudentIds
     */
    public function createAsarSession(
        string $date,
        array $eligibleStudentIds,
        ?int $locationId,
        int $userId,
        ?string $notes = null
    ): WorshipSession {
        $asarType = WorshipType::firstOrCreate(
            ['code' => 'asar'],
            ['name' => 'Salat Asar Berjamaah', 'is_mandatory' => true]
        );

        return DB::transaction(function () use ($asarType, $date, $eligibleStudentIds, $locationId, $userId, $notes) {
            $session = WorshipSession::create([
                'worship_type_id' => $asarType->id,
                'date' => $date,
                'wave_number' => 1,
                'location_id' => $locationId,
                'status' => 'scheduled',
                'created_by' => $userId,
                'notes' => $notes ?? 'Sesi Asar untuk siswa kegiatan sore/ekskul',
            ]);

            $students = Student::whereIn('id', $eligibleStudentIds)
                ->whereIn('status', ['active', 'aktif'])
                ->get();

            $exemptions = WorshipExemption::whereIn('student_id', $students->pluck('id'))
                ->activeOnDate($date)
                ->get()
                ->keyBy('student_id');

            $participantRecords = [];
            $now = now();

            foreach ($students as $student) {
                $hasExemption = $exemptions->get($student->id);

                $participantRecords[] = [
                    'worship_session_id' => $session->id,
                    'student_id' => $student->id,
                    'school_class_id' => $student->current_class_id ?? 1,
                    'status' => $hasExemption ? 'exempt' : 'pending_verification',
                    'scan_record_id' => null,
                    'verification_notes' => $hasExemption ? ('Dispensasi: '.$hasExemption->category_label) : null,
                    'verified_by' => $hasExemption ? $hasExemption->approved_by : null,
                    'verified_at' => $hasExemption ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($participantRecords)) {
                WorshipSessionParticipant::insert($participantRecords);
            }

            $this->qrService->generateToken($session);

            AuditService::log(
                'create_asar',
                'worship_session',
                $session->id,
                null,
                [
                    'date' => $date,
                    'total_participants' => count($participantRecords),
                ],
                $userId
            );

            return $session;
        });
    }

    /**
     * Verify or update a single participant's attendance status.
     */
    public function verifyParticipant(
        WorshipSessionParticipant $participant,
        string $status,
        ?string $notes,
        int $verifierId
    ): void {
        $allowedStatuses = ['present', 'late', 'absent', 'excused', 'exempt'];
        if (! in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException("Status '{$status}' tidak valid.");
        }

        $oldStatus = $participant->status;

        $participant->update([
            'status' => $status,
            'verification_notes' => $notes ?? $participant->verification_notes,
            'verified_by' => $verifierId,
            'verified_at' => now(),
        ]);

        AuditService::log(
            'verify',
            'worship_participant',
            $participant->id,
            ['status' => $oldStatus],
            ['status' => $status, 'notes' => $notes],
            $verifierId
        );
    }

    /**
     * Bulk verify participants in a session (e.g., Wali Kelas verifying their class).
     *
     * @param  array<int, string>  $statuses  [participant_id => status]
     */
    public function bulkVerify(WorshipSession $session, array $statuses, int $verifierId): void
    {
        DB::transaction(function () use ($session, $statuses, $verifierId) {
            $now = now();
            foreach ($statuses as $participantId => $status) {
                if (! in_array($status, ['present', 'late', 'absent', 'excused', 'exempt'], true)) {
                    continue;
                }

                WorshipSessionParticipant::where('id', $participantId)
                    ->where('worship_session_id', $session->id)
                    ->update([
                        'status' => $status,
                        'verified_by' => $verifierId,
                        'verified_at' => $now,
                    ]);
            }
        });
    }

    /**
     * Finalize the worship session.
     * Transitions any remaining 'pending_verification' participants to 'absent',
     * and marks the session as 'verified'.
     */
    public function finalizeSession(WorshipSession $session, int $verifierId): void
    {
        DB::transaction(function () use ($session, $verifierId) {
            $now = now();

            // Mark any unverified participants who never scanned as absent
            WorshipSessionParticipant::where('worship_session_id', $session->id)
                ->where('status', 'pending_verification')
                ->update([
                    'status' => 'absent',
                    'verification_notes' => 'Tidak hadir / tidak melakukan konfirmasi presensi',
                    'verified_by' => $verifierId,
                    'verified_at' => $now,
                ]);

            // Deactivate any active QR tokens
            $session->qrTokens()->where('is_active', true)->update(['is_active' => false]);

            $session->update([
                'status' => 'verified',
                'verified_by' => $verifierId,
                'verified_at' => $now,
            ]);

            AuditService::log(
                'finalize',
                'worship_session',
                $session->id,
                ['status' => 'in_progress'],
                ['status' => 'verified'],
                $verifierId
            );
        });
    }
}
