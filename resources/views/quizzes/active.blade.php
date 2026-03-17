<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ujian Aktif: {{ $quiz->title }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Disable text selection to combat simple cheating -->
    <style>
        body {
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased h-full flex flex-col" 
      x-data="quizTimer({{ $timeLeft }}, '{{ route('quizzes.submit', [$quiz, $attempt]) }}')">

    <!-- Top Navigation Bar (Fixed) -->
    <nav class="bg-indigo-700 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="font-bold text-xl tracking-wider">PSSM CBT</span>
                    </div>
                    <div class="hidden md:block ml-6">
                        <h1 class="text-sm font-medium opacity-90 truncate max-w-md">{{ $quiz->title }}</h1>
                        <p class="text-xs opacity-75">{{ $quiz->subject->name ?? '' }} - {{ auth()->user()->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <!-- TIMER -->
                    <div class="bg-indigo-800 rounded-lg px-4 py-2 flex items-center shadow-inner border border-indigo-600"
                         :class="{ 'bg-red-600 border-red-500 animate-pulse': timeLeft < 300 }">
                        <svg class="w-5 h-5 mr-2 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-mono text-xl font-bold tracking-wider" x-text="formattedTime">--:--:--</span>
                    </div>
                    
                    <button type="button" @click="confirmSubmit()" class="bg-green-500 hover:bg-green-400 text-white font-bold py-2 px-6 rounded-md shadow focus:outline-none focus:ring-2 focus:ring-green-300 transition-colors">
                        Selesai Ujian
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row gap-6">
        
        <form id="quizForm" action="{{ route('quizzes.submit', [$quiz, $attempt]) }}" method="POST" class="w-full flex flex-col md:flex-row gap-6">
            @csrf
            
            <!-- Left Side: Questions Container -->
            <div class="md:w-3/4 space-y-6 pb-20">
                @foreach($questions as $index => $q)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 question-card" id="question-{{ $index + 1 }}">
                        <div class="flex items-start mb-4">
                            <span class="flex-shrink-0 bg-indigo-100 text-indigo-800 font-bold w-10 h-10 rounded-full flex items-center justify-center mr-4 text-lg border border-indigo-200">
                                {{ $index + 1 }}
                            </span>
                            <div class="text-lg text-gray-800 font-medium leading-relaxed pt-1 whitespace-pre-line">{{ $q->question }}</div>
                        </div>

                        <div class="pl-14 space-y-3 mt-4">
                            @if(is_array($q->options))
                                @foreach($q->options as $key => $option)
                                    <label class="relative flex items-center cursor-pointer p-4 border rounded-lg hover:border-indigo-400 hover:bg-indigo-50 transition-colors has-[:checked]:bg-indigo-100 has-[:checked]:border-indigo-500">
                                        <input type="radio" 
                                               name="answers[{{ $q->id }}]" 
                                               value="{{ $key }}" 
                                               class="peer h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                               onclick="document.getElementById('nav-btn-{{ $index + 1 }}').classList.add('bg-indigo-600', 'text-white'); document.getElementById('nav-btn-{{ $index + 1 }}').classList.remove('bg-white', 'text-gray-600');">
                                        
                                        <span class="ml-4 font-bold text-gray-700 w-6">{{ $key }}.</span>
                                        <span class="ml-2 text-gray-800 flex-1">{{ $option }}</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right Side: Navigation & Summary Panel (Sticky) -->
            <div class="md:w-1/4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sticky top-24">
                    <h3 class="font-bold text-gray-800 mb-4 pb-2 border-b flex justify-between items-center">
                        Navigasi Soal
                        <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $questions->count() }} Soal</span>
                    </h3>
                    
                    <div class="grid grid-cols-5 gap-2 mb-6">
                        @foreach($questions as $index => $q)
                            <a href="#question-{{ $index + 1 }}" 
                               id="nav-btn-{{ $index + 1 }}"
                               class="w-full aspect-square flex items-center justify-center rounded border border-gray-300 text-sm font-bold bg-white text-gray-600 hover:bg-gray-100 transition-colors">
                                {{ $index + 1 }}
                            </a>
                        @endforeach
                    </div>

                    <div class="text-xs text-gray-500 space-y-2 mb-6 border-t pt-4">
                        <div class="flex items-center"><div class="w-4 h-4 rounded bg-indigo-600 mr-2"></div> Sudah Dijawab</div>
                        <div class="flex items-center"><div class="w-4 h-4 rounded border border-gray-300 bg-white mr-2"></div> Belum Dijawab</div>
                    </div>

                    <button type="button" @click="confirmSubmit()" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors flex justify-center items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Selesai Kuis
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Alpine JS Logics -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quizTimer', (initialSeconds, submitUrl) => ({
                timeLeft: initialSeconds,
                formattedTime: '--:--:--',
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
                            alert('Waktu habis! Jawaban Anda akan otomatis dikirim.');
                            this.forceSubmit();
                        }
                    }, 1000);

                    // Prevent leaving accidentally
                    window.addEventListener('beforeunload', this.beforeUnloadCheck);
                },

                updateFormattedTime() {
                    const hours = Math.floor(this.timeLeft / 3600);
                    const minutes = Math.floor((this.timeLeft % 3600) / 60);
                    const seconds = this.timeLeft % 60;
                    this.formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                confirmSubmit() {
                    if(confirm("Apakah Anda yakin ingin menyelesaikan ujian ini? Pastikan semua soal telah dijawab. Tindakan ini tidak dapat dibatalkan.")) {
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
                    return 'Ujian sedang berlangsung! Apakah Anda yakin ingin keluar? Timer akan terus berjalan.';
                }
            }))
        })
    </script>
</body>
</html>
