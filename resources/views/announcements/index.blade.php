<x-app-layout>
    @section('header_title', 'Pusat Informasi & Pengumuman')

    <div class="space-y-8">
        <!-- Action Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-amber-50 rounded-2xl text-amber-600">
                    <i class="fas fa-bullhorn text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-900 leading-tight">Mading Digital PSSM</h3>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Informasi Terkini Akademik & Sekolah</p>
                </div>
            </div>
            @if(!auth()->user()->isStudent())
                <a href="{{ route('announcements.create') }}" class="w-full md:w-auto btn-primary">
                    <i class="fas fa-plus mr-2"></i> BUAT PENGUMUMAN
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl flex items-center shadow-sm">
                <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                <p class="text-green-800 font-bold uppercase tracking-widest text-[10px]">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-8">
            @forelse($announcements as $announcement)
                <div class="card-modern overflow-hidden group">
                    <div class="flex flex-col md:flex-row">
                        <!-- Left Decoration / Icon -->
                        <div class="w-full md:w-20 {{ $announcement->priority === 'high' ? 'bg-red-500' : ($announcement->priority === 'normal' ? 'bg-indigo-500' : 'bg-slate-400') }} flex items-center justify-center text-white py-4 md:py-0">
                            <i class="fas {{ $announcement->priority === 'high' ? 'fa-exclamation-triangle animate-bounce' : 'fa-info-circle' }} text-2xl"></i>
                        </div>
                        
                        <div class="flex-1 p-8">
                            <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-6">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        @if($announcement->priority === 'high')
                                            <span class="badge badge-red ring-4 ring-red-50">URGENT</span>
                                        @endif
                                        @if(empty($announcement->class_id))
                                            <span class="badge badge-indigo">PENGUMUMAN GLOBAL</span>
                                        @else
                                            <span class="badge badge-purple">KHUSUS KELAS {{ $announcement->class_->name }}</span>
                                        @endif
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><i class="far fa-clock mr-1"></i> {{ $announcement->created_at->translatedFormat('d M Y, H:i') }}</span>
                                    </div>
                                    <h3 class="text-2xl font-black text-slate-900 tracking-tight leading-tight group-hover:text-indigo-600 transition-colors">
                                        {{ $announcement->title }}
                                    </h3>
                                </div>
                                
                                @if(auth()->user()->isAdmin() || (auth()->user()->isTeacher() && $announcement->author_id === auth()->id()))
                                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-3 bg-red-50 text-red-500 rounded-2xl hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="text-slate-600 font-medium leading-relaxed prose prose-slate max-w-none mb-8">
                                {!! nl2br(e($announcement->content)) !!}
                            </div>

                            <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-400 text-xs">
                                        {{ substr($announcement->author->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-900 leading-none">{{ $announcement->author->name }}</p>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ $announcement->author->role === 'super_admin' ? 'Administrator' : 'Guru Pengampu' }}</p>
                                    </div>
                                </div>
                                <div class="hidden sm:block">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em]">Dipublikasikan Melalui PSSM Core</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card-modern p-20 text-center flex flex-col items-center">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 mb-6">
                        <i class="fas fa-newspaper text-5xl"></i>
                    </div>
                    <h4 class="text-xl font-black text-slate-900 mb-2 italic">Belum Ada Kabar Baru</h4>
                    <p class="text-slate-400 font-medium text-sm">Tetap pantau halaman ini untuk mendapatkan informasi terbaru dari sekolah.</p>
                </div>
            @endforelse
        </div>

        @if($announcements->hasPages())
            <div class="pt-8">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
