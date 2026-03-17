<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengumuman & Informasi') }}
            </h2>
            @if(!auth()->user()->isStudent())
            <a href="{{ route('announcements.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Pengumuman
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6">
                @forelse($announcements as $announcement)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 {{ $announcement->priority === 'high' ? 'border-red-500' : ($announcement->priority === 'normal' ? 'border-blue-500' : 'border-gray-500') }}">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        {{ $announcement->title }}
                                        
                                        @if($announcement->priority === 'high')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Penting
                                            </span>
                                        @endif
                                        @if(empty($announcement->class_id))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Global
                                            </span>
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Oleh: {{ $announcement->author->name }} ({{ $announcement->author->role === 'super_admin' ? 'Admin' : 'Guru' }})
                                        &bull; 
                                        @if($announcement->class_id)
                                            Untuk Kelas: <strong>{{ $announcement->class_->name }} ({{ $announcement->class_->academicYear->name }})</strong> &bull;
                                        @endif
                                        Diterbitkan: {{ $announcement->created_at->translatedFormat('d F Y, H:i') }}
                                    </p>
                                </div>
                                
                                @if(auth()->user()->isAdmin() || (auth()->user()->isTeacher() && $announcement->author_id === auth()->id()))
                                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus pengumuman ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-semibold">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="text-gray-700 prose max-w-none">
                                {!! nl2br(e($announcement->content)) !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-500 text-center italic">
                            Belum ada pengumuman saat ini.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $announcements->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
