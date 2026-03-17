<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $teacher;
    private User $student;
    private Classes $class;
    private Classes $anotherClass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->superAdmin->assignRole('super_admin');

        $this->teacher = User::factory()->create(['role' => 'teacher']);
        $this->teacher->assignRole('teacher');

        $this->student = User::factory()->create(['role' => 'student']);
        $this->student->assignRole('student');

        $this->academicYear = \App\Models\AcademicYear::create([
            'name' => 'Tester',
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
        ]);

        $this->class = Classes::create([
            'name' => '10A',
            'grade_level' => 10,
            'academic_year_id' => $this->academicYear->id,
        ]);
        $this->anotherClass = Classes::create([
            'name' => '10B',
            'grade_level' => 10,
            'academic_year_id' => $this->academicYear->id,
        ]);

        // Assign teacher to teach in $this->class (via Subject)
        $subject = Subject::create(['name' => 'Math', 'code' => 'MTK']);
        $this->class->subjects()->attach($subject->id, ['teacher_id' => $this->teacher->id]);

        // Student is in $this->class
        $this->student->classes()->attach($this->class->id);
    }

    public function test_super_admin_can_view_all_announcements()
    {
        Announcement::create(['title' => 'Global News', 'content' => 'x', 'priority' => 'normal', 'author_id' => $this->superAdmin->id, 'class_id' => null]);
        Announcement::create(['title' => 'Class News', 'content' => 'x', 'priority' => 'normal', 'author_id' => $this->superAdmin->id, 'class_id' => $this->anotherClass->id]);

        $response = $this->actingAs($this->superAdmin)->get(route('announcements.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Global News');
        $response->assertSee('Class News');
    }

    public function test_teacher_can_view_global_and_own_class_announcements()
    {
        Announcement::create(['title' => 'Global News', 'content' => 'x', 'priority' => 'normal', 'author_id' => $this->superAdmin->id, 'class_id' => null]);
        Announcement::create(['title' => 'My Class News', 'content' => 'x', 'priority' => 'normal', 'author_id' => $this->superAdmin->id, 'class_id' => $this->class->id]);
        Announcement::create(['title' => 'Other Class News', 'content' => 'x', 'priority' => 'normal', 'author_id' => $this->superAdmin->id, 'class_id' => $this->anotherClass->id]);

        $response = $this->actingAs($this->teacher)->get(route('announcements.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Global News');
        $response->assertSee('My Class News');
        $response->assertDontSee('Other Class News');
    }

    public function test_student_can_view_global_and_enrolled_class_announcements()
    {
        Announcement::create(['title' => 'Global News', 'content' => 'x', 'priority' => 'normal', 'author_id' => $this->superAdmin->id, 'class_id' => null]);
        Announcement::create(['title' => 'My Class News', 'content' => 'x', 'priority' => 'normal', 'author_id' => $this->superAdmin->id, 'class_id' => $this->class->id]);
        Announcement::create(['title' => 'Other Class News', 'content' => 'x', 'priority' => 'normal', 'author_id' => $this->superAdmin->id, 'class_id' => $this->anotherClass->id]);

        $response = $this->actingAs($this->student)->get(route('announcements.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Global News');
        $response->assertSee('My Class News');
        $response->assertDontSee('Other Class News');
    }

    public function test_teacher_can_create_announcement_for_own_class()
    {
        $response = $this->actingAs($this->teacher)->post(route('announcements.store'), [
            'title' => 'Ujian Besok',
            'content' => 'Jangan lupa belajar.',
            'class_id' => $this->class->id,
            'priority' => 'high',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Ujian Besok',
            'class_id' => $this->class->id,
            'author_id' => $this->teacher->id,
        ]);
    }

    public function test_teacher_cannot_create_global_announcement()
    {
        $response = $this->actingAs($this->teacher)->post(route('announcements.store'), [
            'title' => 'Global Alert',
            'content' => 'Info untuk semua',
            'class_id' => null, // Not allowed for teacher
            'priority' => 'high',
        ]);

        $response->assertSessionHasErrors(['class_id']);
        $this->assertDatabaseMissing('announcements', [
            'title' => 'Global Alert',
        ]);
    }

    public function test_teacher_cannot_create_announcement_for_other_class()
    {
        $response = $this->actingAs($this->teacher)->post(route('announcements.store'), [
            'title' => 'Alert',
            'content' => 'Info',
            'class_id' => $this->anotherClass->id,
            'priority' => 'high',
        ]);

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_global_announcement()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('announcements.store'), [
            'title' => 'Welcome Back',
            'content' => 'School is open.',
            'class_id' => null,
            'priority' => 'low',
            'expires_at' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'title' => 'Welcome Back',
            'class_id' => null,
        ]);
    }

    public function test_student_cannot_access_create_announcement()
    {
        $this->actingAs($this->student)->get(route('announcements.create'))
             ->assertStatus(403);

        $this->actingAs($this->student)->post(route('announcements.store'), [])
             ->assertStatus(403);
    }
}
