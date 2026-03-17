<x-app-layout>
    @section('header_title', 'Persiapan Ujian / Kuis')

    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Back Link -->
        <div class="mb-4">
            <a href="{{ route('quizzes.index') }}" class="inline-flex items-center text-[10px] font-black text-slate-400 hover:text-indigo-600 uppercase tracking-[0.2em] transition-colors">
                <i class="fas fa-arrow-left mr-2 text-[8px]"></i> Kembali ke Daftar Kuis
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-2xl flex items-center shadow-sm">
                <i class="fas fa-check-circle text-green-500 mr-4 text-xl"></i>
                <p class="text-green-800 font-bold uppercase tracking-widest text-[10px]">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl shadow-sm">
                <ul class="list-disc pl-5 text-red-700 text-xs font-bold space-y-1 uppercase tracking-widest">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Exam Card -->
        <div class="card-modern overflow-hidden">
            <div class="flex flex-col lg:flex-row">
                <!-- Left Content: Info -->
                <div class="flex-1 p-10 lg:p-14 border-b lg:border-b-0 lg:border-r border-slate-50">
                    <div class="mb-10">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="badge badge-indigo italic">CBT ONLINE</span>
                            <span class="badge badge-purple uppercase tracking-widest">{{ $quiz->subject->name ?? 'UMUM' }}</span>
                        </div>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tighter leading-tight mb-4 italic uppercase">
                            {{ $quiz->title }}
                        </h3>
                        <p class="text-slate-500 font-medium leading-relaxed max-w-xl">
                            {{ $quiz->description ?? 'Harap baca petunjuk pengerjaan dengan teliti sebelum memulai ujian ini.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 pt-8 border-t border-slate-50">
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Batas Waktu</p>
                            <p class="text-sm font-black text-slate-900 uppercase">
                                {{ \Carbon\Carbon::parse($quiz->start_time)->translatedFormat('d M, H:i') }} - 
                                {{ \Carbon\Carbon::parse($quiz->end_time)->translatedFormat('d M, H:i') }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Durasi Pengerjaan</p>
                            <p class="text-xl font-black text-indigo-600 italic uppercase">
                                {{ $quiz->duration_minutes }} MENIT
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nilai Maksimal</p>
                            <p class="text-sm font-black text-slate-900">SKOR {{ $quiz->max_score }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status Ujian</p>
                            <span class="badge {{ $quiz->is_published ? 'badge-green' : 'badge-red' }}">
                                {{ $quiz->is_published ? 'AKTIF' : 'DRAFT' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Action -->
                <div class="w-full lg:w-96 bg-slate-50/50 p-10 lg:p-14 flex flex-col justify-center items-center">
                    @php
                        $now = now();
                        $isStarted = $now->gte($quiz->start_time);
                        $isEnded = $now->gt($quiz->end_time);
                        $attemptsLeft = $quiz->max_attempts - $attempts->count();
                        $activeAttempt = $attempts->firstWhere(function ($a) { return $a->submitted_at === null; });
                    @endphp

                    <div class="text-center mb-10">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Sisa Kesempatan</p>
                        <div class="relative inline-block">
                            <div class="text-6xl font-black {{ $attemptsLeft > 0 ? 'text-indigo-600' : 'text-red-500' }} italic">
                                {{ $attemptsLeft }}
                            </div>
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">DARI {{ $quiz->max_attempts }} PERCOBAAN</div>
                        </div>
                    </div>

                    <div class="w-full space-y-4">
                        @if($activeAttempt)
                            <a href="{{ route('quizzes.active', [$quiz, $activeAttempt]) }}" class="btn-primary w-full py-5 bg-amber-500 hover:bg-amber-600 shadow-amber-100">
                                <i class="fas fa-play-circle mr-2"></i> LANJUTKAN UJIAN
                            </a>
                            <p class="text-[9px] text-center font-black text-amber-600 uppercase tracking-widest">Ada sesi yang belum selesai!</p>
                        @elseif($isEnded)
                            <div class="p-5 bg-red-100 text-red-700 rounded-2xl text-center font-black text-xs uppercase tracking-widest border border-red-200">
                                <i class="fas fa-times-circle mr-2"></i> Waktu Berakhir
                            </div>
                        @elseif(!$isStarted)
                            <div class="p-5 bg-slate-200 text-slate-500 rounded-2xl text-center font-black text-xs uppercase tracking-widest">
                                <i class="fas fa-lock mr-2"></i> Belum Dibuka
                            </div>
                            <p class="text-[9px] text-center font-black text-slate-400 uppercase tracking-widest mt-2">BUKA PADA {{ \Carbon\Carbon::parse($quiz->start_time)->format('H:i') }} WIB</p>
                        @elseif($attemptsLeft <= 0)
                            <div class="p-5 bg-red-100 text-red-700 rounded-2xl text-center font-black text-xs uppercase tracking-widest border border-red-200">
                                <i class="fas fa-ban mr-2"></i> Kesempatan Habis
                            </div>
                        @else
                            <form action="{{ route('quizzes.take', $quiz) }}" method="POST" class="w-full" onsubmit="return confirm('Mulai ujian sekarang? Waktu akan otomatis berjalan.');">
                                @csrf
                                <button type="submit" class="btn-primary w-full py-5">
                                    <i class="fas fa-pencil-alt mr-2"></i> MULAI SEKARANG
                                </button>
                                <p class="text-[9px] text-center font-black text-slate-400 uppercase tracking-widest mt-4 italic opacity-70">Sistem akan mengunci pengerjaan anda.</p>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Attempts History -->
        @if($attempts->count() > 0)
            <div class="card-modern p-10">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                        <i class="fas fa-history text-sm"></i>
                    </div>
                    <h4 class="text-xl font-black text-slate-900 italic tracking-tight uppercase">Riwayat Percobaan</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($attempts as $attempt)
                        <div class="p-6 rounded-3xl border-2 {{ $attempt->submitted_at ? 'bg-white border-slate-50' : 'bg-amber-50 border-amber-100 animate-pulse' }} flex justify-between items-center transition-all hover:shadow-sm">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">PERCOBAAN KE-{{ $attempt->attempt_number }}</p>
                                <p class="text-xs font-bold text-slate-700">
                                    <i class="far fa-calendar-alt mr-1.5 opacity-40"></i> {{ \Carbon\Carbon::parse($attempt->started_at)->translatedFormat('d M Y, H:i') }}
                                </p>
                                @if($attempt->submitted_at)
                                    <p class="text-[9px] font-black text-green-600 uppercase tracking-tighter mt-1">SELESAI PADA {{ \Carbon\Carbon::parse($attempt->submitted_at)->format('H:i:s') }}</p>
                                @endif
                            </div>
                            
                            <div class="text-right">
                                @if($attempt->submitted_at)
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">SKOR AKHIR</p>
                                    <p class="text-4xl font-black text-indigo-600 italic leading-none">{{ $attempt->score }}</p>
                                @else
                                    <span class="badge badge-indigo">SEDANG BERJALAN</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @php $best = $attempts->max('score'); @endphp
                @if($best !== null && $attempts->where('submitted_at', '!=', null)->count() > 0)
                    <div class="mt-10 p-8 bg-[#0F172A] rounded-[2rem] text-center relative overflow-hidden group">
                        <div class="relative z-10">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] mb-2">HASIL TERBAIK ANDA</p>
                            <p class="text-6xl font-black text-white italic group-hover:scale-110 transition-transform duration-500">{{ $best }}</p>
                        </div>
                        <i class="fas fa-award absolute -left-4 -bottom-4 text-7xl text-white/5 rotate-12"></i>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
