<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Tugas: ') }} {{ $assignment->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Kelas</label>
                            <select name="class_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ (old('class_id', $assignment->class_id) == $c->id) ? 'selected' : '' }}>{{ $c->name }} ({{ $c->academicYear->year }})</option>
                                @endforeach
                            </select>
                            @error('class_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Mata Pelajaran</label>
                            <select name="subject_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}" {{ (old('subject_id', $assignment->subject_id) == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Judul Tugas</label>
                            <input type="text" name="title" value="{{ old('title', $assignment->title) }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Lengkap</label>
                            <textarea name="description" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('description', $assignment->description) }}</textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Deadline (Batas Akhir)</label>
                                <!-- Format to match datetime-local input YYYY-MM-DDThh:mm -->
                                <input type="datetime-local" name="deadline" value="{{ old('deadline', \Carbon\Carbon::parse($assignment->deadline)->format('Y-m-d\TH:i')) }}" required class="shadow appearance-none border rounded py-2 px-3 text-gray-700 w-full focus:outline-none focus:shadow-outline">
                                @error('deadline') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nilai Maksimal</label>
                                <input type="number" name="max_score" value="{{ old('max_score', $assignment->max_score) }}" min="1" max="1000" required class="shadow appearance-none border rounded py-2 px-3 text-gray-700 w-full focus:outline-none focus:shadow-outline">
                                @error('max_score') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Lampiran File Baru (Abaikan jika tidak ingin mengubah)</label>
                            @if($assignment->attachment_path)
                                <p class="text-sm text-green-600 mb-2">File saat ini terlampir. Upload file baru akan menimpa file lama.</p>
                            @endif
                            <input type="file" name="attachment" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            @error('attachment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $assignment->is_published) ? 'checked' : '' }} class="form-checkbox">
                                <span class="ml-2 text-gray-700 font-bold">Publikasikan Langsung ke Siswa</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('assignments.show', $assignment) }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
