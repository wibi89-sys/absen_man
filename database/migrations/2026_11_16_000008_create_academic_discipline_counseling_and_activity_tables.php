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
        // 1. Mata Pelajaran (Subjects)
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Penugasan Guru & Mapel per Rombel (Subject Class Assignments)
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_class_id', 'subject_id', 'academic_year_id'], 'cs_class_sub_year_unique');
        });

        // 3. Nilai / Rekap Capaian Akademik (Academic Grades)
        Schema::create('academic_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_subject_id')->constrained('class_subjects')->cascadeOnDelete();
            $table->string('assessment_name', 100); // e.g. Tugas 1, UH 1, PTS, PAS
            $table->decimal('score', 5, 2);
            $table->text('notes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'is_published']);
        });

        // 4. Kategori Kedisiplinan & Prestasi (Discipline Categories)
        Schema::create('discipline_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['violation', 'achievement'])->default('violation');
            $table->smallInteger('points')->default(0); // positive for achievement, negative or positive point penalty
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 5. Catatan Kedisiplinan & Prestasi Siswa (Discipline Records)
        Schema::create('discipline_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('discipline_categories')->cascadeOnDelete();
            $table->date('date')->index();
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['reported', 'processed', 'resolved'])->default('reported');
            $table->smallInteger('points_awarded')->default(0);
            $table->timestamps();

            $table->index(['student_id', 'date']);
        });

        // 6. Sesi Bimbingan Konseling (Counseling Sessions - Strict Privacy)
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('teachers')->cascadeOnDelete();
            $table->date('session_date')->index();
            $table->enum('category', ['pribadi', 'sosial', 'belajar', 'karir', 'kedisiplinan'])->default('pribadi');
            $table->enum('confidentiality_level', ['standard', 'strictly_confidential'])->default('strictly_confidential');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'follow_up_needed'])->default('scheduled');
            $table->text('case_background'); // Dienkripsi atau hak akses strictly controlled
            $table->text('action_plan')->nullable();
            $table->text('recommendations')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['counselor_id', 'session_date']);
        });

        // 7. Kegiatan Siswa & Ekstrakurikuler (Student Activities)
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('category', ['ekstrakurikuler', 'organisasi', 'keagamaan'])->default('ekstrakurikuler');
            $table->foreignId('supervisor_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->tinyInteger('day_of_week')->nullable()->comment('1=Senin..7=Ahad');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 8. Anggota Kegiatan Siswa (Activity Members)
        Schema::create('student_activity_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_activity_id')->constrained('student_activities')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('role', ['anggota', 'ketua', 'pengurus'])->default('anggota');
            $table->date('joined_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['student_activity_id', 'student_id'], 'sam_act_student_uk');
        });

        // 9. Presensi Kegiatan Siswa (Activity Attendances)
        Schema::create('student_activity_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_activity_id')->constrained('student_activities')->cascadeOnDelete();
            $table->date('date')->index();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'excused'])->default('present');
            $table->timestamps();

            $table->unique(['student_activity_id', 'date', 'student_id'], 'saa_act_date_student_uk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_activity_attendances');
        Schema::dropIfExists('student_activity_members');
        Schema::dropIfExists('student_activities');
        Schema::dropIfExists('counseling_sessions');
        Schema::dropIfExists('discipline_records');
        Schema::dropIfExists('discipline_categories');
        Schema::dropIfExists('academic_grades');
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('subjects');
    }
};
