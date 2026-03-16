# PSSM - API Reference Documentation
## Complete REST API Specification v1.0

**Base URL:** `https://pssm.school/api` (Production) | `http://localhost:8000/api` (Development)  
**Authentication:** Laravel Sanctum (Bearer Token)  
**Content-Type:** `application/json`  
**Timezone:** `Asia/Jakarta (WIB, UTC+7)`

---

## 1. Authentication

### POST `/api/login`

Login dan dapatkan access token.

**Rate Limit:** 5 requests/menit per IP

**Request:**
```json
{
    "email": "siti@school.id",
    "password": "SecurePassword123!"
}
```

**Response 200:**
```json
{
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "Bu Siti",
            "email": "siti@school.id",
            "role": "teacher"
        },
        "token": "1|abc123xyz...",
        "expires_at": "2026-03-17T17:00:00+07:00"
    }
}
```

**Error 401:**
```json
{ "message": "Email atau password salah." }
```

**Error 429:**
```json
{ "message": "Terlalu banyak percobaan. Coba lagi dalam 5 menit." }
```

---

### POST `/api/logout`
🔒 **Auth Required**

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{ "message": "Logout berhasil." }
```

---

### POST `/api/password/reset`

**Request:**
```json
{ "email": "siti@school.id" }
```

**Response 200:**
```json
{ "message": "Link reset password telah dikirim ke email." }
```

---

## 2. Assignments

### GET `/api/assignments`
🔒 **Auth Required** | **Permission:** `assignments.view`

List tugas berdasarkan role user.

**Query Parameters:**

| Param | Type | Default | Keterangan |
|-------|------|---------|------------|
| `page` | int | 1 | Halaman pagination |
| `per_page` | int | 15 | Jumlah per halaman (max: 50) |
| `class_id` | int | - | Filter berdasarkan kelas |
| `subject_id` | int | - | Filter berdasarkan mata pelajaran |
| `status` | string | - | `active`, `past_deadline`, `all` |
| `sort` | string | `deadline` | `deadline`, `created_at`, `title` |
| `order` | string | `asc` | `asc`, `desc` |

**Response 200:**
```json
{
    "data": [
        {
            "id": 1,
            "title": "Soal Integral Trigonometri",
            "description": "Kerjakan soal 1-10 dari halaman 45",
            "deadline": "2026-03-20T23:59:00+07:00",
            "max_score": 100,
            "is_published": true,
            "teacher": { "id": 5, "name": "Bu Siti" },
            "class": { "id": 3, "name": "11 IPA 1" },
            "subject": { "id": 2, "name": "Matematika" },
            "submission_count": 25,
            "total_students": 30,
            "has_attachment": true,
            "created_at": "2026-03-15T08:00:00+07:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 3,
        "per_page": 15,
        "total": 42
    }
}
```

---

### POST `/api/assignments`
🔒 **Auth Required** | **Permission:** `assignments.create` | **Roles:** Teacher, Super Admin

**Request (multipart/form-data):**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `title` | string | ✅ | max:255 |
| `description` | string | ❌ | max:10000 |
| `class_id` | int | ✅ | exists:classes,id |
| `subject_id` | int | ✅ | exists:subjects,id |
| `deadline` | datetime | ✅ | after:now |
| `max_score` | int | ❌ | min:1, max:1000, default:100 |
| `attachment` | file | ❌ | mimes:pdf,doc,docx,jpg,png \| max:10MB |

**Response 201:**
```json
{
    "message": "Tugas berhasil dibuat.",
    "data": {
        "id": 42,
        "title": "Soal Integral Trigonometri",
        "deadline": "2026-03-20T23:59:00+07:00"
    }
}
```

**Error 422:**
```json
{
    "message": "Validasi gagal.",
    "errors": {
        "title": ["Judul wajib diisi."],
        "deadline": ["Deadline harus setelah waktu sekarang."]
    }
}
```

---

### GET `/api/assignments/{id}`
🔒 **Auth Required** | **Permission:** `assignments.view`

**Response 200:** Detail lengkap assignment termasuk daftar submission (untuk teacher).

---

### PUT `/api/assignments/{id}`
🔒 **Auth Required** | **Permission:** `assignments.edit` | **Policy:** Hanya pemilik atau super_admin

**Request:** Sama seperti POST, semua field optional.

---

### DELETE `/api/assignments/{id}`
🔒 **Auth Required** | **Permission:** `assignments.delete` | **Policy:** Hanya pemilik atau super_admin

**Response 200:**
```json
{ "message": "Tugas berhasil dihapus." }
```

> ⚠️ Soft delete — data masih tersimpan di database.

---

### POST `/api/assignments/{id}/submit`
🔒 **Auth Required** | **Roles:** Student

**Request (multipart/form-data):**

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `content` | string | ❌ | max:10000 (untuk essay) |
| `file` | file | ❌ | mimes:pdf,doc,docx,jpg,png \| max:10MB |

> Minimal satu dari `content` atau `file` wajib diisi.

**Response 201:**
```json
{
    "message": "Tugas berhasil disubmit.",
    "data": {
        "id": 100,
        "version": 1,
        "is_late": false,
        "submitted_at": "2026-03-18T14:30:00+07:00"
    }
}
```

**Error 403:**
```json
{ "message": "Tugas sudah dinilai. Hubungi guru untuk re-submit." }
```

---

## 3. Quizzes

### POST `/api/quizzes`
🔒 **Auth Required** | **Permission:** `quizzes.create`

**Request:**
```json
{
    "title": "Kuis Fotosintesis",
    "class_id": 3,
    "subject_id": 5,
    "duration_minutes": 30,
    "start_time": "2026-03-20T08:00:00+07:00",
    "end_time": "2026-03-20T09:00:00+07:00",
    "max_score": 100,
    "max_attempts": 1,
    "questions": [
        {
            "question_text": "Proses fotosintesis menghasilkan...",
            "question_type": "multiple_choice",
            "options": { "A": "CO2", "B": "O2", "C": "N2", "D": "H2" },
            "correct_answer": "B",
            "explanation": "Fotosintesis menghasilkan oksigen (O2).",
            "points": 10
        }
    ]
}
```

---

### POST `/api/quizzes/generate-ai`
🔒 **Auth Required** | **Permission:** `quizzes.create`  
**Rate Limit:** 3 requests/menit per user

**Request:**
```json
{
    "topic": "Fotosintesis pada Tumbuhan",
    "question_count": 10,
    "difficulty": "medium",
    "class_id": 3,
    "subject_id": 5
}
```

**Response 202 (Accepted — processing via queue):**
```json
{
    "message": "Kuis sedang di-generate oleh AI. Notifikasi akan dikirim.",
    "data": { "job_id": "abc123" }
}
```

---

### POST `/api/quizzes/{id}/start`
🔒 **Auth Required** | **Roles:** Student

**Response 200:**
```json
{
    "data": {
        "attempt_id": 50,
        "attempt_number": 1,
        "started_at": "2026-03-20T08:05:00+07:00",
        "expires_at": "2026-03-20T08:35:00+07:00",
        "questions": [
            {
                "id": 1,
                "question_text": "Proses fotosintesis menghasilkan...",
                "question_type": "multiple_choice",
                "options": { "A": "CO2", "B": "O2", "C": "N2", "D": "H2" },
                "points": 10
            }
        ]
    }
}
```

> ⚠️ `correct_answer` TIDAK disertakan di response.

---

### POST `/api/quizzes/{id}/submit`
🔒 **Auth Required** | **Roles:** Student

**Request:**
```json
{
    "attempt_id": 50,
    "answers": {
        "1": "B",
        "2": "A",
        "3": "C"
    }
}
```

**Response 200:**
```json
{
    "data": {
        "score": 80,
        "total_questions": 10,
        "correct_answers": 8,
        "time_taken_seconds": 1200
    }
}
```

---

## 4. Grading

### POST `/api/submissions/{id}/grade`
🔒 **Auth Required** | **Permission:** `assignments.grade` | **Policy:** Teacher pemilik assignment

**Request:**
```json
{
    "score": 85,
    "feedback": "Jawaban sudah baik, tapi perlu perbaikan di bagian kesimpulan."
}
```

**Validation:** `score` min:0, max: `assignment.max_score` (dinamis)

---

### POST `/api/submissions/{id}/ai-feedback`
🔒 **Auth Required** | **Permission:** `assignments.grade`  
**Rate Limit:** 3 requests/menit

Minta AI generate feedback untuk essay submission.

**Response 200:**
```json
{
    "data": {
        "structure_score": 75,
        "grammar_score": 80,
        "argument_score": 70,
        "vocabulary_score": 85,
        "overall_score": 77,
        "strengths": ["Struktur paragraf rapi", "Vocabulary bervariasi"],
        "improvements": ["Tambahkan data pendukung", "Kesimpulan terlalu singkat"],
        "detailed_feedback": "Essay ini membahas topik dengan cukup baik..."
    }
}
```

---

## 5. Analytics

### GET `/api/analytics/dashboard`
🔒 **Auth Required**

Response berbeda berdasarkan role (super_admin/teacher/student).

### GET `/api/analytics/student/{id}`
🔒 **Auth Required** | **Policy:** Hanya diri sendiri, guru terkait, atau super_admin

### GET `/api/analytics/class/{id}`
🔒 **Auth Required** | **Permission:** `analytics.view_class`

---

## 6. Attendance

### POST `/api/attendance`
🔒 **Auth Required** | **Permission:** `attendance.record`

**Request:**
```json
{
    "class_id": 3,
    "subject_id": 2,
    "date": "2026-03-16",
    "records": [
        { "student_id": 10, "status": "present" },
        { "student_id": 11, "status": "absent", "notes": "Tanpa keterangan" },
        { "student_id": 12, "status": "sick", "notes": "Surat dokter" },
        { "student_id": 13, "status": "permission", "notes": "Izin keluarga" }
    ]
}
```

---

## 7. Export

### POST `/api/grades/export`
🔒 **Auth Required** | **Permission:** `admin.export_data`  
**Rate Limit:** 10 requests/jam

**Request:**
```json
{
    "class_id": 3,
    "subject_id": 2,
    "format": "xlsx"
}
```

**Response:** File download (application/vnd.openxmlformats-officedocument.spreadsheetml.sheet)

---

## 8. System

### GET `/api/health`
🔓 **No Auth Required**

**Response 200:**
```json
{
    "status": "healthy",
    "checks": {
        "app": true,
        "database": true,
        "redis": true,
        "storage": true
    },
    "timestamp": "2026-03-16T17:00:00+07:00"
}
```

---

## 9. Standard Error Codes

| HTTP Code | Meaning | Contoh |
|-----------|---------|--------|
| `200` | OK | Request berhasil |
| `201` | Created | Resource baru berhasil dibuat |
| `202` | Accepted | Request diterima, diproses secara async |
| `400` | Bad Request | Request body tidak valid |
| `401` | Unauthorized | Token tidak ada atau expired |
| `403` | Forbidden | Tidak punya permission |
| `404` | Not Found | Resource tidak ditemukan |
| `422` | Unprocessable Entity | Validasi gagal |
| `429` | Too Many Requests | Rate limit tercapai |
| `500` | Internal Server Error | Kesalahan server |

**Standard Error Format:**
```json
{
    "message": "Deskripsi error yang user-friendly (Bahasa Indonesia)",
    "errors": {
        "field_name": ["Pesan validasi spesifik."]
    }
}
```

> ⚠️ Di production, error 500 **tidak pernah** menampilkan stack trace atau detail internal.

---

## 10. Authentication Headers

Semua endpoint yang memerlukan auth **wajib** menyertakan:

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

Untuk file upload, gunakan `Content-Type: multipart/form-data`.

---

**Document Version:** 1.0  
**Last Updated:** 2026-03-16  
**Status:** Ready ✅
