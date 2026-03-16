<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ──
        $permissions = [
            // Assignments
            'assignments.view', 'assignments.create', 'assignments.edit',
            'assignments.delete', 'assignments.grade',
            // Quizzes
            'quizzes.view', 'quizzes.create', 'quizzes.edit',
            'quizzes.delete', 'quizzes.take',
            // Analytics
            'analytics.view_own', 'analytics.view_class', 'analytics.view_school',
            // Attendance
            'attendance.view', 'attendance.record',
            // Announcements
            'announcements.view', 'announcements.create',
            'announcements.edit', 'announcements.delete',
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Classes
            'classes.view', 'classes.manage',
            // Export
            'admin.export_data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── Roles & Assignments ──

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'assignments.view', 'assignments.create', 'assignments.edit',
            'assignments.delete', 'assignments.grade',
            'quizzes.view', 'quizzes.create', 'quizzes.edit', 'quizzes.delete',
            'analytics.view_own', 'analytics.view_class',
            'attendance.view', 'attendance.record',
            'announcements.view', 'announcements.create',
            'announcements.edit', 'announcements.delete',
            'classes.view',
        ]);

        $classLeader = Role::firstOrCreate(['name' => 'class_leader']);
        $classLeader->givePermissionTo([
            'assignments.view', 'quizzes.view', 'quizzes.take',
            'analytics.view_own',
            'attendance.view', 'attendance.record',
            'announcements.view', 'announcements.create',
        ]);

        $student = Role::firstOrCreate(['name' => 'student']);
        $student->givePermissionTo([
            'assignments.view', 'quizzes.view', 'quizzes.take',
            'analytics.view_own', 'attendance.view',
            'announcements.view',
        ]);
    }
}
