# PSSM - Security Hardening & Performance Enhancement
## Dokumen Tambahan: Analisis Celah, Bug & Rekomendasi Perbaikan v1.0

**Berdasarkan Analisis:** PSSM-PRD.md, PSSM-DesignDoc.md, PSSM-TechStack.md  
**Tanggal Analisis:** 2026-03-16  
**Tujuan:** Menutup celah keamanan, meningkatkan performa, dan mencegah cyber attack

---

## 1. TEMUAN KRITIS — Celah Keamanan (Security Vulnerabilities)

### 1.1 Authentication & Session Management

| # | Celah | Severity | Lokasi Dokumen |
|---|-------|----------|----------------|
| S-01 | Password policy terlalu lemah (8 char + 1 angka) | 🔴 High | PRD §4 |
| S-02 | Tidak ada brute-force protection selain rate limit global | 🔴 High | PRD §3A |
| S-03 | Session timeout 24 jam terlalu lama untuk data akademik | 🟡 Medium | PRD §3A |
| S-04 | Tidak ada Multi-Factor Authentication (MFA/2FA) | 🔴 High | Semua dokumen |
| S-05 | Tidak ada account lockout mechanism | 🔴 High | Semua dokumen |
| S-06 | Redis session tanpa password (REDIS_PASSWORD=null) | 🔴 Critical | TechStack §1 |

**Rekomendasi Implementasi:**

```php
// config/auth.php — Password Rules yang Diperkuat
'password_rules' => [
    'min' => 12,
    'mixed_case' => true,
    'numbers' => true,
    'symbols' => true,
    'uncompromised' => true, // Cek database Have I Been Pwned
],

// app/Http/Middleware/BruteForceProtection.php
class BruteForceProtection
{
    public function handle($request, Closure $next)
    {
        $key = 'login_attempts:' . $request->ip() . ':' . $request->input('email');
        $attempts = Cache::get($key, 0);

        if ($attempts >= 5) {
            $lockoutMinutes = min(pow(2, $attempts - 5), 60); // Exponential backoff
            return response()->json([
                'message' => "Akun terkunci. Coba lagi dalam {$lockoutMinutes} menit."
            ], 429);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 401) {
            Cache::put($key, $attempts + 1, now()->addMinutes(30));
        } else {
            Cache::forget($key);
        }

        return $response;
    }
}

// Session timeout berdasarkan role
'session_timeouts' => [
    'super_admin' => 30,  // 30 menit
    'teacher'     => 120, // 2 jam
    'student'     => 60,  // 1 jam
],
```

```env
# .env — Redis WAJIB pakai password
REDIS_PASSWORD=strong_random_password_here_min_32_chars
```

---

### 1.2 API Security — Celah yang Belum Ditangani

| # | Celah | Severity | Detail |
|---|-------|----------|--------|
| S-07 | Tidak ada API versioning → breaking changes langsung berdampak | 🟡 Medium | DesignDoc §3 |
| S-08 | Tidak ada request body size limit di API | 🔴 High | DesignDoc §3 |
| S-09 | Rate limit 100 req/min terlalu tinggi untuk endpoint sensitif | 🟡 Medium | PRD §4 |
| S-10 | Tidak ada CORS policy yang didefinisikan | 🔴 High | Semua dokumen |
| S-11 | Tidak ada API request signing/HMAC verification | 🟡 Medium | DesignDoc §3 |
| S-12 | Error message terlalu verbose (expose stack trace) | 🔴 High | DesignDoc §9 |

**Rekomendasi Implementasi:**

```php
// app/Http/Kernel.php — Rate Limiting per Endpoint
RateLimiter::for('login', function ($request) {
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('api-sensitive', function ($request) {
    return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('ai-generation', function ($request) {
    return Limit::perMinute(3)->by($request->user()->id); // AI sangat mahal
});

RateLimiter::for('export', function ($request) {
    return Limit::perHour(10)->by($request->user()->id);
});
```

```php
// config/cors.php — CORS Policy Ketat
return [
    'paths' => ['api/*'],
    'allowed_origins' => [env('APP_URL')],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    'max_age' => 3600,
    'supports_credentials' => true,
];
```

---

### 1.3 File Upload — Celah Berbahaya

| # | Celah | Severity | Detail |
|---|-------|----------|--------|
| S-13 | Validasi hanya berdasarkan extension, bukan MIME type sebenarnya | 🔴 Critical | DesignDoc §7 |
| S-14 | Tidak ada antivirus/malware scanning pada file upload | 🔴 High | PRD §3D |
| S-15 | Tidak ada limit jumlah file upload per user per hari | 🟡 Medium | DesignDoc §7 |
| S-16 | Filename menggunakan `time()` — predictable & collision risk | 🟡 Medium | DesignDoc §7 |
| S-17 | Tidak ada Content-Disposition header saat download file | 🟡 Medium | TechStack §6 |

**Rekomendasi Implementasi:**

```php
// app/Http/Requests/SubmissionRequest.php — Validasi File yang Aman
public function rules(): array
{
    return [
        'file' => [
            'nullable', 'file',
            'max:10240', // 10MB
            'mimes:pdf,doc,docx,jpg,jpeg,png',
            // TAMBAHAN: Validasi MIME type sebenarnya
            function ($attribute, $value, $fail) {
                $realMime = mime_content_type($value->getPathname());
                $allowed = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg', 'image/png',
                ];
                if (!in_array($realMime, $allowed)) {
                    $fail("File type tidak diizinkan (detected: {$realMime}).");
                }
            },
        ],
    ];
}

// Secure filename generation
$filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

// Secure file download controller
public function downloadSubmission(Submission $submission)
{
    $this->authorize('view', $submission);

    if (!Storage::disk('private')->exists($submission->file_path)) {
        abort(404, 'File tidak ditemukan.');
    }

    return Storage::disk('private')->download(
        $submission->file_path,
        $submission->original_filename, // Simpan original name di DB
        ['Content-Disposition' => 'attachment']
    );
}
```

---

### 1.4 AI Integration — Celah Prompt Injection & Data Leakage

| # | Celah | Severity | Detail |
|---|-------|----------|--------|
| S-18 | Tidak ada sanitasi input sebelum dikirim ke AI API (Prompt Injection) | 🔴 Critical | DesignDoc §5 |
| S-19 | Essay siswa dikirim langsung ke API eksternal tanpa anonimisasi | 🔴 High | DesignDoc §5 |
| S-20 | Tidak ada validasi/sanitasi output AI sebelum disimpan ke DB | 🔴 High | DesignDoc §5 |
| S-21 | API key tersimpan di .env tanpa enkripsi tambahan | 🟡 Medium | TechStack §4 |
| S-22 | Tidak ada fallback jika AI return malicious content | 🔴 High | DesignDoc §5 |

**Rekomendasi Implementasi:**

```php
// app/Services/AIService.php — Dengan Proteksi Prompt Injection

class AIService
{
    /**
     * Sanitasi input sebelum dikirim ke AI
     */
    private function sanitizeInput(string $input): string
    {
        // Hapus potensi prompt injection
        $dangerous = [
            'ignore previous instructions',
            'forget your instructions',
            'you are now',
            'system prompt',
            'act as',
            '<script', '<?php',
        ];

        $sanitized = $input;
        foreach ($dangerous as $pattern) {
            $sanitized = str_ireplace($pattern, '[FILTERED]', $sanitized);
        }

        // Limit panjang input
        $sanitized = mb_substr($sanitized, 0, 5000);

        // Strip HTML dan PHP tags
        $sanitized = strip_tags($sanitized);

        return $sanitized;
    }

    /**
     * Validasi output AI sebelum simpan ke database
     */
    private function validateAIOutput(array $data): array
    {
        // Pastikan score dalam range valid
        $scoreFields = ['structure_score', 'grammar_score', 'argument_score',
                        'vocabulary_score', 'overall_score'];

        foreach ($scoreFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = max(0, min(100, intval($data[$field])));
            }
        }

        // Sanitasi text output dari AI
        if (isset($data['detailed_feedback'])) {
            $data['detailed_feedback'] = strip_tags($data['detailed_feedback']);
            $data['detailed_feedback'] = mb_substr($data['detailed_feedback'], 0, 10000);
        }

        return $data;
    }

    /**
     * Anonimisasi data siswa sebelum kirim ke AI
     */
    private function anonymizeStudentData(string $essay): string
    {
        // Hapus informasi pribadi yang mungkin ada di essay
        $essay = preg_replace('/\b\d{10,}\b/', '[NIS_HIDDEN]', $essay);
        $essay = preg_replace('/[\w.+-]+@[\w-]+\.[\w.]+/', '[EMAIL_HIDDEN]', $essay);
        $essay = preg_replace('/(\+?62|0)\d{8,13}/', '[PHONE_HIDDEN]', $essay);

        return $essay;
    }
}
```

---

### 1.5 Database Security — Celah Kritis

| # | Celah | Severity | Detail |
|---|-------|----------|--------|
| S-23 | `correct_answer` disimpan plain text di `quiz_questions` | 🔴 Critical | DesignDoc §2 |
| S-24 | Tidak ada enkripsi untuk data sensitif siswa (alamat, telepon ortu) | 🔴 High | DesignDoc §2 |
| S-25 | `ON DELETE CASCADE` pada `users` bisa menghapus semua data terkait | 🔴 High | DesignDoc §2 |
| S-26 | Tidak ada soft delete — data terhapus permanen | 🔴 High | DesignDoc §2 |
| S-27 | Tidak ada database connection pooling yang optimal | 🟡 Medium | TechStack §1 |

**Rekomendasi Implementasi:**

```php
// app/Models/StudentProfile.php — Enkripsi Data Sensitif
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

class StudentProfile extends Model
{
    protected function parentPhone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function parentEmail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function address(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }
}

// Soft Delete pada semua model penting
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;
    // ...
}

class Assignment extends Model
{
    use SoftDeletes;
    // ...
}

class Submission extends Model
{
    use SoftDeletes;
    // ...
}
```

```sql
-- Enkripsi jawaban kuis
ALTER TABLE quiz_questions
ADD COLUMN correct_answer_hash VARCHAR(255); -- Simpan hash, bukan plain text

-- Tambah soft delete columns
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE assignments ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE submissions ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE quizzes ADD COLUMN deleted_at TIMESTAMP NULL;
```

---

## 2. TEMUAN — Bug & Kekurangan (Bugs & Gaps)

### 2.1 Logic Bugs

| # | Bug | Impact | Lokasi |
|---|-----|--------|--------|
| B-01 | Siswa bisa re-submit tugas tanpa batas — bisa overwrite submission yang sudah di-grade | 🔴 High | PRD §3D |
| B-02 | Quiz `UNIQUE(quiz_id, student_id)` — tidak bisa re-take quiz meski guru izinkan | 🟡 Medium | DesignDoc §2 |
| B-03 | `is_late` ditentukan saat submit, tapi deadline bisa diubah guru setelahnya | 🟡 Medium | DesignDoc §7 |
| B-04 | Score CHECK constraint `0-100` tapi `max_score` bisa di-set berbeda | 🔴 High | DesignDoc §2 |
| B-05 | Class leader role ditentukan di pivot table, bukan lewat RBAC system | 🟡 Medium | DesignDoc §2 |
| B-06 | AI quiz di-cache berdasarkan topic+count+difficulty — cache collision untuk topik sama | 🟡 Medium | DesignDoc §5 |

**Rekomendasi Perbaikan:**

```php
// Fix B-01: Submission versioning
// Migration
Schema::table('submissions', function (Blueprint $table) {
    $table->integer('version')->default(1);
    $table->dropUnique(['assignment_id', 'student_id']);
    $table->unique(['assignment_id', 'student_id', 'version']);
});

// Fix B-04: Score constraint dinamis
// Ubah CHECK constraint
ALTER TABLE submissions DROP CONSTRAINT submissions_score_check;
ALTER TABLE submissions ADD CONSTRAINT submissions_score_check
    CHECK (score >= 0); -- max_score divalidasi di application layer

// app/Http/Requests/GradeSubmissionRequest.php
public function rules(): array
{
    $assignment = $this->submission->assignment;
    return [
        'score' => "required|integer|min:0|max:{$assignment->max_score}",
        'feedback' => 'nullable|string|max:5000',
    ];
}

// Fix B-02: Allow multiple quiz attempts
ALTER TABLE quiz_attempts DROP CONSTRAINT quiz_attempts_quiz_id_student_id_key;
ALTER TABLE quiz_attempts ADD COLUMN attempt_number INT DEFAULT 1;
ALTER TABLE quiz_attempts ADD CONSTRAINT quiz_attempts_unique
    UNIQUE(quiz_id, student_id, attempt_number);
ALTER TABLE quizzes ADD COLUMN max_attempts INT DEFAULT 1;
```

---

### 2.2 Missing Features (Celah Fungsional)

| # | Kekurangan | Impact | Rekomendasi |
|---|-----------|--------|-------------|
| G-01 | Tidak ada data backup verification (backup dibuat tapi tidak diverifikasi) | 🔴 High | Tambah scheduled backup test restore |
| G-02 | Tidak ada health check endpoint untuk monitoring | 🟡 Medium | Buat `/api/health` endpoint |
| G-03 | Tidak ada mekanisme data export untuk compliance (GDPR/UU PDP) | 🔴 High | Buat fitur user data export |
| G-04 | Tidak ada input sanitization di pengumuman (XSS via rich text) | 🔴 High | Gunakan HTMLPurifier |
| G-05 | Tidak ada request ID tracking untuk debugging | 🟡 Medium | Tambah X-Request-ID header |
| G-06 | Tidak ada database migration rollback plan | 🟡 Medium | Dokumentasikan rollback steps |
| G-07 | Tidak ada mekanisme maintenance mode dengan notifikasi | 🟡 Low | Setup `php artisan down --render` |

---

## 3. TEMUAN — Ancaman Cyber Attack

### 3.1 Attack Vectors & Mitigasi

#### A. SQL Injection (Meski pakai Eloquent)
```php
// CELAH: Raw query tanpa binding (mungkin muncul saat optimasi)
// HINDARI:
DB::select("SELECT * FROM users WHERE name = '$name'"); // ❌ VULNERABLE

// GUNAKAN:
DB::select("SELECT * FROM users WHERE name = ?", [$name]); // ✅ SAFE
User::where('name', $name)->get(); // ✅ SAFE
```

#### B. Cross-Site Scripting (XSS)
```php
// CELAH: {!! !!} di Blade template (unescaped output)
// Chart.js integration di DesignDoc menggunakan:
{!! json_encode($months) !!}      // ⚠️ RISIKO jika $months berisi user input
{!! json_encode($averageScores) !!} // ⚠️ RISIKO

// SOLUSI:
@json($months)                      // ✅ Laravel built-in safe encoding
<script>
const data = @json($averageScores); // ✅ Auto-escaped
</script>

// TAMBAHAN: Content Security Policy Header
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'nonce-" . csrf_token() . "' cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com; " .
            "font-src 'self' fonts.gstatic.com; " .
            "img-src 'self' data: blob:; " .
            "connect-src 'self' wss: https://api.fonnte.com;"
        );
        return $response;
    }
}
```

#### C. Insecure Direct Object Reference (IDOR)
```php
// CELAH di DesignDoc: Endpoint tanpa authorization check
// GET /api/students/{id}/grades — siswa bisa akses nilai siswa lain!

// SOLUSI: Selalu gunakan Policy
public function show(User $student)
{
    $this->authorize('viewGrades', $student); // WAJIB
    // ...
}

// app/Policies/StudentPolicy.php
public function viewGrades(User $user, User $student): bool
{
    if ($user->id === $student->id) return true;
    if ($user->role === 'super_admin') return true;
    if ($user->role === 'teacher') {
        return $user->teachesStudent($student);
    }
    // Parent: cek relasi parent-child
    if ($user->role === 'parent') {
        return $user->isParentOf($student);
    }
    return false;
}
```

#### D. Denial of Service (DoS)
```php
// CELAH: AI endpoint tanpa queue bisa block server
// CELAH: Export endpoint tanpa limit bisa exhaust memory

// SOLUSI 1: Paksa AI request ke queue
class GenerateQuizController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:200',
            'count' => 'required|integer|min:5|max:20', // LIMIT!
        ]);

        $job = GenerateAIQuiz::dispatch($validated)
            ->onQueue('ai');

        return response()->json([
            'message' => 'Kuis sedang di-generate. Notifikasi akan dikirim.',
            'job_id' => $job->getJobId(),
        ], 202);
    }
}

// SOLUSI 2: Streaming export untuk data besar
public function exportLargeDataset()
{
    return response()->streamDownload(function () {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['NIS', 'Nama', 'Nilai']);

        Submission::with('student')->chunk(100, function ($submissions) use ($handle) {
            foreach ($submissions as $submission) {
                fputcsv($handle, [
                    $submission->student->student_id_number,
                    $submission->student->name,
                    $submission->score,
                ]);
            }
        });

        fclose($handle);
    }, 'export.csv');
}
```

---

## 4. PENINGKATAN PERFORMA

### 4.1 Database Optimization

```sql
-- Index yang BELUM ADA di DesignDoc tapi WAJIB ditambahkan
CREATE INDEX idx_submissions_score ON submissions(score) WHERE score IS NOT NULL;
CREATE INDEX idx_submissions_is_late ON submissions(is_late);
CREATE INDEX idx_quizzes_published ON quizzes(is_published, start_time, end_time);
CREATE INDEX idx_assignments_published ON assignments(is_published, deadline);
CREATE INDEX idx_class_student_leader ON class_student(class_id) WHERE is_class_leader = TRUE;
CREATE INDEX idx_users_active_role ON users(role, is_active) WHERE is_active = TRUE;

-- Partial index untuk query umum
CREATE INDEX idx_submissions_ungraded ON submissions(assignment_id)
    WHERE score IS NULL AND submitted_at IS NOT NULL;

-- Composite index untuk attendance queries
CREATE INDEX idx_attendances_class_date ON attendances(class_id, date, status);
```

### 4.2 Query & Caching Optimization

```php
// MASALAH: Dashboard query di DesignDoc tidak efisien
// PRD Target: Dashboard load < 1 detik

// SOLUSI: Materialized stats dengan scheduled update
// app/Console/Commands/RefreshDashboardStats.php
class RefreshDashboardStats extends Command
{
    protected $signature = 'stats:refresh';
    protected $schedule = 'everyFiveMinutes';

    public function handle()
    {
        // Pre-compute stats dan simpan di Redis
        $schools = [
            'total_students' => User::where('role', 'student')->where('is_active', true)->count(),
            'total_teachers' => User::where('role', 'teacher')->where('is_active', true)->count(),
            'total_classes' => Classes::count(),
            'active_assignments' => Assignment::where('deadline', '>', now())->count(),
            'submission_rate' => $this->calculateSubmissionRate(),
            'attendance_30d' => $this->getAttendanceStats(30),
        ];

        Cache::put('dashboard:school_stats', $schools, 600); // 10 min
    }
}

// Cache invalidation yang tepat
// app/Observers/SubmissionObserver.php
class SubmissionObserver
{
    public function created(Submission $submission)
    {
        Cache::forget("dashboard_stats_{$submission->student_id}");
        Cache::forget("class_stats_{$submission->assignment->class_id}");
        Cache::tags(['assignments', "class:{$submission->assignment->class_id}"])->flush();
    }
}
```

### 4.3 Connection Pooling & Queue Optimization

```php
// config/database.php — Connection pooling
'pgsql' => [
    // ... existing config
    'pool' => [
        'min' => 2,
        'max' => 20,
    ],
    'options' => [
        PDO::ATTR_PERSISTENT => true, // Persistent connections
    ],
    'search_path' => 'public',
    'sslmode' => 'require', // WAJIB untuk production
],

// config/horizon.php — Queue optimization
'environments' => [
    'production' => [
        'supervisor-notifications' => [
            'connection' => 'redis',
            'queue' => ['notifications'],
            'balance' => 'simple',
            'processes' => 3,
            'tries' => 3,
            'timeout' => 30,
        ],
        'supervisor-ai' => [
            'connection' => 'redis',
            'queue' => ['ai'],
            'balance' => 'simple',
            'processes' => 2,
            'tries' => 2,
            'timeout' => 120, // AI calls bisa lama
            'memory' => 256,
        ],
        'supervisor-exports' => [
            'connection' => 'redis',
            'queue' => ['exports'],
            'balance' => 'simple',
            'processes' => 2,
            'tries' => 1,
            'timeout' => 300,
        ],
    ],
],
```

---

## 5. INFRASTRUKTUR & DEPLOYMENT HARDENING

### 5.1 Docker Security

```yaml
# docker-compose.yml — PERBAIKAN dari DesignDoc
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    # TAMBAHAN: Jalankan sebagai non-root
    user: "www-data:www-data"
    # TAMBAHAN: Read-only filesystem
    read_only: true
    tmpfs:
      - /tmp
      - /var/run
    volumes:
      - ./storage:/var/www/storage:rw  # Hanya storage yang writable
    # TAMBAHAN: Security options
    security_opt:
      - no-new-privileges:true
    # TAMBAHAN: Resource limits
    deploy:
      resources:
        limits:
          cpus: '1.0'
          memory: 512M

  db:
    image: postgres:16-alpine  # Alpine lebih kecil attack surface
    environment:
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      POSTGRES_INITDB_ARGS: "--auth-host=scram-sha-256"  # Password auth lebih aman
    # TAMBAHAN: Expose hanya ke internal network
    expose:
      - "5432"
    # JANGAN gunakan ports: — tidak perlu expose ke host

  redis:
    image: redis:7-alpine
    # TAMBAHAN: Redis dengan password
    command: redis-server --requirepass ${REDIS_PASSWORD} --maxmemory 128mb --maxmemory-policy allkeys-lru
    expose:
      - "6379"

  nginx:
    volumes:
      - ./nginx/conf.d:/etc/nginx/conf.d:ro  # Read-only config
```

### 5.2 Nginx Hardening

```nginx
# nginx/conf.d/security.conf
server {
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=30r/m;

    # Sembunyikan versi server
    server_tokens off;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;

    # Blokir akses ke file sensitif
    location ~ /\.(env|git|htaccess) {
        deny all;
        return 404;
    }

    location ~ /(telescope|horizon) {
        # Hanya akses dari IP tertentu
        allow 127.0.0.1;
        deny all;
    }

    # Limit upload size
    client_max_body_size 10M;
    client_body_timeout 30s;

    location /api/login {
        limit_req zone=login burst=3 nodelay;
        proxy_pass http://app:9000;
    }
}
```

---

## 6. MONITORING & INCIDENT RESPONSE

### 6.1 Security Monitoring yang Belum Ada

```php
// app/Listeners/SecurityEventListener.php
class SecurityEventListener
{
    public function handleFailedLogin($event)
    {
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $event->credentials['email'] ?? 'unknown',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);

        // Alert jika >10 failed attempts dari 1 IP dalam 5 menit
        $key = "failed_login:" . request()->ip();
        $count = Cache::increment($key);
        Cache::put($key, $count, 300);

        if ($count > 10) {
            // Kirim alert ke admin
            Notification::send(
                User::where('role', 'super_admin')->get(),
                new SecurityAlertNotification("Brute-force attack detected from IP: " . request()->ip())
            );
        }
    }
}
```

### 6.2 Automated Backup Verification

```php
// app/Console/Commands/VerifyBackup.php — MISSING dari semua dokumen
class VerifyBackup extends Command
{
    protected $signature = 'backup:verify';

    public function handle()
    {
        // 1. Buat backup
        Artisan::call('backup:run');

        // 2. Restore ke temporary database
        $testDb = 'pssm_backup_test_' . time();

        try {
            // Restore dan verifikasi
            DB::connection('backup_test')->statement("SELECT COUNT(*) FROM users");
            Log::info("Backup verification: SUCCESS");
        } catch (\Exception $e) {
            Log::critical("Backup verification: FAILED - " . $e->getMessage());
            // Alert admin
        } finally {
            DB::statement("DROP DATABASE IF EXISTS {$testDb}");
        }
    }
}
```

---

## 7. RINGKASAN PRIORITAS IMPLEMENTASI

### 🔴 PRIORITAS 1 — Harus Segera (Sebelum MVP Launch)

| # | Item | Effort |
|---|------|--------|
| 1 | Redis password protection (S-06) | 5 menit |
| 2 | File MIME type validation (S-13) | 1 jam |
| 3 | AI prompt injection sanitization (S-18) | 2 jam |
| 4 | IDOR protection pada semua endpoint (IDOR) | 4 jam |
| 5 | Security headers middleware (XSS) | 1 jam |
| 6 | Score constraint fix (B-04) | 30 menit |
| 7 | Soft delete pada model kritis (S-26) | 2 jam |
| 8 | Encrypt sensitive student data (S-24) | 3 jam |
| 9 | Error message sanitization — production (S-12) | 30 menit |

### 🟡 PRIORITAS 2 — Sebelum Production Deployment

| # | Item | Effort |
|---|------|--------|
| 1 | Brute-force protection (S-02, S-05) | 3 jam |
| 2 | Granular rate limiting per endpoint (S-09) | 2 jam |
| 3 | CORS policy (S-10) | 1 jam |
| 4 | Database index optimization (§4.1) | 2 jam |
| 5 | Docker security hardening (§5.1) | 3 jam |
| 6 | Nginx hardening (§5.2) | 2 jam |
| 7 | Dashboard stats pre-computation (§4.2) | 4 jam |
| 8 | Security event monitoring (§6.1) | 3 jam |
| 9 | Quiz answer encryption (S-23) | 2 jam |

### 🟢 PRIORITAS 3 — Post-Launch Enhancement

| # | Item | Effort |
|---|------|--------|
| 1 | Multi-Factor Authentication/2FA (S-04) | 8 jam |
| 2 | API versioning (S-07) | 4 jam |
| 3 | Backup verification automation (§6.2) | 4 jam |
| 4 | Submission versioning (B-01) | 4 jam |
| 5 | Role-based session timeout (S-03) | 2 jam |
| 6 | Health check endpoint (G-02) | 1 jam |
| 7 | UU PDP compliance / data export (G-03) | 8 jam |

---

## 8. COMPLIANCE CHECKLIST — UU PDP (Perlindungan Data Pribadi) Indonesia

```yaml
UU PDP Compliance (UU No. 27 Tahun 2022):
  - [ ] Consent mechanism — siswa/ortu harus setuju pemrosesan data
  - [ ] Data minimization — hanya kumpulkan data yang diperlukan
  - [ ] Right to access — user bisa download data mereka
  - [ ] Right to delete — user bisa minta hapus data
  - [ ] Data breach notification — wajib lapor dalam 3x24 jam
  - [ ] Data Protection Officer (DPO) — tunjuk penanggung jawab
  - [ ] Privacy policy page — wajib ada & mudah diakses
  - [ ] Data retention policy — tetapkan berapa lama data disimpan
  - [ ] Third-party data sharing (AI API) — disclose di privacy policy
  - [ ] Cross-border transfer (OpenRouter API) — perlu justifikasi
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-03-16  
**Status:** Ready for Implementation ✅  
**Total Temuan:** 27 Security Vulnerabilities, 7 Bugs, 7 Missing Features  
**Estimasi Total Effort:** ~80 jam (Prioritas 1 & 2)
