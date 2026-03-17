<x-app-layout>
    @section('header_title', 'Super Admin Dashboard')

    <div class="space-y-8">
        <!-- Action Bar / Welcome -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-card p-8 rounded-xl border border-border shadow-sm">
            <div class="space-y-1">
                <h3 class="text-2xl font-bold tracking-tight">Ringkasan Sistem</h3>
                <p class="text-sm text-muted-foreground">Kelola ekosistem pendidikan PSSM dari satu panel kontrol.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('users.index') }}" class="btn btn-primary shadow-sm">
                    <i data-lucide="user-plus" class="lucide"></i> Kelola User
                </a>
                <a href="{{ route('classes.index') }}" class="btn btn-outline">
                    <i data-lucide="building" class="lucide"></i> Data Kelas
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @php
                $statItems = [
                    ['label' => 'Total Siswa', 'value' => $stats['total_students'], 'icon' => 'user-check', 'color' => 'text-blue-600', 'bg' => 'bg-blue-500/10'],
                    ['label' => 'Total Guru', 'value' => $stats['total_teachers'], 'icon' => 'users', 'color' => 'text-green-600', 'bg' => 'bg-green-500/10'],
                    ['label' => 'Total Kelas', 'value' => $stats['total_classes'], 'icon' => 'building', 'color' => 'text-amber-600', 'bg' => 'bg-amber-500/10'],
                    ['label' => 'Tugas Aktif', 'value' => $stats['active_assignments'], 'icon' => 'file-text', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-500/10'],
                    ['label' => 'Kuis Aktif', 'value' => $stats['active_quizzes'], 'icon' => 'graduation-cap', 'color' => 'text-purple-600', 'bg' => 'bg-purple-500/10'],
                ];
            @endphp

            @foreach($statItems as $item)
                <div class="card p-6 flex flex-col justify-between space-y-4 hover:border-primary/30 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="p-2.5 rounded-lg {{ $item['bg'] }} {{ $item['color'] }}">
                            <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Live</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">{{ $item['label'] }}</p>
                        <p class="text-2xl font-bold tracking-tight">{{ number_format($item['value']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Analytics Chart -->
            <div class="lg:col-span-2 card">
                <div class="card-header border-b border-border bg-muted/30 flex-row justify-between items-center">
                    <div class="space-y-1">
                        <h3 class="card-title text-lg flex items-center">
                            <i data-lucide="bar-chart-3" class="lucide text-primary"></i> Registrasi Pengguna
                        </h3>
                        <p class="card-description">Statistik 6 bulan terakhir</p>
                    </div>
                </div>
                <div class="card-content p-8">
                    <div class="h-[300px] w-full">
                        <canvas id="userRegistrationChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Classes -->
            <div class="card">
                <div class="card-header border-b border-border bg-muted/30">
                    <h3 class="card-title text-lg flex items-center">
                        <i data-lucide="building-2" class="lucide text-amber-500"></i> Kelas Terbaru
                    </h3>
                </div>
                <div class="card-content p-0">
                    <div class="divide-y divide-border">
                        @forelse($recentClasses as $kelas)
                            <div class="p-5 flex justify-between items-center hover:bg-muted/30 transition-colors">
                                <div class="min-w-0 pr-4">
                                    <p class="font-bold text-sm truncate">{{ $kelas->name }}</p>
                                    <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-tighter mt-1">{{ $kelas->academicYear->name ?? 'Tak Ada TA' }}</p>
                                </div>
                                <div class="shrink-0">
                                    <span class="badge badge-secondary">
                                        {{ $kelas->students_count }} SISWA
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-16 text-muted-foreground">
                                <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-20"></i>
                                <p class="text-[10px] font-bold uppercase tracking-widest">Belum ada kelas</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if($recentClasses->isNotEmpty())
                    <div class="card-footer border-t border-border bg-muted/10 justify-center p-4">
                        <a href="{{ route('classes.index') }}" class="btn btn-ghost btn-sm w-full text-[10px] font-bold uppercase tracking-widest">
                            Lihat Semua <i data-lucide="arrow-right" class="w-3 h-3 ml-2"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('userRegistrationChart').getContext('2d');
            const labels = {!! json_encode($chartData['labels']) !!};
            const data = {!! json_encode($chartData['data']) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Registrasi',
                        data: data,
                        backgroundColor: 'hsl(222.2, 47.4%, 11.2%)', // Primary color
                        borderRadius: 4,
                        hoverBackgroundColor: 'hsl(222.2, 47.4%, 20%)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [4, 4], color: '#f1f5f9' },
                            ticks: { font: { size: 10, weight: '500' }, color: '#64748b' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10, weight: '500' }, color: '#64748b' }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-app-layout>
