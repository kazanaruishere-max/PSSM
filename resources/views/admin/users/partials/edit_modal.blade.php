<!-- Edit User Modal -->
<div x-show="openEdit" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="openEdit = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-50 border border-gray-100">
            <form method="POST" action="{{ route('users.update', $user) }}" x-data="{ role: '{{ $user->role }}' }">
                @csrf @method('PUT')
                
                <div class="bg-white px-8 pt-8 pb-8">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 tracking-tight italic">Edit Pengguna</h3>
                            <p class="text-xs text-indigo-500 font-black uppercase tracking-widest mt-1">ID: #{{ $user->id }}</p>
                        </div>
                        <button type="button" @click="openEdit = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="text-left">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ $user->name }}" required class="w-full bg-gray-50 border-gray-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="text-left">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Email</label>
                            <input type="email" name="email" value="{{ $user->email }}" required class="w-full bg-gray-50 border-gray-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="text-left">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Reset Password</label>
                            <input type="password" name="password" minlength="8" placeholder="Kosongkan jika tidak diubah" class="w-full bg-gray-50 border-gray-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="flex items-end mb-2">
                            <label class="inline-flex items-center cursor-pointer group bg-gray-50 p-4 rounded-xl border border-gray-100 hover:border-indigo-200 transition-all w-full">
                                <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-3 text-xs text-gray-700 font-black uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Status Aktif Akun</span>
                            </label>
                        </div>
                    </div>

                    <!-- Role context hidden -->
                    <input type="hidden" name="role" value="{{ $user->role }}">

                    <!-- Teacher Specific Fields -->
                    <div x-show="role === 'teacher'" class="bg-blue-50/50 p-6 rounded-2xl border-2 border-blue-100 space-y-4">
                        <h4 class="font-black text-blue-900 mb-2 flex items-center uppercase tracking-widest text-xs">
                            <i class="fas fa-chalkboard-teacher mr-2 text-blue-600"></i> Profil Guru
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">NIP / ID Guru</label>
                                <input type="text" name="teacher_id_number" value="{{ $user->teacherProfile->teacher_id_number ?? '' }}" class="w-full bg-white border-blue-200 rounded-xl py-3 px-4 font-bold text-gray-900">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">Spesialisasi</label>
                                <input type="text" name="specialization" value="{{ $user->teacherProfile->specialization ?? '' }}" class="w-full bg-white border-blue-200 rounded-xl py-3 px-4 font-bold text-gray-900">
                            </div>
                        </div>
                    </div>

                    <!-- Student Specific Fields -->
                    <div x-show="role === 'student'" class="bg-green-50/50 p-6 rounded-2xl border-2 border-green-100 space-y-4">
                        <h4 class="font-black text-green-900 mb-2 flex items-center uppercase tracking-widest text-xs">
                            <i class="fas fa-user-graduate mr-2 text-green-600"></i> Profil Siswa
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                            <div>
                                <label class="block text-[10px] font-black text-green-400 uppercase tracking-widest mb-2">NIS / NISN</label>
                                <input type="text" name="student_id_number" value="{{ $user->studentProfile->student_id_number ?? '' }}" class="w-full bg-white border-green-200 rounded-xl py-3 px-4 font-bold text-gray-900">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-green-400 uppercase tracking-widest mb-2 text-left">Tahun Masuk</label>
                                <input type="number" name="enrollment_year" value="{{ $user->studentProfile->enrollment_year ?? date('Y') }}" class="w-full bg-white border-green-200 rounded-xl py-3 px-4 font-bold text-gray-900">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-8 py-6 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100">
                    <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-indigo-100 transition-all uppercase tracking-widest text-sm">
                        Simpan Perubahan
                    </button>
                    <button type="button" @click="openEdit = false" class="w-full sm:w-auto bg-white border-2 border-gray-200 text-gray-500 hover:bg-gray-50 font-black py-4 px-8 rounded-2xl transition-all uppercase tracking-widest text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
