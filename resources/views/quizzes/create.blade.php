<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Kuis Baru') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ isAi: {{ old('is_ai_generated') ? 'true' : 'false' }} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            <strong>Terjadi Kesalahan!</strong>
                            <ul class="list-disc pl-5 mt-2 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('quizzes.store') }}" method="POST" id="quizForm">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Kelas</label>
                                <select name="class_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Mata Pelajaran</label>
                                <select name="subject_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Judul Kuis</label>
                            <input type="text" name="title" value="{{ old('title') }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi (Opsional)</label>
                            <textarea name="description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Waktu Mulai Tersedia</label>
                                <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Waktu Selesai Tersedia</label>
                                <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Durasi Pengerjaan (Menit)</label>
                                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="5" max="180" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Maksimal Percobaan</label>
                                <input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" min="1" max="5" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nilai Maksimal</label>
                                <input type="number" name="max_score" value="{{ old('max_score', 100) }}" min="10" max="1000" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                        </div>

                        <!-- AI GENERATION SECTION -->
                        <div class="mb-6 p-4 border rounded-lg bg-indigo-50 border-indigo-200">
                            <label class="flex items-center mb-4 cursor-pointer">
                                <input type="checkbox" name="is_ai_generated" value="1" x-model="isAi" class="form-checkbox h-5 w-5 text-indigo-600">
                                <span class="ml-2 text-indigo-900 font-bold text-lg">Generate Soal Otomatis Menggunakan AI</span>
                            </label>
                            
                            <p class="text-sm text-indigo-700 mb-4" x-show="!isAi">Centang kotak ini untuk membiarkan AI OpenRouter membuat soal secara otomatis.</p>
                            
                            <div x-show="isAi" class="grid grid-cols-1 md:grid-cols-12 gap-4 mt-4 transition-all duration-300">
                                <div class="md:col-span-6">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Topik Kuis (Sangat Spesifik)</label>
                                    <input type="text" name="ai_topic" value="{{ old('ai_topic') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :required="isAi" placeholder="Contoh: Sejarah Kemerdekaan Indonesia 1945">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah Soal</label>
                                    <input type="number" name="ai_question_count" value="{{ old('ai_question_count', 10) }}" min="5" max="20" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :required="isAi">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tingkat Kesulitan</label>
                                    <select name="ai_difficulty" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :required="isAi">
                                        <option value="easy" {{ old('ai_difficulty') == 'easy' ? 'selected' : '' }}>Mudah</option>
                                        <option value="medium" {{ old('ai_difficulty', 'medium') == 'medium' ? 'selected' : '' }}>Sedang</option>
                                        <option value="hard" {{ old('ai_difficulty') == 'hard' ? 'selected' : '' }}>Sulit</option>
                                    </select>
                                </div>
                                <div class="col-span-full">
                                    <p class="text-xs text-indigo-600 mt-2 italic"><i class="fas fa-magic mr-1"></i> Setelah ditekan Simpan, harap tunggu beberapa saat karena AI sedang menyusun soal.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline" onclick="this.innerHTML='Memproses... Mohon Tunggu'; this.classList.add('opacity-50', 'cursor-not-allowed')">
                                Simpan Kuis
                            </button>
                            <a href="{{ route('quizzes.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
