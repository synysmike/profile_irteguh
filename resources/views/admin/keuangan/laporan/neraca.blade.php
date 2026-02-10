@extends('admin.keuangan.layout')

@section('title', 'Laporan Neraca - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Laporan Neraca</h2>
    <p class="text-gray-600 mt-1">Posisi keuangan (aktiva, pasiva)</p>
</div>
<div class="bg-white rounded-lg shadow border border-gray-200 p-8 text-center text-gray-500">
    Neraca terbentuk dari data jurnal. Pilih periode untuk melihat laporan.
</div>
@endsection
