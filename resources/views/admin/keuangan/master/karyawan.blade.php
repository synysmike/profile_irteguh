@extends('admin.keuangan.layout')

@section('title', 'Data Karyawan - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Data Karyawan</h2>
        <p class="text-gray-600 mt-1">Untuk gaji & PPh 21</p>
    </div>
    <button type="button" onclick="openResourceModal('employeeModal', 'employees', 'Karyawan')" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-semibold">
        + Tambah Karyawan
    </button>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posisi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NPWP</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($employees as $emp)
            <tr id="employeesRow_{{ $emp->id }}">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $emp->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $emp->position ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">Rp {{ number_format($emp->basic_salary, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $emp->npwp ?? '—' }}</td>
                <td class="px-4 py-3">
                    @if($emp->is_active)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm">
                    <button type="button" onclick="openResourceModal('employeeModal', 'employees', 'Karyawan', {{ $emp->id }})" class="text-purple-600 hover:text-purple-800 mr-3">Edit</button>
                    <button type="button" onclick="deleteResource('employees', {{ $emp->id }}, 'Karyawan')" class="text-red-600 hover:text-red-800">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    Belum ada data karyawan.
                    <button type="button" onclick="openResourceModal('employeeModal', 'employees', 'Karyawan')" class="text-purple-600 hover:text-purple-800 ml-1">Tambah yang pertama</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.components.modal', [
    'modalId' => 'employeeModal',
    'title' => 'Tambah Karyawan'
])
@endsection
