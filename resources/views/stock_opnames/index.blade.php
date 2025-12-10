<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Riwayat Stock Opname</h2>
            <a href="{{ route('stock-opnames.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg">+ Mulai Cek Stok</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3">Kode Opname</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">Petugas</th>
                            <th class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($opnames as $op)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-6 py-4 font-bold">{{ $op->kode_opname }}</td>
                            <td class="px-6 py-4">{{ date('d M Y', strtotime($op->tgl_opname)) }}</td>
                            <td class="px-6 py-4">{{ $op->user->name }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('stock-opnames.show', $op->id) }}" class="text-blue-600 hover:underline">Lihat Laporan</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
