<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SubjectControllerTest extends TestCase
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
    }

    public function test_super_admin_can_view_subjects()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('subjects.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.subjects.index');
    }

    public function test_teacher_cannot_view_subjects()
    {
        $response = $this->actingAs($this->teacher)->get(route('subjects.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_subject()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('subjects.store'), [
            'name' => 'Matematika Lanjut',
            'code' => 'MTK-A2',
        ]);

        $response->assertRedirect(route('subjects.index'));
        $this->assertDatabaseHas('subjects', [
            'name' => 'Matematika Lanjut',
            'code' => 'MTK-A2',
        ]);
    }

    public function test_super_admin_can_update_subject()
    {
        $subject = Subject::create([
            'name' => 'Fisika Dasar',
            'code' => 'FIS-01',
        ]);

        $response = $this->actingAs($this->superAdmin)->put(route('subjects.update', $subject), [
            'name' => 'Fisika Lanjut',
            'code' => 'FIS-02',
        ]);

        $response->assertRedirect(route('subjects.index'));
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Fisika Lanjut',
            'code' => 'FIS-02',
        ]);
    }

    public function test_super_admin_can_delete_subject()
    {
        $subject = Subject::create([
            'name' => 'Kimia',
            'code' => 'KIM',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('subjects.destroy', $subject));

        $response->assertRedirect(route('subjects.index'));
        $this->assertDatabaseMissing('subjects', [
            'id' => $subject->id,
        ]);
    }
}
