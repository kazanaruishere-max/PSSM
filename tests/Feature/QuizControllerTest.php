<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class QuizControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private User $student;
    private Classes $class;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->teacher = User::factory()->create(['is_active' => true]);
        $this->teacher->assignRole('teacher');

        $this->student = User::factory()->create(['is_active' => true]);
        $this->student->assignRole('student');

        $this->subject = Subject::create(['name' => 'Math', 'code' => 'M101']);
        
        $academicYear = \App\Models\AcademicYear::create([
            'name' => '2025/2026 Ganjil',
            'year' => '2025/2026', 
            'semester' => 'ghanjil',
            'start_date' => Carbon::now()->subMonths(1),
            'end_date' => Carbon::now()->addMonths(5),
            'is_active' => true
        ]);
        
        $this->class = Classes::create([
            'name' => 'X-A',
            'grade_level' => '10',
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $this->teacher->id
        ]);

        $this->class->students()->attach($this->student->id);
    }

    public function test_student_cannot_take_quiz_outside_time_window()
    {
        $quiz = Quiz::create([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Future Quiz',
            'description' => 'Test',
            'duration_minutes' => 60,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(2),
            'max_attempts' => 1,
            'max_score' => 100,
            'is_ai_generated' => false,
        ]);

        $response = $this->actingAs($this->student)
            ->post(route('quizzes.take', $quiz));

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'student_id' => $this->student->id
        ]);
    }

    public function test_student_cannot_exceed_max_attempts()
    {
        $quiz = Quiz::create([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Active Quiz',
            'description' => 'Test',
            'duration_minutes' => 60,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->addDays(2),
            'max_attempts' => 1,
            'max_score' => 100,
            'is_ai_generated' => false,
        ]);

        // Create one attempt
        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => now(),
            'submitted_at' => now(), // already finished once
            'score' => 50,
        ]);

        // Try to take again
        $response = $this->actingAs($this->student)
            ->post(route('quizzes.take', $quiz));

        $response->assertSessionHasErrors();
        $this->assertEquals(1, QuizAttempt::where('quiz_id', $quiz->id)->count());
    }

    public function test_student_can_take_quiz_and_submit_answers()
    {
        $quiz = Quiz::create([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Active Quiz',
            'description' => 'Test',
            'duration_minutes' => 60,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->addDays(2),
            'max_attempts' => 1,
            'max_score' => 100,
            'is_ai_generated' => false,
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => '1+1=?',
            'question_type' => 'multiple_choice',
            'options' => ['A' => '1', 'B' => '2', 'C' => '3'],
            'correct_answer_hash' => Hash::make('B'),
            'points' => 100,
            'order_number' => 1
        ]);

        // 1. Start Attempt
        $response = $this->actingAs($this->student)
            ->post(route('quizzes.take', $quiz));

        $attempt = QuizAttempt::latest()->first();
        $response->assertRedirect(route('quizzes.active', [$quiz, $attempt]));

        // 2. View active quiz
        $viewResponse = $this->actingAs($this->student)
            ->get(route('quizzes.active', [$quiz, $attempt]));
        
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('1+1=?');
        
        // 3. Submit Answers
        $submitResponse = $this->actingAs($this->student)
            ->post(route('quizzes.submit', [$quiz, $attempt]), [
                'answers' => [
                    $question->id => 'B' // Correct answer
                ]
            ]);

        $submitResponse->assertRedirect(route('quizzes.show', $quiz));
        $submitResponse->assertSessionHas('success');

        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attempt->id,
            'score' => 100, // 100% Correct
        ]);
        
        // Verify submitted_at is set
        $this->assertNotNull($attempt->fresh()->submitted_at);
    }
}
