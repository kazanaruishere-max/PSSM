<x-app-layout>
    @section('header_title', 'Kehadiran Siswa')

    <div class="space-y-8">
        <!-- Welcome / Info Section -->
        <div class="card-modern p-8 bg-gradient-to-br from-indigo-600 to-blue-700 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-3xl font-black italic tracking-tight mb-2">Pencatatan Kehadiran Digital 📝</h3>
                <p class="text-indigo-100 text-lg opacity-90 max-w-xl">Pilih kelas dan mata pelajaran untuk mulai melakukan absensi siswa secara real-time.</p>
            </div>
            <i class="fas fa-calendar-check absolute -right-10 -bottom-10 text-[12rem] text-white/10 -rotate-12"></i>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-2xl flex items-center shadow-sm">
                <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center text-white mr-4">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-green-800 font-bold uppercase tracking-widest text-xs">{{ session('success') }}</p>
            </div>
        @endif

        <div class="card-modern p-8">
            <div class="flex items-center space-x-4 mb-8">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                    <i class="fas fa-filter text-xl"></i>
                </div>
                <div>
                    <h4 class="text-xl font-black text-slate-900 leading-tight">Konfigurasi Absensi</h4>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Filter Pencarian Kelas</p>
                </div>
            </div>

            <form action="{{ route('attendances.create') }}" method="GET" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-2">
                        <label class="label-modern">Pilih Kelas</label>
                        <div class="relative">
                            <select name="class_id" required class="input-modern appearance-none">
                                <option value="">-- PILIH KELAS --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->academicYear->name }})
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="label-modern">Mata Pelajaran</label>
                        <div class="relative">
                            <select id="subject_id" name="subject_id" required class="input-modern appearance-none">
                                <option value="">-- PILIH MAPEL --</option>
                                @if(auth()->user()->isAdmin())
                                    @foreach(\App\Models\Subject::orderBy('name')->get() as $sub)
                                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                @else
                                    @foreach(auth()->user()->taughtSubjects()->with('subject')->get()->unique('subject_id') as $taughtSub)
                                        <option value="{{ $taughtSub->subject->id }}" {{ request('subject_id') == $taughtSub->subject->id ? 'selected' : '' }}>{{ $taughtSub->subject->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="label-modern">Tanggal Pertemuan</label>
                        <div class="relative">
                            <input type="date" name="date" required value="{{ request('date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" class="input-modern">
                            <i class="far fa-calendar absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="btn-primary w-full md:w-auto px-12 py-5 text-sm">
                        Lanjut Input Absensi <i class="fas fa-arrow-right ml-3"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance Overview (History) Placeholder -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card-modern p-8">
                <h4 class="text-lg font-black text-slate-900 mb-6 uppercase tracking-tight">Ringkasan Hari Ini</h4>
                <div class="flex items-center justify-between p-6 bg-slate-50 rounded-3xl border border-slate-100">
                    <div class="text-center flex-1 border-r border-slate-200">
                        <p class="text-3xl font-black text-indigo-600">0</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Hadir</p>
                    </div>
                    <div class="text-center flex-1 border-r border-slate-200">
                        <p class="text-3xl font-black text-yellow-500">0</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Izin/Sakit</p>
                    </div>
                    <div class="text-center flex-1">
                        <p class="text-3xl font-black text-red-500">0</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Alpa</p>
                    </div>
                </div>
            </div>
            <div class="card-modern p-8 flex items-center justify-center text-center">
                <div>
                    <i class="fas fa-chart-line text-4xl text-slate-100 mb-4"></i>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]">Statistik Kehadiran Bulanan</p>
                    <p class="text-[10px] text-slate-300 mt-2">Segera Hadir pada versi MVP Final</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
