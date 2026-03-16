# PSSM - Database Dictionary
## Complete Data Model Reference v1.0

**Database:** PostgreSQL 16  
**ORM:** Laravel Eloquent  
**Encoding:** UTF-8 | **Collation:** id_ID.UTF-8

---

## 1. users

Tabel utama untuk semua pengguna (guru, siswa, admin, ketua kelas).

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | Primary key |
| `name` | VARCHAR(255) | ❌ | - | - | Nama lengkap |
| `email` | VARCHAR(255) | ❌ | - | UNIQUE | Email login |
| `email_verified_at` | TIMESTAMP | ✅ | NULL | - | Waktu verifikasi email |
| `password` | VARCHAR(255) | ❌ | - | - | Bcrypt hash |
| `role` | VARCHAR(50) | ❌ | - | CHECK | `super_admin`, `teacher`, `class_leader`, `student` |
| `avatar_path` | VARCHAR(255) | ✅ | NULL | - | Path ke file avatar |
| `is_active` | BOOLEAN | ❌ | TRUE | - | Status akun aktif |
| `created_at` | TIMESTAMP | ❌ | NOW() | - | Timestamp dibuat |
| `updated_at` | TIMESTAMP | ❌ | NOW() | - | Timestamp terakhir diubah |
| `deleted_at` | TIMESTAMP | ✅ | NULL | - | Soft delete timestamp |

**Indexes:** `idx_users_email` (email), `idx_users_role` (role), `idx_users_active_role` (role, is_active WHERE is_active=TRUE)

**Relasi:**
- Has one `student_profiles` (jika role=student)
- Has one `teacher_profiles` (jika role=teacher)
- Has many `assignments` (sebagai teacher)
- Has many `submissions` (sebagai student)
- Belongs to many `classes` via `class_student`

**Contoh Data:**
```json
{
    "id": 1, "name": "Pak Bambang", "email": "bambang@school.id",
    "role": "super_admin", "is_active": true
}
```

---

## 2. student_profiles

Informasi tambahan khusus siswa. Data sensitif terenkripsi.

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `user_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `student_id_number` | VARCHAR(50) | ❌ | - | UNIQUE | NIS (Nomor Induk Siswa) |
| `date_of_birth` | DATE | ✅ | NULL | - | Tanggal lahir |
| `parent_name` | VARCHAR(255) | ✅ | NULL | - | Nama orang tua |
| `parent_phone` | VARCHAR(20) | ✅ | NULL | - | 🔐 Encrypted — Telepon ortu |
| `parent_email` | VARCHAR(255) | ✅ | NULL | - | 🔐 Encrypted — Email ortu |
| `address` | TEXT | ✅ | NULL | - | 🔐 Encrypted — Alamat |
| `enrollment_year` | INT | ✅ | NULL | - | Tahun masuk |

> 🔐 Kolom bertanda encrypted menggunakan `Crypt::encryptString()` di Model accessor/mutator.

---

## 3. teacher_profiles

Informasi tambahan khusus guru.

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `user_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `teacher_id_number` | VARCHAR(50) | ❌ | - | UNIQUE | NIP (Nomor Induk Pegawai) |
| `specialization` | VARCHAR(100) | ✅ | NULL | - | Bidang: "Matematika", "Fisika" |
| `phone` | VARCHAR(20) | ✅ | NULL | - | 🔐 Encrypted |

---

## 4. academic_years

Tahun ajaran akademik.

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `name` | VARCHAR(50) | ❌ | - | - | "2025/2026" |
| `start_date` | DATE | ❌ | - | - | Awal tahun ajaran |
| `end_date` | DATE | ❌ | - | - | Akhir tahun ajaran |
| `is_active` | BOOLEAN | ❌ | FALSE | - | Hanya 1 yang aktif |

> ⚠️ Validasi di application layer: hanya boleh 1 `is_active=TRUE` secara bersamaan.

---

## 5. subjects

Mata pelajaran.

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `name` | VARCHAR(100) | ❌ | - | - | "Matematika", "Fisika" |
| `code` | VARCHAR(20) | ❌ | - | UNIQUE | "MTK", "FIS" |
| `description` | TEXT | ✅ | NULL | - | Deskripsi mapel |

---

## 6. classes

Kelas (11 IPA 1, 12 IPS 2, dll).

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `name` | VARCHAR(50) | ❌ | - | - | "11 IPA 1" |
| `grade_level` | INT | ❌ | - | CHECK 10-12 | Tingkat: 10, 11, 12 |
| `academic_year_id` | BIGINT | ✅ | - | FK → academic_years(id) | |
| `homeroom_teacher_id` | BIGINT | ✅ | - | FK → users(id) | Wali kelas |

---

## 7. class_student (Pivot)

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `class_id` | BIGINT | ❌ | - | FK → classes(id) CASCADE | |
| `student_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `enrollment_date` | DATE | ❌ | TODAY | - | Tanggal masuk kelas |
| `is_class_leader` | BOOLEAN | ❌ | FALSE | - | Ketua kelas |

**UNIQUE:** (class_id, student_id)

---

## 8. class_subject (Pivot)

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `class_id` | BIGINT | ❌ | - | FK → classes(id) CASCADE | |
| `subject_id` | BIGINT | ❌ | - | FK → subjects(id) CASCADE | |
| `teacher_id` | BIGINT | ✅ | - | FK → users(id) SET NULL | Guru pengampu |
| `schedule` | JSON | ✅ | NULL | - | `{"day":"Monday","time":"08:00-09:30"}` |

**UNIQUE:** (class_id, subject_id)

---

## 9. assignments

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `teacher_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | Guru pembuat |
| `class_id` | BIGINT | ❌ | - | FK → classes(id) CASCADE | Kelas tujuan |
| `subject_id` | BIGINT | ❌ | - | FK → subjects(id) CASCADE | Mata pelajaran |
| `title` | VARCHAR(255) | ❌ | - | - | Judul tugas |
| `description` | TEXT | ✅ | NULL | - | Deskripsi tugas |
| `attachment_path` | VARCHAR(255) | ✅ | NULL | - | Path file lampiran |
| `deadline` | TIMESTAMP | ❌ | - | - | Batas waktu pengumpulan |
| `max_score` | INT | ❌ | 100 | - | Nilai maksimum (1-1000) |
| `is_published` | BOOLEAN | ❌ | FALSE | - | Status publish |
| `deleted_at` | TIMESTAMP | ✅ | NULL | - | Soft delete |

**Indexes:** `idx_assignments_deadline`, `idx_assignments_class_subject`, `idx_assignments_published`

---

## 10. submissions

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `assignment_id` | BIGINT | ❌ | - | FK → assignments(id) CASCADE | |
| `student_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `content` | TEXT | ✅ | NULL | - | Konten essay |
| `file_path` | VARCHAR(255) | ✅ | NULL | - | Path file upload |
| `submitted_at` | TIMESTAMP | ❌ | NOW() | - | Waktu submit |
| `score` | INT | ✅ | NULL | - | Nilai (0 sampai max_score) |
| `feedback` | TEXT | ✅ | NULL | - | Feedback manual guru |
| `ai_feedback` | JSON | ✅ | NULL | - | Feedback AI terstruktur |
| `graded_at` | TIMESTAMP | ✅ | NULL | - | Waktu dinilai |
| `graded_by` | BIGINT | ✅ | NULL | FK → users(id) | Guru penilai |
| `is_late` | BOOLEAN | ❌ | FALSE | - | Apakah telat submit |
| `version` | INT | ❌ | 1 | - | Versi submission |
| `deleted_at` | TIMESTAMP | ✅ | NULL | - | Soft delete |

**UNIQUE:** (assignment_id, student_id, version)

> ⚠️ Score divalidasi di application layer: `0 ≤ score ≤ assignment.max_score`

---

## 11. quizzes

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `teacher_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `class_id` | BIGINT | ❌ | - | FK → classes(id) CASCADE | |
| `subject_id` | BIGINT | ❌ | - | FK → subjects(id) CASCADE | |
| `title` | VARCHAR(255) | ❌ | - | - | |
| `description` | TEXT | ✅ | NULL | - | |
| `duration_minutes` | INT | ❌ | - | - | Durasi kuis dalam menit |
| `start_time` | TIMESTAMP | ✅ | - | - | Waktu mulai tersedia |
| `end_time` | TIMESTAMP | ✅ | - | - | Waktu berakhir |
| `max_score` | INT | ❌ | 100 | - | |
| `max_attempts` | INT | ❌ | 1 | - | Maks percobaan |
| `is_published` | BOOLEAN | ❌ | FALSE | - | |
| `is_ai_generated` | BOOLEAN | ❌ | FALSE | - | Dibuat oleh AI |
| `deleted_at` | TIMESTAMP | ✅ | NULL | - | Soft delete |

---

## 12. quiz_questions

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `quiz_id` | BIGINT | ❌ | - | FK → quizzes(id) CASCADE | |
| `question_type` | VARCHAR(20) | ❌ | - | CHECK | `multiple_choice`, `essay`, `true_false` |
| `question_text` | TEXT | ❌ | - | - | Teks soal |
| `options` | JSON | ✅ | NULL | - | `{"A":"..","B":"..","C":"..","D":".."}` |
| `correct_answer_hash` | VARCHAR(255) | ✅ | NULL | - | 🔐 Hash jawaban benar |
| `explanation` | TEXT | ✅ | NULL | - | Penjelasan jawaban |
| `points` | INT | ❌ | 1 | - | Bobot nilai |
| `order_number` | INT | ❌ | - | - | Urutan soal |

> 🔐 `correct_answer_hash` menggunakan `Hash::make()`. Verifikasi dengan `Hash::check()`.

---

## 13. quiz_attempts

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `quiz_id` | BIGINT | ❌ | - | FK → quizzes(id) CASCADE | |
| `student_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `attempt_number` | INT | ❌ | 1 | - | Percobaan ke-N |
| `started_at` | TIMESTAMP | ❌ | NOW() | - | Waktu mulai |
| `submitted_at` | TIMESTAMP | ✅ | NULL | - | Waktu selesai |
| `score` | INT | ✅ | NULL | - | Nilai |
| `answers` | JSON | ✅ | NULL | - | `{"question_id":"answer"}` |
| `time_taken_seconds` | INT | ✅ | NULL | - | Durasi pengerjaan |

**UNIQUE:** (quiz_id, student_id, attempt_number)

---

## 14. attendances

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `class_id` | BIGINT | ❌ | - | FK → classes(id) CASCADE | |
| `subject_id` | BIGINT | ❌ | - | FK → subjects(id) CASCADE | |
| `student_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `date` | DATE | ❌ | - | - | Tanggal kehadiran |
| `status` | VARCHAR(20) | ❌ | - | CHECK | `present`, `absent`, `sick`, `permission` |
| `notes` | TEXT | ✅ | NULL | - | Keterangan |
| `recorded_by` | BIGINT | ✅ | - | FK → users(id) | Guru/ketua kelas |

**UNIQUE:** (class_id, subject_id, student_id, date)

---

## 15. announcements

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `author_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `class_id` | BIGINT | ✅ | NULL | FK → classes(id) CASCADE | NULL = seluruh sekolah |
| `title` | VARCHAR(255) | ❌ | - | - | |
| `content` | TEXT | ❌ | - | - | Konten (sanitized HTML) |
| `priority` | VARCHAR(20) | ❌ | `normal` | CHECK | `low`,`normal`,`high`,`urgent` |
| `published_at` | TIMESTAMP | ❌ | NOW() | - | |
| `expires_at` | TIMESTAMP | ✅ | NULL | - | Waktu kedaluwarsa |

---

## 16. notifications

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `user_id` | BIGINT | ❌ | - | FK → users(id) CASCADE | |
| `type` | VARCHAR(50) | ❌ | - | - | `assignment_created`, `quiz_graded` |
| `title` | VARCHAR(255) | ❌ | - | - | |
| `message` | TEXT | ❌ | - | - | |
| `data` | JSON | ✅ | NULL | - | Metadata tambahan |
| `read_at` | TIMESTAMP | ✅ | NULL | - | NULL = belum dibaca |

---

## 17. activity_logs

Audit trail semua aktivitas pengguna.

| Column | Type | Nullable | Default | Constraint | Keterangan |
|--------|------|----------|---------|------------|------------|
| `id` | BIGSERIAL | ❌ | auto | PK | |
| `user_id` | BIGINT | ✅ | - | FK → users(id) SET NULL | |
| `action` | VARCHAR(100) | ❌ | - | - | `created_assignment`, `graded_submission` |
| `model` | VARCHAR(50) | ✅ | NULL | - | `Assignment`, `Submission` |
| `model_id` | BIGINT | ✅ | NULL | - | ID record terkait |
| `ip_address` | INET | ✅ | NULL | - | IP pengguna |
| `user_agent` | TEXT | ✅ | NULL | - | Browser info |
| `metadata` | JSON | ✅ | NULL | - | Data tambahan |
| `created_at` | TIMESTAMP | ❌ | NOW() | - | |

---

## Diagram Relasi (ERD Summary)

```
users ─┬─ 1:1 ── student_profiles
       ├─ 1:1 ── teacher_profiles
       ├─ M:N ── classes (via class_student)
       ├─ 1:N ── assignments (as teacher)
       ├─ 1:N ── submissions (as student)
       ├─ 1:N ── quiz_attempts
       ├─ 1:N ── attendances
       ├─ 1:N ── announcements
       └─ 1:N ── notifications

classes ─┬─ M:N ── users/students (via class_student)
         ├─ M:N ── subjects (via class_subject)
         ├─ 1:N ── assignments
         ├─ 1:N ── quizzes
         └─ 1:N ── attendances

assignments ── 1:N ── submissions
quizzes ─┬─ 1:N ── quiz_questions
         └─ 1:N ── quiz_attempts
```

---

**Document Version:** 1.0 | **Last Updated:** 2026-03-16 | **Total Tables:** 17
