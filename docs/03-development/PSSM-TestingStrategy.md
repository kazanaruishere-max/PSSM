# PSSM - Testing Strategy
## Quality Assurance & CI/CD Pipeline v1.0

**Framework:** PHPUnit 11 + Laravel Testing  
**Coverage Target:** ≥ 80%  
**CI/CD:** GitHub Actions

---

## 1. Testing Pyramid

```
         ╱╲
        ╱  ╲         E2E / Browser Tests (5%)
       ╱    ╲        — Login flow, assignment submission flow
      ╱──────╲
     ╱        ╲      Integration / Feature Tests (35%)
    ╱          ╲     — API endpoints, RBAC, file upload
   ╱────────────╲
  ╱              ╲   Unit Tests (60%)
 ╱                ╲  — Services, Models, Middleware, Helpers
╱══════════════════╲
```

---

## 2. Test Directory Structure

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── AIServiceTest.php
│   │   ├── ExportServiceTest.php
│   │   ├── QuizServiceTest.php
│   │   ├── SubmissionServiceTest.php
│   │   └── NotificationServiceTest.php
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── AssignmentTest.php
│   │   ├── SubmissionTest.php
│   │   └── QuizAttemptTest.php
│   ├── Middleware/
│   │   ├── SecurityHeadersTest.php
│   │   ├── BruteForceProtectionTest.php
│   │   └── VerifyResourceOwnershipTest.php
│   └── Helpers/
│       └── SanitizerTest.php
│
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   ├── PasswordResetTest.php
│   │   └── SessionTest.php
│   ├── Assignment/
│   │   ├── CreateAssignmentTest.php
│   │   ├── SubmitAssignmentTest.php
│   │   ├── GradeAssignmentTest.php
│   │   └── ExportGradesTest.php
│   ├── Quiz/
│   │   ├── CreateQuizTest.php
│   │   ├── AIGenerateQuizTest.php
│   │   ├── TakeQuizTest.php
│   │   └── QuizResultsTest.php
│   ├── Attendance/
│   │   └── RecordAttendanceTest.php
│   ├── Dashboard/
│   │   └── DashboardLoadTest.php
│   └── Security/
│       ├── IDORTest.php
│       ├── FileUploadSecurityTest.php
│       ├── RateLimitTest.php
│       ├── CSRFTest.php
│       └── XSSTest.php
│
└── Browser/ (Dusk — optional)
    ├── LoginFlowTest.php
    └── AssignmentFlowTest.php
```

---

## 3. Unit Test Specifications

### 3.1 AIService Tests

```php
class AIServiceTest extends TestCase
{
    // Sanitization
    public function test_sanitizes_prompt_injection_attempts(): void;
    public function test_strips_html_and_php_tags(): void;
    public function test_truncates_input_to_5000_chars(): void;

    // Anonymization
    public function test_anonymizes_phone_numbers(): void;
    public function test_anonymizes_email_addresses(): void;
    public function test_anonymizes_student_id_numbers(): void;

    // AI Response Parsing
    public function test_parses_valid_json_response(): void;
    public function test_handles_markdown_wrapped_json(): void;
    public function test_throws_on_invalid_json(): void;

    // Score Validation
    public function test_clamps_scores_to_0_100_range(): void;
    public function test_strips_tags_from_feedback_text(): void;

    // Error Handling
    public function test_throws_on_api_failure(): void;
    public function test_retries_on_timeout(): void;
    public function test_uses_cache_for_same_quiz_params(): void;
}
```

### 3.2 Submission Service Tests

```php
class SubmissionServiceTest extends TestCase
{
    public function test_creates_submission_with_version_1(): void;
    public function test_increments_version_on_resubmit(): void;
    public function test_blocks_resubmit_after_grading(): void;
    public function test_marks_late_submission(): void;
    public function test_validates_real_mime_type(): void;
    public function test_rejects_php_disguised_as_pdf(): void;
    public function test_generates_uuid_filename(): void;
}
```

### 3.3 Middleware Tests

```php
class BruteForceProtectionTest extends TestCase
{
    public function test_allows_first_5_attempts(): void;
    public function test_blocks_after_5_failed_attempts(): void;
    public function test_resets_on_successful_login(): void;
    public function test_exponential_backoff_lockout(): void;
}

class SecurityHeadersTest extends TestCase
{
    public function test_sets_x_content_type_options(): void;
    public function test_sets_x_frame_options_deny(): void;
    public function test_sets_hsts_in_production(): void;
    public function test_no_hsts_in_development(): void;
}
```

---

## 4. Feature Test Specifications

### 4.1 RBAC Test Matrix

| Action | Super Admin | Teacher | Class Leader | Student |
|--------|:-----------:|:-------:|:------------:|:-------:|
| Create assignment | ✅ 201 | ✅ 201 | ❌ 403 | ❌ 403 |
| Grade submission | ✅ 200 | ✅ 200 (own) | ❌ 403 | ❌ 403 |
| View all analytics | ✅ 200 | ❌ 403 | ❌ 403 | ❌ 403 |
| Record attendance | ✅ 200 | ✅ 200 | ✅ 200 | ❌ 403 |
| Export data | ✅ 200 | ❌ 403 | ❌ 403 | ❌ 403 |
| Manage users | ✅ 200 | ❌ 403 | ❌ 403 | ❌ 403 |

> Setiap cell = 1 test case

### 4.2 Security Test Cases (WAJIB Pass)

```php
class IDORTest extends TestCase
{
    public function test_student_cannot_view_other_student_grades(): void;
    public function test_student_cannot_view_other_class_assignments(): void;
    public function test_teacher_cannot_grade_other_teacher_assignment(): void;
    public function test_parent_can_only_view_own_child(): void;
}

class FileUploadSecurityTest extends TestCase
{
    public function test_rejects_executable_files(): void;
    public function test_rejects_php_with_fake_extension(): void;
    public function test_rejects_files_over_10mb(): void;
    public function test_accepts_valid_pdf(): void;
    public function test_accepts_valid_image(): void;
    public function test_stores_in_private_disk(): void;
}

class RateLimitTest extends TestCase
{
    public function test_login_limited_to_5_per_minute(): void;
    public function test_ai_generate_limited_to_3_per_minute(): void;
    public function test_export_limited_to_10_per_hour(): void;
}
```

---

## 5. Mock Strategy

### External Services

| Service | Mock Method | Kenapa |
|---------|------------|--------|
| OpenRouter AI API | `Http::fake()` | Mahal, lambat, non-deterministic |
| Fonnte WhatsApp | `Http::fake()` | Rate limited, butuh real phone |
| Mailtrap Email | `Notification::fake()` | Jangan kirim email di test |
| Cloudflare R2 | `Storage::fake('r2')` | Hindari real upload |

```php
// Contoh mock AI API
Http::fake([
    'openrouter.ai/api/v1/*' => Http::response([
        'choices' => [[
            'message' => [
                'content' => json_encode([
                    'questions' => [
                        [
                            'question_text' => 'Apa hasil fotosintesis?',
                            'options' => ['A' => 'CO2', 'B' => 'O2', 'C' => 'N2', 'D' => 'H2'],
                            'correct_answer' => 'B',
                            'explanation' => 'Fotosintesis menghasilkan O2.',
                        ]
                    ]
                ])
            ]
        ]]
    ], 200)
]);
```

---

## 6. CI/CD Pipeline (GitHub Actions)

```yaml
# .github/workflows/ci.yml
name: PSSM CI/CD

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_DB: pssm_test
          POSTGRES_USER: test
          POSTGRES_PASSWORD: test
        ports: ['5432:5432']
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

      redis:
        image: redis:7-alpine
        ports: ['6379:6379']

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo_pgsql, redis, mbstring
          coverage: xdebug

      - name: Install Dependencies
        run: |
          composer install --no-interaction --prefer-dist
          npm ci

      - name: Prepare Environment
        run: |
          cp .env.testing .env
          php artisan key:generate
          php artisan migrate --force

      - name: Run Tests
        run: php artisan test --coverage --min=80

      - name: Run Pint (Code Style)
        run: ./vendor/bin/pint --test

  security:
    runs-on: ubuntu-latest
    needs: test
    steps:
      - uses: actions/checkout@v4

      - name: Security Audit
        run: composer audit

      - name: Check Dependencies
        run: npm audit --production

  deploy:
    runs-on: ubuntu-latest
    needs: [test, security]
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v4
      - name: Deploy to Production
        run: |
          # Deploy script sesuai PSSM-MasterWorkflow.md Fase 6
          echo "Deploying to production..."
```

---

## 7. Performance Testing Targets

| Metric | Target | Tool |
|--------|--------|------|
| Dashboard load | < 1 detik | Laravel Telescope |
| API response (GET) | < 200ms | PHPUnit assertion |
| API response (POST) | < 300ms | PHPUnit assertion |
| AI generation | < 15 detik | Timeout config |
| Export 100 siswa | < 5 detik | PHPUnit assertion |
| Zero N+1 queries | 0 detected | Telescope Query Watcher |

```php
// Performance assertion dalam test
public function test_dashboard_loads_within_1_second(): void
{
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $start = microtime(true);
    $response = $this->actingAs($teacher)->get('/dashboard');
    $duration = microtime(true) - $start;

    $response->assertOk();
    $this->assertLessThan(1.0, $duration, 'Dashboard load > 1 second');
}
```

---

## 8. Testing Commands Cheatsheet

```bash
# Run semua test
php artisan test

# Run dengan coverage
php artisan test --coverage --min=80

# Run specific suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test --filter=AIServiceTest
php artisan test --filter=IDORTest

# Run specific method
php artisan test --filter=test_student_cannot_view_other_student_grades

# Parallel testing (lebih cepat)
php artisan test --parallel

# Dengan output verbose
php artisan test -v
```

---

**Document Version:** 1.0 | **Last Updated:** 2026-03-16 | **Coverage Target:** ≥ 80%
