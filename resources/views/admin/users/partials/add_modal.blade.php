<!-- Create User Modal -->
<div x-data="{ open: false, role: 'student' }" x-on:open-modal.window="if ($event.detail === 'add-user') open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="open = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-50 border border-gray-100">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="bg-white px-8 pt-8 pb-8">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">Tambah Pengguna Baru</h3>
                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="text-left">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                            <input type="text" name="name" required class="w-full bg-gray-50 border-gray-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>
                        <div class="text-left">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Email Institusi</label>
                            <input type="email" name="email" required class="w-full bg-gray-50 border-gray-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>
                        <div class="text-left">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Password Awal</label>
                            <input type="password" name="password" minlength="8" required class="w-full bg-gray-50 border-gray-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>
                        <div class="text-left">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Peran (Role)</label>
                            <select name="role" x-model="role" required class="w-full bg-gray-50 border-gray-200 rounded-xl py-3 px-4 font-black text-indigo-600 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all uppercase tracking-wider text-xs">
                                <option value="student">SISWA</option>
                                <option value="teacher">GURU</option>
                                <option value="super_admin">SUPER ADMIN</option>
                            </select>
                        </div>
                    </div>

                    <!-- Role Specific Content (Dynamic) -->
                    <div class="mt-8 space-y-6">
                        <!-- Teacher Specific Fields -->
                        <div x-show="role === 'teacher'" x-transition class="bg-blue-50/50 p-6 rounded-2xl border-2 border-blue-100">
                            <h4 class="font-black text-blue-900 mb-4 flex items-center uppercase tracking-widest text-xs">
                                <i class="fas fa-chalkboard-teacher mr-2"></i> Profil Guru
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">NIP / ID Guru</label>
                                    <input type="text" name="teacher_id_number" :required="role === 'teacher'" class="w-full bg-white border-blue-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-blue-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">Spesialisasi</label>
                                    <input type="text" name="specialization" :required="role === 'teacher'" placeholder="Contoh: Matematika" class="w-full bg-white border-blue-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-blue-500 transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Student Specific Fields -->
                        <div x-show="role === 'student'" x-transition class="bg-green-50/50 p-6 rounded-2xl border-2 border-green-100">
                            <h4 class="font-black text-green-900 mb-4 flex items-center uppercase tracking-widest text-xs">
                                <i class="fas fa-user-graduate mr-2"></i> Profil Siswa
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-green-400 uppercase tracking-widest mb-2">NIS / NISN</label>
                                    <input type="text" name="student_id_number" :required="role === 'student'" class="w-full bg-white border-green-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-green-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-green-400 uppercase tracking-widest mb-2">Tahun Masuk</label>
                                    <input type="number" name="enrollment_year" value="{{ date('Y') }}" class="w-full bg-white border-green-200 rounded-xl py-3 px-4 font-bold text-gray-900 focus:ring-2 focus:ring-green-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-8 py-6 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100">
                    <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-indigo-100 transition-all uppercase tracking-widest text-sm">
                        Simpan Pengguna
                    </button>
                    <button type="button" @click="open = false" class="w-full sm:w-auto bg-white border-2 border-gray-200 text-gray-500 hover:bg-gray-50 font-black py-4 px-8 rounded-2xl transition-all uppercase tracking-widest text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
