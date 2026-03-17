<x-app-layout>
    @section('header_title', 'Dashboard Siswa')

    <div class="space-y-10">
        <!-- Student Hero Section -->
        <div class="relative overflow-hidden rounded-xl border border-border bg-card p-8 md:p-12 shadow-sm">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="text-center md:text-left space-y-2">
                    <h3 class="text-3xl md:text-4xl font-bold tracking-tight">Halo, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h3>
                    <p class="text-muted-foreground text-lg max-w-md">Waktunya bersinar! Cek tugasmu dan selesaikan tantangan hari ini.</p>
                </div>
                <div class="flex gap-4">
                    <div class="flex flex-col items-center justify-center p-6 bg-secondary/50 rounded-lg border border-border min-w-[120px]">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Tugas</p>
                        <p class="text-3xl font-bold">{{ $upcomingAssignments->count() }}</p>
                    </div>
                    <div class="flex flex-col items-center justify-center p-6 bg-secondary/50 rounded-lg border border-border min-w-[120px]">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">Kuis</p>
                        <p class="text-3xl font-bold">{{ $activeQuizzes->count() }}</p>
                    </div>
                </div>
            </div>
            <!-- Subtle background decoration -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-secondary/20 rounded-full blur-3xl"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left: Upcoming Assignments -->
            <div class="card">
                <div class="card-header border-b border-border bg-muted/30 flex-row justify-between items-center">
                    <div class="space-y-1">
                        <h3 class="card-title text-lg flex items-center">
                            <i data-lucide="calendar-days" class="lucide text-primary"></i> Tugas Mendatang
                        </h3>
                    </div>
                    <a href="{{ route('assignments.index') }}" class="btn btn-ghost btn-sm text-[10px] uppercase tracking-widest">
                        Lihat Semua <i data-lucide="chevron-right" class="w-3 h-3 ml-1"></i>
                    </a>
                </div>
                <div class="card-content p-0">
                    <div class="divide-y divide-border">
                        @forelse($upcomingAssignments as $assignment)
                            @php
                                $isSubmitted = in_array($assignment->id, $submittedAssignmentIds);
                                $deadline = \Carbon\Carbon::parse($assignment->deadline);
                                $isUrgent = $deadline->diffInDays(now()) <= 1 && !$isSubmitted;
                            @endphp
                            <div class="p-6 transition-colors hover:bg-muted/30 group">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex-1 space-y-3">
                                        <div class="flex items-center gap-2">
                                            <span class="badge badge-secondary">{{ $assignment->subject->name }}</span>
                                            @if($isSubmitted)
                                                <span class="badge badge-default bg-green-500 hover:bg-green-600 border-transparent text-white">Selesai</span>
                                            @elseif($isUrgent)
                                                <span class="badge badge-destructive animate-pulse">Urgent</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('assignments.show', $assignment) }}" class="text-lg font-semibold tracking-tight hover:underline underline-offset-4">
                                            {{ $assignment->title }}
                                        </a>
                                        <div class="flex items-center text-xs text-muted-foreground">
                                            <i data-lucide="user" class="w-3 h-3 mr-1.5"></i> Guru: {{ $assignment->teacher->name }}
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 space-y-1">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Deadline</p>
                                        <p class="text-sm font-semibold {{ $isUrgent ? 'text-destructive' : 'text-foreground' }}">
                                            {{ $deadline->format('d M, H:i') }}
                                        </p>
                                        @if(!$isSubmitted)
                                            <p class="text-[10px] text-muted-foreground italic">{{ $deadline->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-20 text-muted-foreground">
                                <i data-lucide="check-circle-2" class="w-12 h-12 mb-4 opacity-20"></i>
                                <p class="text-sm font-medium">Semua tugas beres!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right: Quizzes & Grades -->
            <div class="space-y-8">
                <!-- Active Quizzes -->
                <div class="card border-primary/20">
                    <div class="card-header bg-primary text-primary-foreground flex-row justify-between items-center">
                        <h3 class="card-title text-lg flex items-center">
                            <i data-lucide="graduation-cap" class="lucide"></i> Kuis / CBT Aktif
                        </h3>
                        <span class="bg-primary-foreground/20 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest">{{ $activeQuizzes->count() }} LIVE</span>
                    </div>
                    <div class="card-content p-6">
                        <div class="space-y-4">
                            @forelse($activeQuizzes as $quiz)
                                <div class="p-6 rounded-lg border border-border bg-secondary/30 flex flex-col sm:flex-row justify-between items-center gap-6 group hover:border-primary/50 transition-all">
                                    <div class="text-center sm:text-left space-y-2">
                                        <p class="text-xl font-bold tracking-tight">{{ $quiz->title }}</p>
                                        <div class="flex justify-center sm:justify-start gap-2">
                                            <span class="badge badge-outline bg-background">{{ $quiz->subject->name }}</span>
                                            <span class="badge badge-outline bg-background">{{ $quiz->duration_minutes }} MIN</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('quizzes.show', $quiz) }}" class="w-full sm:w-auto btn btn-primary px-8">
                                        Kerjakan <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                                    </a>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 rounded-lg border-2 border-dashed border-border text-muted-foreground">
                                    <i data-lucide="ghost" class="w-10 h-10 mb-3 opacity-20"></i>
                                    <p class="text-[10px] font-bold uppercase tracking-widest">Belum ada kuis aktif</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Recent Grades -->
                <div class="card">
                    <div class="card-header border-b border-border bg-muted/30">
                        <h3 class="card-title text-lg flex items-center">
                            <i data-lucide="award" class="lucide text-yellow-500"></i> Nilai Terbaru
                        </h3>
                    </div>
                    <div class="card-content p-0">
                        <div class="divide-y divide-border">
                            @forelse($recentGrades as $grade)
                                <div class="p-6 flex justify-between items-center hover:bg-muted/30 transition-colors group">
                                    <div class="flex-1 min-w-0 pr-4 space-y-1">
                                        <p class="font-bold truncate text-foreground group-hover:text-primary transition-colors">{{ $grade->assignment->title }}</p>
                                        <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider">
                                            {{ $grade->assignment->subject->name }} &bull; {{ \Carbon\Carbon::parse($grade->graded_at)->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center justify-center w-14 h-14 rounded-lg bg-green-500/10 text-green-600 border border-green-500/20 font-bold text-xl shadow-sm">
                                            {{ $grade->score }}
                                        </div>
                                        <i data-lucide="chevron-right" class="w-4 h-4 text-muted-foreground opacity-50"></i>
                                    </div>
                                </div>
                            @empty
                                <div class="flex items-center justify-center py-16 text-muted-foreground italic">
                                    <p class="text-[10px] font-bold uppercase tracking-widest">Belum ada nilai yang keluar</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
