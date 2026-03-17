<x-app-layout>
    @section('header_title', 'Manajemen Kelas')

    <div class="space-y-8">
        <!-- Action Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600">
                    <i class="fas fa-university text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 leading-tight">Database Ruang Kelas</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Total: {{ $classes->count() }} Kelas Terdaftar</p>
                </div>
            </div>
            <button x-data @click="$dispatch('open-modal', 'add-class')" class="btn-primary w-full md:w-auto">
                <i class="fas fa-plus mr-2"></i> TAMBAH KELAS
            </button>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-2xl flex items-center shadow-sm">
                <i class="fas fa-check-circle text-green-500 mr-4 text-xl"></i>
                <p class="text-green-800 font-bold uppercase tracking-widest text-[10px]">{{ session('success') }}</p>
            </div>
        @endif

        <div class="card-modern overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>NAMA KELAS & TA</th>
                            <th>TINGKAT</th>
                            <th>WALI KELAS</th>
                            <th>DATA MAPEL</th>
                            <th class="text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $class)
                        <tr class="group">
                            <td data-label="NAMA KELAS">
                                <p class="font-black text-slate-900 leading-tight">{{ $class->name }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ $class->academicYear->name }}</p>
                            </td>
                            <td data-label="TINGKAT">
                                <span class="badge badge-indigo">LEVEL {{ $class->grade_level }}</span>
                            </td>
                            <td data-label="WALI KELAS">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400 mr-3 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                        {{ substr($class->homeroomTeacher->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $class->homeroomTeacher->name ?? 'BELUM DITENTUKAN' }}</span>
                                </div>
                            </td>
                            <td data-label="MAPEL">
                                <span class="badge badge-purple">{{ $class->subjects->count() }} MATA PELAJARAN</span>
                            </td>
                            <td class="text-right">
                                <div x-data="{ openEdit: false }" class="flex justify-end items-center space-x-2">
                                    <a href="{{ route('classes.show', $class) }}" class="p-2.5 bg-green-50 text-green-600 rounded-xl hover:bg-green-600 hover:text-white transition-all shadow-sm" title="Kelola">
                                        <i class="fas fa-cog text-xs"></i>
                                    </a>
                                    <button @click="openEdit = true" class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    
                                    <form action="{{ route('classes.destroy', $class) }}" method="POST" class="inline" onsubmit="return confirm('Hapus Kelas ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- Edit Modal -->
                                    <div x-show="openEdit" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                        <div class="flex items-center justify-center min-h-screen p-4">
                                            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openEdit = false"></div>
                                            
                                            <div class="bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-2xl sm:w-full z-50 border border-white/20">
                                                <form method="POST" action="{{ route('classes.update', $class) }}">
                                                    @csrf @method('PUT')
                                                    <div class="p-10">
                                                        <div class="flex justify-between items-center mb-8">
                                                            <h3 class="text-2xl font-black text-slate-900 italic uppercase tracking-tight">Edit Ruang Kelas</h3>
                                                            <button type="button" @click="openEdit = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                                                <i class="fas fa-times text-xl"></i>
                                                            </button>
                                                        </div>
                                                        
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                                            <div>
                                                                <label class="label-modern">Nama Kelas</label>
                                                                <input type="text" name="name" value="{{ $class->name }}" required placeholder="e.g. X MIPA 1" class="input-modern">
                                                            </div>
                                                            <div>
                                                                <label class="label-modern">Tingkat Kelas</label>
                                                                <input type="number" name="grade_level" value="{{ $class->grade_level }}" min="1" max="12" required class="input-modern">
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                                            <div>
                                                                <label class="label-modern">Tahun Ajaran</label>
                                                                <select name="academic_year_id" required class="input-modern">
                                                                    @foreach($academicYears as $year)
                                                                        <option value="{{ $year->id }}" {{ $class->academic_year_id == $year->id ? 'selected' : '' }}>
                                                                            {{ $year->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="label-modern">Wali Kelas (Opsional)</label>
                                                                <select name="homeroom_teacher_id" class="input-modern">
                                                                    <option value="">-- PILIH GURU --</option>
                                                                    @foreach($teachers as $teacher)
                                                                        <option value="{{ $teacher->id }}" {{ $class->homeroom_teacher_id == $teacher->id ? 'selected' : '' }}>
                                                                            {{ $teacher->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="border-t border-slate-100 pt-8 mt-8">
                                                            <label class="label-modern italic">Konfigurasi Mata Pelajaran</label>
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-48 overflow-y-auto p-4 bg-slate-50 rounded-2xl border border-slate-100 custom-scrollbar">
                                                                @php $classSubjectIds = $class->subjects->pluck('id')->toArray(); @endphp
                                                                @foreach($subjects as $subject)
                                                                    <label class="flex items-center p-3 bg-white rounded-xl border border-slate-100 hover:border-indigo-200 transition-all cursor-pointer group/item">
                                                                        <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" {{ in_array($subject->id, $classSubjectIds) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                                        <span class="ml-3 text-xs font-bold text-slate-600 group-hover/item:text-indigo-600 transition-colors">{{ $subject->name }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="bg-slate-50 px-10 py-8 flex flex-col sm:flex-row-reverse gap-4 border-t border-slate-100">
                                                        <button type="submit" class="btn-primary flex-1 sm:flex-none px-12">Simpan Perubahan</button>
                                                        <button type="button" @click="openEdit = false" class="btn-secondary flex-1 sm:flex-none px-12">Batal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-university text-5xl text-slate-100 mb-4 opacity-20"></i>
                                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Belum ada data kelas</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'add-class') open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="open = false"></div>
            
            <div class="bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-2xl sm:w-full z-50 border border-white/20">
                <form method="POST" action="{{ route('classes.store') }}">
                    @csrf
                    <div class="p-10">
                        <div class="flex justify-between items-center mb-8">
                            <h3 class="text-2xl font-black text-slate-900 italic uppercase tracking-tight">Tambah Kelas Baru</h3>
                            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="label-modern">Nama Kelas</label>
                                <input type="text" name="name" required placeholder="e.g. X MIPA 1" class="input-modern">
                            </div>
                            <div>
                                <label class="label-modern">Tingkat Kelas</label>
                                <input type="number" name="grade_level" value="10" min="1" max="12" required class="input-modern">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="label-modern">Tahun Ajaran</label>
                                <select name="academic_year_id" required class="input-modern">
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="label-modern">Wali Kelas (Opsional)</label>
                                <select name="homeroom_teacher_id" class="input-modern">
                                    <option value="">-- PILIH GURU --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-8 mt-8">
                            <label class="label-modern italic">Konfigurasi Mata Pelajaran</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-48 overflow-y-auto p-4 bg-slate-50 rounded-2xl border border-slate-100 custom-scrollbar">
                                @foreach($subjects as $subject)
                                    <label class="flex items-center p-3 bg-white rounded-xl border border-slate-100 hover:border-indigo-200 transition-all cursor-pointer group/item">
                                        <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-3 text-xs font-bold text-slate-600 group-hover/item:text-indigo-600 transition-colors">{{ $subject->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-10 py-8 flex flex-col sm:flex-row-reverse gap-4 border-t border-slate-100">
                        <button type="submit" class="btn-primary flex-1 sm:flex-none px-12">Simpan Kelas</button>
                        <button type="button" @click="open = false" class="btn-secondary flex-1 sm:flex-none px-12">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
