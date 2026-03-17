# PSSM - Powered Smart School Management 🚀
### "Empowering Education with Artificial Intelligence & Enterprise Security"

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Framework-Laravel%2012-FF2D20?logo=laravel)](https://laravel.com)
[![AI](https://img.shields.io/badge/AI-Google%20Gemini-blue?logo=google-gemini)](https://ai.google.dev/)
[![Version](https://img.shields.io/badge/Version-1.0.0--MVP-green)](https://github.com/kazanaru/pssm)

---

## 📖 Jurnal Proyek & Visi
**Kazanaru** menghadirkan **PSSM**, sebuah ekosistem manajemen sekolah modern yang dirancang untuk menjawab tantangan digitalisasi pendidikan di Indonesia. Fokus utama kami adalah efisiensi operasional guru dan keamanan data siswa yang tidak dapat dikompromi.

### **Problem Statement**
Institusi pendidikan saat ini seringkali terjebak dalam tumpukan kertas (Paper-based) dan sistem digital yang kaku. Guru kehilangan waktu berharga untuk mengoreksi esai secara manual atau membuat bank soal. PSSM hadir untuk mengotomatisasi hal tersebut.

---

## 🗺️ Roadmap Pengembangan (Roadmap)

### **Fase 1: Fondasi & MVP (Current ✅)**
- [x] Arsitektur Multi-role (Admin, Guru, Siswa).
- [x] Sistem Manajemen Kelas & Akademik.
- [x] AI Quiz Generator (Multiple Choice).
- [x] AI Essay Feedback (Analisis Naratif).
- [x] Export Rapor PDF & Data CSV.
- [x] Keamanan Hashed Quiz & Private Storage.

---

## 🏗️ Alur Kerja Sistem (Master Workflow)

PSSM dirancang dengan alur kerja yang terintegrasi dari instalasi hingga operasional harian. Berikut adalah gambaran besarnya:

```mermaid
graph TD
    A[Instalasi & Deployment] --> B[Onboarding Data Master]
    B --> C[Aktivitas Akademik Harian]
    C --> D[Evaluasi & Ujian CBT AI]
    D --> E[Pelaporan & Rapor PDF]
    E --> F[Arsip & Penutupan Semester]
```

### **Detail Aktivitas:**
1.  **Deployment:** Setup server Laravel 12 & PostgreSQL 16.
2.  **Onboarding:** Admin mengimpor data guru/siswa via Excel secara massal.
3.  **Harian:** Guru melakukan absensi mobile & posting materi/tugas.
4.  **Ujian:** AI membantu guru membuat soal; Siswa mengerjakan di mode CBT yang terkunci.
5.  **Rapor:** Sistem menghitung nilai otomatis & menghasilkan PDF Rapor profesional.

---

## 🏗️ Arsitektur Keamanan (Security Architecture)

PSSM menggunakan pendekatan **Security-by-Design**:
1.  **Hashed Integrity:** Seluruh kunci jawaban kuis tidak disimpan dalam teks biasa, melainkan melalui proses **Bcrypt Hashing**. Kebocoran database tidak akan membocorkan jawaban.
2.  **Private Vault:** File lampiran tugas disimpan di folder `storage/app/private` yang hanya bisa diakses melalui *Signed Route* Laravel setelah melewati middleware otentikasi.
3.  **Role-Based Access Control (RBAC):** Implementasi ketat menggunakan Spatie, memastikan siswa tidak memiliki celah untuk menyuntikkan data ke modul guru.

---

## 📊 Flowchart Detail Sistem

### **Sistem Absensi Digital**
1.  **Mulai:** Guru/Ketua Kelas membuka modul absensi.
2.  **Pilih:** Memilih Kelas, Mata Pelajaran, dan Tanggal.
3.  **Input:** Menandai status (Hadir, Izin, Sakit, Alpa).
4.  **Simpan:** Data masuk ke tabel `attendances` dengan log penginput.
5.  **Audit:** Super Admin dapat melihat riwayat perubahan data absensi.

---

## 🚀 Panduan Instalasi Profesional

### **Persyaratan Sistem**
- PHP 8.3 or higher
- PostgreSQL 16 or SQLite
- Node.js 20+
- Redis (Optional for caching)

### **Instalasi Cepat**
```bash
# Clone
git clone https://github.com/kazanaru/pssm.git && cd pssm

# Backend
composer install
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Frontend (Shadcn Style)
npm install
npm run build

# Start
php artisan serve
```

---

## 📜 Dokumen Lengkap & Jurnal Teknis
Untuk pembahasan mendalam mengenai filosofi, arsitektur keamanan, dan workflow sistem secara menyeluruh (artikel & jurnal 1000+ baris), silakan baca:
👉 **[JURNAL TEKNIS PSSM (The Ultimate Guide)](docs/JOURNAL.md)**

---

## 🤝 Kontribusi & Dukungan
Proyek ini bersifat open-source. Kami menyambut kontributor yang ingin membantu memajukan pendidikan digital. Silakan buat *Pull Request* atau ajukan *Issue* jika menemukan bug.

---
**Copyright © 2026 Kazanaru.**  
*Built with ❤️ for better Indonesian Education.*
