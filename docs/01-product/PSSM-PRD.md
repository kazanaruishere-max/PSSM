# PSSM - Powered Smart School Management
## Product Requirements Document (PRD) v1.0

**Project Name:** PSSM (Powered Smart School Management)  
**Framework:** Laravel 11  
**Target:** Sekolah menengah & atas di Indonesia  
**Timeline:** 8 minggu (MVP)  
**Budget:** Rp 0 (Free tier infrastructure)

---

## 1. Executive Summary

### Problem Statement

> "Sekolah di Indonesia masih bergantung pada sistem manual (kertas, Excel) untuk manajemen akademik. Guru menghabiskan 15+ jam/minggu untuk administrasi, siswa kesulitan tracking progress, dan orang tua tidak punya visibilitas real-time."

### Solution: PSSM

**Tagline:** *"Digitalisasi Sekolah dalam 1 Platform"*

**Core Value Propositions:**
1. **AI-Powered Education** - Kuis otomatis, feedback instan, analitik cerdas
2. **Zero Paper** - 100% digital dari absensi hingga raport
3. **Real-time Collaboration** - Guru, siswa, orang tua terhubung langsung
4. **Cost-Efficient** - Free tier infrastructure, no vendor lock-in
5. **Indonesian-First** - Bahasa Indonesia, kurikulum lokal, zona waktu WIB

---

## 2. User Personas

### Persona 1: Guru (Teacher)
**Nama:** Bu Siti (35 tahun)  
**Role:** Guru Matematika SMA  
**Pain Points:**
- Koreksi tugas 120 siswa memakan waktu 10 jam/minggu
- Rekap nilai pakai Excel sering error
- Sulit tracking mana siswa yang belum submit tugas

**Goals:**
- Otomasi koreksi kuis pilihan ganda
- Ekspor nilai langsung ke Excel/PDF
- Notifikasi otomatis untuk siswa yang telat submit

**User Stories:**
```
US-1: Sebagai guru, saya ingin membuat kuis dengan AI generator
      agar tidak perlu manual menulis 20+ soal setiap minggu.
      
US-2: Sebagai guru, saya ingin sistem auto-grade kuis pilihan ganda
      agar tidak perlu koreksi manual.
      
US-3: Sebagai guru, saya ingin export nilai ke Excel
      agar bisa langsung print raport.
```

---

### Persona 2: Siswa (Student)
**Nama:** Budi (16 tahun)  
**Role:** Siswa kelas 11  
**Pain Points:**
- Lupa deadline tugas karena tidak ada reminder
- Tidak tahu nilai tugas sebelumnya berapa
- Materi pelajaran tercecer di berbagai platform

**Goals:**
- Dashboard terpusat untuk semua mata pelajaran
- Notifikasi deadline via email/WhatsApp
- Tracking progress nilai real-time

**User Stories:**
```
US-4: Sebagai siswa, saya ingin melihat semua tugas dalam 1 dashboard
      agar tidak perlu cek grup WhatsApp 5 kelas.
      
US-5: Sebagai siswa, saya ingin submit tugas langsung di website
      agar tidak perlu kirim email ke guru.
      
US-6: Sebagai siswa, saya ingin melihat grafik nilai saya
      agar bisa tracking apakah naik atau turun.
```

---

### Persona 3: Ketua Kelas (Class Leader)
**Nama:** Ani (17 tahun)  
**Role:** Ketua kelas 12 IPA 1  
**Pain Points:**
- Harus manual broadcast pengumuman ke grup WA
- Rekap absensi manual pakai kertas
- Sulit koordinasi acara kelas

**Goals:**
- Fitur broadcast pengumuman ke semua siswa sekelas
- Sistem absensi digital (QR code/manual input)
- Kalender event kelas

**User Stories:**
```
US-7: Sebagai ketua kelas, saya ingin broadcast pengumuman
      agar semua teman sekelas dapat notifikasi.
      
US-8: Sebagai ketua kelas, saya ingin input absensi harian
      agar guru tidak perlu manual rekap.
```

---

### Persona 4: Admin Sekolah (Super Admin)
**Nama:** Pak Bambang (45 tahun)  
**Role:** Wakil Kepala Sekolah Bidang Kurikulum  
**Pain Points:**
- Data siswa tersebar di berbagai Excel
- Sulit monitoring kinerja guru
- Tidak ada sistem backup data

**Goals:**
- Dashboard analytics seluruh sekolah
- Manajemen user terpusat
- Audit trail semua aktivitas

**User Stories:**
```
US-9: Sebagai admin, saya ingin melihat dashboard analytics
      untuk monitoring jumlah tugas, kuis, dan kehadiran siswa.
      
US-10: Sebagai admin, saya ingin export data seluruh siswa
       agar bisa backup ke Google Drive setiap bulan.
```

---

## 3. Features Breakdown

### Phase 1: Core Features (Week 1-4)

#### A. Authentication & Authorization
**Priority:** P0 (Critical)

**Features:**
- Login via email/password (Laravel Breeze)
- Role-based access control (RBAC)
  - Super Admin
  - Teacher
  - Class Leader
  - Student
- Password reset via email
- Session management (Redis)

**Acceptance Criteria:**
- User dapat login dalam <2 detik
- Session expire setelah 24 jam inaktif
- Password harus minimal 8 karakter + 1 angka
- Email verification wajib untuk siswa baru

---

#### B. Dashboard (Multi-Role)
**Priority:** P0

**Super Admin Dashboard:**
```
┌─────────────────────────────────────────┐
│ Total Siswa: 450                        │
│ Total Guru: 25                          │
│ Total Kelas: 15                         │
│ Tugas Aktif: 120                        │
└─────────────────────────────────────────┘

Charts:
- Grafik kehadiran 30 hari terakhir
- Grafik submission rate tugas
- Top 5 siswa terbaik
```

**Teacher Dashboard:**
```
┌─────────────────────────────────────────┐
│ Kelas Anda: 3 kelas (11 IPA 1, 2, 3)   │
│ Tugas Pending Review: 45                │
│ Kuis Aktif: 2                           │
└─────────────────────────────────────────┘

Quick Actions:
[+ Buat Tugas] [+ Buat Kuis] [📊 Lihat Nilai]
```

**Student Dashboard:**
```
┌─────────────────────────────────────────┐
│ Tugas Deadline Terdekat:                │
│ - Matematika: Soal Integral (2 hari)    │
│ - Fisika: Laporan Praktikum (5 hari)   │
│                                         │
│ Nilai Rata-rata: 85.5                  │
└─────────────────────────────────────────┘

[Lihat Semua Tugas] [Lihat Jadwal] [Lihat Nilai]
```

**Acceptance Criteria:**
- Dashboard load dalam <1 detik
- Data real-time (via Laravel Echo + Pusher)
- Responsive mobile (Tailwind CSS)

---

#### C. Manajemen Kelas (Class Management)
**Priority:** P0

**Fitur:**
- CRUD kelas (nama, tingkat, tahun ajaran)
- Assign siswa ke kelas (bulk import CSV)
- Assign guru ke mata pelajaran
- Kalender akademik (libur, ujian, event)

**Database Schema:**
```sql
-- classes table
id, name, grade_level, academic_year, homeroom_teacher_id

-- class_student (pivot)
id, class_id, student_id, enrollment_date

-- class_subject (pivot)
id, class_id, subject_id, teacher_id, schedule
```

**Acceptance Criteria:**
- Admin dapat import 100+ siswa via CSV dalam <30 detik
- Siswa otomatis masuk grup kelas setelah di-assign
- Sistem prevent duplicate assignment

---

#### D. Tugas (Assignments)
**Priority:** P0

**Fitur:**
1. **Buat Tugas** (Teacher)
   - Judul, deskripsi, deadline
   - Attachment (PDF, Word, image)
   - Point/bobot nilai
   - Mata pelajaran

2. **Submit Tugas** (Student)
   - Upload file (max 10MB)
   - Text submission (untuk essay)
   - Late submission warning

3. **Grading** (Teacher)
   - Manual scoring
   - Comment/feedback
   - Rubric scoring (optional)

**Database Schema:**
```sql
-- assignments
id, teacher_id, class_id, subject_id, title, description, 
deadline, max_score, created_at

-- submissions
id, assignment_id, student_id, content, file_path, 
submitted_at, score, feedback, graded_at
```

**Acceptance Criteria:**
- Siswa dapat submit tugas dalam <5 detik
- File upload support: PDF, DOCX, JPG, PNG
- Siswa dapat re-submit sebelum deadline
- Email notification 24 jam sebelum deadline

---

#### E. Kuis (Quizzes)
**Priority:** P1

**Fitur:**
1. **Buat Kuis Manual**
   - Pilihan ganda (A/B/C/D/E)
   - Essay
   - True/False
   - Timer per soal

2. **AI Quiz Generator** 🤖
   - Input: Topik + Jumlah soal
   - Output: Kuis lengkap dengan kunci jawaban
   - Powered by: OpenRouter (Claude/GPT)

3. **Kerjakan Kuis** (Student)
   - Full-screen mode (anti-cheat)
   - Auto-submit saat timer habis
   - Show score immediately (pilgan)

**AI Prompt Example:**
```
Generate 10 multiple choice questions about "Photosynthesis in Plants"
for high school biology (Indonesian curriculum).

Format:
1. Question text
2. Options: A, B, C, D
3. Correct answer
4. Explanation

Return JSON format.
```

**Acceptance Criteria:**
- AI generate kuis dalam <10 detik
- Auto-grading akurasi 100% untuk pilihan ganda
- Timer akurasi ±1 detik (JavaScript + server-side validation)

---

### Phase 2: Advanced Features (Week 5-6)

#### F. AI Feedback System
**Priority:** P1

**Fitur:**
- Auto-analyze student essay submissions
- Generate constructive feedback
- Highlight grammar errors (Bahasa Indonesia)
- Suggest improvements

**AI Prompt Example:**
```
Analyze this student essay about "Global Warming" (max 500 words):

"{student_essay_text}"

Provide feedback on:
1. Structure (intro, body, conclusion)
2. Grammar errors (Bahasa Indonesia)
3. Argument strength
4. Vocabulary usage

Return JSON with scores (0-100) and suggestions.
```

**Acceptance Criteria:**
- Feedback generated dalam <15 detik
- Accuracy: Manual review 80%+ agree with AI feedback
- Support Bahasa Indonesia + English

---

#### G. Analytics & Reports
**Priority:** P1

**Fitur:**
1. **Student Analytics**
   - Grafik nilai per mata pelajaran
   - Attendance rate
   - Submission rate
   - Comparison dengan rata-rata kelas

2. **Teacher Analytics**
   - Student engagement rate
   - Average score per assignment
   - Most difficult topics (banyak siswa score rendah)

3. **Export Reports**
   - Excel (Laravel Excel)
   - PDF (DomPDF)
   - Format: Raport, Rekap Nilai, Absensi

**Acceptance Criteria:**
- Export 100 siswa dalam <5 detik
- Charts render dalam <1 detik (Chart.js)
- PDF format sesuai standar raport sekolah

---

#### H. Attendance System
**Priority:** P2

**Fitur:**
1. **Manual Input** (Teacher/Class Leader)
   - Checklist siswa hadir/izin/sakit/alpha
   - Input per hari per mata pelajaran

2. **QR Code Scan** (Future: Phase 3)
   - Generate QR code unik per kelas per hari
   - Siswa scan via mobile
   - Auto-record kehadiran

**Database Schema:**
```sql
-- attendances
id, class_id, subject_id, date, student_id, 
status (present/absent/sick/permission), notes
```

**Acceptance Criteria:**
- Input 30 siswa dalam <2 menit
- Data tersimpan real-time
- Recap attendance per bulan

---

### Phase 3: Nice-to-Have (Week 7-8)

#### I. Notifikasi Multi-Channel
**Channels:**
- Email (Laravel Mail + Mailtrap/SendGrid)
- WhatsApp (via Fonnte API - free tier)
- In-app notification (Laravel Echo)

**Triggers:**
- Tugas baru dibuat → Siswa
- Deadline H-1 → Siswa
- Tugas di-grade → Siswa
- Siswa submit tugas → Guru

---

#### J. Parent Portal
**Fitur:**
- Parent dapat melihat nilai anak
- Parent dapat melihat attendance anak
- Parent tidak bisa edit apapun (read-only)

**User Stories:**
```
US-11: Sebagai orang tua, saya ingin melihat nilai anak saya
       agar bisa monitoring tanpa perlu tanya langsung.
```

---

## 4. Non-Functional Requirements

### Performance
- Page load time: <2 detik (95th percentile)
- API response time: <500ms
- Database query optimization (N+1 problem prevention)
- Image optimization (WebP format, lazy loading)

### Security
- SQL injection prevention (Laravel Eloquent ORM)
- XSS protection (Blade templating auto-escape)
- CSRF protection (Laravel default)
- File upload validation (whitelist extension)
- Rate limiting (100 req/min per IP)

### Scalability
- Support 1,000 concurrent users
- Database indexing (foreign keys, search columns)
- Redis caching (query results, session)
- CDN untuk static assets (Cloudflare free)

### Availability
- Uptime target: 99% (allow 7 jam downtime/bulan)
- Database backup daily (automated script)
- Error logging (Laravel Telescope)

---

## 5. Success Metrics

### Adoption Metrics
- **Week 1-2:** 50% guru aktif buat tugas
- **Week 3-4:** 70% siswa submit tugas via platform
- **Month 2:** 90% tugas paperless
- **Month 3:** 100% nilai tersimpan digital

### Engagement Metrics
- Daily Active Users (DAU): 60% dari total users
- Average session duration: >5 menit
- Assignment submission rate: >85%

### Quality Metrics
- AI feedback accuracy: >80% (manual review)
- Bug report rate: <1% dari total transactions
- User satisfaction (survey): >4.0/5.0

---

## 6. Out of Scope (v1.0)

❌ **Not Included:**
- Pembayaran SPP (future: v2.0)
- Mobile app (future: v2.0)
- Video conference integration
- Perpustakaan digital
- GPS bus tracking
- Multi-language (hanya Bahasa Indonesia)

---

## 7. Risks & Mitigations

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| AI API rate limit | High | Medium | Cache hasil AI 24 jam, fallback manual |
| Data loss | Critical | Low | Daily backup + replica database |
| Performance degradation | High | Medium | Redis caching + query optimization |
| Low user adoption | High | Medium | Training session + user manual |
| Budget overrun | Medium | Low | Free tier only, monitor usage |

---

## 8. Timeline & Milestones

### Week 1-2: Foundation
- [ ] Laravel project setup
- [ ] Database schema design
- [ ] Authentication (Breeze)
- [ ] Role-based access control
- [ ] Basic dashboard (4 roles)

### Week 3-4: Core Features
- [ ] Class management (CRUD)
- [ ] Assignment CRUD
- [ ] Submission system
- [ ] Manual grading
- [ ] Excel export

### Week 5-6: AI Features
- [ ] OpenRouter integration
- [ ] AI quiz generator
- [ ] AI feedback system
- [ ] Analytics dashboard
- [ ] Charts (Chart.js)

### Week 7-8: Polish & Launch
- [ ] Attendance system
- [ ] Email notifications
- [ ] Bug fixes
- [ ] User testing (10 guru + 30 siswa)
- [ ] Production deployment

---

## 9. Approval

**Product Owner:** ✅ Approved  
**Tech Lead:** ✅ Approved  
**Stakeholder (Sekolah):** ⏳ Pending review

**Next Steps:**
1. Review PRD dengan tim
2. Create Design Doc
3. Start development (Week 1)

---

**Document Version:** 1.0  
**Last Updated:** 2026-03-16  
**Status:** Ready for Development ✅
