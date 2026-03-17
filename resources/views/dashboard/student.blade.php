<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg shadow-lg p-6 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold mb-1">Halo, {{ auth()->user()->name }}! 👋</h3>
                    <p class="text-blue-100">Tetap semangat belajar hari ini. Ada beberapa tugas yang harus kamu selesaikan.</p>
                </div>
                <!-- Profile picture placeholder -->
                <div class="hidden sm:block">
                    <div class="h-16 w-16 rounded-full bg-white bg-opacity-20 flex items-center justify-center text-2xl font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Urgensi Tugas (Upcoming Assignments) -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-5 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-lg">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i> Tugas Mendatang
                        </h3>
                        <a href="{{ route('assignments.index') }}" class="text-sm text-indigo-600 hover:underline">Lihat Semua</a>
                    </div>
                    <div class="p-0">
                        <ul class="divide-y divide-gray-200">
                            @forelse($upcomingAssignments as $assignment)
                                @php
                                    $isSubmitted = in_array($assignment->id, $submittedAssignmentIds);
                                    $daysLeft = now()->diffInDays($assignment->deadline, false);
                                    $urgencyColor = $daysLeft <= 1 ? 'text-red-600 bg-red-50' : ($daysLeft <= 3 ? 'text-yellow-600 bg-yellow-50' : 'text-gray-600');
                                @endphp
                                <li class="p-5 hover:bg-gray-50 transition duration-150">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                @if($isSubmitted)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 mr-2">Selesai</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 mr-2">Belum</span>
                                                @endif
                                                <span class="text-xs text-indigo-600 font-semibold">{{ $assignment->subject->name }}</span>
                                            </div>
                                            <a href="{{ route('assignments.show', $assignment) }}" class="text-gray-900 font-bold hover:text-indigo-600 text-base">
                                                {{ $assignment->title }}
                                            </a>
                                            <p class="text-sm text-gray-500 mt-1">Guru: {{ $assignment->teacher->name }}</p>
                                        </div>
                                        <div class="text-right pl-4">
                                            <div class="text-sm font-bold {{ $urgencyColor }} px-3 py-1 rounded-lg">
                                                {{ \Carbon\Carbon::parse($assignment->deadline)->format('d M y, H:i') }}
                                            </div>
                                            @if(!$isSubmitted)
                                                <p class="text-xs text-red-500 mt-1 font-semibold">Tenggat: {{ \Carbon\Carbon::parse($assignment->deadline)->diffForHumans() }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="p-8 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="mt-2 font-medium">Yeay! Tidak ada tugas mendesak.</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Kolom Kanan: Kuis & History Nilai -->
                <div class="space-y-6">
                    
                    <!-- Kuis Aktif -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-5 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800 text-lg">
                                <i class="fas fa-laptop-code text-purple-500 mr-2"></i> Kuis / Ujian Aktif
                            </h3>
                        </div>
                        <div class="p-4">
                            @forelse($activeQuizzes as $quiz)
                                <div class="mb-3 p-4 border rounded-lg border-purple-100 bg-purple-50 flex justify-between items-center group hover:bg-purple-100 transition">
                                    <div>
                                        <p class="font-bold text-purple-900 text-sm md:text-base">{{ $quiz->title }}</p>
                                        <p class="text-xs text-purple-700 mt-1">{{ $quiz->subject->name }} | {{ $quiz->duration_minutes }} Menit</p>
                                    </div>
                                    <a href="{{ route('quizzes.show', $quiz) }}" class="bg-purple-600 text-white text-sm font-bold py-2 px-4 rounded shadow hover:bg-purple-700 transition">
                                        Kerjakan
                                    </a>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">Tidak ada kuis yang sedang berlangsung.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- History Nilai Terbaru -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-5 border-b border-gray-200">
                            <h3 class="font-bold text-gray-800 text-lg">
                                <i class="fas fa-award text-yellow-500 mr-2"></i> Nilai Terkini
                            </h3>
                        </div>
                        <div class="p-0">
                            <ul class="divide-y divide-gray-100">
                                @forelse($recentGrades as $grade)
                                    <li class="p-4 flex justify-between items-center">
                                        <div>
                                            <p class="font-medium text-sm text-gray-800 line-clamp-1">{{ $grade->assignment->title }}</p>
                                            <p class="text-xs text-gray-500">{{ $grade->assignment->subject->name }} • {{ \Carbon\Carbon::parse($grade->graded_at)->diffForHumans() }}</p>
                                        </div>
                                        <div class="bg-green-50 text-green-700 font-black text-lg px-3 py-1 rounded">
                                            {{ $grade->score }}
                                        </div>
                                    </li>
                                @empty
                                    <li class="p-6 text-center text-sm text-gray-500">Belum ada tugas yang dinilai.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
