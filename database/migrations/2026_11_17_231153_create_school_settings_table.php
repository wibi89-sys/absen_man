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
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('MADRASAH ONE');
            $table->string('school_slogan')->nullable()->default('Integrated Student Monitoring System');
            $table->string('npsn', 20)->nullable()->default('20261101');
            $table->string('nsm', 30)->nullable()->default('131132730001');
            $table->string('school_level', 50)->default('Madrasah Aliyah (MA)');
            $table->string('principal_name')->default('Dr. H. Ahmad Fauzi, M.Pd.');
            $table->string('principal_nip', 30)->nullable()->default('197508152002121003');
            $table->text('school_address')->nullable();
            $table->string('school_phone', 30)->nullable()->default('(022) 7891234');
            $table->string('school_email')->nullable()->default('info@madrasah-one.sch.id');
            $table->string('school_website')->nullable()->default('https://absen.laksanatech.com');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->boolean('enable_demo_mode')->default(true);
            $table->string('gate_welcome_text')->nullable()->default('Selamat Datang di Madrasah Tercinta. Awali Hari dengan Senyum dan Semangat!');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
