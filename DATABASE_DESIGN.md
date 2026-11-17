# Rancangan Basis Data & Skema ERD (DATABASE_DESIGN.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Versi:** 1.0  
**Tanggal:** 16 November 2026  

---

## 1. Standar Konvensi Basis Data

1. **Storage Engine:** Seluruh tabel menggunakan **InnoDB** untuk menjamin integritas relasional, foreign keys, dan transaksi ACID.
2. **Karakter Set & Collation:** `utf8mb4` dengan collation `utf8mb4_unicode_ci` untuk mendukung karakter Arab (Al-Qur'an/nama Arab) dan penulisan multibyte lainnya.
3. **Primary Key:** `bigint unsigned AUTO_INCREMENT` (atau UUID jika diperlukan untuk token/audit).
4. **Foreign Key:** `ON UPDATE CASCADE` dan `ON DELETE RESTRICT` (atau `ON DELETE CASCADE` hanya pada detail transaksi seperti `worship_session_participants` yang terikat pada parent session). Data siswa atau guru tidak boleh di-hard delete jika telah memiliki riwayat transaksi.
5. **Auditing Fields:** Setiap tabel transaksi penting mencakup kolom `created_by` dan `updated_by` yang merujuk ke tabel `users`.
6. **Zona Waktu:** Semua nilai tanggal dan waktu disimpan dalam format timestamp UTC atau datetime lokal konsisten dengan zona waktu madrasah (`Asia/Jakarta`, UTC+7).

---

## 2. Struktur Modul & Skema Tabel

```
+----------------------------------------------------------------------------------------------------+
|                                      MADRASAH ONE MODULAR ERD                                      |
+----------------------------------------------------------------------------------------------------+
|                                                                                                    |
|  [Identity & RBAC]         [Master Data]               [School Attendance]                         |
|  - users                   - academic_years            - attendance_types                          |
|  - roles                   - semesters                 - attendance_sessions                       |
|  - permissions             - grades                    - daily_attendances                         |
|  - role_user               - school_classes            - attendance_corrections                    |
|  - user_profiles           - students                  - attendance_evidence                       |
|  - audit_logs              - student_class_histories                                               |
|  - login_audits            - teachers                  [Worship Monitoring]                        |
|                            - subjects                  - worship_types (Zuhur/Asar)                |
|  [Counseling (BK)]         - teacher_assignments      - worship_locations                         |
|  - counseling_sessions     - parents                   - worship_schedules                         |
|  - counseling_notes        - student_parent            - worship_schedule_groups (Gelombang)       |
|  - counseling_follow_ups   - school_locations          - worship_supervisors                       |
|                            - school_calendars          - worship_sessions                          |
|  [Discipline & Activity]   - school_schedules          - worship_session_participants (Kunci Sesi) |
|  - discipline_categories                               - worship_qr_tokens (Dinamis Sesi)          |
|  - discipline_records      [Academic]                  - worship_attendances (Log Scan)            |
|  - discipline_follow_ups   - assessments               - worship_verifications (Wali/Petugas)      |
|  - student_activities      - assessment_scores         - worship_exemptions (Dispensasi Privat)    |
|  - activity_members        - subject_enrollments       - worship_attendance_corrections            |
+----------------------------------------------------------------------------------------------------+
```

---

### 2.1 Domain Identitas & Keamanan (Identity & Access Control)

#### `users`
Tabel akun autentikasi utama bagi seluruh pemangku kepentingan.
- `id` (bigint unsigned, PK)
- `name` (varchar(255))
- `email` (varchar(255), unique)
- `username` (varchar(100), unique, nullable) -- untuk kemudahan login NIS/NIP
- `password` (varchar(255))
- `phone` (varchar(30), nullable)
- `avatar` (varchar(255), nullable)
- `is_active` (boolean, default true)
- `remember_token` (varchar(100), nullable)
- `created_at`, `updated_at` (timestamps)
- `deleted_at` (softDeletes, nullable)

#### `roles` & `permissions` & `role_user`
Sistem RBAC fleksibel (menggunakan format standar atau Spatie Permission schema).
- `roles`: `id`, `name` (super_admin, admin, kepsek, waka, guru, wali_kelas, guru_bk, petugas_keagamaan, guru_piket, siswa, orang_tua), `display_name`, `description`.
- `permissions`: `id`, `name`, `group`.
- `role_user`: `user_id`, `role_id` (composite PK).

#### `user_profiles`
Profil pelengkap sesuai role (NIK, jenis kelamin, alamat, TTL, dsb).
- `id` (bigint unsigned, PK)
- `user_id` (bigint unsigned, FK -> users.id, unique)
- `gender` (enum('L', 'P'))
- `birth_place` (varchar(100), nullable)
- `birth_date` (date, nullable)
- `address` (text, nullable)
- `meta` (json, nullable) -- atribut tambahan fleksibel

#### `audit_logs` & `login_audits`
Pencatatan rekam jejak untuk kepatuhan keamanan dan transparansi.
- `audit_logs`: `id`, `user_id` (nullable), `event` (created, updated, deleted, login, export), `auditable_type`, `auditable_id`, `old_values` (json), `new_values` (json), `ip_address`, `user_agent`, `created_at`.
- `login_audits`: `id`, `user_id`, `ip_address`, `user_agent`, `status` (success, failed), `reason`, `logged_at`.

---

### 2.2 Domain Master Data Madrasah

#### `academic_years` & `semesters`
- `academic_years`: `id`, `name` (contoh: "2026/2027"), `start_date`, `end_date`, `is_active` (boolean, default false).
- `semesters`: `id`, `academic_year_id` (FK), `semester` (enum('1', '2') / ganjil-genap), `is_active` (boolean).

#### `grades` & `school_classes`
- `grades`: `id`, `level` (10, 11, 12), `name` (X, XI, XII).
- `school_classes`: `id`, `academic_year_id` (FK), `grade_id` (FK), `name` (contoh: "X-A", "XI-IPA 1"), `homeroom_teacher_id` (FK -> teachers.id, nullable), `capacity` (int, default 36), `is_active` (boolean).

#### `students` & `student_class_histories`
- `students`:
  - `id` (bigint unsigned, PK)
  - `user_id` (bigint unsigned, FK -> users.id, unique, nullable)
  - `nis` (varchar(30), unique)
  - `nisn` (varchar(30), unique, nullable)
  - `full_name` (varchar(255))
  - `nickname` (varchar(100), nullable)
  - `gender` (enum('L', 'P'))
  - `current_class_id` (bigint unsigned, FK -> school_classes.id, nullable)
  - `status` (enum('active', 'inactive', 'graduated', 'moved', 'dropped_out'), default 'active')
  - `enrollment_date` (date)
  - `photo` (varchar(255), nullable)
- `student_class_histories`:
  - `id` (bigint unsigned, PK)
  - `student_id` (FK -> students.id)
  - `school_class_id` (FK -> school_classes.id)
  - `academic_year_id` (FK -> academic_years.id)
  - `semester_id` (FK -> semesters.id)
  - `enrolled_at` (date)

#### `teachers` & `subjects` & `teacher_assignments`
- `teachers`: `id`, `user_id` (FK -> users.id, unique), `nip` (varchar(30), nullable), `full_name`, `title_prefix`, `title_suffix`, `status` (active, inactive).
- `subjects`: `id`, `code` (varchar(20), unique), `name`, `grade_level` (nullable), `is_religious` (boolean, default false).
- `teacher_assignments`: `id`, `teacher_id`, `subject_id`, `school_class_id`, `academic_year_id`, `semester_id`.

#### `parents` & `student_parent`
- `parents`: `id`, `user_id` (FK -> users.id, unique), `nik`, `full_name`, `relationship_type` (ayah, ibu, wali), `occupation`, `phone`.
- `student_parent`: `student_id`, `parent_id`, `is_primary_contact` (boolean), `is_verified` (boolean, default false), `verified_by` (FK -> users.id, nullable). *Mencegah orang tua liar mengklaim data anak tanpa verifikasi admin.*

#### `school_locations` & `school_calendars`
- `school_locations`: `id`, `name` (Masjid Utama, Musholla Putri, Lapangan, Ruang Kelas X), `capacity`, `location_type`.
- `school_calendars`: `id`, `academic_year_id`, `title`, `date`, `event_type` (holiday, exam, regular, special_schedule).

---

### 2.3 Domain Absensi Harian Sekolah (School Attendance)

#### `attendance_types`
- `id`, `code` (masuk, pulang, apel_pagi), `name`, `default_start_time`, `default_end_time`.

#### `attendance_sessions`
- `id`, `date` (date), `attendance_type_id` (FK), `academic_year_id`, `created_by`, `status` (open, closed).

#### `daily_attendances`
- `id` (bigint unsigned, PK)
- `attendance_session_id` (FK -> attendance_sessions.id)
- `student_id` (FK -> students.id)
- `date` (date)
- `status` (enum('present', 'late', 'sick', 'excused', 'absent', 'early_departure'))
- `check_in_time` (time, nullable)
- `check_out_time` (time, nullable)
- `minutes_late` (int unsigned, default 0)
- `recorded_by` (FK -> users.id)
- `source` (enum('manual_piket', 'manual_homeroom', 'qr_scan', 'system'))
- `notes` (text, nullable)
- **Unique Constraint:** `UNIQUE KEY (student_id, date, attendance_session_id)` -> Mencegah duplikasi absensi harian siswa pada sesi yang sama!

#### `attendance_corrections`
- `id`, `daily_attendance_id` (FK), `old_status`, `new_status`, `reason` (text), `requested_by` (FK), `approved_by` (FK), `created_at`.

---

### 2.4 Domain Monitoring Ibadah (Worship Monitoring - Zuhur & Asar)

#### `worship_types`
- `id`, `code` (zuhur, asar, dhuha, tadarus), `name`, `is_mandatory` (boolean).

#### `worship_schedules` & `worship_schedule_groups` (Gelombang Zuhur)
- `worship_schedules`: `id`, `worship_type_id`, `academic_year_id`, `day_of_week` (1-7), `start_time`, `end_time`, `is_active`.
- `worship_schedule_groups`: `id`, `worship_schedule_id`, `wave_number` (int: 1, 2, ...), `school_class_id` (FK -> school_classes.id), `location_id` (FK -> school_locations.id).

#### `worship_sessions`
- `id` (bigint unsigned, PK)
- `worship_type_id` (FK -> worship_types.id)
- `date` (date)
- `wave_number` (int, default 1)
- `location_id` (FK -> school_locations.id)
- `status` (enum('scheduled', 'in_progress', 'completed', 'verified', 'cancelled'))
- `created_by` (FK -> users.id)
- `verified_by` (FK -> users.id, nullable)
- `verified_at` (datetime, nullable)

#### `worship_session_participants` *(Tabel Kunci Ketetapan Denominator)*
Dibuat secara otomatis sebelum sesi dimulai berdasarkan gelombang rombel (Zuhur) atau kelayakan aktivitas siswa (Asar).
- `id` (bigint unsigned, PK)
- `worship_session_id` (FK -> worship_sessions.id, ON DELETE CASCADE)
- `student_id` (FK -> students.id)
- `school_class_id` (FK -> school_classes.id)
- `status` (enum('pending_verification', 'present', 'late', 'absent', 'excused', 'exempt'), default 'pending_verification')
- `scan_record_id` (FK -> worship_attendances.id, nullable)
- `verification_notes` (varchar(255), nullable)
- **Unique Constraint:** `UNIQUE KEY (worship_session_id, student_id)` -> Mencegah siswa terdaftar ganda di sesi yang sama.

#### `worship_qr_tokens`
Token QR dinamis yang berputar secara berkala untuk mencegah replay/foto layar.
- `id` (bigint unsigned, PK)
- `worship_session_id` (FK -> worship_sessions.id)
- `token` (varchar(128), unique)
- `valid_from` (datetime)
- `valid_until` (datetime)
- `is_active` (boolean, default true)

#### `worship_attendances` (Bukti Awal Scan Siswa)
- `id` (bigint unsigned, PK)
- `worship_session_id` (FK)
- `student_id` (FK)
- `scanned_at` (datetime)
- `qr_token_id` (FK -> worship_qr_tokens.id)
- `device_fingerprint` (varchar(255), nullable)
- `ip_address` (varchar(45), nullable)
- `source` (enum('qr_student_camera', 'qr_officer_scanner', 'manual_officer'))

#### `worship_exemptions` *(Pengecualian Ibadah Sensitif)*
Disimpan terpisah dengan izin akses paling ketat (halangan syar'i mahasiswi/siswi haid, sakit tertentu, dispensasi resmi).
- `id` (bigint unsigned, PK)
- `student_id` (FK -> students.id)
- `start_date` (date)
- `end_date` (date)
- `reason_category` (enum('sharia_halangan', 'sick', 'emergency', 'dispensation'))
- `notes` (text, nullable) -- Terenkripsi atau terproteksi otorisasi ketat
- `approved_by` (FK -> users.id)
- `is_confidential` (boolean, default true)

---

### 2.5 Domain Bimbingan Konseling (BK - Strict Privacy)

#### `counseling_sessions` & `counseling_notes`
- `counseling_sessions`:
  - `id` (bigint unsigned, PK)
  - `student_id` (FK -> students.id)
  - `counselor_id` (FK -> teachers.id)
  - `session_date` (date)
  - `category` (enum('pribadi', 'sosial', 'belajar', 'karir', 'kedisiplinan'))
  - `confidentiality_level` (enum('standard', 'strictly_confidential'), default 'strictly_confidential')
  - `status` (enum('scheduled', 'in_progress', 'completed', 'follow_up_needed'))
- `counseling_notes`:
  - `id` (bigint unsigned, PK)
  - `counseling_session_id` (FK)
  - `case_background` (text) -- Dienkripsi aplikasi
  - `action_plan` (text)
  - `recommendations` (text)
- `counseling_follow_ups`:
  - `id`, `counseling_session_id`, `follow_up_date`, `notes`, `status`.

---

### 2.6 Domain Kedisiplinan & Kegiatan Siswa

#### `discipline_categories` & `discipline_records`
- `discipline_categories`: `id`, `name`, `type` (enum('violation', 'achievement')), `points` (int).
- `discipline_records`: `id`, `student_id`, `category_id`, `date`, `description`, `reported_by` (FK), `status` (reported, processed, resolved), `points_awarded`.

#### `student_activities` & `student_activity_attendances`
- `student_activities`: `id`, `name` (Pramuka, Paskibra, OSIM, PMR), `category`, `supervisor_id` (FK -> teachers.id).
- `student_activity_members`: `id`, `activity_id`, `student_id`, `joined_date`, `is_active`.
- `student_activity_attendances`: `id`, `activity_id`, `date`, `student_id`, `status`.

---

### 2.7 Domain Portal Komunikasi & Laporan

#### `announcements` & `notifications`
- `announcements`: `id`, `title`, `content`, `target_role` (all, parents, teachers, students), `published_at`, `expires_at`, `created_by`.
- `notifications`: (Standar Laravel database notifications).

---

## 3. Strategi Indexing & Optimasi Query

1. **Filter Tanggal & Status:** Kolom `(date, status)` pada `daily_attendances` dan `worship_sessions` diindeks komposit untuk mempercepat pemuatan dashboard dan rekap.
2. **Pencarian Siswa:** Index pada `(current_class_id, status)` di tabel `students`.
3. **Lookup QR:** Index pada `(token, is_active, valid_until)` di tabel `worship_qr_tokens`.
4. **Relasi Orang Tua:** Index pada `(parent_id, student_id, is_verified)` di tabel `student_parent`.
