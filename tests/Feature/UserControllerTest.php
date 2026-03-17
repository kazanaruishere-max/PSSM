<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles and Permissions
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'teacher']);
        Role::firstOrCreate(['name' => 'student']);
        
        $manageDataPermission = Permission::firstOrCreate(['name' => 'master_data.manage']);
        Role::findByName('super_admin')->givePermissionTo($manageDataPermission);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('teacher');
    }

    public function test_super_admin_can_view_users()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    public function test_teacher_cannot_view_users()
    {
        $response = $this->actingAs($this->teacher)->get(route('users.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_teacher_user()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), [
            'name' => 'Guru Budiman',
            'email' => 'budiman@example.com',
            'password' => 'password123',
            'role' => 'teacher',
            'teacher_id_number' => '123456789',
            'specialization' => 'Matematika',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect(route('users.index'));
        
        $user = User::where('email', 'budiman@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('teacher'));

        $profile = TeacherProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('123456789', $profile->teacher_id_number);
        $this->assertEquals('Matematika', $profile->specialization);
        $this->assertEquals('081234567890', $profile->phone); // Decrypts automatically
    }

    public function test_super_admin_can_create_student_user()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), [
            'name' => 'Siswa Teladan',
            'email' => 'siswa@example.com',
            'password' => 'password123',
            'role' => 'student',
            'student_id_number' => '987654321',
            'date_of_birth' => '2010-01-01',
            'parent_name' => 'Orang Tua',
            'parent_phone' => '089876543210',
            'address' => 'Jl. Pendidikan No. 1',
        ]);
        $response->assertSessionHasNoErrors();
        if (session('error')) {
            dd(session('error'));
        }
        $response->assertRedirect(route('users.index'));
        
        $user = User::where('email', 'siswa@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('student'));

        $profile = StudentProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('987654321', $profile->student_id_number);
        $this->assertEquals('Orang Tua', $profile->parent_name);
        $this->assertEquals('089876543210', $profile->parent_phone); // Decrypts automatically
    }

    public function test_super_admin_can_update_user_and_profile()
    {
        $user = User::factory()->create(['email' => 'old_student@example.com']);
        $user->assignRole('student');
        StudentProfile::create([
            'user_id' => $user->id,
            'student_id_number' => '111',
            'parent_name' => 'Old Parent',
        ]);

        $response = $this->actingAs($this->superAdmin)->put(route('users.update', $user), [
            'name' => 'New Name',
            'email' => 'new_student@example.com',
            'role' => 'student', // sent from hidden input in form
            'is_active' => 1,
            'student_id_number' => '222',
            'parent_name' => 'New Parent',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));
        
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new_student@example.com', $user->email);

        $profile = StudentProfile::where('user_id', $user->id)->first();
        $this->assertEquals('222', $profile->student_id_number);
        $this->assertEquals('New Parent', $profile->parent_name);
    }

    public function test_super_admin_can_soft_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->superAdmin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }
}
