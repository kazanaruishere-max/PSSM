<x-app-layout>
    @section('header_title', 'Buat Kuis Baru')

    <div class="max-w-4xl mx-auto py-8 px-4" x-data="{ isAi: {{ old('is_ai_generated') ? 'true' : 'false' }} }">
        <!-- Breadcrumbs / Back -->
        <div class="mb-6">
            <a href="{{ route('quizzes.index') }}" class="inline-flex items-center text-xs font-black text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Kuis
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/50 border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="p-8 bg-gradient-to-r from-indigo-600 to-purple-600 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-2xl font-black tracking-tight uppercase italic">Pengaturan Kuis Baru</h3>
                    <p class="text-indigo-100 text-xs font-bold uppercase tracking-widest mt-1 opacity-80">Konfigurasi Ujian Online & CBT</p>
                </div>
                <i class="fas fa-laptop-code absolute -right-4 -bottom-4 text-8xl text-white/10 rotate-12"></i>
            </div>

            <div class="p-8">
                @if($errors->any())
                    <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl shadow-sm">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            <h4 class="text-red-800 font-black uppercase tracking-widest text-xs">Terjadi Kesalahan Validasi</h4>
                        </div>
                        <ul class="list-disc pl-5 text-red-700 text-xs font-bold space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('quizzes.store') }}" method="POST" id="quizForm" class="space-y-8">
                    @csrf
                    
                    <!-- Basic Info Section -->
                    <div class="space-y-6">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100 pb-2">Informasi Dasar</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Pilih Kelas</label>
                                <select name="class_id" required class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Pilih Mata Pelajaran</label>
                                <select name="subject_id" required class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Judul Kuis / Ujian</label>
                            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Penilaian Harian Matematika - Aljabar" 
                                class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-black text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Petunjuk Pengerjaan (Opsional)</label>
                            <textarea name="description" rows="3" placeholder="Tuliskan instruksi kuis di sini..." 
                                class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- Scheduling Section -->
                    <div class="space-y-6">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100 pb-2">Jadwal & Durasi</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Waktu Mulai Tersedia</label>
                                <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required 
                                    class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Waktu Selesai Tersedia</label>
                                <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required 
                                    class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Durasi (Menit)</label>
                                <div class="relative">
                                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="5" max="180" required 
                                        class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-black text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <i class="far fa-clock absolute right-5 top-1/2 -translate-y-1/2 text-gray-300"></i>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Maks. Percobaan</label>
                                <input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" min="1" max="5" required 
                                    class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-black text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Nilai Maksimal</label>
                                <input type="number" name="max_score" value="{{ old('max_score', 100) }}" min="10" max="1000" required 
                                    class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 px-5 font-black text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- AI Generation Section -->
                    <div class="p-8 rounded-3xl transition-all duration-500 {{ old('is_ai_generated') || '$isAi' ? 'bg-indigo-900 text-white shadow-xl shadow-indigo-200' : 'bg-gray-50 border-2 border-dashed border-gray-200' }}"
                         :class="isAi ? 'bg-indigo-900 text-white border-transparent' : 'bg-gray-50 text-gray-500 border-gray-200'">
                        
                        <label class="flex items-center mb-6 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="is_ai_generated" value="1" x-model="isAi" class="sr-only">
                                <div class="w-14 h-7 bg-gray-300 rounded-full shadow-inner group-hover:bg-gray-400 transition-colors" :class="isAi ? 'bg-indigo-500' : 'bg-gray-300'"></div>
                                <div class="absolute left-1 top-1 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300" :class="isAi ? 'translate-x-7' : ''"></div>
                            </div>
                            <span class="ml-4 font-black uppercase tracking-widest text-sm" :class="isAi ? 'text-white' : 'text-gray-900'">Gunakan Kecerdasan Buatan (AI)</span>
                        </label>
                        
                        <div x-show="isAi" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                            <div class="p-4 bg-white/10 rounded-2xl border border-white/20 text-xs font-bold italic leading-relaxed">
                                <i class="fas fa-info-circle mr-2"></i> AI akan menyusun soal secara otomatis berdasarkan topik yang Anda berikan. Harap tunggu hingga 45 detik saat menyimpan.
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                <div class="md:col-span-12">
                                    <label class="block text-[10px] font-black uppercase tracking-widest mb-2 opacity-70">Topik Kuis Spesifik</label>
                                    <input type="text" name="ai_topic" value="{{ old('ai_topic') }}" :required="isAi" 
                                        class="w-full bg-white/10 border-white/30 rounded-2xl py-4 px-5 font-black text-white focus:ring-2 focus:ring-white transition-all placeholder:text-white/30" 
                                        placeholder="Contoh: Sejarah Perkembangan Islam di Nusantara">
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-[10px] font-black uppercase tracking-widest mb-2 opacity-70">Jumlah Soal</label>
                                    <input type="number" name="ai_question_count" value="{{ old('ai_question_count', 10) }}" min="5" max="20" :required="isAi" 
                                        class="w-full bg-white/10 border-white/30 rounded-2xl py-4 px-5 font-black text-white focus:ring-2 focus:ring-white transition-all">
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-[10px] font-black uppercase tracking-widest mb-2 opacity-70">Tingkat Kesulitan</label>
                                    <select name="ai_difficulty" :required="isAi" 
                                        class="w-full bg-white/10 border-white/30 rounded-2xl py-4 px-5 font-black text-white focus:ring-2 focus:ring-white transition-all">
                                        <option value="easy" class="text-gray-900">MUDAH</option>
                                        <option value="medium" class="text-gray-900">SEDANG</option>
                                        <option value="hard" class="text-gray-900">SULIT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="!isAi" class="text-center py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em]">Sistem Manual Aktif</p>
                        </div>
                    </div>

                    <!-- Final Settings -->
                    <div class="p-6 bg-green-50 rounded-3xl border-2 border-green-100 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-green-600 shadow-sm mr-4">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div>
                                <p class="text-xs font-black text-green-900 uppercase tracking-widest">Langsung Publikasikan</p>
                                <p class="text-[10px] text-green-700 font-bold uppercase tracking-tighter opacity-70">Siswa dapat melihat jadwal kuis ini</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                        </label>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-8 flex flex-col sm:flex-row gap-4">
                        <button type="submit" id="submitBtn" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-black py-5 px-8 rounded-3xl shadow-xl shadow-indigo-100 transition-all transform hover:-translate-y-1 uppercase tracking-widest text-sm flex justify-center items-center">
                            <span id="btnText">SIMPAN KUIS & GENERATE</span>
                            <i class="fas fa-magic ml-2" id="btnIcon"></i>
                        </button>
                        <a href="{{ route('quizzes.index') }}" class="w-full sm:w-auto bg-white border-2 border-gray-200 text-gray-500 hover:bg-gray-50 font-black py-5 px-10 rounded-3xl transition-all uppercase tracking-widest text-sm text-center">
                            BATAL
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('quizForm');
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');

        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btnText.innerHTML = 'MEMPROSES SOAL... MOHON TUNGGU';
            btnIcon.className = 'fas fa-spinner fa-spin ml-2';
        });
    </script>
</x-app-layout>
