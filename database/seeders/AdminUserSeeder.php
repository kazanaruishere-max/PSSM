<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@pssm.school'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('Admin@PSSM2026!'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');

        $teacher = User::firstOrCreate(
            ['email' => 'guru@pssm.school'],
            [
                'name' => 'Bu Siti (Demo Teacher)',
                'password' => bcrypt('Teacher@PSSM2026!'),
                'role' => 'teacher',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $teacher->assignRole('teacher');

        $student = User::firstOrCreate(
            ['email' => 'siswa@pssm.school'],
            [
                'name' => 'Budi Santoso (Demo Student)',
                'password' => bcrypt('Student@PSSM2026!'),
                'role' => 'student',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $student->assignRole('student');
    }
}
