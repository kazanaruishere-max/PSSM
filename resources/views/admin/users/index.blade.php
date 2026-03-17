<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Data Master: Pengguna
            </h2>
            <button x-data @click="$dispatch('open-modal', 'add-user')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Tambah Pengguna
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b">
                    <form method="GET" action="{{ route('users.index') }}" class="flex items-center">
                        <label for="role" class="mr-2 text-sm font-medium text-gray-700">Filter Role:</label>
                        <select name="role" id="role" onchange="this.form.submit()" class="shadow appearance-none border rounded py-1 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm">
                            <option value="">Semua Role</option>
                            <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>Guru</option>
                            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Siswa</option>
                        </select>
                    </form>
                </div>
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama & Email</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Info Profil</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <div class="flex items-center">
                                            <div class="ml-3">
                                                <p class="text-gray-900 font-semibold whitespace-no-wrap">
                                                    {{ $user->name }}
                                                </p>
                                                <p class="text-gray-500 whitespace-no-wrap text-xs">
                                                    {{ $user->email }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <span class="capitalize px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : ($user->role === 'teacher' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                            {{ $user->role === 'super_admin' ? 'Super Admin' : ($user->role === 'teacher' ? 'Guru' : 'Siswa') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        @if($user->role === 'student' && $user->studentProfile)
                                            <div class="text-xs text-gray-600">NIS/NISN: {{ $user->studentProfile->student_id_number ?? '-' }}</div>
                                        @elseif($user->role === 'teacher' && $user->teacherProfile)
                                            <div class="text-xs text-gray-600">NIP: {{ $user->teacherProfile->teacher_id_number ?? '-' }}</div>
                                            <div class="text-xs text-gray-600">Mapel: {{ $user->teacherProfile->specialization ?? '-' }}</div>
                                        @else
                                            <span class="text-gray-400 text-xs italic">Tidak ada info khusus</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right font-medium">
                                        <div x-data="{ openEdit: false }">
                                            <button @click="openEdit = true" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Nonaktifkan/Hapus Pengguna ini secara permanen?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            @endif

                                            <!-- Edit Modal -->
                                            <div x-show="openEdit" class="fixed justify-center items-center flex inset-0 z-50 overflow-y-auto" style="display: none;">
                                                <div class="fixed inset-0 bg-gray-500 opacity-75" @click="openEdit = false"></div>
                                                <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-2xl sm:w-full z-50">
                                                    <form method="POST" action="{{ route('users.update', $user) }}" x-data="{ role: '{{ $user->role }}' }">
                                                        @csrf @method('PUT')
                                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Pengguna</h3>
                                                            
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-left">
                                                                <div>
                                                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                                                                    <input type="text" name="name" value="{{ $user->name }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                                                                    <input type="email" name="email" value="{{ $user->email }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                                </div>
                                                            </div>

                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-left">
                                                                <div>
                                                                    <label class="block text-gray-700 text-sm font-bold mb-2">Reset Password (Biarkan kosong jika tidak ingin diubah)</label>
                                                                    <input type="password" name="password" minlength="8" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                                </div>
                                                                <div class="flex items-end mb-2">
                                                                    <label class="inline-flex items-center">
                                                                        <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                                        <span class="ml-2 text-sm text-gray-700 font-bold">Status Aktif</span>
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            <!-- Hide role change to prevent complexity, just readonly or hidden input for role context -->
                                                            <input type="hidden" name="role" value="{{ $user->role }}">

                                                            <!-- Teacher Specific Fields -->
                                                            <div x-show="role === 'teacher'" class="border-t pt-4 mt-4 text-left">
                                                                <h4 class="font-bold text-gray-800 mb-3">Data Guru</h4>
                                                                <div class="grid grid-cols-1 gap-4">
                                                                    <div>
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">NIP / Nomor Induk Guru</label>
                                                                        <input type="text" name="teacher_id_number" value="{{ $user->teacherProfile->teacher_id_number ?? '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">Spesialisasi Mapel</label>
                                                                        <input type="text" name="specialization" value="{{ $user->teacherProfile->specialization ?? '' }}" placeholder="Contoh: Matematika" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                                                                        <input type="text" name="phone" value="{{ $user->teacherProfile->phone ?? '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Student Specific Fields -->
                                                            <div x-show="role === 'student'" class="border-t pt-4 mt-4 text-left">
                                                                <h4 class="font-bold text-gray-800 mb-3">Data Siswa</h4>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                    <div>
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">NISN / NIS</label>
                                                                        <input type="text" name="student_id_number" value="{{ $user->studentProfile->student_id_number ?? '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">Tahun Masuk</label>
                                                                        <input type="number" name="enrollment_year" value="{{ $user->studentProfile->enrollment_year ?? date('Y') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir</label>
                                                                        <input type="date" name="date_of_birth" value="{{ $user->studentProfile && $user->studentProfile->date_of_birth ? $user->studentProfile->date_of_birth->format('Y-m-d') : '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Orang Tua/Wali</label>
                                                                        <input type="text" name="parent_name" value="{{ $user->studentProfile->parent_name ?? '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">No. Telp Orang Tua/Wali</label>
                                                                        <input type="text" name="parent_phone" value="{{ $user->studentProfile->parent_phone ?? '' }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                                                    </div>
                                                                    <div class="md:col-span-2">
                                                                        <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Domisili</label>
                                                                        <textarea name="address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">{{ $user->studentProfile->address ?? '' }}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse rounded-b-lg border-t">
                                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto text-sm">
                                                                Simpan
                                                            </button>
                                                            <button type="button" @click="openEdit = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto text-sm">
                                                                Batal
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                        Data belum tersedia.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="px-5 py-5 bg-white border-t border-gray-200">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div x-data="{ open: false, role: 'student' }" x-on:open-modal.window="if ($event.detail === 'add-user') open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" @click="open = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-50">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Tambah Pengguna Baru</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="text-left">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                                <input type="text" name="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="text-left">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                                <input type="email" name="email" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="text-left">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Password Awal</label>
                                <input type="password" name="password" minlength="8" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="text-left">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Role</label>
                                <select name="role" x-model="role" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="student">Siswa</option>
                                    <option value="teacher">Guru</option>
                                    <option value="super_admin">Super Admin</option>
                                </select>
                            </div>
                        </div>

                        <!-- Teacher Specific Fields -->
                        <div x-show="role === 'teacher'" x-transition class="border-t pt-4 mt-4 text-left bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <h4 class="font-bold text-gray-800 mb-3"><span class="text-blue-600">★</span> Data Profil Guru</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NIP / Nomor Induk Guru</label>
                                    <input type="text" name="teacher_id_number" :required="role === 'teacher'" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Spesialisasi Mapel</label>
                                    <input type="text" name="specialization" :required="role === 'teacher'" placeholder="Contoh: Matematika" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon</label>
                                    <input type="text" name="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                </div>
                            </div>
                        </div>

                        <!-- Student Specific Fields -->
                        <div x-show="role === 'student'" x-transition class="border-t pt-4 mt-4 text-left bg-green-50 p-4 rounded-lg border border-green-100">
                            <h4 class="font-bold text-gray-800 mb-3"><span class="text-green-600">★</span> Data Profil Siswa</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">NISN / NIS</label>
                                    <input type="text" name="student_id_number" :required="role === 'student'" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tahun Masuk</label>
                                    <input type="number" name="enrollment_year" value="{{ date('Y') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir</label>
                                    <input type="date" name="date_of_birth" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Orang Tua/Wali</label>
                                    <input type="text" name="parent_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">No. Telp Orang Tua/Wali</label>
                                    <input type="text" name="parent_phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat Domisili</label>
                                    <textarea name="address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg border-t">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto text-sm">
                            Simpan Pengguna
                        </button>
                        <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
