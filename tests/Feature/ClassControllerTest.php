<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClassControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles and Permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        
        $manageDataPermission = Permission::firstOrCreate(['name' => 'master_data.manage']);
        $superAdminRole->givePermissionTo($manageDataPermission);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('teacher');

        $this->academicYear = AcademicYear::create([
            'name' => 'Semester Ganjil 2024/2025',
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_view_classes()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('classes.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.classes.index');
    }

    public function test_teacher_cannot_view_classes()
    {
        $response = $this->actingAs($this->teacher)->get(route('classes.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_class()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('classes.store'), [
            'name' => 'X MIPA 1',
            'grade_level' => 10,
            'academic_year_id' => $this->academicYear->id,
            'homeroom_teacher_id' => $this->teacher->id,
        ]);

        $response->assertRedirect(route('classes.index'));
        $this->assertDatabaseHas('classes', [
            'name' => 'X MIPA 1',
            'grade_level' => 10,
            'academic_year_id' => $this->academicYear->id,
            'homeroom_teacher_id' => $this->teacher->id,
        ]);
    }

    public function test_super_admin_can_create_class_with_subjects()
    {
        $subject1 = Subject::create(['name' => 'Math', 'code' => 'MTK']);
        $subject2 = Subject::create(['name' => 'Physics', 'code' => 'FIS']);

        $response = $this->actingAs($this->superAdmin)->post(route('classes.store'), [
            'name' => 'X MIPA 2',
            'grade_level' => 10,
            'academic_year_id' => $this->academicYear->id,
            'subjects' => [$subject1->id, $subject2->id],
        ]);

        $response->assertRedirect(route('classes.index'));
        $class = Classes::where('name', 'X MIPA 2')->first();
        
        $this->assertEquals(2, $class->subjects()->count());
        $this->assertTrue($class->subjects->contains($subject1));
        $this->assertTrue($class->subjects->contains($subject2));
    }

    public function test_super_admin_can_update_class()
    {
        $class = Classes::create([
            'name' => 'X IPS 1',
            'grade_level' => 10,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->put(route('classes.update', $class), [
            'name' => 'XI IPS 1',
            'grade_level' => 11,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response->assertRedirect(route('classes.index'));
        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'name' => 'XI IPS 1',
            'grade_level' => 11,
        ]);
    }

    public function test_super_admin_can_delete_class()
    {
        $class = Classes::create([
            'name' => 'X IPS 2',
            'grade_level' => 10,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('classes.destroy', $class));

        $response->assertRedirect(route('classes.index'));
        $this->assertDatabaseMissing('classes', [
            'id' => $class->id,
        ]);
    }
}
