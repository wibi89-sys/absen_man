<?php

use App\Http\Controllers\Academic\AcademicGradeController;
use App\Http\Controllers\Academic\SubjectController;
use App\Http\Controllers\Activity\StudentActivityController;
use App\Http\Controllers\Attendance\DailyAttendanceController;
use App\Http\Controllers\Attendance\GateAttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Communication\AnnouncementController;
use App\Http\Controllers\Communication\ParentMessageController;
use App\Http\Controllers\Counseling\CounselingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Discipline\DisciplineController;
use App\Http\Controllers\MasterData\AcademicYearController;
use App\Http\Controllers\MasterData\AttendanceMachineController;
use App\Http\Controllers\MasterData\ParentController;
use App\Http\Controllers\MasterData\SchoolClassController;
use App\Http\Controllers\MasterData\SchoolLocationController;
use App\Http\Controllers\MasterData\StudentController;
use App\Http\Controllers\MasterData\TeacherController;
use App\Http\Controllers\MasterData\UserController;
use App\Http\Controllers\Parent\ParentPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Setting\SchoolSettingController;
use App\Http\Controllers\Worship\WorshipExemptionController;
use App\Http\Controllers\Worship\WorshipRecapController;
use App\Http\Controllers\Worship\WorshipSessionController;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Route;

// Redirect Root to Dashboard or Login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil & Keamanan Akun Pengguna
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Modul Pengaturan Madrasah (Super Admin, Admin, Kepsek)
    Route::middleware('role:super_admin,admin,kepsek')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SchoolSettingController::class, 'index'])->name('index');
        Route::put('/', [SchoolSettingController::class, 'update'])->name('update');
    });

    // Master Data Routes (Admin, Super Admin, Kepsek, Waka)
    Route::middleware('role:super_admin,admin,kepsek,waka')->prefix('master')->name('master.')->group(function () {
        // Manajemen Akun & Pengguna
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/update-roles', [UserController::class, 'updateRoles'])->name('users.update-roles');

        Route::resource('students', StudentController::class)->except(['show']);
        Route::resource('teachers', TeacherController::class)->except(['show']);
        Route::resource('classes', SchoolClassController::class)->except(['show']);

        // Academic Years
        Route::get('academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
        Route::post('academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
        Route::post('academic-years/{academicYear}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-years.set-active');

        // Locations
        Route::get('locations', [SchoolLocationController::class, 'index'])->name('locations.index');
        Route::post('locations', [SchoolLocationController::class, 'store'])->name('locations.store');
        Route::put('locations/{location}', [SchoolLocationController::class, 'update'])->name('locations.update');
        Route::delete('locations/{location}', [SchoolLocationController::class, 'destroy'])->name('locations.destroy');

        // Mesin Absensi Biometrik LAN (Solution X100C / ZKTeco)
        Route::get('machines', [AttendanceMachineController::class, 'index'])->name('machines.index');
        Route::post('machines', [AttendanceMachineController::class, 'store'])->name('machines.store');
        Route::get('machines/{machine}/test-connection', [AttendanceMachineController::class, 'testConnection'])->name('machines.test-connection');

        // Parents & Verification
        Route::get('parents', [ParentController::class, 'index'])->name('parents.index');
        Route::post('parents/{parent}/link-student', [ParentController::class, 'linkStudent'])->name('parents.link-student');
        Route::post('parents/{parent}/students/{student}/toggle-verify', [ParentController::class, 'toggleVerify'])->name('parents.toggle-verify');
    });

    // Modul Absensi Sekolah Multi-Metode
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Metode Mandiri Siswa (GPS Geolocation, Scan QR Layar Gerbang & QR Dinamis Kartu OSIS)
        Route::middleware('role:siswa,super_admin,admin')->group(function () {
            Route::post('/self-gps', [GateAttendanceController::class, 'selfGpsCheckIn'])->name('self-gps');
            Route::post('/scan-dynamic-qr', [GateAttendanceController::class, 'scanDynamicQr'])->name('scan-dynamic-qr');
            Route::get('/student-osis-token', [GateAttendanceController::class, 'studentOsisTokenJson'])->name('student-osis-token');
        });

        // Jurnal Presensi, Kiosk Pos Gerbang, & Layar TV QR Dinamis
        Route::middleware('role:super_admin,admin,kepsek,waka,guru_piket,wali_kelas')->group(function () {
            Route::get('/', [DailyAttendanceController::class, 'index'])->name('index');
            Route::get('/sheet', [DailyAttendanceController::class, 'sheet'])->name('sheet');
            Route::post('/batch', [DailyAttendanceController::class, 'storeBatch'])->name('store-batch');
            Route::post('/{dailyAttendance}/correct', [DailyAttendanceController::class, 'correct'])->name('correct');
            Route::get('/recap', [DailyAttendanceController::class, 'recap'])->name('recap');
            Route::get('/recap/print', [DailyAttendanceController::class, 'printRecap'])->name('recap.print');
            Route::get('/recap/export', [DailyAttendanceController::class, 'exportCsv'])->name('recap.export');

            // Pos Gerbang & TV QR Dinamis
            Route::get('/gate-scanner', [GateAttendanceController::class, 'gateScanner'])->name('gate-scanner');
            Route::post('/gate-scanner/process', [GateAttendanceController::class, 'processGateScan'])->name('gate-scanner.process');
            Route::get('/dynamic-qr-display', [GateAttendanceController::class, 'dynamicQrDisplay'])->name('dynamic-qr-display');
            Route::get('/dynamic-qr-token', [GateAttendanceController::class, 'dynamicQrTokenJson'])->name('dynamic-qr-token');
        });
    });

    // Quick helper API for loading active students of a class
    Route::get('/api/classes/{schoolClass}/students', function (SchoolClass $schoolClass) {
        return response()->json(
            $schoolClass->students()->whereIn('status', ['active', 'aktif'])->orderBy('full_name')->get(['id', 'full_name', 'nis', 'gender'])
        );
    })->name('api.class.students');

    // Modul Monitoring Ibadah Terpadu (Fase 3)
    Route::prefix('worship')->name('worship.')->group(function () {
        // Sessions & Verification Management
        Route::middleware('role:super_admin,admin,kepsek,waka,petugas_keagamaan,wali_kelas,guru')->group(function () {
            Route::get('/sessions', [WorshipSessionController::class, 'index'])->name('sessions.index');
            Route::get('/sessions/create', [WorshipSessionController::class, 'create'])->name('sessions.create');
            Route::post('/sessions', [WorshipSessionController::class, 'store'])->name('sessions.store');
            Route::get('/sessions/{session}', [WorshipSessionController::class, 'show'])->name('sessions.show');
            Route::get('/sessions/{session}/display-qr', [WorshipSessionController::class, 'displayQr'])->name('sessions.display-qr');
            Route::get('/sessions/{session}/token-json', [WorshipSessionController::class, 'tokenJson'])->middleware('throttle:120,1')->name('sessions.token-json');
            Route::post('/sessions/{session}/verify', [WorshipSessionController::class, 'verify'])->name('sessions.verify');
            Route::post('/sessions/{session}/finalize', [WorshipSessionController::class, 'finalize'])->name('sessions.finalize');

            // Recap & Export
            Route::get('/recap', [WorshipRecapController::class, 'index'])->name('recap.index');
            Route::get('/recap/export', [WorshipRecapController::class, 'export'])->name('recap.export');
            Route::get('/recap/print', [WorshipRecapController::class, 'print'])->name('recap.print');
        });

        // Student / Officer Scanner & Process Scan
        Route::get('/sessions/{session}/scan', [WorshipSessionController::class, 'scanner'])->name('sessions.scan');
        Route::post('/sessions/{session}/scan', [WorshipSessionController::class, 'processScan'])->middleware('throttle:60,1')->name('sessions.process-scan');

        // Sensitive Exemptions Management (Strict Privacy)
        Route::middleware('role:super_admin,admin,petugas_keagamaan,guru_bk')->group(function () {
            Route::get('/exemptions', [WorshipExemptionController::class, 'index'])->name('exemptions.index');
            Route::get('/exemptions/create', [WorshipExemptionController::class, 'create'])->name('exemptions.create');
            Route::post('/exemptions', [WorshipExemptionController::class, 'store'])->name('exemptions.store');
            Route::delete('/exemptions/{exemption}', [WorshipExemptionController::class, 'destroy'])->name('exemptions.destroy');
        });
    });

    // Modul Portal Orang Tua (Fase 4)
    Route::middleware('role:orang_tua,super_admin,admin')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/children/{student}', [ParentPortalController::class, 'childDetail'])->name('children.show');
        Route::get('/announcements', [ParentPortalController::class, 'announcements'])->name('announcements');
        Route::get('/messages', [ParentPortalController::class, 'messages'])->name('messages');
        Route::post('/messages/{message}/read', [ParentPortalController::class, 'markMessageRead'])->name('messages.read');
    });

    // Modul Komunikasi & Pengumuman (Fase 4)
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::middleware('role:super_admin,admin,kepsek,waka,wali_kelas')->group(function () {
            Route::resource('announcements', AnnouncementController::class);
            Route::get('messages/create', [ParentMessageController::class, 'create'])->name('messages.create');
            Route::post('messages', [ParentMessageController::class, 'store'])->name('messages.store');
        });
    });

    // Modul Akademik (Fase 5)
    Route::middleware('role:super_admin,admin,kepsek,waka,guru,wali_kelas')->prefix('academic')->name('academic.')->group(function () {
        Route::get('subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::post('subjects/assign', [SubjectController::class, 'assign'])->name('subjects.assign');

        Route::get('grades', [AcademicGradeController::class, 'index'])->name('grades.index');
        Route::post('grades/batch', [AcademicGradeController::class, 'batchStore'])->name('grades.batch');
        Route::post('grades/toggle-publish', [AcademicGradeController::class, 'togglePublish'])->name('grades.toggle-publish');
    });

    // Modul Kedisiplinan & Prestasi (Fase 5)
    Route::middleware('role:super_admin,admin,kepsek,waka,guru_piket,wali_kelas,guru_bk')->prefix('discipline')->name('discipline.')->group(function () {
        Route::get('/', [DisciplineController::class, 'index'])->name('index');
        Route::get('/create', [DisciplineController::class, 'create'])->name('create');
        Route::post('/', [DisciplineController::class, 'store'])->name('store');
        Route::post('/{record}/status', [DisciplineController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{record}', [DisciplineController::class, 'destroy'])->name('destroy');
    });

    // Modul Bimbingan Konseling (BK - Strict Privacy, Fase 5)
    Route::middleware('role:guru_bk,super_admin')->prefix('counseling')->name('counseling.')->group(function () {
        Route::get('/', [CounselingController::class, 'index'])->name('index');
        Route::get('/create', [CounselingController::class, 'create'])->name('create');
        Route::post('/', [CounselingController::class, 'store'])->name('store');
        Route::get('/{counseling}', [CounselingController::class, 'show'])->name('show');
        Route::put('/{counseling}', [CounselingController::class, 'update'])->name('update');
    });

    // Modul Kegiatan Siswa & Ekstrakurikuler (Fase 5)
    Route::middleware('role:super_admin,admin,kepsek,waka,wali_kelas,guru,petugas_keagamaan')->prefix('activities')->name('activities.')->group(function () {
        Route::get('/', [StudentActivityController::class, 'index'])->name('index');
        Route::post('/', [StudentActivityController::class, 'store'])->name('store');
        Route::get('/{activity}', [StudentActivityController::class, 'show'])->name('show');
        Route::post('/{activity}/members', [StudentActivityController::class, 'storeMember'])->name('members.store');
        Route::post('/{activity}/attendance', [StudentActivityController::class, 'recordAttendance'])->name('attendance.store');
        Route::post('/{activity}/generate-asar', [StudentActivityController::class, 'generateAsarSession'])->name('generate-asar');
    });
});

// Endpoint Integrasi Mesin Absensi Biometrik Solution X100C / ZKTeco via LAN ADMS Push
Route::match(['GET', 'POST'], '/api/machine/cdata', [AttendanceMachineController::class, 'receiveAdmsPush'])->name('machine.adms-cdata');
Route::match(['GET', 'POST'], '/iclock/cdata', [AttendanceMachineController::class, 'receiveAdmsPush']);
