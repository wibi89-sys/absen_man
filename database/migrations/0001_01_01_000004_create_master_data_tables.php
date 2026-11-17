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
        // 1. Tahun Ajaran
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // contoh: 2026/2027
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 2. Semester
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('semester', ['1', '2']); // 1: Ganjil, 2: Genap
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 3. Tingkat / Grade
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level'); // 10, 11, 12
            $table->string('name', 10); // X, XI, XII
            $table->timestamps();
        });

        // 4. Lokasi Sekolah / Ibadah
        Schema::create('school_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Masjid Utama, Musholla Putri, Ruang Kelas X-A, dsb
            $table->string('location_type', 50)->default('general'); // masjid, musholla, classroom, hall, field
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Guru & Tenaga Kependidikan
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nip', 30)->nullable()->unique();
            $table->string('full_name', 255);
            $table->string('title_prefix', 20)->nullable();
            $table->string('title_suffix', 20)->nullable();
            $table->string('phone', 30)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Rombongan Belajar (Kelas)
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('grade_id')->constrained()->restrictOnDelete();
            $table->string('name', 50); // Contoh: X-A, XI-MIPA 1, XII-IPS 2
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->unsignedSmallInteger('capacity')->default(36);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Siswa
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('nis', 30)->unique();
            $table->string('nisn', 30)->nullable()->unique();
            $table->string('full_name', 255);
            $table->string('nickname', 100)->nullable();
            $table->enum('gender', ['L', 'P']);
            $table->foreignId('current_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'graduated', 'moved', 'dropped_out'])->default('active');
            $table->date('enrollment_date');
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['current_class_id', 'status']);
            $table->index('gender');
        });

        // 8. Riwayat Rombel Siswa
        Schema::create('student_class_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->date('enrolled_at');
            $table->timestamps();
        });

        // 9. Orang Tua / Wali
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nik', 30)->nullable();
            $table->string('full_name', 255);
            $table->enum('relationship_type', ['ayah', 'ibu', 'wali'])->default('ayah');
            $table->string('occupation', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 10. Relasi Siswa - Orang Tua (dengan verifikasi sah admin)
        Schema::create('student_parent', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->primary(['student_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_parent');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('student_class_histories');
        Schema::dropIfExists('students');
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('school_locations');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('academic_years');
    }
};
