<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Kehadiran (Absensi) Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-green-100 rounded-lg p-4 text-center border border-green-200">
                            <span class="block text-sm font-semibold text-green-800 uppercase tracking-wide">Hadir</span>
                            <span class="block text-3xl font-bold text-green-600">{{ $attendances->where('status', 'present')->count() }}</span>
                        </div>
                        <div class="bg-yellow-100 rounded-lg p-4 text-center border border-yellow-200">
                            <span class="block text-sm font-semibold text-yellow-800 uppercase tracking-wide">Telat</span>
                            <span class="block text-3xl font-bold text-yellow-600">{{ $attendances->where('status', 'late')->count() }}</span>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-4 text-center border border-blue-200">
                            <span class="block text-sm font-semibold text-blue-800 uppercase tracking-wide">Izin/Sakit</span>
                            <span class="block text-3xl font-bold text-blue-600">{{ $attendances->where('status', 'excused')->count() }}</span>
                        </div>
                        <div class="bg-red-100 rounded-lg p-4 text-center border border-red-200">
                            <span class="block text-sm font-semibold text-red-800 uppercase tracking-wide">Alpa</span>
                            <span class="block text-3xl font-bold text-red-600">{{ $attendances->where('status', 'absent')->count() }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 shadow-sm border mt-4">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan Tambahan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $attendance->date->translatedFormat('l, d F Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-indigo-600">{{ $attendance->subject->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $attendance->class_->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($attendance->status === 'present')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Hadir
                                                </span>
                                            @elseif($attendance->status === 'late')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Terlambat
                                                </span>
                                            @elseif($attendance->status === 'excused')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Izin/Sakit
                                                </span>
                                            @elseif($attendance->status === 'absent')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Tidak Hadir (Alpa)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $attendance->notes ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 italic">Belum ada data kehadiran yang tercatat untuk Anda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
