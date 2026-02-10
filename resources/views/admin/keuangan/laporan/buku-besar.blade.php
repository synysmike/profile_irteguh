@extends('admin.keuangan.layout')

@section('title', 'Buku Besar - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Buku Besar</h2>
    <p class="text-gray-600 mt-1">Detail per akun perkiraan</p>
</div>
<div class="bg-white rounded-lg shadow border border-gray-200 p-8 text-center text-gray-500">
    Buku besar menampilkan mutasi per akun dari jurnal. Pilih akun dan periode.
</div>
@endsection
