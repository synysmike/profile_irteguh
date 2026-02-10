@extends('admin.keuangan.layout')

@section('title', 'Laporan Pajak - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Laporan Pajak</h2>
    <p class="text-gray-600 mt-1">SPT Tahunan Badan (CV), SPT Masa PPh 21, SPT Masa PPN</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-2">SPT Tahunan Badan (CV)</h3>
        <p class="text-sm text-gray-600">Otomatis dari laporan keuangan.</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-2">SPT Masa PPh 21</h3>
        <p class="text-sm text-gray-600">Bulanan.</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-2">SPT Masa PPN</h3>
        <p class="text-sm text-gray-600">Bulanan jika omzet > Rp 4,8 miliar.</p>
    </div>
</div>
@endsection
