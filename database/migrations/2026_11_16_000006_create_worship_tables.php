<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('worship_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->boolean('is_mandatory')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('worship_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worship_type_id')->constrained('worship_types')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name', 100);
            $table->tinyInteger('day_of_week')->comment('1=Senin, 7=Ahad');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('worship_schedule_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worship_schedule_id')->constrained('worship_schedules')->cascadeOnDelete();
            $table->unsignedTinyInteger('wave_number')->default(1);
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('school_locations')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('worship_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worship_type_id')->constrained('worship_types')->cascadeOnDelete();
            $table->foreignId('worship_schedule_id')->nullable()->constrained('worship_schedules')->nullOnDelete();
            $table->date('date')->index();
            $table->unsignedTinyInteger('wave_number')->default(1);
            $table->foreignId('location_id')->nullable()->constrained('school_locations')->nullOnDelete();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'verified', 'cancelled'])->default('scheduled')->index();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'status']);
        });

        Schema::create('worship_qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worship_session_id')->constrained('worship_sessions')->cascadeOnDelete();
            $table->string('token', 128)->unique();
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['token', 'is_active', 'valid_until']);
        });

        Schema::create('worship_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worship_session_id')->constrained('worship_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->dateTime('scanned_at');
            $table->foreignId('qr_token_id')->nullable()->constrained('worship_qr_tokens')->nullOnDelete();
            $table->string('device_fingerprint')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->enum('source', ['qr_student_camera', 'qr_officer_scanner', 'manual_officer'])->default('qr_student_camera');
            $table->timestamps();

            $table->index(['worship_session_id', 'student_id']);
        });

        Schema::create('worship_session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worship_session_id')->constrained('worship_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->enum('status', ['pending_verification', 'present', 'late', 'absent', 'excused', 'exempt'])->default('pending_verification');
            $table->foreignId('scan_record_id')->nullable()->constrained('worship_attendances')->nullOnDelete();
            $table->string('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['worship_session_id', 'student_id'], 'ws_participants_session_student_uk');
            $table->index(['worship_session_id', 'status'], 'ws_participants_session_status_idx');
        });

        Schema::create('worship_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('reason_category', ['sharia_halangan', 'sick', 'emergency', 'dispensation'])->default('sharia_halangan');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_confidential')->default(true);
            $table->timestamps();

            $table->index(['student_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worship_exemptions');
        Schema::dropIfExists('worship_session_participants');
        Schema::dropIfExists('worship_attendances');
        Schema::dropIfExists('worship_qr_tokens');
        Schema::dropIfExists('worship_sessions');
        Schema::dropIfExists('worship_schedule_groups');
        Schema::dropIfExists('worship_schedules');
        Schema::dropIfExists('worship_types');
    }
};
