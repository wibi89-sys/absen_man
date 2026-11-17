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
        // 1. Pengumuman Madrasah
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('content');
            $table->enum('category', ['general', 'academic', 'attendance', 'worship', 'emergency'])->default('general');
            $table->enum('target_role', ['all', 'parents', 'teachers', 'students'])->default('all');
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->boolean('is_pinned')->default(false);
            $table->dateTime('published_at');
            $table->dateTime('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['target_role', 'published_at']);
        });

        // 2. Pesan Komunikasi Resmi Madrasah / Wali Kelas ke Orang Tua
        Schema::create('parent_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('message');
            $table->enum('category', ['academic', 'attendance', 'worship', 'general'])->default('general');
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'is_read']);
            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_messages');
        Schema::dropIfExists('announcements');
    }
};
