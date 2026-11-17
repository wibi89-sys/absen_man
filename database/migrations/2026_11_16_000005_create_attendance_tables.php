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
        // 1. Jenis Absensi Sekolah
        Schema::create('attendance_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // masuk, pulang, apel
            $table->string('name', 100);
            $table->time('default_start_time')->default('06:30:00');
            $table->time('default_end_time')->default('07:15:00');
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(15);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Sesi Absensi Harian Sekolah
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('title')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['attendance_type_id', 'date'], 'unique_session_per_type_date');
            $table->index(['date', 'status']);
        });

        // 3. Pencatatan Kehadiran Harian Siswa
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'late', 'sick', 'excused', 'absent', 'early_departure']);
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->unsignedSmallInteger('minutes_late')->default(0);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('source', ['manual_piket', 'manual_homeroom', 'qr_scan', 'system'])->default('manual_piket');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Mencegah duplikasi absensi siswa pada sesi yang sama!
            $table->unique(['attendance_session_id', 'student_id'], 'unique_student_per_session');
            $table->index(['date', 'status']);
            $table->index(['student_id', 'date']);
        });

        // 4. Koreksi & Audit Log Perubahan Kehadiran
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_attendance_id')->constrained()->cascadeOnDelete();
            $table->string('old_status', 30);
            $table->string('new_status', 30);
            $table->text('reason');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->index('daily_attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('daily_attendances');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('attendance_types');
    }
};
