<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Input Kehadiran: {{ $class->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between items-center border-b pb-4">
                        <div>
                            <p class="font-bold text-lg">Mata Pelajaran: <span class="text-indigo-600">{{ $subject->name }}</span></p>
                            <p class="font-semibold text-gray-600">Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</p>
                        </div>
                        <a href="{{ route('attendances.index') }}" class="text-gray-500 hover:text-gray-700 underline text-sm">Kembali</a>
                    </div>
                    
                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            Terdapat kesalahan dalam pengisian form. Pastikan semua status kehadiran telah diisi.
                        </div>
                    @endif

                    <form action="{{ route('attendances.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class->id }}">
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                        <input type="hidden" name="date" value="{{ $date }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 shadow-sm border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Kehadiran</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan Tambahan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($students as $index => $student)
                                        @php
                                            $existingStatus = $existingAttendances->has($student->id) ? $existingAttendances[$student->id]->status : 'present';
                                            $existingNotes = $existingAttendances->has($student->id) ? $existingAttendances[$student->id]->notes : '';
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $student->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-4">
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" required class="form-radio text-green-600" name="attendances[{{ $student->id }}][status]" value="present" {{ $existingStatus == 'present' ? 'checked' : '' }}>
                                                        <span class="ml-2 text-sm text-green-800 font-semibold bg-green-100 px-2 rounded">Hadir</span>
                                                    </label>
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" required class="form-radio text-yellow-600" name="attendances[{{ $student->id }}][status]" value="late" {{ $existingStatus == 'late' ? 'checked' : '' }}>
                                                        <span class="ml-2 text-sm text-yellow-800 font-semibold bg-yellow-100 px-2 rounded">Telat</span>
                                                    </label>
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" required class="form-radio text-blue-600" name="attendances[{{ $student->id }}][status]" value="excused" {{ $existingStatus == 'excused' ? 'checked' : '' }}>
                                                        <span class="ml-2 text-sm text-blue-800 font-semibold bg-blue-100 px-2 rounded">Izin/Sakit</span>
                                                    </label>
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" required class="form-radio text-red-600" name="attendances[{{ $student->id }}][status]" value="absent" {{ $existingStatus == 'absent' ? 'checked' : '' }}>
                                                        <span class="ml-2 text-sm text-red-800 font-semibold bg-red-100 px-2 rounded">Alpa</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <input type="text" name="attendances[{{ $student->id }}][notes]" value="{{ $existingNotes }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md placeholder-gray-400" placeholder="Opsional (cth: Surat Dokter)">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">Belum ada siswa yang terdaftar di kelas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($students->count() > 0)
                            <div class="mt-6">
                                <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline shadow transition duration-150 ease-in-out">
                                    Simpan Kehadiran
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
