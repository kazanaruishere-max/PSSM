<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Data Master: Kelas
            </h2>
            <button x-data @click="$dispatch('open-modal', 'add-class')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kelas
            </button>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Kelas</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tingkat</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Wali Kelas</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mata Pelajaran (Total)</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($classes as $class)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-semibold text-gray-900">
                                        {{ $class->name }}
                                        <div class="text-xs text-gray-500 font-normal">TA: {{ $class->academicYear->name }}</div>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        Kelas {{ $class->grade_level }}
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        {{ $class->homeroomTeacher ? $class->homeroomTeacher->name : 'Belum Ditentukan' }}
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $class->subjects->count() }} Mapel
                                        </span>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right font-medium">
                                        <div x-data="{ openEdit: false }">
                                            <button @click="openEdit = true" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            
                                            <form action="{{ route('classes.destroy', $class) }}" method="POST" class="inline" onsubmit="return confirm('Hapus Kelas ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>

                                            <!-- Edit Modal -->
                                            <div x-show="openEdit" class="fixed justify-center items-center flex inset-0 z-50 overflow-y-auto" style="display: none;">
                                                <div class="fixed inset-0 bg-gray-500 opacity-75" @click="openEdit = false"></div>
                                                <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-2xl sm:w-full z-50">
                                                    <form method="POST" action="{{ route('classes.update', $class) }}">
                                                        @csrf @method('PUT')
                                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Kelas</h3>
                                                            
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                                <div class="text-left">
                                                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Kelas</label>
                                                                    <input type="text" name="name" value="{{ $class->name }}" required placeholder="e.g. X MIPA 1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                                </div>
                                                                <div class="text-left">
                                                                    <label class="block text-gray-700 text-sm font-bold mb-2">Tingkat Kelas</label>
                                                                    <input type="number" name="grade_level" value="{{ $class->grade_level }}" min="1" max="12" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                                </div>
                                                            </div>

                                                            <div class="mb-4 text-left">
                                                                <label class="block text-gray-700 text-sm font-bold mb-2">Tahun Ajaran</label>
                                                                <select name="academic_year_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                                    @foreach($academicYears as $year)
                                                                        <option value="{{ $year->id }}" {{ $class->academic_year_id == $year->id ? 'selected' : '' }}>
                                                                            {{ $year->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="mb-4 text-left">
                                                                <label class="block text-gray-700 text-sm font-bold mb-2">Wali Kelas (Opsional)</label>
                                                                <select name="homeroom_teacher_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                                    <option value="">-- Pilih Wali Kelas --</option>
                                                                    @foreach($teachers as $teacher)
                                                                        <option value="{{ $teacher->id }}" {{ $class->homeroom_teacher_id == $teacher->id ? 'selected' : '' }}>
                                                                            {{ $teacher->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="mb-4 text-left border-t pt-4 mt-6">
                                                                <label class="block text-gray-700 text-sm font-bold mb-2">Mata Pelajaran (Checklist untuk menambahkan)</label>
                                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 border rounded bg-gray-50">
                                                                    @php $classSubjectIds = $class->subjects->pluck('id')->toArray(); @endphp
                                                                    @foreach($subjects as $subject)
                                                                        <label class="inline-flex items-center bg-white p-2 rounded shadow-sm border border-gray-200">
                                                                            <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" {{ in_array($subject->id, $classSubjectIds) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                                            <span class="ml-2 text-sm text-gray-700">{{ $subject->name }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse rounded-b-lg border-t">
                                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                                Simpan
                                                            </button>
                                                            <button type="button" @click="openEdit = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                                Batal
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                        Data belum tersedia.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal using generic alpine modal dispatch -->
    <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'add-class') open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" @click="open = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-50">
                <form method="POST" action="{{ route('classes.store') }}">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Tambah Kelas Baru</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="text-left">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Kelas</label>
                                <input type="text" name="name" required placeholder="e.g. X MIPA 1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="text-left">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tingkat Kelas</label>
                                <input type="number" name="grade_level" value="10" min="1" max="12" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                        </div>

                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tahun Ajaran</label>
                            <select name="academic_year_id" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4 text-left">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Wali Kelas (Opsional)</label>
                            <select name="homeroom_teacher_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">-- Pilih Wali Kelas --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4 text-left border-t pt-4 mt-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Mata Pelajaran (Checklist untuk menambahkan)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 border rounded bg-gray-50">
                                @foreach($subjects as $subject)
                                    <label class="inline-flex items-center bg-white p-2 rounded shadow-sm border border-gray-200">
                                        <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">{{ $subject->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg border-t">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none z-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
