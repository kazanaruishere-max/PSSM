# PSSM - MVP Completion & Comprehensive Bug Fix Plan

Dokumen ini merinci langkah-langkah untuk menyelesaikan aplikasi **PSSM** hingga mencapai status **MVP** yang matang, sekaligus memperbaiki semua error, bug, dan celah keamanan yang ditemukan.

---

## **1. Perbaikan Bug & Keamanan (P0)**

### **1.1 Konsistensi Penyimpanan File**
- **Masalah:** [AssignmentController.php](file:///c:/Users/Lenovo/PROJECT/PSSM/app/Http/Controllers/AssignmentController.php) menggunakan disk `public`, sementara [SubmissionService.php](file:///c:/Users/Lenovo/PROJECT/PSSM/app/Services/SubmissionService.php) menggunakan disk `private`.
- **Solusi:** Ubah semua unggahan lampiran tugas dan jawaban siswa ke disk `private` untuk mencegah akses publik tanpa izin.
- **Tindakan:** 
    - Update `AssignmentController@store` dan `update` untuk menggunakan disk `private`.
    - Buat route `assignments.download` untuk mengunduh lampiran tugas secara aman.

### **1.2 Perlindungan IDOR (Insecure Direct Object Reference)**
- **Masalah:** Beberapa controller belum memverifikasi kepemilikan data secara ketat.
- **Solusi:** Tambahkan pengecekan kepemilikan di `SubmissionController`, `QuizController`, dan `ReportController`.
- **Tindakan:** Pastikan guru hanya bisa melihat/menilai tugas di kelas yang mereka ajar.

### **1.3 Sanitasi Nama File & Header Injection**
- **Masalah:** Nama file unduhan bisa menyebabkan *Header Injection* jika mengandung karakter khusus.
- **Solusi:** Gunakan `preg_replace` untuk membersihkan nama file sebelum dikirim ke header.
- **Tindakan:** Implementasikan sanitasi di `ReportController` dan `SubmissionController`.

---

## **2. Implementasi Fitur MVP yang Kurang (P1)**

### **2.1 Bulk Import User (Admin)**
- **Tujuan:** Memungkinkan admin mengunggah ratusan siswa/guru via Excel.
- **Tindakan:**
    - Buat `BulkImportController`.
    - Gunakan `maatwebsite/excel` untuk memproses file `.xlsx`.
    - Tambahkan UI di `admin.users.index`.

### **2.2 AI Essay Feedback**
- **Tujuan:** Memberikan saran otomatis pada jawaban essay siswa.
- **Tindakan:**
    - Hubungkan `AIService@generateEssayFeedback` ke `SubmissionController`.
    - Tambahkan kolom `ai_feedback` pada tabel `submissions`.
    - Tambahkan tombol "Generate AI Feedback" di halaman detail pengumpulan tugas.

### **2.3 WhatsApp Notification Service**
- **Tujuan:** Notifikasi real-time via WhatsApp (Fonnte API).
- **Tindakan:**
    - Buat [WhatsAppService.php](file:///c:/Users/Lenovo/PROJECT/PSSM/app/Services/WhatsAppService.php).
    - Tambahkan trigger di `AssignmentController@store` dan `AnnouncementController@store`.

### **2.4 Dashboard Analytics (Visual)**
- **Tujuan:** Menampilkan data dalam bentuk grafik (Chart.js).
- **Tindakan:**
    - Update `DashboardController` untuk mengirimkan data statistik bulanan.
    - Tambahkan grafik di [super_admin.blade.php](file:///c:/Users/Lenovo/PROJECT/PSSM/resources/views/dashboard/super_admin.blade.php) dan [teacher.blade.php](file:///c:/Users/Lenovo/PROJECT/PSSM/resources/views/dashboard/teacher.blade.php).

### **2.5 Ekspor Rapor PDF**
- **Tujuan:** Dokumen resmi hasil belajar.
- **Tindakan:**
    - Gunakan `barryvdh/laravel-dompdf`.
    - Buat template blade `pdf.report_card`.
    - Tambahkan route dan logic di `ReportController`.

---

## **3. Optimasi & Hardening (P2)**

### **3.1 Optimasi Query (N+1 Problem)**
- **Tindakan:** Audit seluruh controller untuk memastikan penggunaan `Eager Loading` (`with()`) pada setiap relasi Eloquent.

### **3.2 Penanganan Error Global**
- **Tindakan:** Pastikan [app.php](file:///c:/Users/Lenovo/PROJECT/PSSM/bootstrap/app.php) tidak membocorkan *stack trace* di lingkungan produksi dan memberikan pesan error yang ramah pengguna dalam Bahasa Indonesia.

---

## **4. Checklist Penyelesaian MVP**
- [ ] Perbaikan disk penyimpanan (Private Disk)
- [ ] Fitur Bulk Import User
- [ ] Fitur AI Essay Feedback
- [ ] Fitur WhatsApp Notification
- [ ] Visual Dashboard (Charts)
- [ ] Ekspor Rapor PDF
- [ ] Audit Keamanan & Sanitasi Data
