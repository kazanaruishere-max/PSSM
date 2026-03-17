<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Kuis: {{ $quiz->title }}
            </h2>
            @if(auth()->user()->hasRole('super_admin') || auth()->user()->id === $quiz->teacher_id)
                <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kuis ini beserta semua jawaban siswa? Tindakan ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Hapus Kuis
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Quiz Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Mata Pelajaran</p>
                            <p class="font-bold">{{ $quiz->subject->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Kelas</p>
                            <p class="font-bold">{{ $quiz->class->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sistem Pembuatan</p>
                            @if($quiz->is_ai_generated)
                                <span class="px-2 py-1 text-xs font-bold rounded bg-purple-100 text-purple-700">AI Generated</span>
                            @else
                                <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-700">Manual</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jadwal Pelaksanaan</p>
                            <p class="font-bold text-sm">
                                {{ \Carbon\Carbon::parse($quiz->start_time)->format('d/m/Y H:i') }} - 
                                {{ \Carbon\Carbon::parse($quiz->end_time)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Durasi & Kesempatan</p>
                            <p class="font-bold">{{ $quiz->duration_minutes }} Menit / {{ $quiz->max_attempts }}x Percobaan</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nilai Maksimal</p>
                            <p class="font-bold text-green-600">{{ $quiz->max_score }}</p>
                        </div>
                    </div>

                    @if($quiz->description)
                    <div class="mt-4 p-4 bg-gray-50 rounded">
                        <p class="text-sm text-gray-600 mb-1">Deskripsi:</p>
                        <p class="text-gray-800">{{ $quiz->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Questions Preview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ expanded: false }">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                    <h3 class="font-bold text-lg text-gray-800">
                        Pratinjau Soal ({{ $quiz->questions->count() }} Soal)
                    </h3>
                    <button class="text-gray-500">
                        <svg class="w-6 h-6 transform transition-transform" :class="{'rotate-180': expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>
                
                <div x-show="expanded" x-collapse class="p-6">
                    @forelse($quiz->questions as $index => $q)
                        <div class="mb-6 pb-6 border-b last:border-b-0 last:mb-0 last:pb-0">
                            <h4 class="font-bold text-gray-800 mb-2">{{ $index + 1 }}. {{ $q->question_text }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                                @if(is_array($q->options))
                                    @foreach($q->options as $key => $option)
                                        <div class="p-3 rounded border bg-white flex items-start">
                                            <span class="font-bold mr-2 text-gray-500">{{ $key }}.</span>
                                            <span>{{ $option }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @if($q->explanation)
                                <div class="mt-3 text-sm text-blue-800 bg-blue-50 p-3 rounded">
                                    <strong>Penjelasan:</strong> {{ $q->explanation }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 italic">Belum ada soal untuk kuis ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Student Attempts Summary for Teachers -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg text-gray-800">Laporan Hasil Siswa</h3>
                        <a href="{{ route('reports.quiz', $quiz) }}" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded inline-flex items-center">
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
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Durasi</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kesempatan Ke</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attempts as $attempt)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 font-bold whitespace-no-wrap">{{ $attempt->student->name }}</p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">
                                            {{ $attempt->submitted_at ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d/m/Y H:i') : 'Sedang Mengerjakan / Batal' }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">
                                            {{ $attempt->time_taken_seconds ? floor($attempt->time_taken_seconds / 60) . ' menit ' . ($attempt->time_taken_seconds % 60) . ' detik' : '-' }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">{{ $attempt->attempt_number }}</p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        @if($attempt->submitted_at)
                                            <span class="font-bold text-lg {{ $attempt->score >= ($quiz->max_score * 0.75) ? 'text-green-600' : 'text-yellow-600' }}">
                                                {{ $attempt->score }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 font-medium">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                                        Belum ada siswa yang mengerjakan kuis ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
