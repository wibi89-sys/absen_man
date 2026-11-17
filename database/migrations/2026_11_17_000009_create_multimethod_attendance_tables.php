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
        // 1. Tambah koordinat GPS & radius geofencing pada lokasi sekolah
        Schema::table('school_locations', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('capacity');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->unsignedSmallInteger('radius_meters')->default(100)->after('longitude');
        });

        // 2. Tambah RFID/NFC UID & QR Token statis untuk kartu pelajar OSIS
        Schema::table('students', function (Blueprint $table) {
            $table->string('rfid_uid', 50)->nullable()->unique()->after('nisn');
            $table->string('qr_token', 64)->nullable()->unique()->after('rfid_uid');
        });

        // 3. Tambah metadata GPS, jarak, dan ganti tipe source pada presensi harian
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->string('source', 50)->default('manual_piket')->change();
            $table->decimal('latitude', 10, 8)->nullable()->after('source');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->unsignedInteger('distance_meters')->nullable()->after('longitude');
            $table->string('device_info', 255)->nullable()->after('distance_meters');
        });

        // 4. Tabel Token QR Dinamis di Gerbang Sekolah / Depan Kelas
        Schema::create('attendance_qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('created_at_epoch');
            $table->unsignedBigInteger('expires_at_epoch');
            $table->boolean('is_revoked')->default(false);
            $table->timestamps();

            $table->index(['attendance_session_id', 'is_revoked', 'expires_at_epoch'], 'att_qr_active_idx');
        });

        // 5. Tabel Konfigurasi Mesin Absensi Biometrik LAN (Solution X100C / ZKTeco)
        Schema::create('attendance_machines', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // e.g. "Mesin Gerbang Utama Solution X100C"
            $table->string('ip_address', 45); // e.g. "192.168.1.201"
            $table->unsignedSmallInteger('port')->default(4370);
            $table->string('comm_key', 50)->nullable();
            $table->enum('protocol', ['adms_http', 'socket_udp', 'socket_tcp', 'rest_api'])->default('adms_http');
            $table->string('location_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_status', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_machines');
        Schema::dropIfExists('attendance_qr_tokens');

        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'distance_meters', 'device_info']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['rfid_uid', 'qr_token']);
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_meters']);
        });
    }
};
