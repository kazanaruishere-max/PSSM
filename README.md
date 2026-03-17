# PSSM — Powered Smart School Management

> *"Digitalisasi Sekolah dalam 1 Platform"*

**Framework:** Laravel 11 · **PHP:** 8.3+ · **Database:** PostgreSQL 16 · **Cache:** Redis 7  
**Target:** Sekolah menengah & atas di Indonesia  
**Timeline:** 8 minggu (MVP) · **Budget:** Rp 0 (free tier)

---

## ✨ Core Features

- 🤖 **AI-Powered Quiz Generator** — Generate kuis otomatis dari topik apapun
- 📝 **Assignment Management** — Buat, submit, dan nilai tugas secara digital
- 📊 **Real-time Analytics** — Dashboard performa siswa, kelas, dan sekolah
- 📋 **Attendance System** — Absensi digital dengan rekap otomatis
- 🔔 **Multi-channel Notifications** — Email, WhatsApp, in-app
- 📁 **Export Reports** — Excel & PDF untuk raport dan rekap nilai
- 🔐 **Role-Based Access** — 4 role: Super Admin, Guru, Ketua Kelas, Siswa

---

## 📂 Folder Dokumentasi

```
PSSM/
├── README.md                          ← Anda di sini
│
├── docs/
│   ├── 01-product/                    # Kebutuhan Produk
│   │   └── PSSM-PRD.md               # Product Requirements Document
│   │
│   ├── 02-architecture/               # Arsitektur & Data Model
│   │   ├── PSSM-DesignDoc.md          # Technical architecture & workflows
│   │   ├── PSSM-TechStack.md          # Technology stack & setup guide
│   │   └── PSSM-DatabaseDictionary.md # Data dictionary (17 tabel)
│   │
│   ├── 03-development/                # Development & API
│   │   ├── PSSM-MasterWorkflow.md     # Master workflow (7 fase)
│   │   ├── PSSM-APIReference.md       # REST API specification
│   │   ├── PSSM-TestingStrategy.md    # Testing plan & CI/CD
│   │   └── PSSM-ProjectStructure.md   # Laravel folder structure
│   │
│   ├── 04-security/                   # Security & Operations
│   │   ├── PSSM-SecurityHardening.md  # 27 security fixes & patches
│   │   └── PSSM-DisasterRecovery.md   # Backup & incident response
│   │
│   └── 05-guides/                     # Panduan Pengguna
│       └── PSSM-UserGuide.md          # Guide untuk guru, siswa, admin
│
└── (Laravel project files akan di sini setelah development dimulai)
```

---

## 📖 Urutan Baca Dokumentasi

| Urutan | Dokumen | Untuk Siapa |
|:------:|---------|-------------|
| 1 | [PRD](docs/01-product/PSSM-PRD.md) | Semua stakeholder |
| 2 | [DesignDoc](docs/02-architecture/PSSM-DesignDoc.md) | Developer |
| 3 | [TechStack](docs/02-architecture/PSSM-TechStack.md) | Developer |
| 4 | [DatabaseDictionary](docs/02-architecture/PSSM-DatabaseDictionary.md) | Developer |
| 5 | [MasterWorkflow](docs/03-development/PSSM-MasterWorkflow.md) | Developer (⭐ mulai coding dari sini) |
| 6 | [APIReference](docs/03-development/PSSM-APIReference.md) | Frontend & Backend Dev |
| 7 | [ProjectStructure](docs/03-development/PSSM-ProjectStructure.md) | Developer |
| 8 | [SecurityHardening](docs/04-security/PSSM-SecurityHardening.md) | Developer & DevOps |
| 9 | [TestingStrategy](docs/03-development/PSSM-TestingStrategy.md) | Developer & QA |
| 10 | [DisasterRecovery](docs/04-security/PSSM-DisasterRecovery.md) | DevOps & Admin |
| 11 | [UserGuide](docs/05-guides/PSSM-UserGuide.md) | Guru, Siswa, Admin Sekolah |

---

## 🚀 Quick Start

```bash
# 1. Clone & install
git clone https://github.com/your-repo/pssm.git && cd pssm
composer install && npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
createdb pssm_db
php artisan migrate --seed

# 4. Run
npm run dev &
php artisan serve
```

> 📋 Detail lengkap → [PSSM-MasterWorkflow.md](docs/03-development/PSSM-MasterWorkflow.md)

---

## 📊 Tech Stack Overview

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11, PHP 8.3+ |
| Frontend | Blade, Tailwind CSS v4, Alpine.js |
| Database | PostgreSQL 16 |
| Cache/Queue | Redis 7, Laravel Horizon |
| AI | OpenRouter (Claude/GPT) |
| Charts | Chart.js |
| Email | Mailtrap / SendGrid |
| WhatsApp | Fonnte API |
| Storage | Cloudflare R2 |
| Monitoring | Sentry, Laravel Telescope/Pulse |
| CI/CD | GitHub Actions |

---

## 📄 License & Ownership

**MIT License** — © 2026 PSSM Team / Developer (Pembuat).

**Deklarasi Kepemilikan:**
Seluruh hak kepemilikan (ownership) dan hak kekayaan intelektual atas source code, desain database, arsitektur, dan seluruh dokumen terkait project PSSM ini adalah mutlak dan sepenuhnya milik Pembuat / Developer asli. 

Penggunaan, modifikasi, dan distribusi perangkat lunak ini tunduk pada syarat dan ketentuan dari [MIT License](LICENSE).

---

**Total Dokumen:** 11 files · **Total Size:** ~200 KB  
**Status:** ✅ Ready for Development
