<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $query = User::with(['roles', 'studentProfile', 'teacherProfile'])->orderBy('name');
        
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Role::whereIn('name', ['super_admin', 'teacher', 'student'])->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,teacher,student',
            
            // Teacher fields
            'teacher_id_number' => 'required_if:role,teacher|nullable|string|max:50',
            'specialization' => 'required_if:role,teacher|nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            
            // Student fields
            'student_id_number' => 'required_if:role,student|nullable|string|max:50|unique:student_profiles,student_id_number',
            'date_of_birth' => 'nullable|date',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'enrollment_year' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => true,
            ]);

            $user->assignRole($validated['role']);

            if ($validated['role'] === 'teacher') {
                TeacherProfile::create([
                    'user_id' => $user->id,
                    'teacher_id_number' => $validated['teacher_id_number'] ?? null,
                    'specialization' => $validated['specialization'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                ]);
            } elseif ($validated['role'] === 'student') {
                StudentProfile::create([
                    'user_id' => $user->id,
                    'student_id_number' => $validated['student_id_number'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'parent_name' => $validated['parent_name'] ?? null,
                    'parent_phone' => $validated['parent_phone'] ?? null,
                    'parent_email' => $validated['parent_email'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'enrollment_year' => $validated['enrollment_year'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, User $user)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'password' => 'nullable|string|min:8', // Only update if filled
            'is_active' => 'sometimes|boolean',
            
            // Teacher fields
            'teacher_id_number' => 'required_if:role,teacher|nullable|string|max:50',
            'specialization' => 'required_if:role,teacher|nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            
            // Student fields
            'student_id_number' => ['required_if:role,student', 'nullable', 'string', 'max:50', Rule::unique('student_profiles')->ignore($user->studentProfile?->id)],
            'date_of_birth' => 'nullable|date',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'enrollment_year' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->is_active = $request->has('is_active');
            
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            // Profile updates
            if ($user->role === 'teacher') {
                $user->teacherProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'teacher_id_number' => $validated['teacher_id_number'] ?? null,
                        'specialization' => $validated['specialization'] ?? null,
                        'phone' => $validated['phone'] ?? null,
                    ]
                );
            } elseif ($user->role === 'student') {
                $user->studentProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_id_number' => $validated['student_id_number'] ?? null,
                        'date_of_birth' => $validated['date_of_birth'] ?? null,
                        'parent_name' => $validated['parent_name'] ?? null,
                        'parent_phone' => $validated['parent_phone'] ?? null,
                        'parent_email' => $validated['parent_email'] ?? null,
                        'address' => $validated['address'] ?? null,
                        'enrollment_year' => $validated['enrollment_year'] ?? null,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete(); // Soft delete applies

        return redirect()->route('users.index')->with('success', 'User berhasil dinonaktifkan/dihapus.');
    }
}
