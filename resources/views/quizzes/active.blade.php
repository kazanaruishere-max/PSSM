<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ujian Aktif: {{ $quiz->title }} | PSSM CBT</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-color: #F8FAFC !important;
        }
        .question-card {
            @apply bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8 transition-all duration-300;
        }
        .option-label {
            @apply relative flex items-center cursor-pointer p-5 border-2 border-slate-50 rounded-2xl hover:border-indigo-100 hover:bg-indigo-50/30 transition-all;
        }
        .option-label:has(input:checked) {
            @apply border-indigo-600 bg-indigo-50 shadow-md shadow-indigo-100/50;
        }
        .option-label:has(input:checked) .option-key {
            @apply bg-indigo-600 text-white border-transparent;
        }
        .option-key {
            @apply w-10 h-10 flex items-center justify-center rounded-xl border-2 border-slate-100 text-slate-400 font-black text-sm mr-4 transition-all;
        }
        .timer-box {
            @apply bg-slate-900 text-white px-6 py-3 rounded-2xl flex items-center shadow-lg border border-white/10;
        }
        .timer-box.urgent {
            @apply bg-red-600 animate-pulse border-red-400;
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #F1F5F9; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
    </style>
</head>
<body class="antialiased h-full flex flex-col" 
      x-data="quizTimer({{ $timeLeft }}, '{{ route('quizzes.submit', [$quiz, $attempt]) }}')">

    <!-- Top Navigation Bar -->
    <nav class="h-24 bg-[#0F172A] text-white shadow-2xl sticky top-0 z-50 px-8 lg:px-12">
        <div class="max-w-[1600px] mx-auto h-full flex justify-between items-center">
            <div class="flex items-center space-x-6">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <div class="hidden md:block">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-0.5">Ujian Sedang Berlangsung</p>
                    <h1 class="text-lg font-black tracking-tight italic uppercase truncate max-w-lg">{{ $quiz->title }}</h1>
                </div>
            </div>

            <div class="flex items-center space-x-8">
                <!-- TIMER -->
                <div class="timer-box" :class="{ 'urgent': timeLeft < 300 }">
                    <i class="far fa-clock mr-3 text-indigo-400" :class="{ 'text-white': timeLeft < 300 }"></i>
                    <span class="font-black text-2xl tracking-tighter tabular-nums" x-text="formattedTime">00:00:00</span>
                </div>
                
                <button type="button" @click="confirmSubmit()" class="hidden sm:flex bg-green-500 hover:bg-green-400 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-green-500/20 transition-all transform active:scale-95 uppercase tracking-widest text-xs">
                    Selesai Ujian <i class="fas fa-check-double ml-2"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="flex-grow max-w-[1600px] w-full mx-auto px-8 lg:px-12 py-12 flex flex-col lg:flex-row gap-12">
        
        <form id="quizForm" action="{{ route('quizzes.submit', [$quiz, $attempt]) }}" method="POST" class="w-full flex flex-col lg:flex-row gap-12">
            @csrf
            
            <!-- Left Side: Questions Container -->
            <div class="lg:flex-1 space-y-10 pb-32">
                @foreach($questions as $index => $q)
                    <div class="question-card" id="question-{{ $index + 1 }}">
                        <div class="flex items-start mb-8">
                            <span class="flex-shrink-0 bg-indigo-50 text-indigo-600 font-black w-14 h-14 rounded-2xl flex items-center justify-center mr-6 text-xl border-2 border-indigo-100 shadow-sm">
                                {{ $index + 1 }}
                            </span>
                            <div class="text-xl text-slate-900 font-bold leading-relaxed pt-2 whitespace-pre-line tracking-tight">
                                {{ $q->question_text }}
                            </div>
                        </div>

                        <div class="pl-0 sm:pl-20 space-y-4">
                            @if(is_array($q->options))
                                @foreach($q->options as $key => $option)
                                    <label class="option-label">
                                        <input type="radio" 
                                               name="answers[{{ $q->id }}]" 
                                               value="{{ $key }}" 
                                               class="sr-only peer"
                                               onclick="markAnswered({{ $index + 1 }})">
                                        
                                        <div class="option-key">{{ $key }}</div>
                                        <span class="text-slate-700 font-bold text-lg flex-1">{{ $option }}</span>
                                        <div class="hidden peer-checked:block text-indigo-600">
                                            <i class="fas fa-check-circle text-2xl"></i>
                                        </div>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right Side: Navigation & Summary Panel (Sticky) -->
            <div class="w-full lg:w-[380px]">
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-8 sticky top-32">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="font-black text-slate-900 italic uppercase tracking-tight text-lg">Navigasi Soal</h3>
                        <span class="bg-slate-100 text-slate-500 px-4 py-1.5 rounded-full text-[10px] font-black tracking-widest">{{ $questions->count() }} BUTIR</span>
                    </div>
                    
                    <div class="grid grid-cols-5 gap-3 mb-10">
                        @foreach($questions as $index => $q)
                            <a href="#question-{{ $index + 1 }}" 
                               id="nav-btn-{{ $index + 1 }}"
                               class="w-full aspect-square flex items-center justify-center rounded-2xl border-2 border-slate-100 text-sm font-black text-slate-400 hover:bg-slate-50 hover:border-indigo-200 transition-all shadow-sm">
                                {{ $index + 1 }}
                            </a>
                        @endforeach
                    </div>

                    <div class="space-y-3 mb-10 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                        <div class="flex items-center text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <div class="w-4 h-4 rounded-lg bg-indigo-600 mr-3 shadow-md shadow-indigo-200"></div> SUDAH DIJAWAB
                        </div>
                        <div class="flex items-center text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <div class="w-4 h-4 rounded-lg border-2 border-slate-200 bg-white mr-3"></div> BELUM DIJAWAB
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button type="button" @click="confirmSubmit()" class="w-full bg-green-500 hover:bg-green-600 text-white font-black py-5 px-4 rounded-3xl shadow-xl shadow-green-100 transition-all transform hover:-translate-y-1 uppercase tracking-widest text-sm flex justify-center items-center">
                            <i class="fas fa-paper-plane mr-3"></i> KIRIM JAWABAN
                        </button>
                        <p class="text-[9px] text-center font-black text-slate-400 uppercase tracking-[0.2em] leading-relaxed">
                            Pastikan koneksi internet stabil<br>sebelum mengirim jawaban
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Alpine JS Logics -->
    <script>
        function markAnswered(index) {
            const btn = document.getElementById('nav-btn-' + index);
            btn.classList.add('bg-indigo-600', 'text-white', 'border-transparent', 'shadow-indigo-200');
            btn.classList.remove('bg-white', 'text-slate-400', 'border-slate-100');
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('quizTimer', (initialSeconds, submitUrl) => ({
                timeLeft: initialSeconds,
                formattedTime: '00:00:00',
                timer: null,

                init() {
                    if(this.timeLeft <= 0) {
                        this.forceSubmit();
                        return;
                    }
                    this.updateFormattedTime();
                    this.timer = setInterval(() => {
                        this.timeLeft--;
                        this.updateFormattedTime();
                        
                        if (this.timeLeft <= 0) {
                            clearInterval(this.timer);
                            alert('Waktu pengerjaan telah habis!');
                            this.forceSubmit();
                        }
                    }, 1000);

                    window.addEventListener('beforeunload', this.beforeUnloadCheck);
                },

                updateFormattedTime() {
                    const hours = Math.floor(this.timeLeft / 3600);
                    const minutes = Math.floor((this.timeLeft % 3600) / 60);
                    const seconds = this.timeLeft % 60;
                    this.formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                confirmSubmit() {
                    if(confirm("Selesaikan ujian sekarang? Jawaban yang sudah dikirim tidak dapat diubah kembali.")) {
                        this.forceSubmit();
                    }
                },

                forceSubmit() {
                    window.removeEventListener('beforeunload', this.beforeUnloadCheck);
                    document.getElementById('quizForm').submit();
                },

                beforeUnloadCheck(e) {
                    e.preventDefault();
                    e.returnValue = '';
                    return 'Ujian sedang berlangsung! Jangan tutup halaman ini.';
                }
            }))
        })
    </script>
</body>
</html>
