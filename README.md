# PSSM - Powered Smart School Management 🚀
### "Zero Paper, AI-Powered, Enterprise-Grade Education System"

![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)
![Laravel](https://img.shields.io/badge/Framework-Laravel%2012-FF2D20?logo=laravel)
![TailwindCSS](https://img.shields.io/badge/Styling-TailwindCSS-38B2AC?logo=tailwind-css)
![AI](https://img.shields.io/badge/AI-Google%20Gemini-blue?logo=google-gemini)

---

## 📖 Jurnal Proyek: Transformasi Digital Pendidikan Indonesia
**Author:** Kazanaru  
**Status:** MVP (Minimum Viable Product) Matang  
**Versi:** 1.0.0 (Core Engine)

### **Abstrak**
Dalam era Industri 4.0, institusi pendidikan di Indonesia masih menghadapi tantangan besar dalam efisiensi administrasi dan adaptasi teknologi cerdas. **PSSM (Powered Smart School Management)** hadir sebagai solusi komprehensif yang tidak hanya mendigitalisasi data ("Zero Paper"), tetapi juga mengintegrasikan Kecerdasan Buatan (AI) untuk membantu guru dalam proses pedagogik yang repetitif seperti pembuatan kuis dan analisis esai.

### **Latar Belakang & Masalah**
1. **Beban Kerja Guru:** Guru menghabiskan 40% waktu mereka untuk tugas administratif daripada mengajar.
2. **Keamanan Data:** Banyak sistem sekolah yang rentan terhadap penyusup dan kebocoran nilai.
3. **Fragmentasi Data:** Informasi tugas, absensi, dan pengumuman sering tersebar di berbagai platform (WA, Grup, dll).

---

## ✨ Fitur Unggulan (USP)

### 🤖 **Smart Academy (AI Integration)**
*   **AI Quiz Generator:** Membuat soal pilihan ganda secara otomatis berdasarkan topik tertentu menggunakan Google Gemini API.
*   **AI Essay Feedback:** Memberikan saran perbaikan tata bahasa, struktur, dan kekuatan argumen pada jawaban esai siswa secara instan.

### 🏫 **Manajemen Akademik Terintegrasi**
*   **Digital Attendance:** Absensi real-time yang dapat diinput oleh Guru atau Ketua Kelas dengan audit trail lengkap.
*   **CBT (Computer Based Test):** Sistem ujian online profesional dengan timer presisi dan perlindungan anti-contek (disable text selection/hashing answers).
*   **Smart Dashboard:** Visualisasi data menggunakan Chart.js untuk memantau tren kehadiran dan progres nilai siswa.

### 🛡️ **Keamanan Tingkat Tinggi (Enterprise Security)**
*   **Hashed Answer Keys:** Kunci jawaban disimpan dalam bentuk hash Bcrypt, mencegah kebocoran meskipun akses database ditembus.
*   **RBAC (Role-Based Access Control):** Otorisasi ketat untuk Super Admin, Guru, Ketua Kelas, dan Siswa menggunakan Spatie Laravel Permission.
*   **Private Storage Disk:** Seluruh dokumen tugas siswa disimpan di storage yang tidak dapat diakses secara publik.

---

## 🛠️ Tech Stack (Arsitektur Teknologi)

| Komponen | Teknologi |
| :--- | :--- |
| **Backend** | Laravel 12.x (PHP 8.3+) |
| **Frontend** | Shadcn UI Style + Tailwind CSS + Alpine.js |
| **Database** | PostgreSQL / SQLite (Development) |
| **AI Engine** | Google Gemini API (Generative AI) |
| **Icons** | Lucide SVG Icons |
| **PDF Engine** | Barryvdh Laravel DomPDF |

---

## 🚀 Panduan Instalasi (Local Development)

### **Prasyarat**
*   PHP >= 8.3
*   Composer
*   Node.js & NPM
*   Gemini API Key (untuk fitur AI)

### **Langkah-langkah**
1.  **Clone Repository**
    ```bash
    git clone https://github.com/kazanaru/pssm.git
    cd pssm
    ```
2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```
3.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
4.  **Database Migration & Seeding**
    ```bash
    php artisan migrate --seed
    ```
5.  **Compile Assets**
    ```bash
    npm run build
    ```
6.  **Run Server**
    ```bash
    php artisan serve
    ```

---

## 📜 Lisensi & Hak Cipta
Proyek ini didistribusikan di bawah **Lisensi MIT**. Seluruh hak kepemilikan intelektual dan kekayaan intelektual atas kode sumber ini sepenuhnya milik **Kazanaru**.

Copyright © 2026 **Kazanaru**.

---

## 📧 Kontak & Kontribusi
Jika Anda tertarik untuk berkolaborasi atau memiliki pertanyaan mengenai implementasi PSSM di sekolah Anda, silakan hubungi pengembang melalui profil GitHub ini.

---
*"PSSM: Empowering Teachers, Elevating Students."*
