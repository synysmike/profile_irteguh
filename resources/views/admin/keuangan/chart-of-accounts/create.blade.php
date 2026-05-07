@extends('admin.keuangan.layout')

@section('title', 'Tambah Akun Perkiraan - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <a href="{{ route('admin.keuangan.chart-of-accounts.index') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">&larr; Daftar Akun</a>
    <h2 class="text-2xl font-bold text-gray-800 mt-2">Tambah Akun Perkiraan (COA)</h2>
    <p class="text-gray-600 mt-1">Isi kode, nama, dan tipe akun.</p>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 p-6">
    <form action="{{ route('admin.keuangan.chart-of-accounts.store') }}" method="POST">
        @csrf
        @include('admin.keuangan.chart-of-accounts._form', ['account' => null, 'parents' => $parents])
        <div class="mt-6 flex gap-3">
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-medium">Simpan</button>
            <a href="{{ route('admin.keuangan.chart-of-accounts.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Batal</a>
        </div>
    </form>
</div>
@endsection
