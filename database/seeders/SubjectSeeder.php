<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Matematika', 'code' => 'MTK', 'description' => 'Matematika Wajib & Peminatan'],
            ['name' => 'Fisika', 'code' => 'FIS', 'description' => 'Fisika SMA'],
            ['name' => 'Kimia', 'code' => 'KIM', 'description' => 'Kimia SMA'],
            ['name' => 'Biologi', 'code' => 'BIO', 'description' => 'Biologi SMA'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN', 'description' => 'Bahasa dan Sastra Indonesia'],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG', 'description' => 'Bahasa Inggris Wajib'],
            ['name' => 'Sejarah Indonesia', 'code' => 'SEJ', 'description' => 'Sejarah Indonesia'],
            ['name' => 'Informatika', 'code' => 'INF', 'description' => 'Informatika / TIK'],
            ['name' => 'Ekonomi', 'code' => 'EKO', 'description' => 'Ekonomi SMA'],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PKN', 'description' => 'Pendidikan Pancasila & Kewarganegaraan'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['code' => $subject['code']], $subject);
        }
    }
}
