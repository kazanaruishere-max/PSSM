<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkImportController extends Controller
{
    /**
     * Import users from an Excel/CSV file.
     */
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'role' => 'required|in:teacher,student',
        ]);

        $role = $request->role;
        $file = $request->file('file');

        try {
            $data = Excel::toCollection(new class implements ToCollection, WithHeadingRow {
                public function collection(Collection $rows) {}
            }, $file)->first();

            DB::beginTransaction();
            $count = 0;

            foreach ($data as $row) {
                if (empty($row['email']) || empty($row['name'])) continue;

                // Create user
                $password = $row['password'] ?? Str::random(12);
                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => Hash::make($password),
                    'role' => $role,
                    'is_active' => true,
                ]);

                $user->assignRole($role);

                // Create profile
                if ($role === 'teacher') {
                    TeacherProfile::create([
                        'user_id' => $user->id,
                        'teacher_id_number' => $row['nip'] ?? $row['teacher_id'] ?? null,
                        'specialization' => $row['specialization'] ?? null,
                    ]);
                } else {
                    StudentProfile::create([
                        'user_id' => $user->id,
                        'student_id_number' => $row['nis'] ?? $row['student_id'] ?? null,
                        'enrollment_year' => $row['enrollment_year'] ?? date('Y'),
                    ]);
                }
                $count++;
            }

            DB::commit();
            return back()->with('success', "Berhasil mengimpor {$count} user.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}
