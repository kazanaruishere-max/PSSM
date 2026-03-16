# PSSM - Project Structure
## Complete Laravel Folder Architecture v1.0

**Framework:** Laravel 11.x  
**Pattern:** MVC + Service Layer + Repository

---

## Full Project Tree

```
pssm/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── RefreshDashboardStats.php      # Cron: dashboard cache refresh
│   │       └── VerifyBackup.php               # Cron: backup verification
│   │
│   ├── Exceptions/
│   │   ├── AIServiceException.php             # Custom: AI API errors
│   │   └── BusinessException.php              # Custom: business logic errors
│   │
│   ├── Exports/
│   │   ├── GradesExport.php                   # Excel: export nilai
│   │   └── AttendanceExport.php               # Excel: export absensi
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   └── PasswordResetController.php
│   │   │   │
│   │   │   ├── DashboardController.php        # Multi-role dashboard
│   │   │   ├── AssignmentController.php       # CRUD tugas
│   │   │   ├── SubmissionController.php       # Submit & download tugas
│   │   │   ├── QuizController.php             # CRUD kuis
│   │   │   ├── QuizAttemptController.php      # Mengerjakan kuis
│   │   │   ├── GradingController.php          # Penilaian
│   │   │   ├── AttendanceController.php       # Absensi
│   │   │   ├── AnnouncementController.php     # Pengumuman
│   │   │   ├── AnalyticsController.php        # Dashboard analytics
│   │   │   ├── ExportController.php           # Export Excel/PDF
│   │   │   ├── ClassController.php            # Manajemen kelas
│   │   │   ├── UserController.php             # Manajemen user (admin)
│   │   │   └── NotificationController.php     # Notifikasi in-app
│   │   │
│   │   ├── Middleware/
│   │   │   ├── SecurityHeaders.php            # X-Frame, CSP, HSTS
│   │   │   ├── BruteForceProtection.php       # Login throttle + lockout
│   │   │   ├── VerifyResourceOwnership.php    # IDOR protection
│   │   │   └── TrackActivity.php              # Audit trail logging
│   │   │
│   │   └── Requests/
│   │       ├── Auth/
│   │       │   └── RegisterRequest.php        # Password validation
│   │       ├── StoreAssignmentRequest.php
│   │       ├── SubmitAssignmentRequest.php     # File MIME validation
│   │       ├── GradeSubmissionRequest.php      # Dynamic max_score
│   │       ├── StoreQuizRequest.php
│   │       ├── SubmitQuizRequest.php
│   │       ├── RecordAttendanceRequest.php
│   │       └── ImportStudentsRequest.php       # CSV validation
│   │
│   ├── Models/
│   │   ├── User.php                           # SoftDeletes, HasRoles
│   │   ├── StudentProfile.php                 # Encrypted attributes
│   │   ├── TeacherProfile.php                 # Encrypted phone
│   │   ├── AcademicYear.php
│   │   ├── Subject.php
│   │   ├── Classes.php                        # "Class" is reserved in PHP
│   │   ├── Assignment.php                     # SoftDeletes, LogsActivity
│   │   ├── Submission.php                     # SoftDeletes, versioning
│   │   ├── Quiz.php                           # SoftDeletes
│   │   ├── QuizQuestion.php                   # Hashed correct_answer
│   │   ├── QuizAttempt.php
│   │   ├── Attendance.php
│   │   ├── Announcement.php
│   │   └── Notification.php
│   │
│   ├── Notifications/
│   │   ├── NewAssignmentNotification.php
│   │   ├── SubmissionGradedNotification.php
│   │   ├── DeadlineReminderNotification.php
│   │   ├── QuizAvailableNotification.php
│   │   └── SecurityAlertNotification.php      # Admin: brute-force alert
│   │
│   ├── Observers/
│   │   ├── SubmissionObserver.php             # Cache invalidation
│   │   └── AssignmentObserver.php             # Notification trigger
│   │
│   ├── Policies/
│   │   ├── AssignmentPolicy.php               # view, create, update, delete
│   │   ├── SubmissionPolicy.php               # grade, view, download
│   │   ├── QuizPolicy.php
│   │   ├── StudentPolicy.php                  # viewGrades (IDOR protection)
│   │   └── ClassPolicy.php
│   │
│   ├── Services/
│   │   ├── AIService.php                      # OpenRouter: sanitize, anonymize
│   │   ├── QuizService.php                    # start, submit, autoGrade
│   │   ├── SubmissionService.php              # submit, versioning, file upload
│   │   ├── ExportService.php                  # Excel, PDF, streaming CSV
│   │   ├── NotificationService.php            # Multi-channel dispatch
│   │   ├── WhatsAppService.php                # Fonnte API integration
│   │   └── DashboardService.php               # Cached stats provider
│   │
│   ├── Jobs/
│   │   ├── SendAssignmentNotification.php     # Queue: notifications
│   │   ├── GenerateAIQuiz.php                 # Queue: ai
│   │   ├── GenerateAIFeedback.php             # Queue: ai
│   │   └── ExportLargeDataset.php             # Queue: exports
│   │
│   └── Listeners/
│       └── SecurityEventListener.php          # Failed login monitoring
│
├── bootstrap/
│   └── app.php                                # Middleware, exceptions config
│
├── config/
│   ├── app.php                                # timezone: Asia/Jakarta
│   ├── auth.php                               # Password rules
│   ├── cors.php                               # CORS policy (strict)
│   ├── database.php                           # PostgreSQL + SSL
│   ├── filesystems.php                        # Local, private, R2
│   ├── horizon.php                            # Queue workers config
│   ├── permission.php                         # Spatie RBAC
│   ├── services.php                           # OpenRouter, Fonnte
│   ├── session.php                            # Encrypted, secure cookies
│   └── telescope.php                          # Slow query threshold
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── AssignmentFactory.php
│   │   ├── SubmissionFactory.php
│   │   └── QuizFactory.php
│   │
│   ├── migrations/
│   │   ├── 0001_create_users_table.php
│   │   ├── 0002_create_student_profiles_table.php
│   │   ├── 0003_create_teacher_profiles_table.php
│   │   ├── 0004_create_academic_years_table.php
│   │   ├── 0005_create_subjects_table.php
│   │   ├── 0006_create_classes_table.php
│   │   ├── 0007_create_class_student_table.php
│   │   ├── 0008_create_class_subject_table.php
│   │   ├── 0009_create_assignments_table.php
│   │   ├── 0010_create_submissions_table.php
│   │   ├── 0011_create_quizzes_table.php
│   │   ├── 0012_create_quiz_questions_table.php
│   │   ├── 0013_create_quiz_attempts_table.php
│   │   ├── 0014_create_attendances_table.php
│   │   ├── 0015_create_announcements_table.php
│   │   ├── 0016_create_notifications_table.php
│   │   └── 0017_create_activity_logs_table.php
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php                 # Master seeder
│       ├── RoleAndPermissionSeeder.php        # 4 roles, 27 permissions
│       ├── AdminUserSeeder.php                # Default super admin
│       ├── SubjectSeeder.php                  # Mapel default
│       └── DemoDataSeeder.php                 # Data demo (dev only)
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php                  # Main layout (auth users)
│   │   │   ├── guest.blade.php                # Login/register layout
│   │   │   └── partials/
│   │   │       ├── sidebar.blade.php
│   │   │       ├── topbar.blade.php
│   │   │       └── footer.blade.php
│   │   │
│   │   ├── components/
│   │   │   ├── alert.blade.php
│   │   │   ├── button.blade.php
│   │   │   ├── card.blade.php
│   │   │   ├── modal.blade.php
│   │   │   ├── badge.blade.php
│   │   │   ├── table.blade.php
│   │   │   └── chart.blade.php
│   │   │
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   ├── register.blade.php
│   │   │   ├── forgot-password.blade.php
│   │   │   └── reset-password.blade.php
│   │   │
│   │   ├── dashboard/
│   │   │   ├── super-admin.blade.php
│   │   │   ├── teacher.blade.php
│   │   │   ├── student.blade.php
│   │   │   └── class-leader.blade.php
│   │   │
│   │   ├── assignments/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── show.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── submit.blade.php
│   │   │
│   │   ├── quizzes/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── ai-generate.blade.php
│   │   │   ├── take.blade.php
│   │   │   └── results.blade.php
│   │   │
│   │   ├── analytics/
│   │   │   ├── student.blade.php
│   │   │   ├── class.blade.php
│   │   │   └── school.blade.php
│   │   │
│   │   ├── attendance/
│   │   │   ├── index.blade.php
│   │   │   └── record.blade.php
│   │   │
│   │   ├── announcements/
│   │   │   ├── index.blade.php
│   │   │   └── create.blade.php
│   │   │
│   │   ├── users/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── import.blade.php
│   │   │
│   │   ├── exports/
│   │   │   └── report-card.blade.php          # PDF template raport
│   │   │
│   │   └── errors/
│   │       ├── 403.blade.php
│   │       ├── 404.blade.php
│   │       ├── 500.blade.php
│   │       └── 503.blade.php                  # Maintenance mode
│   │
│   ├── css/
│   │   └── app.css                            # Tailwind imports
│   │
│   └── js/
│       ├── app.js                             # Alpine.js init
│       └── chart-config.js                    # Chart.js defaults
│
├── routes/
│   ├── web.php                                # Blade routes (auth, dashboard)
│   ├── api.php                                # REST API routes
│   └── console.php                            # Scheduled commands
│
├── storage/
│   ├── app/
│   │   ├── public/                            # Public files (avatars)
│   │   └── private/                           # Private files (submissions)
│   │       └── submissions/
│   │           └── {assignment_id}/
│   │               └── {uuid}.pdf
│   ├── framework/
│   └── logs/
│       ├── laravel.log
│       └── security.log                       # Security events
│
├── tests/
│   ├── Unit/
│   │   ├── Services/
│   │   ├── Models/
│   │   ├── Middleware/
│   │   └── Helpers/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Assignment/
│   │   ├── Quiz/
│   │   ├── Attendance/
│   │   ├── Dashboard/
│   │   └── Security/
│   └── Browser/                               # Laravel Dusk (optional)
│
├── docs/                                      # ← Dokumentasi project
│   ├── 01-product/
│   │   └── PSSM-PRD.md
│   ├── 02-architecture/
│   │   ├── PSSM-DesignDoc.md
│   │   ├── PSSM-TechStack.md
│   │   └── PSSM-DatabaseDictionary.md
│   ├── 03-development/
│   │   ├── PSSM-MasterWorkflow.md
│   │   ├── PSSM-APIReference.md
│   │   ├── PSSM-TestingStrategy.md
│   │   └── PSSM-ProjectStructure.md
│   ├── 04-security/
│   │   ├── PSSM-SecurityHardening.md
│   │   └── PSSM-DisasterRecovery.md
│   └── 05-guides/
│       └── PSSM-UserGuide.md
│
├── nginx/
│   └── conf.d/
│       ├── default.conf                       # Main server config
│       └── security.conf                      # Rate limit, headers
│
├── .github/
│   └── workflows/
│       └── ci.yml                             # GitHub Actions CI/CD
│
├── .env.example                               # Template environment
├── .env.testing                               # Test environment
├── .gitignore
├── composer.json
├── docker-compose.yml
├── Dockerfile
├── package.json
├── tailwind.config.js
├── vite.config.js
├── phpunit.xml
├── deploy.sh                                  # Production deploy script
└── README.md                                  # Project entry point
```

---

## Layer Architecture Map

```
Request Flow:

Browser → Nginx → Route → Middleware → Controller → FormRequest
                                            ↓
                                        Service Layer → Repository/Eloquent → Database
                                            ↓
                                        Response (Blade view / JSON)
                                            ↓
                                        Observer → Job Queue → Notification
```

| Layer | Folder | Responsibility |
|-------|--------|----------------|
| **Routes** | `routes/` | URL mapping & middleware assignment |
| **Middleware** | `app/Http/Middleware/` | Auth, security, rate limiting |
| **Controllers** | `app/Http/Controllers/` | Request handling, response formatting |
| **Form Requests** | `app/Http/Requests/` | Input validation & authorization |
| **Services** | `app/Services/` | Business logic (AI, export, quiz) |
| **Models** | `app/Models/` | Data structure, relationships, accessors |
| **Policies** | `app/Policies/` | Authorization rules per resource |
| **Observers** | `app/Observers/` | Model event hooks (cache invalidation) |
| **Jobs** | `app/Jobs/` | Async tasks (notifications, AI calls) |
| **Notifications** | `app/Notifications/` | Email, WhatsApp, in-app messages |
| **Exports** | `app/Exports/` | Excel/PDF generation |

---

## Naming Conventions

| Item | Convention | Contoh |
|------|-----------|--------|
| Model | Singular PascalCase | `Assignment`, `QuizAttempt` |
| Controller | PascalCase + `Controller` | `AssignmentController` |
| Service | PascalCase + `Service` | `AIService`, `QuizService` |
| Request | Verb + PascalCase + `Request` | `StoreAssignmentRequest` |
| Policy | PascalCase + `Policy` | `AssignmentPolicy` |
| Job | Verb + PascalCase | `SendAssignmentNotification` |
| Migration | `create_{table}_table` | `create_assignments_table` |
| Seeder | PascalCase + `Seeder` | `RoleAndPermissionSeeder` |
| Factory | PascalCase + `Factory` | `AssignmentFactory` |
| Test | PascalCase + `Test` | `AssignmentTest`, `IDORTest` |
| View | kebab-case | `super-admin.blade.php` |
| Route | kebab-case | `/api/quiz-attempts` |

---

## File Count Summary

| Category | Count |
|----------|:-----:|
| Controllers | 14 |
| Models | 14 |
| Services | 7 |
| Middleware | 4 |
| Form Requests | 8 |
| Policies | 5 |
| Jobs | 4 |
| Notifications | 5 |
| Migrations | 17 |
| Blade Views | ~40 |
| Tests | ~30 |
| **Total PHP files** | **~150** |

---

**Document Version:** 1.0 | **Last Updated:** 2026-03-16
