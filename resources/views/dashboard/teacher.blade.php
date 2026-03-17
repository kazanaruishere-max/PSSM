<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="text-gray-600">
                Selamat mengajar, <strong>{{ auth()->user()->name }}</strong>.
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                            <i class="fas fa-chalkboard-teacher text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-semibold">Kelas Saya</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $classesCount }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                            <i class="fas fa-file-signature text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-semibold">Tugas Menunggu Dinilai</p>
                            <p class="text-2xl font-bold {{ $pendingGrading > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $pendingGrading }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-semibold">Tugas Aktif</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $activeAssignments }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                            <i class="fas fa-question-circle text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-semibold">Kuis Aktif</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $activeQuizzes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Action Buttons -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Aksi Cepat</h3>
                    <div class="flex flex-col space-y-3">
                        <a href="{{ route('assignments.create') }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fas fa-plus mr-2"></i> Buat Tugas Baru
                        </a>
                        <a href="{{ route('quizzes.create') }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                            <i class="fas fa-magic mr-2"></i> Buat Kuis (AI)
                        </a>
                    </div>
                    
                    <div class="mt-8">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Nilai Rata-Rata Kelas (Mock)</h3>
                        <div class="relative h-40">
                            <canvas id="averageScoreChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Assignments -->
                <div class="col-span-1 lg:col-span-2 bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2 flex justify-between items-center">
                        Tugas Terkini
                        <a href="{{ route('assignments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-normal">Lihat Semua</a>
                    </h3>
                    
                    <ul class="divide-y divide-gray-200">
                        @forelse($recentAssignments as $assignment)
                            <li class="py-4 hover:bg-gray-50 -mx-6 px-6 transition-colors">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <a href="{{ route('assignments.show', $assignment) }}">{{ $assignment->title }}</a>
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $assignment->class->name }} | {{ $assignment->subject->name }}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <p class="text-sm {{ now()->gt($assignment->deadline) ? 'text-red-500' : 'text-gray-900' }}">
                                            Batas: {{ \Carbon\Carbon::parse($assignment->deadline)->format('d M') }}
                                        </p>
                                        @if($assignment->is_published)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Dipublikasi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Draft
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4 text-center text-sm text-gray-500">
                                Belum ada tugas yang dibuat.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('averageScoreChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['10A', '10B', '11 IPA'],
                    datasets: [{
                        label: 'Rata-rata Nilai',
                        data: [85, 78, 92],
                        backgroundColor: ['rgba(99, 102, 241, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)'],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100 } }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
