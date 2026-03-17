<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AcademicYearControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles and Permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        
        $manageDataPermission = Permission::firstOrCreate(['name' => 'master_data.manage']);
        $superAdminRole->givePermissionTo($manageDataPermission);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('teacher');
    }

    public function test_super_admin_can_view_academic_years()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('academic-years.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.academic_years.index');
    }

    public function test_teacher_cannot_view_academic_years()
    {
        $response = $this->actingAs($this->teacher)->get(route('academic-years.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_academic_year()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('academic-years.store'), [
            'name' => 'Semester Ganjil 2024/2025',
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('academic-years.index'));
        $this->assertDatabaseHas('academic_years', [
            'name' => 'Semester Ganjil 2024/2025',
            'start_date' => '2024-07-01 00:00:00', // assuming datetime format if needed or just skip date check
            'is_active' => true,
        ]);
    }

    public function test_creating_active_year_deactivates_others()
    {
        $oldYear = AcademicYear::create([
            'name' => 'Semester Genap 2023/2024',
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin)->post(route('academic-years.store'), [
            'name' => 'Semester Ganjil 2024/2025',
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
        ]);

        // Refresh old year from DB
        $oldYear->refresh();
        $this->assertFalse((bool)$oldYear->is_active);
        $this->assertDatabaseHas('academic_years', [
            'name' => 'Semester Ganjil 2024/2025',
            'is_active' => true,
        ]);
    }
}
