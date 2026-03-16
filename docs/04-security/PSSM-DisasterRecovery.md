# PSSM - Disaster Recovery & Incident Response
## Business Continuity Plan v1.0

**RTO (Recovery Time Objective):** < 2 jam  
**RPO (Recovery Point Objective):** < 24 jam (daily backup)  
**Uptime Target:** 99% (≤ 7 jam downtime/bulan)

---

## 1. Backup Strategy

### 1.1 Automated Daily Backup

```bash
# Jadwal: Setiap hari 02:00 WIB

# Database backup
pg_dump -h $DB_HOST -U $DB_USER -d pssm_db \
    --format=custom \
    --compress=9 \
    --file="pssm_backup_$(date +%Y%m%d_%H%M%S).dump"

# Upload ke cloud storage
rclone copy pssm_backup_*.dump r2:pssm-backups/daily/
```

### 1.2 Laravel Backup Package

```bash
composer require spatie/laravel-backup
```

```php
// config/backup.php
return [
    'backup' => [
        'name' => 'pssm',
        'source' => [
            'databases' => ['pgsql'],
            'files' => [
                'include' => [storage_path('app/private')], // File submissions
                'exclude' => [storage_path('app/public')],
            ],
        ],
        'destination' => [
            'disks' => ['r2'], // Cloudflare R2
        ],
    ],
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days'       => 7,
            'keep_daily_backups_for_days'     => 30,
            'keep_weekly_backups_for_weeks'   => 8,
            'keep_monthly_backups_for_months' => 6,
        ],
    ],
];
```

### 1.3 Backup Schedule

| Tipe | Frekuensi | Retention | Lokasi |
|------|-----------|-----------|--------|
| Database full | Harian 02:00 | 30 hari | Cloudflare R2 |
| File uploads | Harian 02:30 | 30 hari | Cloudflare R2 |
| Weekly snapshot | Minggu 03:00 | 8 minggu | R2 + local |
| Monthly archive | Tanggal 1 | 6 bulan | R2 |

### 1.4 Backup Verification

```php
// app/Console/Commands/VerifyBackup.php — Jalankan weekly
// 1. Download backup terbaru
// 2. Restore ke temporary database
// 3. Verifikasi row count pada tabel kritis
// 4. Log hasil dan alert jika gagal

Schedule::command('backup:run')->dailyAt('02:00');
Schedule::command('backup:clean')->dailyAt('03:00');
Schedule::command('backup:verify')->weeklyOn(0, '04:00');
```

---

## 2. Disaster Scenarios & Recovery Procedures

### Scenario A: Database Corruption / Data Loss

**Severity:** 🔴 Critical | **RTO:** < 1 jam

**Langkah Recovery:**
```bash
# 1. Aktifkan maintenance mode
php artisan down --render="errors::503" --retry=300

# 2. Identifikasi backup terbaru
rclone ls r2:pssm-backups/daily/ --max-depth 1

# 3. Download backup
rclone copy r2:pssm-backups/daily/pssm_backup_YYYYMMDD.dump ./

# 4. Restore database
pg_restore -h $DB_HOST -U $DB_USER -d pssm_db \
    --clean --if-exists \
    pssm_backup_YYYYMMDD.dump

# 5. Verifikasi data
psql -h $DB_HOST -U $DB_USER -d pssm_db \
    -c "SELECT COUNT(*) FROM users; SELECT COUNT(*) FROM assignments;"

# 6. Clear cache
php artisan cache:clear
php artisan config:cache

# 7. Bring back online
php artisan up
```

---

### Scenario B: Application Server Down

**Severity:** 🔴 Critical | **RTO:** < 30 menit

**Langkah Recovery:**
```bash
# 1. Cek health endpoint
curl -s https://pssm.school/api/health

# 2. Cek container status (jika Docker)
docker ps -a | grep pssm
docker logs pssm-app --tail 50

# 3. Restart containers
docker-compose down
docker-compose up -d

# 4. Jika masih gagal — redeploy
git pull origin main
docker-compose build --no-cache
docker-compose up -d

# 5. Verifikasi
curl -s https://pssm.school/api/health
```

---

### Scenario C: Redis Down (Cache/Session Lost)

**Severity:** 🟡 Medium | **RTO:** < 15 menit

**Impact:** User session hilang (semua user logout), cache kosong.

**Langkah Recovery:**
```bash
# 1. Restart Redis
docker restart pssm-redis
# atau
redis-cli -a $REDIS_PASSWORD ping

# 2. Jika Redis corrupt — flush dan rebuild
redis-cli -a $REDIS_PASSWORD FLUSHALL

# 3. Rebuild cache
php artisan cache:clear
php artisan config:cache
php artisan dashboard:refresh

# 4. User perlu login ulang (session hilang)
```

---

### Scenario D: Security Breach / Data Breach

**Severity:** 🔴 Critical | **Wajib lapor UU PDP: 3×24 jam**

**Langkah Immediate (Jam 0-1):**
```
1. ISOLASI: Blokir IP penyerang via Cloudflare
2. MAINTENANCE: php artisan down
3. PRESERVE: Jangan hapus logs — ini bukti forensik
4. ASSESS: Identifikasi data apa yang terekspos
```

**Langkah Short-term (Jam 1-24):**
```
1. Reset semua API keys (OpenRouter, Fonnte, Sentry)
2. Force logout semua user: php artisan cache:flush
3. Reset semua user password (force reset on next login)
4. Review activity_logs untuk trace aktivitas penyerang
5. Patch vulnerability yang dieksploitasi
```

**Langkah Compliance (Hari 1-3):**
```
1. Dokumentasikan insiden:
   - Waktu terdeteksi
   - Data yang terdampak
   - Jumlah user terdampak
   - Root cause
   
2. Notifikasi pihak terkait:
   - Kepala sekolah / manajemen
   - User yang datanya terdampak (email)
   - Otoritas perlindungan data (jika diperlukan UU PDP)

3. Post-mortem report
```

---

### Scenario E: AI API Down / Rate Limited

**Severity:** 🟢 Low | **Tidak blocking core features**

**Langkah:**
```
1. Fitur AI otomatis menampilkan pesan:
   "AI sedang tidak tersedia. Silakan buat kuis secara manual."
   
2. Cek status OpenRouter: https://status.openrouter.ai

3. Jika rate limited:
   - Cache existing results lebih lama
   - Switch ke model yang lebih murah (fallback)
   
4. Guru tetap bisa buat kuis manual
```

---

## 3. Monitoring & Alert Setup

### 3.1 Health Check Monitoring

```yaml
# Uptime monitoring (via UptimeRobot — free)
Checks:
  - URL: https://pssm.school/api/health
    Interval: 5 menit
    Alert: Email + WhatsApp ke admin

  - URL: https://pssm.school
    Interval: 5 menit
    Alert: Email ke tech lead
```

### 3.2 Alert Escalation Matrix

| Level | Kondisi | Responder | Response Time |
|-------|---------|-----------|---------------|
| 🟢 Info | Disk > 70%, queue slow | Tech Lead | < 24 jam |
| 🟡 Warning | API errors > 5%, Redis timeout | Tech Lead | < 2 jam |
| 🔴 Critical | Health check DOWN, DB error | Tech Lead + Kepsek | < 30 menit |
| 🚨 Emergency | Data breach, total outage | Semua tim + Kepsek | Segera |

### 3.3 Contact List

```yaml
Tech Lead:
  Name: [Nama]
  Phone: [Nomor]
  Email: [Email]

Backup:
  Name: [Nama]
  Phone: [Nomor]

Kepala Sekolah:
  Name: [Nama]
  Phone: [Nomor]
```

---

## 4. Infrastructure Redundancy

### 4.1 Current (MVP — Single Server)

```
[Cloudflare CDN] → [Railway/VPS] → [Supabase DB]
                                  → [Upstash Redis]
                                  → [Cloudflare R2]
```

**Single points of failure:** Server aplikasi

### 4.2 Target (Scale — Multi-server)

```
[Cloudflare CDN/WAF]
    ├→ [App Server 1] ──→ [Supabase DB Primary]
    └→ [App Server 2] ──→ [Supabase DB Replica]
                     ──→ [Upstash Redis Cluster]
                     ──→ [Cloudflare R2]
```

---

## 5. Rollback Procedures

### Application Rollback

```bash
# 1. Maintenance mode
php artisan down

# 2. Revert ke commit sebelumnya
git log --oneline -5  # Cari commit terakhir yang stabil
git revert HEAD
# atau
git reset --hard <commit-hash>

# 3. Rebuild
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 4. Rollback migration jika diperlukan
php artisan migrate:rollback --step=1

# 5. Clear cache & restart
php artisan config:cache
php artisan route:cache
php artisan queue:restart
php artisan up
```

### Database Rollback

```bash
# Rollback migration terakhir
php artisan migrate:rollback --step=1

# Rollback semua migration dari batch tertentu
php artisan migrate:rollback --batch=5

# JANGAN PERNAH: migrate:fresh di production!
```

---

## 6. Post-Incident Review Template

```markdown
## Incident Report — [Tanggal]

### Timeline
- **Terdeteksi:** [Waktu]
- **Acknowledged:** [Waktu]
- **Mitigated:** [Waktu]
- **Resolved:** [Waktu]
- **Total downtime:** [Durasi]

### Impact
- Users terdampak: [Jumlah]
- Data loss: [Ya/Tidak]
- Revenue impact: [Estimasi]

### Root Cause
[Deskripsi]

### Resolution
[Langkah yang diambil]

### Action Items
- [ ] [Improvement 1]
- [ ] [Improvement 2]

### Lessons Learned
[Apa yang bisa dicegah di masa depan]
```

---

**Document Version:** 1.0 | **Last Updated:** 2026-03-16 | **Review Schedule:** Bulanan
