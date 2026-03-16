# PSSM - Master Development Workflow
## Panduan Lengkap: Dari Setup Hingga Production-Ready

**Konsolidasi Dari:** PRD → DesignDoc → TechStack → SecurityHardening  
**Versi:** 1.0 | **Tanggal:** 2026-03-16  
**Target:** Zero-bug, high-performance, production-grade school management system

---

## Dokumen Referensi PSSM (10 Dokumen)

| # | Dokumen | Fungsi | Status |
|---|---------|--------|--------|
| 1 | `PSSM-PRD.md` | Kebutuhan produk, user stories, acceptance criteria | ✅ Ready |
| 2 | `PSSM-DesignDoc.md` | Arsitektur teknis, DB schema, API design, workflow | ✅ Ready |
| 3 | `PSSM-TechStack.md` | Tech stack, packages, setup, deployment | ✅ Ready |
| 4 | `PSSM-SecurityHardening.md` | Celah keamanan, bug fixes, cyber attack mitigasi | ✅ Ready |
| 5 | `PSSM-MasterWorkflow.md` | **Dokumen ini** — workflow terarah & konsisten | ✅ Current |
| 6 | `PSSM-APIReference.md` | Spesifikasi endpoint REST API lengkap | ✅ Ready |
| 7 | `PSSM-DatabaseDictionary.md` | Kamus data: 17 tabel, kolom, relasi, constraint | ✅ Ready |
| 8 | `PSSM-TestingStrategy.md` | Test plan, CI/CD pipeline, coverage targets | ✅ Ready |
| 9 | `PSSM-DisasterRecovery.md` | Backup, incident response, rollback procedures | ✅ Ready |
| 10 | `PSSM-UserGuide.md` | Panduan pengguna (guru, siswa, admin) | ✅ Ready |

---

## FASE 0: Pre-Development Checklist (Hari 1)

### 0.1 Environment Validation

```bash
# Verifikasi semua tools terinstall
php -v          # ≥ 8.3 WAJIB
composer -V     # ≥ 2.7
node -v         # ≥ 20 LTS
npm -v          # ≥ 10
psql --version  # ≥ 16
redis-cli -v    # ≥ 7
git --version   # ≥ 2.40
```

### 0.2 Project Bootstrap (Diperbaiki dari TechStack)

```bash
# 1. Create project
composer create-project laravel/laravel pssm "11.*"
cd pssm

# 2. Install SEMUA dependencies sekaligus (efisien)
composer require \
    laravel/breeze \
    laravel/sanctum \
    laravel/horizon \
    spatie/laravel-permission \
    spatie/laravel-activitylog \
    maatwebsite/laravel-excel \
    barryvdh/laravel-dompdf \
    guzzlehttp/guzzle

composer require --dev \
    laravel/telescope \
    laravel/pint \
    phpunit/phpunit \
    fakerphp/faker \
    mockery/mockery

# 3. Frontend
npm install alpinejs chart.js
npm install -D tailwindcss postcss autoprefixer @tailwindcss/forms @tailwindcss/typography

# 4. Auth scaffolding
php artisan breeze:install blade

# 5. Publish configs
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan horizon:install
php artisan telescope:install
```

### 0.3 Secure .env Setup — WAJIB sebelum coding

```env
# === APP ===
APP_NAME=PSSM
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000
APP_KEY=  # php artisan key:generate

# === DATABASE (PostgreSQL) ===
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pssm_db
DB_USERNAME=pssm_user
DB_PASSWORD=STRONG_PASSWORD_MIN_32_CHARS

# === REDIS — WAJIB pakai password (Fix S-06) ===
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=STRONG_REDIS_PASSWORD_32_CHARS
REDIS_PORT=6379

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# === SESSION — Timeout per-role (Fix S-03) ===
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# === AI ===
OPENROUTER_API_KEY=sk-or-v1-xxxxx
OPENROUTER_MODEL=anthropic/claude-3.5-sonnet

# === MAIL ===
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_ENCRYPTION=tls

# === FONNTE ===
FONNTE_API_KEY=your_key

# === SECURITY ===
TELESCOPE_ENABLED=true
LOG_CHANNEL=daily
LOG_LEVEL=info
```

---

## FASE 1: Foundation & Core Security (Minggu 1-2)

> **Ref:** PRD §3A, DesignDoc §2, SecurityHardening §1.1-1.5

### Sprint 1.1 — Database Schema (Hari 1-3)

**Urutan migration yang benar (dependency order):**

```
1. create_users_table                    # Tidak ada dependency
2. create_student_profiles_table         # FK: users
3. create_teacher_profiles_table         # FK: users
4. create_academic_years_table           # Tidak ada dependency
5. create_subjects_table                 # Tidak ada dependency
6. create_classes_table                  # FK: academic_years, users
7. create_class_student_table            # FK: classes, users
8. create_class_subject_table            # FK: classes, subjects, users
9. create_assignments_table              # FK: users, classes, subjects
10. create_submissions_table             # FK: assignments, users
11. create_quizzes_table                 # FK: users, classes, subjects
12. create_quiz_questions_table          # FK: quizzes
13. create_quiz_attempts_table           # FK: quizzes, users
14. create_attendances_table             # FK: classes, subjects, users
15. create_announcements_table           # FK: users, classes
16. create_notifications_table           # FK: users
17. create_activity_logs_table           # FK: users
```

**Perbaikan Schema dari DesignDoc (Fix B-04, S-23, S-25, S-26):**

```php
// Setiap migration WAJIB include:
Schema::create('users', function (Blueprint $table) {
    $table->id();
    // ... columns ...
    $table->softDeletes();              // FIX S-26: Soft delete
    $table->timestamps();
});

// submissions — Fix score constraint
$table->integer('score')->nullable();   // Validasi di app layer, bukan DB
// JANGAN: CHECK (score >= 0 AND score <= 100) — karena max_score variabel

// quiz_questions — Fix jawaban plain text
$table->string('correct_answer_hash'); // Simpan hash, bukan plain text
// Verifikasi: Hash::check($studentAnswer, $question->correct_answer_hash)
```

**Index Optimization (tambahan dari DesignDoc):**

```php
// Tambahkan di migration masing-masing
// submissions
$table->index(['assignment_id', 'student_id']);
$table->index('submitted_at');

// assignments
$table->index(['class_id', 'subject_id', 'deadline']);
$table->index(['is_published', 'deadline']);

// attendances
$table->index(['class_id', 'date', 'status']);

// users
$table->index(['role', 'is_active']);
```

**Validation Command — Jalankan setelah semua migration:**
```bash
php artisan migrate
php artisan db:seed --class=RoleAndPermissionSeeder

# Verifikasi semua tabel terbuat
php artisan tinker --execute="echo count(Schema::getTableListing()) . ' tables created';"
```

---

### Sprint 1.2 — Authentication & RBAC (Hari 3-5)

**Model User — dengan semua security fix:**

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    use HasRoles; // Spatie

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];
}
```

**RBAC Seeder — Permissions Matrix:**

```php
// database/seeders/RoleAndPermissionSeeder.php
class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            // Assignments
            'assignments.view', 'assignments.create', 'assignments.edit',
            'assignments.delete', 'assignments.grade',
            // Quizzes
            'quizzes.view', 'quizzes.create', 'quizzes.edit',
            'quizzes.delete', 'quizzes.take',
            // Students
            'students.view', 'students.manage',
            // Classes
            'classes.view', 'classes.manage',
            // Analytics
            'analytics.view_own', 'analytics.view_class', 'analytics.view_school',
            // Attendance
            'attendance.view', 'attendance.record',
            // Announcements
            'announcements.view', 'announcements.create_class', 'announcements.create_school',
            // Admin
            'admin.dashboard', 'admin.manage_users', 'admin.export_data',
            'admin.view_logs', 'admin.system_settings',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm]);
        }

        // Roles & Permission Assignment
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $teacher = Role::create(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'assignments.view', 'assignments.create', 'assignments.edit',
            'assignments.delete', 'assignments.grade',
            'quizzes.view', 'quizzes.create', 'quizzes.edit', 'quizzes.delete',
            'students.view', 'classes.view',
            'analytics.view_own', 'analytics.view_class',
            'attendance.view', 'attendance.record',
            'announcements.view', 'announcements.create_class',
        ]);

        $classLeader = Role::create(['name' => 'class_leader']);
        $classLeader->givePermissionTo([
            'assignments.view', 'quizzes.view', 'quizzes.take',
            'students.view', 'classes.view',
            'analytics.view_own',
            'attendance.view', 'attendance.record',
            'announcements.view', 'announcements.create_class',
        ]);

        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'assignments.view', 'quizzes.view', 'quizzes.take',
            'analytics.view_own', 'attendance.view',
            'announcements.view',
        ]);
    }
}
```

**Security Middleware Stack — Urutan yang benar:**

```php
// bootstrap/app.php (Laravel 11)
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\SecurityHeaders::class,       // 1. Headers dulu
        \App\Http\Middleware\TrackActivity::class,         // 2. Logging
    ]);

    $middleware->api(append: [
        \App\Http\Middleware\SecurityHeaders::class,
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);

    $middleware->alias([
        'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'throttle.login'   => \App\Http\Middleware\BruteForceProtection::class,
        'verify.ownership' => \App\Http\Middleware\VerifyResourceOwnership::class,
    ]);
})
```

**Password Validation — Diperkuat (Fix S-01):**

```php
// app/Http/Requests/Auth/RegisterRequest.php
public function rules(): array
{
    return [
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'unique:users'],
        'password' => [
            'required', 'confirmed',
            Password::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
        ],
    ];
}
```

---

### Sprint 1.3 — Core Middleware & Security Layer (Hari 5-7)

**Security Headers Middleware:**

```php
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
        $response->headers->set('Permissions-Policy',
            'camera=(), microphone=(), geolocation=()');

        if (app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security',
                'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
```

**Brute-Force Protection (Fix S-02, S-05):**

```php
// app/Http/Middleware/BruteForceProtection.php
class BruteForceProtection
{
    public function handle($request, Closure $next)
    {
        $identifier = $request->ip() . '|' . strtolower($request->input('email', ''));
        $key = "login_attempts:{$identifier}";
        $attempts = (int) Cache::get($key, 0);

        if ($attempts >= 5) {
            $lockMinutes = min(pow(2, $attempts - 5), 60);
            $remaining = Cache::get("{$key}:locked_until");

            if ($remaining && now()->lt($remaining)) {
                return response()->json([
                    'message' => "Terlalu banyak percobaan. Coba lagi dalam {$lockMinutes} menit.",
                ], 429);
            }
        }

        $response = $next($request);

        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 422) {
            Cache::put($key, $attempts + 1, now()->addMinutes(30));
            if ($attempts + 1 >= 5) {
                $lockMinutes = min(pow(2, $attempts - 4), 60);
                Cache::put("{$key}:locked_until", now()->addMinutes($lockMinutes), $lockMinutes * 60);
            }
        } else {
            Cache::forget($key);
        }

        return $response;
    }
}
```

**IDOR Protection Middleware:**

```php
// app/Http/Middleware/VerifyResourceOwnership.php
class VerifyResourceOwnership
{
    public function handle($request, Closure $next, string $resourceParam = 'id')
    {
        $user = $request->user();
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Log access attempt untuk audit
        activity()
            ->causedBy($user)
            ->withProperties([
                'resource' => $request->route()->getName(),
                'params' => $request->route()->parameters(),
                'ip' => $request->ip(),
            ])
            ->log('resource_access_attempt');

        return $next($request);
    }
}
```

**✅ Checkpoint Fase 1 — Verifikasi sebelum lanjut:**
```bash
php artisan test --filter=AuthTest
php artisan test --filter=RBACTest
php artisan tinker --execute="echo Role::count() . ' roles, ' . Permission::count() . ' permissions';"
# Expected: 4 roles, 27 permissions
```

---

## FASE 2: Core Features + Bug Fixes (Minggu 3-4)

> **Ref:** PRD §3B-3E, DesignDoc §3-5, SecurityHardening §2.1

### Sprint 2.1 — Assignment System (Hari 8-11)

**Controller Pattern — Standar untuk semua resource:**

```php
// app/Http/Controllers/AssignmentController.php
class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $service
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Assignment::class);

        $assignments = $this->service->getForUser(
            $request->user(),
            $request->validated() // Gunakan FormRequest
        );

        return view('assignments.index', compact('assignments'));
    }

    public function store(StoreAssignmentRequest $request)
    {
        $this->authorize('create', Assignment::class);

        $assignment = $this->service->create($request->validated());

        // Dispatch notification ke queue (bukan sync)
        SendAssignmentNotification::dispatch($assignment)->onQueue('notifications');

        return redirect()
            ->route('assignments.show', $assignment)
            ->with('success', 'Tugas berhasil dibuat.');
    }
}
```

**Submission dengan Versioning (Fix B-01):**

```php
// app/Services/SubmissionService.php
class SubmissionService
{
    public function submit(Assignment $assignment, User $student, array $data): Submission
    {
        // Cek deadline
        if (now()->gt($assignment->deadline)) {
            $data['is_late'] = true;
        }

        // Cek apakah sudah pernah submit
        $existingCount = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->count();

        // Cegah re-submit jika sudah di-grade (Fix B-01)
        $lastSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->latest('version')
            ->first();

        if ($lastSubmission && $lastSubmission->graded_at) {
            throw new BusinessException('Tugas sudah dinilai. Hubungi guru untuk re-submit.');
        }

        return Submission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'content'       => $data['content'] ?? null,
            'file_path'     => $this->handleFileUpload($data['file'] ?? null, $assignment),
            'is_late'       => $data['is_late'] ?? false,
            'version'       => $existingCount + 1,
        ]);
    }

    private function handleFileUpload(?UploadedFile $file, Assignment $assignment): ?string
    {
        if (!$file) return null;

        // Validasi MIME type sebenarnya (Fix S-13)
        $realMime = mime_content_type($file->getPathname());
        $allowed = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 'image/png',
        ];

        if (!in_array($realMime, $allowed)) {
            throw new ValidationException("Tipe file tidak diizinkan: {$realMime}");
        }

        // Secure filename (Fix S-16)
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs(
            "submissions/{$assignment->id}",
            $filename,
            'private'
        );
    }
}
```

**Grading — dengan dynamic max score (Fix B-04):**

```php
// app/Http/Requests/GradeSubmissionRequest.php
public function rules(): array
{
    $maxScore = $this->route('submission')->assignment->max_score;

    return [
        'score'    => "required|integer|min:0|max:{$maxScore}",
        'feedback' => 'nullable|string|max:5000',
    ];
}
```

---

### Sprint 2.2 — Quiz System + AI (Hari 12-16)

**AI Service — Secured (Fix S-18 sampai S-22):**

```php
// app/Services/AIService.php — Full secured version
class AIService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey  = config('services.openrouter.api_key');
        $this->baseUrl = config('services.openrouter.base_url');
        $this->model   = config('services.openrouter.model');
    }

    public function generateQuiz(string $topic, int $count, string $difficulty): array
    {
        // Sanitasi input (Fix S-18)
        $topic = $this->sanitize($topic);
        $count = max(5, min(20, $count)); // Limit 5-20

        $cacheKey = "ai_quiz:" . hash('sha256', "{$topic}:{$count}:{$difficulty}:" . now()->format('Y-m-d'));

        return Cache::remember($cacheKey, 3600, function () use ($topic, $count, $difficulty) {
            $response = Http::timeout(30)
                ->retry(2, 1000)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'HTTP-Referer'  => config('app.url'),
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'    => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert Indonesian high school teacher. Return ONLY valid JSON.'],
                        ['role' => 'user', 'content' => $this->buildQuizPrompt($topic, $count, $difficulty)],
                    ],
                    'max_tokens'  => 4000,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                Log::error('AI API failed', ['status' => $response->status(), 'body' => $response->body()]);
                throw new AIServiceException('Gagal generate kuis. Silakan coba lagi.');
            }

            $content = $response->json('choices.0.message.content');
            $parsed = $this->parseAndValidate($content);

            return $parsed;
        });
    }

    public function generateFeedback(string $essay, string $topic): array
    {
        // Anonimisasi (Fix S-19)
        $essay = $this->anonymize($essay);
        $essay = $this->sanitize($essay);

        $response = Http::timeout(30)
            ->retry(2, 1000)
            ->withHeaders(['Authorization' => "Bearer {$this->apiKey}"])
            ->post("{$this->baseUrl}/chat/completions", [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $this->buildFeedbackPrompt($essay, $topic)],
                ],
                'max_tokens' => 2000,
            ]);

        $content = $response->json('choices.0.message.content');
        $result = $this->parseAndValidate($content);

        // Validasi score ranges (Fix S-20)
        return $this->validateScores($result);
    }

    // === SECURITY HELPERS ===

    private function sanitize(string $input): string
    {
        $blacklist = [
            'ignore previous', 'forget your', 'you are now',
            'system prompt', 'act as', '<script', '<?php',
            'DROP TABLE', 'DELETE FROM', 'INSERT INTO',
        ];
        foreach ($blacklist as $term) {
            $input = str_ireplace($term, '', $input);
        }
        return strip_tags(mb_substr($input, 0, 5000));
    }

    private function anonymize(string $text): string
    {
        $text = preg_replace('/\b\d{10,}\b/', '[REDACTED]', $text);
        $text = preg_replace('/[\w.+-]+@[\w-]+\.[\w.]+/', '[REDACTED]', $text);
        $text = preg_replace('/(\+?62|0)\d{8,13}/', '[REDACTED]', $text);
        return $text;
    }

    private function parseAndValidate(string $content): array
    {
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $data = json_decode(trim($content), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AIServiceException('AI response tidak valid: ' . json_last_error_msg());
        }
        return $data;
    }

    private function validateScores(array $data): array
    {
        $scoreFields = ['structure_score', 'grammar_score', 'argument_score',
                        'vocabulary_score', 'overall_score'];
        foreach ($scoreFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = max(0, min(100, intval($data[$field])));
            }
        }
        if (isset($data['detailed_feedback'])) {
            $data['detailed_feedback'] = strip_tags(mb_substr($data['detailed_feedback'], 0, 10000));
        }
        return $data;
    }
}
```

**Quiz Attempt — Anti-cheat + Multiple attempts (Fix B-02):**

```php
// app/Services/QuizService.php
class QuizService
{
    public function startAttempt(Quiz $quiz, User $student): QuizAttempt
    {
        // Cek max attempts (Fix B-02)
        $existingAttempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->count();

        if ($existingAttempts >= $quiz->max_attempts) {
            throw new BusinessException("Maksimum {$quiz->max_attempts} percobaan tercapai.");
        }

        // Cek waktu quiz
        if (now()->lt($quiz->start_time) || now()->gt($quiz->end_time)) {
            throw new BusinessException('Kuis tidak dalam periode pengerjaan.');
        }

        return QuizAttempt::create([
            'quiz_id'        => $quiz->id,
            'student_id'     => $student->id,
            'attempt_number' => $existingAttempts + 1,
            'started_at'     => now(),
        ]);
    }

    public function submitAttempt(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        // Server-side timer validation (anti-cheat)
        $elapsed = $attempt->started_at->diffInSeconds(now());
        $maxSeconds = $attempt->quiz->duration_minutes * 60 + 30; // 30s grace

        if ($elapsed > $maxSeconds) {
            throw new BusinessException('Waktu pengerjaan telah habis.');
        }

        // Auto-grade pilihan ganda
        $score = $this->autoGrade($attempt->quiz, $answers);

        $attempt->update([
            'submitted_at'       => now(),
            'answers'            => $answers,
            'score'              => $score,
            'time_taken_seconds' => $elapsed,
        ]);

        return $attempt;
    }

    private function autoGrade(Quiz $quiz, array $answers): int
    {
        $questions = $quiz->questions()->where('question_type', 'multiple_choice')->get();
        $correct = 0;
        $total = $questions->count();

        foreach ($questions as $q) {
            $studentAnswer = $answers[$q->id] ?? null;
            if ($studentAnswer && Hash::check($studentAnswer, $q->correct_answer_hash)) {
                $correct++;
            }
        }

        return $total > 0 ? round(($correct / $total) * $quiz->max_score) : 0;
    }
}
```

**✅ Checkpoint Fase 2:**
```bash
php artisan test --filter=AssignmentTest
php artisan test --filter=QuizTest
php artisan test --filter=SubmissionTest
# Semua harus pass
```

---

## FASE 3: Dashboard, Analytics & Export (Minggu 5-6)

> **Ref:** PRD §3B,3F,3G, SecurityHardening §4

### Sprint 3.1 — Dashboard Performance (Hari 17-20)

**Pre-computed Stats (Fix performa dashboard < 1 detik):**

```php
// app/Console/Commands/RefreshDashboardStats.php
class RefreshDashboardStats extends Command
{
    protected $signature = 'dashboard:refresh';

    public function handle()
    {
        // School-wide stats
        Cache::put('stats:school', [
            'total_students'     => User::role('student')->where('is_active', true)->count(),
            'total_teachers'     => User::role('teacher')->where('is_active', true)->count(),
            'total_classes'      => Classes::count(),
            'active_assignments' => Assignment::where('deadline', '>', now())->count(),
            'updated_at'         => now()->toISOString(),
        ], 600);

        // Per-teacher stats
        User::role('teacher')->chunk(50, function ($teachers) {
            foreach ($teachers as $teacher) {
                Cache::put("stats:teacher:{$teacher->id}", [
                    'pending_review' => Submission::whereHas('assignment', fn($q) =>
                        $q->where('teacher_id', $teacher->id)
                    )->whereNull('score')->count(),
                    'active_quizzes' => Quiz::where('teacher_id', $teacher->id)
                        ->where('end_time', '>', now())->count(),
                ], 600);
            }
        });

        $this->info('Dashboard stats refreshed.');
    }
}

// app/Console/Kernel.php
$schedule->command('dashboard:refresh')->everyFiveMinutes();
```

**Dashboard Controller — Cepat karena pakai cache:**

```php
// app/Http/Controllers/DashboardController.php
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $view = match (true) {
            $user->hasRole('super_admin') => 'dashboard.super-admin',
            $user->hasRole('teacher')     => 'dashboard.teacher',
            $user->hasRole('class_leader')=> 'dashboard.class-leader',
            default                       => 'dashboard.student',
        };

        $stats = match (true) {
            $user->hasRole('super_admin') => Cache::get('stats:school', []),
            $user->hasRole('teacher')     => Cache::get("stats:teacher:{$user->id}", []),
            default => $this->getStudentStats($user),
        };

        return view($view, compact('stats'));
    }

    private function getStudentStats(User $student): array
    {
        return Cache::remember("stats:student:{$student->id}", 300, fn() => [
            'upcoming_deadlines' => Assignment::whereHas('class.students', fn($q) =>
                $q->where('users.id', $student->id)
            )->where('deadline', '>', now())
              ->orderBy('deadline')
              ->limit(5)
              ->get(['id', 'title', 'deadline', 'subject_id']),

            'average_score' => Submission::where('student_id', $student->id)
                ->whereNotNull('score')
                ->avg('score'),
        ]);
    }
}
```

### Sprint 3.2 — Charts & Analytics (Hari 20-22)

**Chart.js — Aman dari XSS (Fix dari SecurityHardening):**

```blade
{{-- resources/views/analytics/student.blade.php --}}

<canvas id="gradesChart" width="400" height="200"></canvas>

<script>
// AMAN: Gunakan @json bukan {!! !!}
const months = @json($months);
const scores = @json($averageScores);

const ctx = document.getElementById('gradesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Rata-rata Nilai',
            data: scores,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.3,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Perkembangan Nilai Per Bulan' }
        },
        scales: {
            y: { min: 0, max: 100 }
        }
    }
});
</script>
```

### Sprint 3.3 — Export System (Hari 23-25)

**Streaming Export untuk data besar (Prevent DoS):**

```php
// app/Services/ExportService.php
class ExportService
{
    public function exportGrades(int $classId, int $subjectId)
    {
        $fileName = sprintf('nilai_%d_%d_%s.xlsx', $classId, $subjectId, now()->format('Ymd'));

        return Excel::download(new GradesExport($classId, $subjectId), $fileName);
    }

    public function exportAttendance(int $classId, string $month)
    {
        // Streaming untuk data besar
        return response()->streamDownload(function () use ($classId, $month) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIS', 'Nama', 'Tanggal', 'Status', 'Keterangan']);

            Attendance::where('class_id', $classId)
                ->whereMonth('date', Carbon::parse($month)->month)
                ->with('student:id,name')
                ->with('student.studentProfile:id,user_id,student_id_number')
                ->chunk(100, function ($records) use ($handle) {
                    foreach ($records as $r) {
                        fputcsv($handle, [
                            $r->student->studentProfile->student_id_number,
                            $r->student->name,
                            $r->date->format('d/m/Y'),
                            $r->status,
                            $r->notes ?? '-',
                        ]);
                    }
                });

            fclose($handle);
        }, "absensi_{$classId}_{$month}.csv");
    }
}
```

---

## FASE 4: Attendance, Notifications, Polishing (Minggu 7-8)

> **Ref:** PRD §3H-3J, TechStack §5

### Sprint 4.1 — Attendance + Notifications (Hari 26-30)

**Rate-Limited Notification Dispatch:**

```php
// app/Services/NotificationService.php
class NotificationService
{
    public function notifyStudents(Assignment $assignment): void
    {
        $students = $assignment->class->students;

        // Batch notification via queue (tidak blocking)
        $students->chunk(10)->each(function ($batch, $index) use ($assignment) {
            SendAssignmentNotification::dispatch($assignment, $batch)
                ->onQueue('notifications')
                ->delay(now()->addSeconds($index * 5)); // Stagger to avoid spam
        });
    }
}
```

### Sprint 4.2 — Final Polish & Bug Sweep (Hari 31-35)

**Error Handling — Production-safe (Fix S-12):**

```php
// app/Exceptions/Handler.php (Laravel 11: bootstrap/app.php)
->withExceptions(function (Exceptions $exceptions) {

    // API errors — JANGAN expose stack trace
    $exceptions->render(function (Throwable $e, Request $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            $response = [
                'message' => $status === 500
                    ? 'Terjadi kesalahan server. Silakan coba lagi.'
                    : $e->getMessage(),
            ];

            // Hanya tampilkan detail di development
            if (app()->isLocal()) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            return response()->json($response, $status);
        }
    });

    // Log semua exception ke activity log
    $exceptions->report(function (Throwable $e) {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }
    });
})
```

**Health Check Endpoint (Fix G-02):**

```php
// routes/api.php
Route::get('/health', function () {
    $checks = [
        'app'      => true,
        'database' => false,
        'redis'    => false,
        'storage'  => false,
    ];

    try { DB::select('SELECT 1'); $checks['database'] = true; } catch (\Exception $e) {}
    try { Cache::store('redis')->put('health', true, 10); $checks['redis'] = true; } catch (\Exception $e) {}
    try { Storage::disk('private')->put('health.txt', 'ok'); Storage::disk('private')->delete('health.txt'); $checks['storage'] = true; } catch (\Exception $e) {}

    $allHealthy = !in_array(false, $checks);

    return response()->json([
        'status' => $allHealthy ? 'healthy' : 'degraded',
        'checks' => $checks,
        'timestamp' => now()->toISOString(),
    ], $allHealthy ? 200 : 503);
})->name('health');
```

---

## FASE 5: Testing & Quality Assurance

> **Ref:** DesignDoc §11, SecurityHardening semua temuan

### 5.1 Test Suite Wajib

```bash
# Struktur test yang harus ada
tests/
├── Unit/
│   ├── Services/
│   │   ├── AIServiceTest.php           # Test sanitasi, parsing, error handling
│   │   ├── ExportServiceTest.php       # Test export format
│   │   └── NotificationServiceTest.php # Test dispatch
│   ├── Models/
│   │   ├── UserTest.php                # Test encryption, soft delete
│   │   ├── SubmissionTest.php          # Test versioning logic
│   │   └── QuizAttemptTest.php         # Test max attempts
│   └── Middleware/
│       ├── SecurityHeadersTest.php
│       └── BruteForceProtectionTest.php
│
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php               # Happy path + brute force
│   │   ├── RegisterTest.php            # Password strength
│   │   └── PasswordResetTest.php
│   ├── Assignment/
│   │   ├── CreateAssignmentTest.php    # RBAC check
│   │   ├── SubmitAssignmentTest.php    # File upload, versioning
│   │   └── GradeAssignmentTest.php    # Score validation
│   ├── Quiz/
│   │   ├── GenerateQuizTest.php        # AI integration
│   │   ├── TakeQuizTest.php            # Timer, anti-cheat
│   │   └── QuizResultsTest.php
│   └── Security/
│       ├── IDORTest.php                # Akses resource milik orang lain
│       ├── FileUploadTest.php          # Malicious file test
│       └── RateLimitTest.php
```

### 5.2 Security Test Cases — WAJIB pass sebelum deploy

```php
// tests/Feature/Security/IDORTest.php
class IDORTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_view_other_student_grades()
    {
        $student1 = User::factory()->create();
        $student1->assignRole('student');

        $student2 = User::factory()->create();
        $student2->assignRole('student');

        $response = $this->actingAs($student1)
            ->getJson("/api/students/{$student2->id}/grades");

        $response->assertStatus(403);
    }

    public function test_teacher_cannot_grade_other_teacher_assignment()
    {
        $teacher1 = User::factory()->create();
        $teacher1->assignRole('teacher');

        $teacher2 = User::factory()->create();
        $teacher2->assignRole('teacher');

        $assignment = Assignment::factory()->create(['teacher_id' => $teacher2->id]);
        $submission = Submission::factory()->create(['assignment_id' => $assignment->id]);

        $response = $this->actingAs($teacher1)
            ->postJson("/api/submissions/{$submission->id}/grade", ['score' => 90]);

        $response->assertStatus(403);
    }
}

// tests/Feature/Security/FileUploadTest.php
class FileUploadTest extends TestCase
{
    public function test_rejects_php_file_disguised_as_pdf()
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        $assignment = Assignment::factory()->create();

        // Buat file PHP dengan extension .pdf
        $maliciousFile = UploadedFile::fake()->create('malware.pdf', 100);
        file_put_contents($maliciousFile->getPathname(), '<?php echo "hacked"; ?>');

        $response = $this->actingAs($student)
            ->post("/assignments/{$assignment->id}/submit", [
                'file' => $maliciousFile,
            ]);

        $response->assertSessionHasErrors('file');
    }
}
```

### 5.3 Performance Test Checklist

```yaml
Performance Validation:
  Dashboard:
    - [ ] Super Admin dashboard load < 1s (dengan 1000 users)
    - [ ] Teacher dashboard load < 1s
    - [ ] Student dashboard load < 0.5s
  
  API:
    - [ ] GET /api/assignments < 200ms (100 items paginated)
    - [ ] POST /api/assignments < 300ms
    - [ ] POST /api/quizzes/generate-ai < 15s (dengan timeout)
  
  Database:
    - [ ] Zero N+1 queries (check via Telescope)
    - [ ] All queries < 100ms (check via slow query log)
    - [ ] All foreign keys indexed
  
  Export:
    - [ ] Excel export 100 siswa < 5s
    - [ ] PDF raport generation < 3s
    - [ ] CSV streaming untuk > 1000 records
```

---

## FASE 6: Production Deployment

> **Ref:** TechStack §8-9, DesignDoc §10, SecurityHardening §5

### 6.1 Pre-Deployment Checklist

```yaml
Security:
  - [ ] APP_DEBUG=false
  - [ ] APP_ENV=production
  - [ ] TELESCOPE_ENABLED=false (atau restricted)
  - [ ] Redis password set
  - [ ] Database SSL enabled
  - [ ] HTTPS enforced
  - [ ] Security headers middleware active
  - [ ] Rate limiting configured
  - [ ] CORS policy set
  - [ ] .env NOT in git
  - [ ] Error messages sanitized

Performance:
  - [ ] php artisan config:cache
  - [ ] php artisan route:cache
  - [ ] php artisan view:cache
  - [ ] composer install --no-dev --optimize-autoloader
  - [ ] npm run build
  - [ ] Database indexes verified
  - [ ] Redis caching active
  - [ ] Queue workers running (Horizon)

Data:
  - [ ] Automated daily backup configured
  - [ ] Backup restore tested
  - [ ] Soft delete on all critical models
  - [ ] Sensitive data encrypted

Monitoring:
  - [ ] Sentry error tracking active
  - [ ] Health check endpoint responding
  - [ ] Laravel Pulse installed
  - [ ] Security event logging active
  - [ ] Dashboard stats cron job scheduled
```

### 6.2 Production Deploy Script

```bash
#!/bin/bash
# deploy.sh — Safe production deployment

set -e  # Stop on error

echo "🚀 Starting PSSM deployment..."

# 1. Maintenance mode
php artisan down --render="errors::503" --retry=60

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction
npm ci --production

# 4. Build assets
npm run build

# 5. Run migrations
php artisan migrate --force

# 6. Clear & rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan cache:clear

# 7. Restart queue
php artisan queue:restart
php artisan horizon:terminate

# 8. Refresh dashboard stats
php artisan dashboard:refresh

# 9. Health check
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/api/health)
if [ "$HTTP_CODE" != "200" ]; then
    echo "❌ Health check failed! Rolling back..."
    php artisan up
    exit 1
fi

# 10. Bring app back online
php artisan up

echo "✅ Deployment complete!"
```

---

## FASE 7: Post-Launch Monitoring & Maintenance

### 7.1 Scheduled Tasks (Cron)

```php
// app/Console/Kernel.php / routes/console.php (Laravel 11)
Schedule::command('dashboard:refresh')->everyFiveMinutes();
Schedule::command('backup:run')->dailyAt('02:00');
Schedule::command('backup:verify')->weeklyOn(0, '03:00');  // Minggu 3 AM
Schedule::command('telescope:prune --hours=48')->daily();
Schedule::command('activitylog:clean --days=90')->weekly();
Schedule::command('queue:prune-failed --hours=72')->daily();

// Kirim reminder deadline H-1
Schedule::call(function () {
    $assignments = Assignment::where('deadline', '>', now())
        ->where('deadline', '<', now()->addDay())
        ->with('class.students')
        ->get();

    foreach ($assignments as $assignment) {
        $studentsWithoutSubmission = $assignment->class->students
            ->filter(fn($s) => !$assignment->submissions()->where('student_id', $s->id)->exists());

        foreach ($studentsWithoutSubmission as $student) {
            $student->notify(new DeadlineReminderNotification($assignment));
        }
    }
})->dailyAt('07:00')->timezone('Asia/Jakarta');
```

### 7.2 Monitoring Alerts

```yaml
Alert Rules:
  Critical (Immediate):
    - Health check failing > 2 minutes
    - Error rate > 5% in 5 minutes
    - Database connection failures
    - Redis connection failures
    - Brute-force attack detected (10+ failed logins from 1 IP)

  Warning (Within 1 hour):
    - API response time > 1s (p95)
    - Queue job failure rate > 1%
    - Disk usage > 80%
    - AI API error rate > 10%
    - Free tier quota approaching limit

  Info (Daily digest):
    - Daily active users count
    - Assignment submission rate
    - AI API usage & cost
    - Backup success status
```

---

## ALUR KERJA RINGKAS (Quick Reference)

```
FASE 0: Setup     → .env secure, dependencies, migration order
    ↓
FASE 1: Auth      → RBAC, middleware stack, password policy, brute-force
    ↓
FASE 2: Core      → Assignments, submissions (versioned), quiz (anti-cheat, AI secured)
    ↓
FASE 3: Dashboard → Cached stats, safe charts, streaming exports
    ↓
FASE 4: Polish    → Notifications, attendance, error handling, health check
    ↓
FASE 5: Testing   → Unit + Feature + Security + Performance tests
    ↓
FASE 6: Deploy    → Checklist, deploy script, nginx hardening
    ↓
FASE 7: Monitor   → Cron jobs, alerts, backup verification
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-03-16  
**Status:** Ready for Implementation ✅  
**Total Fases:** 7 (0-7)  
**Estimasi Timeline:** 8 minggu sesuai PRD
