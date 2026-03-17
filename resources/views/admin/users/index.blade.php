<x-app-layout>
    @section('header_title', 'Manajemen Pengguna')

    <div class="space-y-8">
        <!-- Action Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600">
                    <i class="fas fa-users-cog text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-900 leading-tight">Database Pengguna</h3>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Total: {{ $users->total() }} Akun Terdaftar</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <form method="GET" action="{{ route('users.index') }}" class="flex-1 md:flex-none">
                    <select name="role" onchange="this.form.submit()" class="w-full bg-gray-50 border-gray-200 rounded-xl text-xs font-black text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 uppercase tracking-wider py-3 px-4">
                        <option value="">SEMUA ROLE</option>
                        <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>SUPER ADMIN</option>
                        <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>GURU</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>SISWA</option>
                    </select>
                </form>
                <button x-data @click="$dispatch('open-modal', 'add-user')" class="flex-1 md:flex-none bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3 px-6 rounded-xl shadow-lg shadow-indigo-100 transition-all flex items-center justify-center whitespace-nowrap">
                    <i class="fas fa-plus mr-2"></i> TAMBAH USER
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl flex items-center shadow-sm">
                <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                <p class="text-green-800 font-bold">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Table Container -->
        <div class="table-container">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>NAMA & EMAIL</th>
                            <th>ROLE</th>
                            <th>STATUS</th>
                            <th>INFO PROFIL</th>
                            <th class="text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr class="group">
                            <td>
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center font-black text-xs mr-3 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-gray-900 leading-tight">{{ $user->name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold mt-0.5 tracking-wider">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest
                                    {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-700 border border-purple-200' : ($user->role === 'teacher' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-green-100 text-green-700 border border-green-200') }}">
                                    {{ $user->role === 'super_admin' ? 'Super Admin' : ($user->role === 'teacher' ? 'Guru' : 'Siswa') }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $user->is_active ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span>
                                    <span class="text-xs font-black uppercase tracking-tighter {{ $user->is_active ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'student' && $user->studentProfile)
                                    <div class="flex flex-col space-y-1">
                                        <span class="text-[10px] text-gray-400 font-black uppercase tracking-tighter">NIS: {{ $user->studentProfile->student_id_number ?? '-' }}</span>
                                        <span class="text-[10px] text-gray-400 font-black uppercase tracking-tighter">MASUK: {{ $user->studentProfile->enrollment_year ?? '-' }}</span>
                                    </div>
                                @elseif($user->role === 'teacher' && $user->teacherProfile)
                                    <div class="flex flex-col space-y-1">
                                        <span class="text-[10px] text-gray-400 font-black uppercase tracking-tighter">NIP: {{ $user->teacherProfile->teacher_id_number ?? '-' }}</span>
                                        <span class="text-[10px] text-gray-400 font-black uppercase tracking-tighter">MAPEL: {{ $user->teacherProfile->specialization ?? '-' }}</span>
                                    </div>
                                @else
                                    <span class="text-[10px] text-gray-300 font-black uppercase tracking-widest italic italic">Sistem</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end items-center space-x-2" x-data="{ openEdit: false }">
                                    <button @click="openEdit = true" class="p-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pengguna ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Edit Modal Implementation (Moved to a standard x-modal later if needed) -->
                                    <!-- Keep the existing modal logic but restyle it -->
                                    @include('admin.users.partials.edit_modal', ['user' => $user])
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-user-slash text-4xl text-gray-200 mb-4"></i>
                                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Tidak ada data pengguna ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modals (Add User) -->
    @include('admin.users.partials.add_modal')
</x-app-layout>
