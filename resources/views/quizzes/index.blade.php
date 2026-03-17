<x-app-layout>
    @section('header_title', 'Daftar Kuis CBT')

    <div class="space-y-8">
        <!-- Action Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-purple-50 rounded-2xl text-purple-600">
                    <i class="fas fa-laptop-code text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-900 leading-tight">Ujian & Kuis Online</h3>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Sistem CBT Berbasis AI</p>
                </div>
            </div>
            @can('quizzes.create')
                <a href="{{ route('quizzes.create') }}" class="w-full md:w-auto bg-purple-600 hover:bg-purple-700 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-purple-100 transition-all flex items-center justify-center uppercase tracking-widest text-sm">
                    <i class="fas fa-magic mr-2"></i> BUAT KUIS BARU (AI)
                </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl flex items-center shadow-sm">
                <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                <p class="text-green-800 font-bold">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Table Container -->
        <div class="table-container">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>JUDUL KUIS</th>
                            <th>KELAS & MAPEL</th>
                            <th>JADWAL PELAKSANAAN</th>
                            <th>DURASI</th>
                            <th>METODE</th>
                            <th class="text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quizzes as $quiz)
                        <tr class="group">
                            <td>
                                <a href="{{ route('quizzes.show', $quiz) }}" class="font-black text-gray-900 hover:text-purple-600 transition-colors text-base leading-tight block">
                                    {{ $quiz->title }}
                                </a>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($quiz->is_published)
                                        <span class="text-[8px] font-black bg-green-100 text-green-600 px-1.5 py-0.5 rounded uppercase tracking-widest">Live</span>
                                    @else
                                        <span class="text-[8px] font-black bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded uppercase tracking-widest">Draft</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col space-y-1">
                                    <span class="text-xs font-black text-purple-700 uppercase tracking-widest bg-purple-50 px-2 py-0.5 rounded self-start">{{ $quiz->class_->name ?? '-' }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ $quiz->subject->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col space-y-1">
                                    <div class="flex items-center text-[10px] font-bold text-gray-600">
                                        <i class="far fa-clock mr-1.5 text-green-500 w-3"></i> 
                                        {{ \Carbon\Carbon::parse($quiz->start_time)->format('d/m, H:i') }}
                                    </div>
                                    <div class="flex items-center text-[10px] font-bold text-gray-400">
                                        <i class="far fa-circle-xmark mr-1.5 text-red-400 w-3"></i> 
                                        {{ \Carbon\Carbon::parse($quiz->end_time)->format('d/m, H:i') }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <span class="bg-gray-100 text-gray-700 font-black text-xs px-3 py-1 rounded-lg">
                                        {{ $quiz->duration_minutes }} <span class="text-[10px] font-bold text-gray-400">MIN</span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($quiz->is_ai_generated)
                                    <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest bg-purple-100 text-purple-700 border border-purple-200 shadow-sm">
                                        <i class="fas fa-robot mr-1 text-[8px]"></i> AI GEN
                                    </span>
                                @else
                                    <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest bg-gray-100 text-gray-600 border border-gray-200">
                                        MANUAL
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('quizzes.show', $quiz) }}" class="inline-flex items-center bg-gray-50 hover:bg-purple-600 text-gray-400 hover:text-white font-black py-2.5 px-5 rounded-xl transition-all text-[10px] uppercase tracking-widest border border-gray-100 hover:border-purple-600 shadow-sm">
                                    LIHAT <i class="fas fa-chevron-right ml-2 text-[8px]"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-microchip text-5xl text-gray-100 mb-4 opacity-20"></i>
                                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Belum ada kuis online</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($quizzes->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100">
                    {{ $quizzes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
