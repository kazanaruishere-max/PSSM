<x-app-layout>
    @section('header_title', 'Daftar Tugas')

    <div class="space-y-8">
        <!-- Action Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-blue-50 rounded-2xl text-blue-600">
                    <i class="fas fa-tasks text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-900 leading-tight">Manajemen Tugas</h3>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Kelola & Pantau Progress Siswa</p>
                </div>
            </div>
            @can('assignments.create')
                <a href="{{ route('assignments.create') }}" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-indigo-100 transition-all flex items-center justify-center uppercase tracking-widest text-sm">
                    <i class="fas fa-plus mr-2"></i> BUAT TUGAS BARU
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
                            <th>JUDUL TUGAS</th>
                            <th>KELAS & MAPEL</th>
                            <th>GURU PENGAMPU</th>
                            <th>DEADLINE</th>
                            <th>STATUS</th>
                            <th class="text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignments as $assignment)
                        <tr class="group">
                            <td>
                                <a href="{{ route('assignments.show', $assignment) }}" class="font-black text-gray-900 hover:text-indigo-600 transition-colors text-base leading-tight block">
                                    {{ $assignment->title }}
                                </a>
                                <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">ID: #{{ $assignment->id }}</p>
                            </td>
                            <td>
                                <div class="flex flex-col space-y-1">
                                    <span class="text-xs font-black text-indigo-700 uppercase tracking-widest bg-indigo-50 px-2 py-0.5 rounded self-start">{{ $assignment->class_->name ?? '-' }}</span>
                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">{{ $assignment->subject->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-500 mr-2 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                                        {{ substr($assignment->teacher->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">{{ $assignment->teacher->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                @php $deadline = \Carbon\Carbon::parse($assignment->deadline); @endphp
                                <div class="flex flex-col">
                                    <span class="text-xs font-black {{ now()->gt($deadline) ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $deadline->format('d M Y') }}
                                    </span>
                                    <span class="text-[10px] font-bold text-gray-400">{{ $deadline->format('H:i') }} WIB</span>
                                </div>
                            </td>
                            <td>
                                @if($assignment->is_published)
                                    <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest bg-green-100 text-green-700 border border-green-200">
                                        PUBLISHED
                                    </span>
                                @else
                                    <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest bg-yellow-100 text-yellow-700 border border-yellow-200">
                                        DRAFT
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('assignments.show', $assignment) }}" class="inline-flex items-center bg-gray-50 hover:bg-indigo-600 text-gray-400 hover:text-white font-black py-2 px-4 rounded-xl transition-all text-[10px] uppercase tracking-widest border border-gray-100 hover:border-indigo-600">
                                    DETAIL <i class="fas fa-arrow-right ml-2 text-[8px]"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-clipboard-list text-5xl text-gray-100 mb-4"></i>
                                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">Belum ada tugas yang dibuat</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($assignments->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
