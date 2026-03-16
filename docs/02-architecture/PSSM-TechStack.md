# PSSM - Tech Stack Documentation
## Complete Technology Specifications & Setup Guide

**Framework:** Laravel 11.x  
**PHP Version:** 8.3+  
**Target Infrastructure:** Zero-Cost (Free Tier Only)  
**Development Time:** 8 weeks

---

## 1. Core Technology Stack

### Backend Framework

```json
{
  "framework": "Laravel 11.x",
  "php_version": ">=8.3",
  "architecture": "MVC + Service Layer",
  "why_laravel": [
    "Mature ecosystem (10+ years)",
    "Built-in authentication (Breeze/Sanctum)",
    "Eloquent ORM (prevents SQL injection)",
    "Job queues (Horizon) for async tasks",
    "Large Indonesian community",
    "Extensive documentation in Bahasa"
  ]
}
```

**Installation:**
```bash
# Install Laravel via Composer
composer create-project laravel/laravel pssm "11.*"

# Navigate to project
cd pssm

# Install Laravel Breeze (Authentication scaffolding)
composer require laravel/breeze --dev
php artisan breeze:install blade

# Run migrations
php artisan migrate
```

---

### Database

**Primary:** PostgreSQL 16  
**Why PostgreSQL over MySQL:**
- Better JSON support (for quiz options, AI feedback)
- Superior full-text search (untuk search materi)
- ACID compliance (transaksi nilai lebih aman)
- Free tier generous (Supabase, Neon)

```bash
# Install PostgreSQL locally
# macOS
brew install postgresql@16

# Ubuntu/Debian
sudo apt install postgresql-16

# Windows
# Download from postgresql.org
```

**Configuration (.env):**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pssm_db
DB_USERNAME=postgres
DB_PASSWORD=secret
```

---

### Caching & Queue

**Redis 7**  
**Usage:**
- Session storage (faster than database)
- Query result caching
- Job queue backend (Laravel Horizon)

```bash
# Install Redis
# macOS
brew install redis

# Ubuntu/Debian
sudo apt install redis-server

# Start Redis
redis-server
```

**Laravel Configuration:**
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 2. Laravel Packages & Dependencies

### Essential Packages

```json
{
  "require": {
    "php": "^8.3",
    "laravel/framework": "^11.0",
    "laravel/breeze": "^2.0",
    "laravel/sanctum": "^4.0",
    "laravel/horizon": "^5.0",
    "laravel/telescope": "^5.0",
    
    "maatwebsite/laravel-excel": "^3.1",
    "barryvdh/laravel-dompdf": "^3.0",
    "spatie/laravel-permission": "^6.0",
    "spatie/laravel-activitylog": "^4.0",
    "guzzlehttp/guzzle": "^7.8"
  },
  "require-dev": {
    "fakerphp/faker": "^1.23",
    "mockery/mockery": "^1.6",
    "nunomaduro/collision": "^8.0",
    "phpunit/phpunit": "^11.0",
    "laravel/pint": "^1.0"
  }
}
```

### Package Breakdown

#### A. Authentication & Authorization
```bash
# Laravel Breeze (Lightweight auth scaffolding)
composer require laravel/breeze --dev
php artisan breeze:install blade

# Laravel Sanctum (API authentication)
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Spatie Permission (RBAC)
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

**Usage Example:**
```php
// Assign role to user
$user->assignRole('teacher');

// Check permission
if ($user->can('create_assignment')) {
    // Allow
}

// In Blade
@can('create_assignment')
    <button>Create Assignment</button>
@endcan
```

---

#### B. Excel Export
```bash
composer require maatwebsite/laravel-excel
```

**Usage Example:**
```php
// app/Exports/GradesExport.php
<?php

namespace App\Exports;

use App\Models\Submission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GradesExport implements FromCollection, WithHeadings
{
    protected $classId;
    protected $subjectId;
    
    public function __construct($classId, $subjectId)
    {
        $this->classId = $classId;
        $this->subjectId = $subjectId;
    }
    
    public function collection()
    {
        return Submission::query()
            ->whereHas('assignment', function ($q) {
                $q->where('class_id', $this->classId)
                  ->where('subject_id', $this->subjectId);
            })
            ->with(['student', 'assignment'])
            ->get()
            ->map(function ($submission) {
                return [
                    'NIS' => $submission->student->studentProfile->student_id_number,
                    'Nama' => $submission->student->name,
                    'Tugas' => $submission->assignment->title,
                    'Nilai' => $submission->score ?? 'Belum dinilai',
                    'Tanggal Submit' => $submission->submitted_at->format('d/m/Y H:i'),
                ];
            });
    }
    
    public function headings(): array
    {
        return ['NIS', 'Nama', 'Tugas', 'Nilai', 'Tanggal Submit'];
    }
}

// Controller usage
use App\Exports\GradesExport;
use Maatwebsite\Excel\Facades\Excel;

public function exportGrades($classId, $subjectId)
{
    return Excel::download(
        new GradesExport($classId, $subjectId),
        'nilai_kelas_' . $classId . '.xlsx'
    );
}
```

---

#### C. PDF Generation
```bash
composer require barryvdh/laravel-dompdf
```

**Usage Example:**
```php
use Barryvdh\DomPDF\Facade\Pdf;

public function downloadReportCard($studentId)
{
    $student = User::with(['studentProfile', 'submissions.assignment'])
        ->findOrFail($studentId);
    
    $pdf = Pdf::loadView('pdf.report-card', [
        'student' => $student,
        'grades' => $this->calculateGrades($student),
    ]);
    
    return $pdf->download('raport_' . $student->name . '.pdf');
}
```

---

#### D. Activity Logging
```bash
composer require spatie/laravel-activitylog
```

**Configuration:**
```php
// config/activitylog.php
return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
    'log_name' => 'default',
    'subject_returns_soft_deleted_models' => false,
];

// Usage in Model
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Assignment extends Model
{
    use LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'deadline', 'max_score'])
            ->logOnlyDirty();
    }
}

// Query logs
$lastActivities = Activity::all()->last();
```

---

#### E. Job Queue (Laravel Horizon)
```bash
composer require laravel/horizon

php artisan horizon:install
```

**Configuration:**
```php
// config/horizon.php
return [
    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default', 'notifications', 'ai'],
                'balance' => 'auto',
                'processes' => 10,
                'tries' => 3,
            ],
        ],
    ],
];
```

**Job Example:**
```php
// app/Jobs/SendAssignmentNotification.php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Assignment;
use App\Notifications\NewAssignmentNotification;

class SendAssignmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $assignment;
    
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }
    
    public function handle()
    {
        // Get all students in class
        $students = $this->assignment->class->students;
        
        foreach ($students as $student) {
            $student->notify(new NewAssignmentNotification($this->assignment));
        }
    }
}

// Dispatch job
SendAssignmentNotification::dispatch($assignment);
```

---

## 3. Frontend Stack

### Blade Templating + Tailwind CSS

```json
{
  "templating": "Blade (Laravel default)",
  "css": "Tailwind CSS v4",
  "javascript": "Alpine.js (lightweight reactivity)",
  "icons": "Heroicons",
  "charts": "Chart.js"
}
```

**Installation:**
```bash
# Install Tailwind CSS
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# Install Alpine.js
npm install alpinejs

# Install Chart.js
npm install chart.js
```

**Tailwind Config:**
```javascript
// tailwind.config.js
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
        },
      },
      fontFamily: {
        sans: ['Inter var', 'sans-serif'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
```

---

### Alpine.js Examples

```html
<!-- Dropdown Component -->
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="btn btn-primary">
        Menu
    </button>
    
    <div x-show="open" 
         @click.away="open = false"
         class="absolute mt-2 w-48 bg-white rounded shadow-lg">
        <a href="#" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
        <a href="#" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
    </div>
</div>

<!-- Modal Component -->
<div x-data="{ open: false }">
    <button @click="open = true">Open Modal</button>
    
    <div x-show="open" 
         x-transition
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg">
            <h2 class="text-xl font-bold">Modal Title</h2>
            <p>Modal content here...</p>
            <button @click="open = false" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>
```

---

### Chart.js Integration

```html
<!-- resources/views/analytics/student.blade.php -->
<canvas id="gradesChart" width="400" height="200"></canvas>

<script>
const ctx = document.getElementById('gradesChart').getContext('2d');
const gradesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($months) !!},
        datasets: [{
            label: 'Rata-rata Nilai',
            data: {!! json_encode($averageScores) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top',
            },
            title: {
                display: true,
                text: 'Perkembangan Nilai Per Bulan'
            }
        }
    }
});
</script>
```

---

## 4. AI Integration

### OpenRouter Configuration

```env
# .env
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxx
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1
OPENROUTER_MODEL=anthropic/claude-3.5-sonnet
```

**Service Implementation:**
```php
// config/services.php
return [
    // ... other services
    
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model' => env('OPENROUTER_MODEL', 'anthropic/claude-3.5-sonnet'),
    ],
];
```

**HTTP Client Setup:**
```php
// app/Services/AIService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    
    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->baseUrl = config('services.openrouter.base_url');
        $this->model = config('services.openrouter.model');
    }
    
    public function chat(string $prompt, array $messages = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => 'PSSM AI Assistant',
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => $this->model,
            'messages' => array_merge([
                ['role' => 'system', 'content' => 'You are an expert Indonesian high school teacher.'],
            ], $messages, [
                ['role' => 'user', 'content' => $prompt],
            ]),
            'max_tokens' => 4000,
            'temperature' => 0.7,
        ]);
        
        if ($response->failed()) {
            throw new \Exception('AI API request failed: ' . $response->body());
        }
        
        return $response->json();
    }
}
```

---

## 5. Notification Channels

### Email (Mailtrap)

**Free Tier:** 500 emails/month  
**Configuration:**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@pssm.school
MAIL_FROM_NAME="${APP_NAME}"
```

**Notification Example:**
```php
// app/Notifications/NewAssignmentNotification.php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Assignment;

class NewAssignmentNotification extends Notification
{
    use Queueable;
    
    public $assignment;
    
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }
    
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tugas Baru: ' . $this->assignment->title)
            ->line('Anda mendapat tugas baru dari ' . $this->assignment->teacher->name)
            ->line('Mata Pelajaran: ' . $this->assignment->subject->name)
            ->line('Deadline: ' . $this->assignment->deadline->format('d M Y, H:i'))
            ->action('Lihat Tugas', url('/assignments/' . $this->assignment->id))
            ->line('Jangan lupa kerjakan tepat waktu!');
    }
    
    public function toArray($notifiable)
    {
        return [
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'deadline' => $this->assignment->deadline,
        ];
    }
}
```

---

### WhatsApp (Fonnte API)

**Free Tier:** 100 messages/month  
**Configuration:**
```env
FONNTE_API_KEY=your_fonnte_api_key
FONNTE_BASE_URL=https://api.fonnte.com
```

**Service Implementation:**
```php
// app/Services/WhatsAppService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    private string $apiKey;
    private string $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.fonnte.api_key');
        $this->baseUrl = config('services.fonnte.base_url');
    }
    
    public function sendMessage(string $phoneNumber, string $message): bool
    {
        // Fonnte expects phone number without +
        $phoneNumber = str_replace('+', '', $phoneNumber);
        
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->post($this->baseUrl . '/send', [
            'target' => $phoneNumber,
            'message' => $message,
        ]);
        
        return $response->successful();
    }
}

// Usage
$whatsapp = new WhatsAppService();
$whatsapp->sendMessage('628123456789', 'Anda punya tugas baru: Matematika - Soal Integral');
```

---

## 6. File Storage

### Local Storage (Development)

```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    
    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'visibility' => 'private',
    ],
],
```

**Create Symbolic Link:**
```bash
php artisan storage:link
```

---

### S3-Compatible Storage (Production)

**Options:**
- AWS S3 (expensive)
- **Cloudflare R2** (free 10GB) ← Recommended
- Wasabi (cheap alternative)

```env
# Cloudflare R2 configuration
FILESYSTEM_DISK=r2

AWS_ACCESS_KEY_ID=your_r2_access_key
AWS_SECRET_ACCESS_KEY=your_r2_secret_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your_bucket_name
AWS_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com
AWS_URL=https://your_public_r2_domain.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

---

## 7. Development Tools

### Laravel Telescope (Debugging)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Access:** http://localhost/telescope

**Features:**
- Request logs
- Query logs (detect N+1 problems)
- Exception tracking
- Job monitoring
- Cache metrics

---

### Laravel Pint (Code Formatting)

```bash
composer require laravel/pint --dev

# Run formatter
./vendor/bin/pint
```

---

### PHPUnit (Testing)

```bash
# Run tests
php artisan test

# With coverage
php artisan test --coverage
```

**Example Test:**
```php
// tests/Feature/AssignmentTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_teacher_can_create_assignment(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        
        $response = $this->actingAs($teacher)->post('/assignments', [
            'title' => 'Test Assignment',
            'description' => 'Test Description',
            'deadline' => now()->addDays(7)->toDateTimeString(),
            'class_id' => 1,
            'subject_id' => 1,
            'max_score' => 100,
        ]);
        
        $response->assertStatus(302); // Redirect after success
        $this->assertDatabaseHas('assignments', [
            'title' => 'Test Assignment',
            'teacher_id' => $teacher->id,
        ]);
    }
}
```

---

## 8. Zero-Cost Infrastructure

### Deployment Options

#### Option A: VPS (Railway.app)
```yaml
Cost: $0 (500 hours/month free)
Specs:
  - 512MB RAM
  - 1GB disk
  - Unlimited bandwidth

Pros:
  - Full control
  - Support Docker
  - Auto-deploy from GitHub

Cons:
  - Need to manage server
  - 500 hours = ~20 days (not full month)
```

**Setup:**
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Initialize project
railway init

# Deploy
railway up
```

---

#### Option B: Serverless (Vercel + Supabase)
```yaml
Frontend: Vercel (free tier)
Database: Supabase (500MB free)
Files: Cloudflare R2 (10GB free)

Pros:
  - Zero maintenance
  - Auto-scaling
  - Global CDN

Cons:
  - Cold starts (serverless functions)
  - Limited to stateless apps
```

**Note:** Laravel on Vercel requires Laravel Vapor (paid), so **Railway is recommended**.

---

### Database Hosting

#### Supabase (Recommended)
```yaml
Free Tier:
  - 500MB database
  - Unlimited API requests
  - Real-time subscriptions
  - Auto backups (7 days)

Connection:
  Host: db.xxx.supabase.co
  Port: 5432
  Database: postgres
  SSL: Required
```

---

### Redis Hosting

#### Upstash Redis
```yaml
Free Tier:
  - 10,000 commands/day
  - 256MB storage
  - Global replication

Connection:
  Host: redis-xxxxx.upstash.io
  Port: 6379
  Password: xxxxx
  TLS: Required
```

---

## 9. Environment Setup Checklist

### Local Development

```bash
# 1. Clone repository
git clone https://github.com/your-repo/pssm.git
cd pssm

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate app key
php artisan key:generate

# 6. Create database
createdb pssm_db

# 7. Run migrations
php artisan migrate

# 8. Seed database
php artisan db:seed

# 9. Build assets
npm run dev

# 10. Start server
php artisan serve

# 11. Start queue worker (separate terminal)
php artisan queue:work

# 12. Start Horizon (optional)
php artisan horizon
```

---

### Production Deployment

```bash
# 1. Set production environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pssm.school

# 2. Optimize autoloader
composer install --optimize-autoloader --no-dev

# 3. Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Build production assets
npm run build

# 5. Migrate database
php artisan migrate --force

# 6. Restart queue workers
php artisan queue:restart

# 7. Clear application cache
php artisan cache:clear
```

---

## 10. Performance Benchmarks

### Target Metrics

```yaml
Page Load Time:
  - Home: <1s
  - Dashboard: <1.5s
  - Assignment List: <1s

API Response Time:
  - GET /api/assignments: <200ms
  - POST /api/assignments: <300ms
  - AI Quiz Generation: <10s

Database Query:
  - N+1 Prevention: Mandatory
  - Slow Query Threshold: >100ms
  - Index Coverage: >90%

Concurrent Users:
  - Target: 500 users
  - Database Connections: 20 pool size
  - Redis Connections: 10 pool size
```

---

## 11. Security Checklist

```yaml
Application Security:
  - [x] HTTPS enforced
  - [x] CSRF protection enabled
  - [x] SQL injection prevention (Eloquent ORM)
  - [x] XSS protection (Blade auto-escape)
  - [x] Rate limiting (100 req/min)
  - [x] Password hashing (bcrypt)
  - [x] File upload validation (whitelist extensions)

Data Security:
  - [x] Database encryption at rest
  - [x] Sensitive data encrypted (personal info)
  - [x] Backup strategy (daily automated)
  - [x] GDPR compliance (data deletion)

Access Control:
  - [x] Role-based access control (RBAC)
  - [x] Session timeout (24 hours)
  - [x] Password reset flow (email verification)
  - [x] Activity logging (audit trail)
```

---

## 12. Monitoring Stack

### Error Tracking

**Sentry (Free Tier):**
```bash
composer require sentry/sentry-laravel

php artisan sentry:publish --dsn=your_sentry_dsn
```

**Configuration:**
```env
SENTRY_LARAVEL_DSN=https://xxxxx@o000000.ingest.sentry.io/0000000
SENTRY_TRACES_SAMPLE_RATE=0.1
```

---

### Application Monitoring

**Laravel Telescope (Development):**
- Installed by default
- Access: /telescope

**Laravel Pulse (Production):**
```bash
composer require laravel/pulse

php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
```

---

## 13. Cost Breakdown

### Development (Local)

```yaml
Hardware: Already owned ($0)
Software:
  - PHP: Free
  - PostgreSQL: Free
  - Redis: Free
  - Composer: Free
  - Node.js: Free
  
Total: $0
```

---

### Production (Month 1-3)

```yaml
Hosting:
  - Railway.app: $0 (free tier)
  - OR VPS (1GB RAM): $5/month (Hetzner Cloud)

Database:
  - Supabase: $0 (free 500MB)

Cache:
  - Upstash Redis: $0 (free tier)

Storage:
  - Cloudflare R2: $0 (free 10GB)

Email:
  - Mailtrap: $0 (free 500/month)

WhatsApp:
  - Fonnte: $0 (free 100 messages)

AI:
  - OpenRouter: $5 free credits (then pay-as-you-go)

Domain:
  - .sch.id: Rp 50k/year (~$3)

SSL Certificate:
  - Let's Encrypt: Free

Total: $0-8/month
```

---

### Production (Scale: 1,000 students)

```yaml
Hosting: $20/month (Railway Pro)
Database: $25/month (Supabase Pro - 8GB)
Cache: $10/month (Upstash paid tier)
AI API: $50/month (heavy usage)
WhatsApp: $30/month (Fonnte 1k messages)

Total: ~$135/month

Revenue Potential:
  - School subscription: $500-1,000/month
  - Profit margin: $365-865/month
```

---

## 14. Quick Start Commands

```bash
# Fresh Installation
git clone https://github.com/your-repo/pssm.git
cd pssm
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run dev
php artisan serve

# Development Workflow
php artisan make:model Assignment -mfsc  # Model + migration + factory + seeder + controller
php artisan make:service AssignmentService
php artisan make:request StoreAssignmentRequest
php artisan make:policy AssignmentPolicy

# Testing
php artisan test
php artisan test --filter AssignmentTest

# Production Deploy
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
php artisan queue:restart
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-03-16  
**Status:** Ready for Development ✅  
**Estimated Setup Time:** 2-4 hours  
**Total Project Cost (Month 1-3):** **$0-8** 🎉
