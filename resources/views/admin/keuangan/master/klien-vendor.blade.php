@extends('admin.keuangan.layout')

@section('title', 'Data Klien & Vendor - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Data Klien & Vendor</h2>
    <p class="text-gray-600 mt-1">Untuk transaksi penjualan (klien) dan pembelian (vendor). Terkait dengan menu Pembukuan.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <a href="{{ route('admin.customers.index') }}" class="block bg-white rounded-lg shadow border border-gray-200 p-6 hover:border-purple-500 hover:shadow-md transition">
        <div class="text-4xl mb-4">👥</div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Data Klien (Customer)</h3>
        <p class="text-sm text-gray-600 mb-4">Daftar pelanggan untuk transaksi penjualan dan faktur penjualan.</p>
        <span class="text-purple-600 font-medium text-sm">Kelola Customer →</span>
    </a>
    <a href="{{ route('admin.suppliers.index') }}" class="block bg-white rounded-lg shadow border border-gray-200 p-6 hover:border-purple-500 hover:shadow-md transition">
        <div class="text-4xl mb-4">🏪</div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Data Vendor (Tempat Grosir)</h3>
        <p class="text-sm text-gray-600 mb-4">Daftar pemasok untuk transaksi pembelian dan faktur pembelian.</p>
        <span class="text-purple-600 font-medium text-sm">Kelola Tempat Grosir →</span>
    </a>
</div>

<div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
    Menu <strong>Pembukuan</strong> di navbar admin (Tempat Grosir & Customer) mengelola data yang sama. Data Klien & Vendor di sini terhubung langsung ke menu tersebut.
</div>
@endsection
