<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Kelas: {{ $class->name }}
            </h2>
            <a href="{{ route('classes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
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

            <div class="flex flex-col md:flex-row gap-6">
                <!-- Data Kelas -->
                <div class="w-full md:w-1/3 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Kelas</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">Nama Kelas:</span>
                                    <span class="font-semibold">{{ $class->name }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">Tingkat:</span>
                                    <span class="font-semibold">{{ $class->grade_level }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">Tahun Ajaran:</span>
                                    <span class="font-semibold">{{ $class->academicYear->name }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">Wali Kelas:</span>
                                    <span class="font-semibold">{{ $class->homeroomTeacher ? $class->homeroomTeacher->name : 'Belum ditentukan' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Siswa:</span>
                                    <span class="font-semibold">{{ $class->students->count() }} Orang</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manage Mapel (Teachers) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Guru Mata Pelajaran</h3>
                            
                            @forelse($class->subjects as $subject)
                                <div class="mb-4 bg-gray-50 p-3 rounded border">
                                    <div class="font-semibold text-gray-800">{{ $subject->name }}</div>
                                    <form action="{{ route('classes.assign-teacher', [$class, $subject]) }}" method="POST" class="mt-2 flex items-center space-x-2">
                                        @csrf
                                        @php 
                                            // Get the assigned teacher ID for this specific subject and class
                                            $assignedTeacherId = DB::table('class_subject')
                                                ->where('class_id', $class->id)
                                                ->where('subject_id', $subject->id)
                                                ->value('teacher_id');
                                        @endphp
                                        <select name="teacher_id" class="text-sm shadow appearance-none border rounded w-full py-1.5 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                            <option value="">-- Pilih Guru --</option>
                                            @foreach($availableTeachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ $assignedTeacherId == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->name }} 
                                                    @if($teacher->teacherProfile && $teacher->teacherProfile->specialization)
                                                        ({{ $teacher->teacherProfile->specialization }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-1.5 px-3 rounded">
                                            Set
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500 italic">Belum ada mata pelajaran untuk kelas ini. Edit kelas untuk menambahkan mata pelajaran.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Daftar Siswa -->
                <div class="w-full md:w-2/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Daftar Siswa di Kelas Ini</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full leading-normal mb-4">
                                    <thead>
                                        <tr>
                                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NIS/NISN</th>
                                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Siswa</th>
                                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($class->students as $index => $student)
                                        <tr>
                                            <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">{{ $index + 1 }}</td>
                                            <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">{{ $student->studentProfile->student_id_number ?? '-' }}</td>
                                            <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm font-semibold">{{ $student->name }}</td>
                                            <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm text-right">
                                                <form action="{{ route('classes.remove-student', [$class, $student]) }}" method="POST" onsubmit="return confirm('Keluarkan siswa ini dari kelas?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-semibold">Keluarkan</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500 italic">
                                                Belum ada siswa yang ditambahkan ke kelas ini.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tambah Siswa Form -->
                    <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Tambahkan Siswa ke Kelas</h3>
                            
                            <form action="{{ route('classes.assign-students', $class) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Siswa (Bisa Pilih Banyak)</label>
                                    <select name="student_ids[]" multiple class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-48" required>
                                        @foreach($availableStudents as $student)
                                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->studentProfile->student_id_number ?? 'No NIS' }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-2">Tips: Tahan tombol <kbd class="bg-gray-200 px-1 rounded">Ctrl</kbd> (Windows) atau <kbd class="bg-gray-200 px-1 rounded">Cmd</kbd> (Mac) untuk memilih lebih dari satu.</p>
                                </div>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                                    Tambahkan Terpilih
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
