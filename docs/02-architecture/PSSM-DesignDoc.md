# PSSM - Design Document
## Technical Architecture & Implementation Guide

**Framework:** Laravel 11  
**PHP Version:** 8.3+  
**Database:** PostgreSQL 16  
**Cache:** Redis 7  
**Queue:** Laravel Horizon (Redis-backed)

---

## 1. System Architecture

### High-Level Architecture

```
┌──────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                           │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐         │
│  │  Browser   │  │   Mobile   │  │  WhatsApp  │         │
│  │  (Blade)   │  │  (Future)  │  │   Bot      │         │
│  └─────┬──────┘  └─────┬──────┘  └─────┬──────┘         │
└────────┼────────────────┼────────────────┼────────────────┘
         │                │                │
         └────────────────┴────────────────┘
                          │
┌─────────────────────────▼────────────────────────────────┐
│                  WEB SERVER (Nginx)                       │
│  ┌────────────────────────────────────────────────────┐  │
│  │  Static Assets (CSS, JS, Images)                   │  │
│  │  CDN: Cloudflare (Free Tier)                       │  │
│  └────────────────────────────────────────────────────┘  │
└─────────────────────────┬────────────────────────────────┘
                          │
┌─────────────────────────▼────────────────────────────────┐
│              APPLICATION LAYER (Laravel)                  │
│                                                           │
│  ┌──────────────────────────────────────────────────┐   │
│  │  Route Layer (web.php, api.php)                  │   │
│  └────────────┬─────────────────────────────────────┘   │
│               │                                           │
│  ┌────────────▼─────────────────────────────────────┐   │
│  │  Middleware Layer                                │   │
│  │  ├─ Authentication (Sanctum)                     │   │
│  │  ├─ Authorization (Gates, Policies)              │   │
│  │  ├─ CSRF Protection                              │   │
│  │  ├─ Rate Limiting (100 req/min)                  │   │
│  │  └─ Logging (Monolog)                            │   │
│  └────────────┬─────────────────────────────────────┘   │
│               │                                           │
│  ┌────────────▼─────────────────────────────────────┐   │
│  │  Controller Layer                                │   │
│  │  ├─ AssignmentController                         │   │
│  │  ├─ QuizController                               │   │
│  │  ├─ GradingController                            │   │
│  │  └─ AnalyticsController                          │   │
│  └────────────┬─────────────────────────────────────┘   │
│               │                                           │
│  ┌────────────▼─────────────────────────────────────┐   │
│  │  Service Layer (Business Logic)                  │   │
│  │  ├─ AssignmentService                            │   │
│  │  ├─ AIService (OpenRouter integration)           │   │
│  │  ├─ NotificationService                          │   │
│  │  └─ ExportService (Excel, PDF)                   │   │
│  └────────────┬─────────────────────────────────────┘   │
│               │                                           │
│  ┌────────────▼─────────────────────────────────────┐   │
│  │  Repository Layer (Data Access)                  │   │
│  │  ├─ AssignmentRepository                         │   │
│  │  ├─ UserRepository                               │   │
│  │  └─ Eloquent ORM                                 │   │
│  └────────────┬─────────────────────────────────────┘   │
└───────────────┼───────────────────────────────────────────┘
                │
┌───────────────▼───────────────────────────────────────────┐
│                  DATA LAYER                               │
│                                                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
│  │ PostgreSQL   │  │    Redis     │  │  S3/Local    │   │
│  │ (Primary DB) │  │   (Cache)    │  │  (Files)     │   │
│  └──────────────┘  └──────────────┘  └──────────────┘   │
└───────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────┐
│                  EXTERNAL SERVICES                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
│  │  OpenRouter  │  │   Mailtrap   │  │   Fonnte     │   │
│  │  (AI API)    │  │   (Email)    │  │ (WhatsApp)   │   │
│  └──────────────┘  └──────────────┘  └──────────────┘   │
└───────────────────────────────────────────────────────────┘
```

---

## 2. Database Schema Design

### Entity Relationship Diagram (ERD)

```sql
-- ============================================
-- USERS & AUTHENTICATION
-- ============================================

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL CHECK (role IN ('super_admin', 'teacher', 'class_leader', 'student')),
    avatar_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

-- Student Profile Extension
CREATE TABLE student_profiles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    student_id_number VARCHAR(50) UNIQUE NOT NULL, -- NIS
    date_of_birth DATE,
    parent_name VARCHAR(255),
    parent_phone VARCHAR(20),
    parent_email VARCHAR(255),
    address TEXT,
    enrollment_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teacher Profile Extension
CREATE TABLE teacher_profiles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    teacher_id_number VARCHAR(50) UNIQUE NOT NULL, -- NIP
    specialization VARCHAR(100), -- "Matematika", "Fisika", etc.
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- ACADEMIC STRUCTURE
-- ============================================

CREATE TABLE academic_years (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- "2024/2025"
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- "Matematika", "Fisika"
    code VARCHAR(20) UNIQUE NOT NULL, -- "MTK", "FIS"
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- "11 IPA 1"
    grade_level INT NOT NULL CHECK (grade_level BETWEEN 10 AND 12), -- 10, 11, 12
    academic_year_id BIGINT REFERENCES academic_years(id),
    homeroom_teacher_id BIGINT REFERENCES users(id), -- Wali kelas
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pivot: Class has many Students
CREATE TABLE class_student (
    id BIGSERIAL PRIMARY KEY,
    class_id BIGINT REFERENCES classes(id) ON DELETE CASCADE,
    student_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    enrollment_date DATE DEFAULT CURRENT_DATE,
    is_class_leader BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(class_id, student_id)
);

-- Pivot: Class has many Subjects with assigned Teacher
CREATE TABLE class_subject (
    id BIGSERIAL PRIMARY KEY,
    class_id BIGINT REFERENCES classes(id) ON DELETE CASCADE,
    subject_id BIGINT REFERENCES subjects(id) ON DELETE CASCADE,
    teacher_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    schedule JSON, -- {"day": "Monday", "time": "08:00-09:30"}
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(class_id, subject_id)
);

-- ============================================
-- ASSIGNMENTS & SUBMISSIONS
-- ============================================

CREATE TABLE assignments (
    id BIGSERIAL PRIMARY KEY,
    teacher_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    class_id BIGINT REFERENCES classes(id) ON DELETE CASCADE,
    subject_id BIGINT REFERENCES subjects(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    attachment_path VARCHAR(255), -- Path to uploaded file
    deadline TIMESTAMP NOT NULL,
    max_score INT DEFAULT 100,
    is_published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_assignments_deadline ON assignments(deadline);
CREATE INDEX idx_assignments_class_subject ON assignments(class_id, subject_id);

CREATE TABLE submissions (
    id BIGSERIAL PRIMARY KEY,
    assignment_id BIGINT REFERENCES assignments(id) ON DELETE CASCADE,
    student_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    content TEXT, -- For text-based submissions
    file_path VARCHAR(255), -- For file uploads
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score INT CHECK (score >= 0 AND score <= 100),
    feedback TEXT,
    ai_feedback JSON, -- {"grammar_score": 80, "suggestions": [...]}
    graded_at TIMESTAMP,
    graded_by BIGINT REFERENCES users(id),
    is_late BOOLEAN DEFAULT FALSE,
    
    UNIQUE(assignment_id, student_id)
);

CREATE INDEX idx_submissions_student ON submissions(student_id);
CREATE INDEX idx_submissions_assignment ON submissions(assignment_id);

-- ============================================
-- QUIZZES & QUESTIONS
-- ============================================

CREATE TABLE quizzes (
    id BIGSERIAL PRIMARY KEY,
    teacher_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    class_id BIGINT REFERENCES classes(id) ON DELETE CASCADE,
    subject_id BIGINT REFERENCES subjects(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration_minutes INT NOT NULL, -- Quiz duration
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    max_score INT DEFAULT 100,
    is_published BOOLEAN DEFAULT FALSE,
    is_ai_generated BOOLEAN DEFAULT FALSE, -- Track AI-generated quizzes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quiz_questions (
    id BIGSERIAL PRIMARY KEY,
    quiz_id BIGINT REFERENCES quizzes(id) ON DELETE CASCADE,
    question_type VARCHAR(20) NOT NULL CHECK (question_type IN ('multiple_choice', 'essay', 'true_false')),
    question_text TEXT NOT NULL,
    options JSON, -- For multiple choice: {"A": "...", "B": "...", "C": "...", "D": "..."}
    correct_answer VARCHAR(255), -- "A" for multiple choice, full text for essay
    explanation TEXT, -- AI-generated explanation
    points INT DEFAULT 1,
    order_number INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quiz_attempts (
    id BIGSERIAL PRIMARY KEY,
    quiz_id BIGINT REFERENCES quizzes(id) ON DELETE CASCADE,
    student_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP,
    score INT,
    answers JSON, -- {"question_id": "answer", ...}
    time_taken_seconds INT,
    
    UNIQUE(quiz_id, student_id) -- One attempt per student per quiz
);

-- ============================================
-- ATTENDANCE
-- ============================================

CREATE TABLE attendances (
    id BIGSERIAL PRIMARY KEY,
    class_id BIGINT REFERENCES classes(id) ON DELETE CASCADE,
    subject_id BIGINT REFERENCES subjects(id) ON DELETE CASCADE,
    student_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    status VARCHAR(20) NOT NULL CHECK (status IN ('present', 'absent', 'sick', 'permission')),
    notes TEXT,
    recorded_by BIGINT REFERENCES users(id), -- Teacher or Class Leader
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(class_id, subject_id, student_id, date)
);

CREATE INDEX idx_attendances_date ON attendances(date);
CREATE INDEX idx_attendances_student ON attendances(student_id);

-- ============================================
-- ANNOUNCEMENTS
-- ============================================

CREATE TABLE announcements (
    id BIGSERIAL PRIMARY KEY,
    author_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    class_id BIGINT REFERENCES classes(id) ON DELETE CASCADE, -- NULL = all school
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    priority VARCHAR(20) DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_announcements_class ON announcements(class_id);
CREATE INDEX idx_announcements_published ON announcements(published_at);

-- ============================================
-- NOTIFICATIONS
-- ============================================

CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- 'assignment_created', 'quiz_graded', etc.
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON, -- Additional metadata
    read_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notifications_user_read ON notifications(user_id, read_at);

-- ============================================
-- ACTIVITY LOGS (Audit Trail)
-- ============================================

CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL, -- 'created_assignment', 'graded_submission'
    model VARCHAR(50), -- 'Assignment', 'Submission'
    model_id BIGINT,
    ip_address INET,
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_activity_logs_user ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created ON activity_logs(created_at);
```

---

## 3. API Design

### RESTful Endpoints

#### Authentication
```
POST   /api/login              # Login user
POST   /api/logout             # Logout user
POST   /api/register           # Register new user (admin only)
POST   /api/password/reset     # Request password reset
```

#### Assignments
```
GET    /api/assignments                    # List assignments (filtered by role)
POST   /api/assignments                    # Create assignment (teacher only)
GET    /api/assignments/{id}               # Get assignment details
PUT    /api/assignments/{id}               # Update assignment
DELETE /api/assignments/{id}               # Delete assignment
GET    /api/assignments/{id}/submissions   # List submissions for assignment
POST   /api/assignments/{id}/submit        # Submit assignment (student only)
```

#### Quizzes
```
GET    /api/quizzes                # List quizzes
POST   /api/quizzes                # Create quiz
POST   /api/quizzes/generate-ai    # AI-generated quiz
GET    /api/quizzes/{id}           # Get quiz details
POST   /api/quizzes/{id}/start     # Start quiz attempt
POST   /api/quizzes/{id}/submit    # Submit quiz answers
GET    /api/quizzes/{id}/results   # Get quiz results
```

#### Grading
```
POST   /api/submissions/{id}/grade    # Grade submission
GET    /api/students/{id}/grades      # Get student grades
POST   /api/grades/export             # Export grades to Excel
```

#### Analytics
```
GET    /api/analytics/dashboard       # Dashboard stats
GET    /api/analytics/student/{id}    # Student performance
GET    /api/analytics/class/{id}      # Class performance
```

#### Attendance
```
GET    /api/attendance                    # List attendance records
POST   /api/attendance                    # Record attendance
GET    /api/attendance/student/{id}       # Student attendance history
GET    /api/attendance/export             # Export attendance report
```

---

## 4. Core Workflows

### Workflow 1: Assignment Submission

```
┌─────────┐                ┌─────────┐                ┌─────────┐
│ Teacher │                │ System  │                │ Student │
└────┬────┘                └────┬────┘                └────┬────┘
     │                          │                          │
     │ 1. Create Assignment     │                          │
     │─────────────────────────>│                          │
     │                          │                          │
     │                          │ 2. Store in DB          │
     │                          │───────────┐             │
     │                          │           │             │
     │                          │<──────────┘             │
     │                          │                          │
     │                          │ 3. Notify Students      │
     │                          │─────────────────────────>│
     │                          │   (Email + In-app)       │
     │                          │                          │
     │                          │                          │
     │                          │ 4. View Assignment      │
     │                          │<─────────────────────────│
     │                          │                          │
     │                          │ 5. Submit Work          │
     │                          │<─────────────────────────│
     │                          │   (File upload)          │
     │                          │                          │
     │                          │ 6. Store Submission     │
     │                          │───────────┐             │
     │                          │           │             │
     │                          │<──────────┘             │
     │                          │                          │
     │                          │ 7. Notify Teacher       │
     │<─────────────────────────│   (New submission)       │
     │                          │                          │
     │ 8. Review & Grade        │                          │
     │─────────────────────────>│                          │
     │                          │                          │
     │                          │ 9. Update Score         │
     │                          │───────────┐             │
     │                          │           │             │
     │                          │<──────────┘             │
     │                          │                          │
     │                          │ 10. Notify Student      │
     │                          │─────────────────────────>│
     │                          │    (Graded)              │
     │                          │                          │
```

---

### Workflow 2: AI Quiz Generation

```
┌─────────┐          ┌─────────┐          ┌───────────┐
│ Teacher │          │ System  │          │OpenRouter │
└────┬────┘          └────┬────┘          └─────┬─────┘
     │                    │                      │
     │ 1. Request AI Quiz │                      │
     │   (Topic, Count)   │                      │
     │───────────────────>│                      │
     │                    │                      │
     │                    │ 2. Build AI Prompt   │
     │                    │──────────┐           │
     │                    │          │           │
     │                    │<─────────┘           │
     │                    │                      │
     │                    │ 3. Call OpenRouter   │
     │                    │─────────────────────>│
     │                    │                      │
     │                    │                      │
     │                    │ 4. Generate Questions│
     │                    │<─────────────────────│
     │                    │   (JSON response)    │
     │                    │                      │
     │                    │ 5. Parse & Store     │
     │                    │──────────┐           │
     │                    │          │           │
     │                    │<─────────┘           │
     │                    │                      │
     │ 6. Return Quiz ID  │                      │
     │<───────────────────│                      │
     │                    │                      │
     │ 7. Review Quiz     │                      │
     │───────────────────>│                      │
     │                    │                      │
     │ 8. Publish Quiz    │                      │
     │───────────────────>│                      │
     │                    │                      │
```

---

## 5. Service Layer Design

### AIService Implementation

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AIService
{
    private string $apiKey;
    private string $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->baseUrl = 'https://openrouter.ai/api/v1';
    }
    
    /**
     * Generate quiz questions using AI
     * 
     * @param string $topic
     * @param int $questionCount
     * @param string $difficulty
     * @return array
     */
    public function generateQuiz(string $topic, int $questionCount = 10, string $difficulty = 'medium'): array
    {
        // Cache key to prevent duplicate API calls
        $cacheKey = "ai_quiz_" . md5($topic . $questionCount . $difficulty);
        
        return Cache::remember($cacheKey, 3600, function () use ($topic, $questionCount, $difficulty) {
            $prompt = $this->buildQuizPrompt($topic, $questionCount, $difficulty);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => 'anthropic/claude-3.5-sonnet',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert Indonesian high school teacher. Generate quiz questions in Bahasa Indonesia.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7,
            ]);
            
            if ($response->failed()) {
                throw new \Exception('AI API request failed: ' . $response->body());
            }
            
            $content = $response->json()['choices'][0]['message']['content'];
            
            // Parse JSON response
            return $this->parseQuizResponse($content);
        });
    }
    
    /**
     * Build prompt for quiz generation
     */
    private function buildQuizPrompt(string $topic, int $count, string $difficulty): string
    {
        return <<<PROMPT
Generate {$count} multiple choice questions about "{$topic}" for Indonesian high school students (SMA).

Difficulty level: {$difficulty}

Requirements:
1. Questions must be in Bahasa Indonesia
2. Each question has 4 options (A, B, C, D)
3. Include correct answer and brief explanation
4. Follow Indonesian curriculum standards

Return ONLY valid JSON in this format:
{
  "questions": [
    {
      "question_text": "...",
      "options": {
        "A": "...",
        "B": "...",
        "C": "...",
        "D": "..."
      },
      "correct_answer": "A",
      "explanation": "..."
    }
  ]
}
PROMPT;
    }
    
    /**
     * Parse AI response to structured array
     */
    private function parseQuizResponse(string $content): array
    {
        // Remove markdown code blocks if present
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse AI response: ' . json_last_error_msg());
        }
        
        return $data['questions'] ?? [];
    }
    
    /**
     * Generate feedback for student essay
     * 
     * @param string $essayText
     * @param string $topic
     * @return array
     */
    public function generateEssayFeedback(string $essayText, string $topic): array
    {
        $prompt = <<<PROMPT
Analyze this student essay about "{$topic}":

"{$essayText}"

Provide constructive feedback in Bahasa Indonesia covering:
1. Struktur (intro, body, conclusion)
2. Grammar dan ejaan
3. Kekuatan argumen
4. Vocabulary usage

Return JSON:
{
  "structure_score": 0-100,
  "grammar_score": 0-100,
  "argument_score": 0-100,
  "vocabulary_score": 0-100,
  "overall_score": 0-100,
  "strengths": ["..."],
  "improvements": ["..."],
  "detailed_feedback": "..."
}
PROMPT;
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => 'anthropic/claude-3.5-sonnet',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 2000,
        ]);
        
        $content = $response->json()['choices'][0]['message']['content'];
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        
        return json_decode($content, true);
    }
}
```

---

### ExportService Implementation

```php
<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GradesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    /**
     * Export grades to Excel
     * 
     * @param int $classId
     * @param int $subjectId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportGradesToExcel(int $classId, int $subjectId)
    {
        $fileName = "grades_class_{$classId}_subject_{$subjectId}_" . now()->format('Y-m-d') . ".xlsx";
        
        return Excel::download(new GradesExport($classId, $subjectId), $fileName);
    }
    
    /**
     * Export student report card to PDF
     * 
     * @param int $studentId
     * @param string $semester
     * @return \Illuminate\Http\Response
     */
    public function exportReportCard(int $studentId, string $semester)
    {
        $student = User::with(['studentProfile', 'classes'])->findOrFail($studentId);
        
        // Fetch all grades for this semester
        $grades = $this->getStudentGrades($studentId, $semester);
        
        $pdf = Pdf::loadView('exports.report-card', [
            'student' => $student,
            'grades' => $grades,
            'semester' => $semester,
        ]);
        
        return $pdf->download("raport_{$student->name}_{$semester}.pdf");
    }
    
    private function getStudentGrades(int $studentId, string $semester): array
    {
        // Complex query to aggregate all grades
        // Implementation details...
        return [];
    }
}
```

---

## 6. Frontend Architecture

### Blade Components Structure

```
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php          # Main layout
│   │   ├── guest.blade.php        # For login/register
│   │   └── dashboard.blade.php    # Dashboard layout
│   │
│   ├── components/
│   │   ├── alert.blade.php
│   │   ├── button.blade.php
│   │   ├── card.blade.php
│   │   ├── modal.blade.php
│   │   └── nav/
│   │       ├── sidebar.blade.php
│   │       └── topbar.blade.php
│   │
│   ├── dashboard/
│   │   ├── super-admin.blade.php
│   │   ├── teacher.blade.php
│   │   ├── student.blade.php
│   │   └── class-leader.blade.php
│   │
│   ├── assignments/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── show.blade.php
│   │   └── submit.blade.php
│   │
│   ├── quizzes/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── take.blade.php
│   │   └── results.blade.php
│   │
│   └── analytics/
│       ├── student.blade.php
│       ├── class.blade.php
│       └── school.blade.php
```

---

### Tailwind CSS Configuration

```javascript
// tailwind.config.js
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
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
        },
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
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

## 7. Security Considerations

### Authentication & Authorization

```php
// app/Policies/AssignmentPolicy.php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Assignment;

class AssignmentPolicy
{
    /**
     * Determine if user can view assignment
     */
    public function view(User $user, Assignment $assignment): bool
    {
        // Teachers can view their own assignments
        if ($user->role === 'teacher' && $assignment->teacher_id === $user->id) {
            return true;
        }
        
        // Students can view if they're in the class
        if ($user->role === 'student') {
            return $user->classes()->where('classes.id', $assignment->class_id)->exists();
        }
        
        // Admins can view all
        if ($user->role === 'super_admin') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determine if user can create assignment
     */
    public function create(User $user): bool
    {
        return $user->role === 'teacher' || $user->role === 'super_admin';
    }
    
    /**
     * Determine if user can update assignment
     */
    public function update(User $user, Assignment $assignment): bool
    {
        return $user->id === $assignment->teacher_id || $user->role === 'super_admin';
    }
}
```

---

### File Upload Security

```php
// app/Http/Controllers/AssignmentController.php

public function storeSubmission(Request $request, Assignment $assignment)
{
    $validated = $request->validate([
        'content' => 'nullable|string|max:10000',
        'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
    ]);
    
    // Prevent path traversal attacks
    $filePath = null;
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        
        // Generate secure filename
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Store in private storage (not publicly accessible)
        $filePath = $file->storeAs(
            'submissions/' . $assignment->id,
            $filename,
            'private' // Use private disk
        );
    }
    
    Submission::create([
        'assignment_id' => $assignment->id,
        'student_id' => auth()->id(),
        'content' => $validated['content'] ?? null,
        'file_path' => $filePath,
        'is_late' => now()->gt($assignment->deadline),
    ]);
}
```

---

## 8. Performance Optimization

### Database Query Optimization

```php
// BAD: N+1 Query Problem
$assignments = Assignment::all();
foreach ($assignments as $assignment) {
    echo $assignment->teacher->name; // Triggers 1 query per iteration
}

// GOOD: Eager Loading
$assignments = Assignment::with('teacher')->get();
foreach ($assignments as $assignment) {
    echo $assignment->teacher->name; // No additional queries
}

// EVEN BETTER: Select only needed columns
$assignments = Assignment::with('teacher:id,name')
    ->select('id', 'title', 'teacher_id', 'deadline')
    ->get();
```

---

### Redis Caching Strategy

```php
// Cache dashboard statistics (5 minutes TTL)
$stats = Cache::remember('dashboard_stats_' . $user->id, 300, function () use ($user) {
    return [
        'total_assignments' => Assignment::where('teacher_id', $user->id)->count(),
        'pending_submissions' => Submission::whereHas('assignment', function ($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->whereNull('score')->count(),
        'total_students' => $user->classes()->withCount('students')->sum('students_count'),
    ];
});

// Cache AI-generated quiz (1 hour TTL)
$quiz = Cache::remember("ai_quiz_{$topic}_{$count}", 3600, function () {
    return $this->aiService->generateQuiz($topic, $count);
});
```

---

## 9. Monitoring & Logging

### Laravel Telescope Configuration

```php
// config/telescope.php
'watchers' => [
    Watchers\QueryWatcher::class => [
        'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
        'slow' => 100, // Log queries slower than 100ms
    ],
    
    Watchers\RequestWatcher::class => [
        'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
        'size_limit' => env('TELESCOPE_RESPONSE_SIZE_LIMIT', 64),
    ],
    
    Watchers\ExceptionWatcher::class => true,
],
```

---

### Custom Error Logging

```php
// app/Exceptions/Handler.php

public function report(Throwable $exception)
{
    if ($this->shouldReport($exception)) {
        // Log to database
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'exception',
            'model' => get_class($exception),
            'ip_address' => request()->ip(),
            'metadata' => [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ],
        ]);
    }
    
    parent::report($exception);
}
```

---

## 10. Deployment Architecture

### Docker Compose Setup

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: pssm-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./storage:/var/www/storage
    networks:
      - pssm-network
    depends_on:
      - db
      - redis
  
  nginx:
    image: nginx:alpine
    container_name: pssm-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./nginx/conf.d:/etc/nginx/conf.d
    networks:
      - pssm-network
  
  db:
    image: postgres:16
    container_name: pssm-db
    restart: unless-stopped
    environment:
      POSTGRES_DB: pssm_db
      POSTGRES_USER: pssm_user
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db-data:/var/lib/postgresql/data
    networks:
      - pssm-network
  
  redis:
    image: redis:7-alpine
    container_name: pssm-redis
    restart: unless-stopped
    networks:
      - pssm-network

volumes:
  db-data:

networks:
  pssm-network:
    driver: bridge
```

---

## 11. Testing Strategy

### Unit Test Example

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AIService;

class AIServiceTest extends TestCase
{
    public function test_can_generate_quiz()
    {
        $aiService = new AIService();
        
        $questions = $aiService->generateQuiz('Photosynthesis', 5);
        
        $this->assertCount(5, $questions);
        $this->assertArrayHasKey('question_text', $questions[0]);
        $this->assertArrayHasKey('options', $questions[0]);
        $this->assertArrayHasKey('correct_answer', $questions[0]);
    }
}
```

---

### Feature Test Example

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Assignment;

class AssignmentTest extends TestCase
{
    public function test_teacher_can_create_assignment()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        
        $response = $this->actingAs($teacher)->post('/api/assignments', [
            'title' => 'Test Assignment',
            'description' => 'Test Description',
            'deadline' => now()->addDays(7),
            'class_id' => 1,
            'subject_id' => 1,
        ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('assignments', [
            'title' => 'Test Assignment',
            'teacher_id' => $teacher->id,
        ]);
    }
    
    public function test_student_cannot_create_assignment()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $response = $this->actingAs($student)->post('/api/assignments', [
            'title' => 'Unauthorized Assignment',
        ]);
        
        $response->assertStatus(403); // Forbidden
    }
}
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-03-16  
**Status:** Ready for Implementation ✅
