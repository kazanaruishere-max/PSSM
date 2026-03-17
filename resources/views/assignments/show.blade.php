<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $assignment->title }}
            </h2>
            <div class="space-x-2">
                @can('assignments.edit')
                    <a href="{{ route('assignments.edit', $assignment) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                        Edit
                    </a>
                @endcan
                @can('assignments.delete')
                    <form action="{{ route('assignments.destroy', $assignment) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus tugas ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Hapus
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
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

            <!-- Assignment Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Mata Pelajaran</p>
                            <p class="font-bold">{{ $assignment->subject->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Kelas</p>
                            <p class="font-bold">{{ $assignment->class->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Guru</p>
                            <p class="font-bold">{{ $assignment->teacher->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Batas Waktu</p>
                            <p class="font-bold {{ now()->gt($assignment->deadline) ? 'text-red-500' : 'text-blue-600' }}">
                                {{ $assignment->deadline }}
                                {{ now()->gt($assignment->deadline) ? '(Tenggat Waktu Berakhir)' : '' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nilai Maksimal</p>
                            <p class="font-bold">{{ $assignment->max_score }}</p>
                        </div>
                    </div>

                    <div class="mt-6 prose max-w-none">
                        <p class="text-sm text-gray-500 mb-2">Deskripsi Tugas</p>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            {!! nl2br(e($assignment->description)) !!}
                        </div>
                    </div>

                    @if($assignment->attachment_path)
                    <div class="mt-6">
                        <p class="text-sm text-gray-500 mb-2">Lampiran File</p>
                        <a href="{{ Storage::url($assignment->attachment_path) }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            Download Lampiran
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Student Submission Form -->
            @if(auth()->user()->hasRole('student') || auth()->user()->hasRole('class_leader'))
                @php
                    $latestSubmission = $submissions->first();
                    $isGraded = $latestSubmission && $latestSubmission->graded_at != null;
                @endphp
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-bold text-lg mb-4">Pengumpulan Tugas Anda</h3>
                        
                        @if($latestSubmission)
                            <div class="mb-6 p-4 border rounded-lg {{ $isGraded ? 'bg-green-50 border-green-200' : 'bg-blue-50 border-blue-200' }}">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-semibold text-gray-700">Versi Pengumpulan: #{{ $latestSubmission->version }}</span>
                                    <span class="text-sm text-gray-500">{{ $latestSubmission->created_at }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-sm text-gray-500 block">Status:</span>
                                    @if($isGraded)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Sudah Dinilai</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Menunggu Penilaian</span>
                                    @endif
                                </div>
                                
                                @if($latestSubmission->file_path)
                                <div class="mb-4">
                                    <a href="{{ route('submissions.download', $latestSubmission) }}" class="text-indigo-600 hover:text-indigo-900 underline text-sm">
                                        Unduh File Tersimpan
                                    </a>
                                </div>
                                @endif

                                @if($isGraded)
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="flex items-center space-x-4">
                                            <div>
                                                <span class="text-sm text-gray-500 block">Nilai Anda:</span>
                                                <span class="text-2xl font-bold text-green-600">{{ $latestSubmission->score }} <span class="text-sm text-gray-500 font-normal">/ {{ $assignment->max_score }}</span></span>
                                            </div>
                                        </div>
                                        @if($latestSubmission->teacher_feedback)
                                        <div class="mt-4">
                                            <span class="text-sm text-gray-500 block">Komentar Guru:</span>
                                            <p class="text-gray-800 mt-1 italic">"{{ $latestSubmission->teacher_feedback }}"</p>
                                        </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(!$isGraded)
                            <form action="{{ route('submissions.store', $assignment) }}" method="POST" enctype="multipart/form-data" class="mt-6 border-t pt-6">
                                @csrf
                                <h4 class="font-medium text-gray-800 mb-3">{{ $latestSubmission ? 'Kirim Ulang Tugas (Versi Baru)' : 'Kirim Tugas Baru' }}</h4>
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Jawaban Text (Opsional)</label>
                                    <textarea name="content" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Tulis jawaban di sini..."></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">File Tugas</label>
                                    <input type="file" name="file" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <p class="text-xs text-gray-500 mt-1">Maksimum 20MB. Format: PDF, Word, Image.</p>
                                </div>
                                <div class="flex items-center justify-end mt-4">
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                                        {{ $latestSubmission ? 'Kirim Versi Baru' : 'Kumpulkan Tugas' }}
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Teacher Grading View -->
            @can('assignments.grade')
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg">Pengumpulan Siswa ({{ $submissions->count() }})</h3>
                        <a href="{{ route('reports.assignment', $assignment) }}" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export CSV
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Siswa</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Waktu Submit</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">File</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status & Nilai</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($submissions as $submission)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap font-bold">{{ $submission->student->name }}</p>
                                        <p class="text-gray-500 text-xs">V.{{ $submission->version }}</p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap {{ $submission->is_late ? 'text-red-500 font-semibold' : '' }}">
                                            {{ $submission->created_at->format('d/m/Y H:i') }}
                                            @if($submission->is_late)
                                                <br><span class="text-xs text-red-500">Terlambat</span>
                                            @endif
                                        </p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        @if($submission->file_path)
                                            <a href="{{ route('submissions.download', $submission) }}" class="text-blue-600 hover:text-blue-900 underline">Download File</a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        @if($submission->graded_at)
                                            <span class="font-bold text-green-600">{{ $submission->score }} / {{ $assignment->max_score }}</span>
                                        @else
                                            <span class="text-yellow-600 font-semibold">Perlu Dinilai</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
                                        <!-- Alpine x-data for grading modal inside the loop for simplicity -->
                                        <div x-data="{ open: false }">
                                            <button @click="open = true" class="text-indigo-600 hover:text-indigo-900 font-bold focus:outline-none">
                                                {{ $submission->graded_at ? 'Edit Nilai' : 'Beri Nilai' }}
                                            </button>

                                            <!-- Grading Modal -->
                                            <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                    <div x-show="open" @click="open = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                                                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                                    </div>
                                                    
                                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                                    
                                                    <div x-show="open" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                        <form action="{{ route('submissions.grade', $submission) }}" method="POST">
                                                            @csrf
                                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                                                    Beri Nilai: {{ $submission->student->name }}
                                                                </h3>
                                                                
                                                                @if($submission->content)
                                                                <div class="mb-4 bg-gray-50 p-3 rounded text-sm text-gray-700">
                                                                    <strong>Jawaban Teks:</strong><br>
                                                                    {{ $submission->content }}
                                                                </div>
                                                                @endif

                                                                <div class="mb-4">
                                                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nilai (0 - {{ $assignment->max_score }})</label>
                                                                    <input type="number" name="score" value="{{ $submission->score }}" min="0" max="{{ $assignment->max_score }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                                </div>
                                                                <div class="mb-4">
                                                                    <label class="block text-gray-700 text-sm font-bold mb-2">Komentar / Feedback Akademik</label>
                                                                    <textarea name="feedback" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ $submission->teacher_feedback }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                                    Simpan Nilai
                                                                </button>
                                                                <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                                    Batal
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                        Belum ada pengumpulan dari siswa.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endcan

        </div>
    </div>
</x-app-layout>
