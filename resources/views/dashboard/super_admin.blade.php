<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Super Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-4 text-gray-600">
                Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong>.
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500 font-semibold truncate">Total Siswa</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_students']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-500 font-semibold truncate">Total Guru</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_teachers']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-500 font-semibold truncate">Total Kelas</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_classes']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                    <p class="text-sm text-gray-500 font-semibold truncate">Tugas Aktif</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['active_assignments']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <p class="text-sm text-gray-500 font-semibold truncate">Kuis Aktif</p>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['active_quizzes']) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Analytics Chart Placeholder -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Aktivitas Sistem (Data Mock)</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="systemActivityChart"></canvas>
                    </div>
                </div>

                <!-- Recent Classes -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Kelas Terdaftar Baru</h3>
                    <ul class="divide-y divide-gray-200">
                        @forelse($recentClasses as $kelas)
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $kelas->name }}</p>
                                    <p class="text-xs text-gray-500 whitespace-nowrap">{{ $kelas->academicYear ? $kelas->academicYear->year : 'Tak Ada Tahun Ajaran' }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $kelas->students_count }} Siswa
                                </span>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-500 text-center">Belum ada kelas terdaftar.</li>
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
            const ctx = document.getElementById('systemActivityChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                    datasets: [{
                        label: 'Pengumpulan Tugas',
                        data: [120, 190, 150, 220, 300, 50, 40],
                        borderColor: 'rgb(99, 102, 241)',
                        tension: 0.3,
                        fill: true,
                        backgroundColor: 'rgba(99, 102, 241, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
