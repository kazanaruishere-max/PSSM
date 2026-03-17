<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
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
        
        $this->teacher = User::factory()->create(['role' => 'teacher']);
        $this->teacher->assignRole('teacher');

        $this->student = User::factory()->create(['role' => 'student']);
        $this->student->assignRole('student');

        $academicYear = AcademicYear::create([
            'name' => 'Tester',
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
        ]);

        $this->class = Classes::create([
            'name' => '10A',
            'grade_level' => 10,
            'academic_year_id' => $academicYear->id,
        ]);

        // Assign teacher to teach in $this->class (via Subject)
        $this->subject = Subject::create(['name' => 'Math', 'code' => 'MTK']);
        $this->class->subjects()->attach($this->subject->id, ['teacher_id' => $this->teacher->id]);

        // Student is in $this->class
        $this->student->classes()->attach($this->class->id);
    }

    public function test_teacher_can_access_attendance_create_form_for_own_subject()
    {
        $response = $this->actingAs($this->teacher)->get(route('attendances.create', [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('attendances.create');
        $response->assertSee($this->student->name);
    }

    public function test_teacher_cannot_access_attendance_form_for_unassigned_subject()
    {
        $otherSubject = Subject::create(['name' => 'Physics', 'code' => 'FIS']);
        // Not assigned to this teacher

        $response = $this->actingAs($this->teacher)->get(route('attendances.create', [
            'class_id' => $this->class->id,
            'subject_id' => $otherSubject->id,
            'date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(403);
    }

    public function test_teacher_can_store_bulk_attendance()
    {
        $date = now()->format('Y-m-d');
        
        $response = $this->actingAs($this->teacher)->post(route('attendances.store'), [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'date' => $date,
            'attendances' => [
                $this->student->id => [
                    'status' => 'present',
                    'notes' => 'On time',
                ]
            ]
        ]);

        $response->assertRedirect(route('attendances.index'));
        $this->assertDatabaseHas('attendances', [
            'class_id' => $this->class->id,
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'status' => 'present',
        ]);
    }

    public function test_student_can_view_own_attendance_history()
    {
        Attendance::create([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'student_id' => $this->student->id,
            'recorded_by' => $this->teacher->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'excused',
        ]);

        $response = $this->actingAs($this->student)->get(route('attendances.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('attendances.student_index');
        $response->assertSee('Izin/Sakit');
    }

    public function test_student_cannot_access_attendance_create_form()
    {
        $response = $this->actingAs($this->student)->get(route('attendances.create', [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(403);
    }

    public function test_student_cannot_store_attendance()
    {
        $response = $this->actingAs($this->student)->post(route('attendances.store'), [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'date' => now()->format('Y-m-d'),
            'attendances' => [
                $this->student->id => ['status' => 'present']
            ]
        ]);

        $response->assertStatus(403);
    }
}
