<x-app-layout>
    @section('header_title', 'Dashboard Guru')

    <div class="space-y-8">
        <!-- Teacher Welcome -->
        <div class="relative overflow-hidden rounded-xl border border-border bg-card p-8 shadow-sm">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-lg bg-primary flex items-center justify-center text-primary-foreground shadow-lg shadow-primary/20">
                        <i data-lucide="book-open" class="w-8 h-8"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-2xl md:text-3xl font-bold tracking-tight">Selamat Mengajar, {{ auth()->user()->name }}! 📚</h3>
                        <p class="text-muted-foreground">Hari ini adalah hari yang luar biasa untuk berbagi ilmu.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                        <i data-lucide="plus" class="lucide"></i> Buat Tugas
                    </a>
                    <a href="{{ route('quizzes.create') }}" class="btn btn-outline">
                        <i data-lucide="sparkles" class="lucide"></i> Kuis AI
                    </a>
                </div>
            </div>
            <!-- Background element -->
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-primary/5 rounded-full blur-3xl"></div>
        </div>

        <!-- Teacher Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $teacherStats = [
                    ['label' => 'Kelas Saya', 'value' => $classesCount, 'icon' => 'school', 'color' => 'text-indigo-600', 'bg' => 'bg-indigo-500/10', 'desc' => 'Aktif Mengajar'],
                    ['label' => 'Perlu Dinilai', 'value' => $pendingGrading, 'icon' => 'file-edit', 'color' => 'text-red-600', 'bg' => 'bg-red-500/10', 'desc' => 'Tugas Masuk'],
                    ['label' => 'Tugas Aktif', 'value' => $activeAssignments, 'icon' => 'list-todo', 'color' => 'text-blue-600', 'bg' => 'bg-blue-500/10', 'desc' => 'Sedang Berjalan'],
                    ['label' => 'Kuis Aktif', 'value' => $activeQuizzes, 'icon' => 'graduation-cap', 'color' => 'text-purple-600', 'bg' => 'bg-purple-500/10', 'desc' => 'CBT Online'],
                ];
            @endphp

            @foreach($teacherStats as $stat)
                <div class="card p-6 space-y-4 hover:border-primary/30 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="p-2.5 rounded-lg {{ $stat['bg'] }} {{ $stat['color'] }}">
                            <i data-lucide="{{ $stat['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">{{ $stat['label'] }}</p>
                            <p class="text-2xl font-bold tracking-tight">{{ number_format($stat['value']) }}</p>
                        </div>
                    </div>
                    <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-tighter">{{ $stat['desc'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Chart -->
            <div class="card">
                <div class="card-header border-b border-border bg-muted/30">
                    <h3 class="card-title text-lg flex items-center">
                        <i data-lucide="pie-chart" class="lucide text-primary"></i> Statistik Tugas
                    </h3>
                </div>
                <div class="card-content p-8">
                    <div class="h-64">
                        <canvas id="submissionRateChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right: Recent Assignments -->
            <div class="lg:col-span-2 card">
                <div class="card-header border-b border-border bg-muted/30 flex-row justify-between items-center">
                    <h3 class="card-title text-lg flex items-center">
                        <i data-lucide="history" class="lucide text-primary"></i> Tugas Terbaru
                    </h3>
                    <a href="{{ route('assignments.index') }}" class="btn btn-ghost btn-sm text-[10px] font-bold uppercase tracking-widest">Lihat Semua</a>
                </div>
                
                <div class="card-content p-0">
                    <div class="divide-y divide-border">
                        @forelse($recentAssignments as $assignment)
                            <div class="p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:bg-muted/30 transition-colors group">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="badge badge-secondary">{{ $assignment->class_->name ?? 'N/A' }}</span>
                                        <span class="badge badge-outline">{{ $assignment->subject->name ?? 'N/A' }}</span>
                                    </div>
                                    <a href="{{ route('assignments.show', $assignment) }}" class="text-lg font-bold tracking-tight hover:text-primary transition-colors block">
                                        {{ $assignment->title }}
                                    </a>
                                </div>
                                <div class="flex items-center gap-8 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 pt-4 md:pt-0">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Deadline</p>
                                        <p class="text-sm font-semibold {{ now()->gt($assignment->deadline) ? 'text-destructive' : 'text-foreground' }}">
                                            {{ \Carbon\Carbon::parse($assignment->deadline)->format('d M, H:i') }}
                                        </p>
                                    </div>
                                    <div class="shrink-0">
                                        @if($assignment->is_published)
                                            <span class="badge badge-default bg-green-500/10 text-green-600 border-green-500/20">PUBLISHED</span>
                                        @else
                                            <span class="badge badge-secondary">DRAFT</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-20 text-muted-foreground">
                                <i data-lucide="pen-tool" class="w-12 h-12 mb-4 opacity-20"></i>
                                <p class="text-[10px] font-bold uppercase tracking-widest">Belum ada tugas dibuat</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('submissionRateChart').getContext('2d');
            const labels = {!! json_encode($chartData['labels']) !!};
            const data = {!! json_encode($chartData['data']) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pengumpulan',
                        data: data,
                        borderColor: 'hsl(222.2, 47.4%, 11.2%)',
                        backgroundColor: 'rgba(15, 23, 42, 0.05)',
                        borderWidth: 2,
                        pointBackgroundColor: 'white',
                        pointBorderColor: 'hsl(222.2, 47.4%, 11.2%)',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { display: false },
                        x: { display: false }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-app-layout>
