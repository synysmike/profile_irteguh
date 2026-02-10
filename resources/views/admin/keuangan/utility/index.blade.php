@extends('admin.keuangan.layout')

@section('title', 'Utility - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Utility</h2>
    <p class="text-gray-600 mt-1">Export, backup, user management</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-2">Export PDF/Excel</h3>
        <p class="text-sm text-gray-600">Laporan keuangan & pajak dalam format PDF atau Excel.</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-2">Backup Database</h3>
        <p class="text-sm text-gray-600">PostgreSQL/MySQL dump untuk keamanan data.</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5 md:col-span-2">
        <h3 class="font-semibold text-gray-800 mb-2">User Management</h3>
        <p class="text-sm text-gray-600 mb-3">Akses admin, akuntan, staf untuk modul keuangan.</p>
        <a href="{{ route('admin.users.index') }}" class="text-purple-600 font-medium text-sm hover:underline">Kelola User Admin →</a>
    </div>
</div>
@endsection
