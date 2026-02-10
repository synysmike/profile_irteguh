@extends('admin.keuangan.layout')

@section('title', 'PPh Badan - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">PPh Badan</h2>
    <p class="text-gray-600 mt-1">Hitung laba kena pajak, simulasi tarif 22%</p>
</div>
<div class="bg-white rounded-lg shadow border border-gray-200 p-8 text-center text-gray-500">
    Data laba kena pajak diambil dari Laporan Laba Rugi. Simulasi tarif 22% untuk PPh Badan CV.
</div>
@endsection
