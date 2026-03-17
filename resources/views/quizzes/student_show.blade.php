<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Container -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex flex-col md:flex-row gap-6">
                    <div class="flex-1 border-r pr-6">
                         <h3 class="text-2xl font-bold mb-2 text-gray-800">{{ $quiz->title }}</h3>
                         <p class="text-gray-600 mb-6">{{ $quiz->description ?? 'Tidak ada deskripsi.' }}</p>
                         
                         <div class="grid grid-cols-2 gap-4 text-sm">
                             <div>
                                 <span class="block text-gray-500 font-semibold mb-1">Mata Pelajaran</span>
                                 <span class="block font-bold text-gray-800">{{ $quiz->subject->name ?? '-' }}</span>
                             </div>
                             <div>
                                 <span class="block text-gray-500 font-semibold mb-1">Nilai Maksimal</span>
                                 <span class="block font-bold text-green-600">{{ $quiz->max_score }}</span>
                             </div>
                             <div>
                                 <span class="block text-gray-500 font-semibold mb-1">Waktu Pelaksanaan</span>
                                 <span class="block text-gray-800">
                                     Mulai: {{ \Carbon\Carbon::parse($quiz->start_time)->format('d/m/Y H:i') }}<br>
                                     Akhir: {{ \Carbon\Carbon::parse($quiz->end_time)->format('d/m/Y H:i') }}
                                 </span>
                             </div>
                             <div>
                                 <span class="block text-gray-500 font-semibold mb-1">Durasi Pengerjaan</span>
                                 <span class="block font-bold text-gray-800">{{ $quiz->duration_minutes }} Menit</span>
                             </div>
                         </div>
                    </div>

                    <div class="w-full md:w-1/3 flex flex-col justify-center items-center bg-gray-50 p-6 rounded-lg">
                        @php
                            $now = now();
                            $isStarted = $now->gte($quiz->start_time);
                            $isEnded = $now->gt($quiz->end_time);
                            $attemptsLeft = $quiz->max_attempts - $attempts->count();
                            
                            // Check if currently doing an attempt (unfinished)
                            $activeAttempt = $attempts->firstWhere(function ($a) {
                                return $a->submitted_at === null;
                            });
                        @endphp

                        <div class="text-center mb-6">
                            <span class="block text-gray-500 text-sm font-semibold mb-1">Kesempatan Mengerjakan</span>
                            <span class="text-3xl font-bold {{ $attemptsLeft > 0 ? 'text-indigo-600' : 'text-red-500' }}">
                                {{ $attemptsLeft }} 
                                <span class="text-sm font-normal text-gray-500">dari {{ $quiz->max_attempts }}</span>
                            </span>
                        </div>

                        @if($activeAttempt)
                            <!-- Already Started -->
                            <a href="{{ route('quizzes.active', [$quiz, $activeAttempt]) }}" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded text-center shadow-lg transform transition-transform hover:scale-105">
                                <i class="fas fa-play mr-2"></i> Lanjutkan Pengerjaan
                            </a>
                            <p class="text-xs text-yellow-600 mt-2 font-medium">Anda memiliki sesi pengerjaan yang belum di-submit!</p>
                        @elseif($isEnded)
                            <button disabled class="w-full bg-gray-400 text-white font-bold py-3 px-6 rounded text-center cursor-not-allowed">
                                Waktu Telah Berakhir
                            </button>
                        @elseif(!$isStarted)
                            <button disabled class="w-full bg-gray-400 text-white font-bold py-3 px-6 rounded text-center cursor-not-allowed">
                                Belum Dimulai
                            </button>
                            <p class="text-xs text-gray-500 mt-2">Kuis akan dibuka pada {{ \Carbon\Carbon::parse($quiz->start_time)->format('d M Y, H:i') }}</p>
                        @elseif($attemptsLeft <= 0)
                            <button disabled class="w-full bg-red-400 text-white font-bold py-3 px-6 rounded text-center cursor-not-allowed">
                                Kesempatan Habis
                            </button>
                        @else
                            <form action="{{ route('quizzes.take', $quiz) }}" method="POST" class="w-full" onsubmit="return confirm('Apakah Anda yakin ingin memulai kuis sekarang? Waktu akan otomatis berjalan mundur.');">
                                @csrf
                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded text-center shadow-lg transform transition-transform hover:scale-105">
                                    <i class="fas fa-pencil-alt mr-2"></i> Mulai Kerjakan
                                </button>
                                <p class="text-xs text-center text-gray-500 mt-2 italic">Pastikan koneksi internet stabil sebelum mulai.</p>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- History of Attempts -->
            @if($attempts->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="font-bold text-lg mb-4 text-gray-800">Riwayat Pengerjaan Anda</h4>
                    <div class="flex flex-col space-y-4">
                        @foreach($attempts as $attempt)
                            <div class="border rounded-lg p-4 flex justify-between items-center {{ $attempt->submitted_at ? 'bg-gray-50' : 'bg-yellow-50 border-yellow-200' }}">
                                <div>
                                    <span class="font-bold text-gray-800 block text-lg">Percobaan {{ $attempt->attempt_number }}</span>
                                    <span class="text-sm text-gray-500">Mulai: {{ \Carbon\Carbon::parse($attempt->started_at)->format('d M Y, H:i:s') }}</span>
                                    @if($attempt->submitted_at)
                                        <br><span class="text-sm text-gray-500">Submit: {{ \Carbon\Carbon::parse($attempt->submitted_at)->format('d M Y, H:i:s') }}</span>
                                    @endif
                                </div>
                                
                                <div class="text-right">
                                    @if($attempt->submitted_at)
                                        <span class="text-sm text-gray-500 block mb-1">Nilai:</span>
                                        <span class="text-3xl font-black text-green-600">{{ $attempt->score }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                            Sedang Mengerjakan
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @php
                       $best = $attempts->max('score');
                    @endphp
                    @if($best !== null && $attempts->where('submitted_at', '!=', null)->count() > 0)
                    <div class="mt-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg text-center">
                        <span class="text-indigo-800 font-medium">Nilai Terbaik Anda: </span>
                        <span class="text-2xl font-black text-indigo-900 ml-2">{{ $best }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
