@extends('admin.layout')

@section('title', $title ?? 'Keuangan - Admin')

@section('content')
<style>
/* Layout Keuangan: sidebar kiri, main kanan (tidak bergantung Tailwind) */
.keuangan-layout { display: flex; flex-direction: row; gap: 1.5rem; align-items: flex-start; }
.keuangan-sidebar { width: 14rem; min-width: 14rem; flex-shrink: 0; position: sticky; top: 2rem; }
.keuangan-main { flex: 1; min-width: 0; }
.keuangan-sidebar .nav-dropdown-wrap .nav-dropdown { display: none; }
.keuangan-sidebar .nav-dropdown-wrap.open .nav-dropdown { display: block; }
.keuangan-sidebar .nav-dropdown-wrap:hover .nav-dropdown { display: block; }
.keuangan-sidebar .nav-dropdown-wrap.open .nav-dropdown-chevron { transform: rotate(180deg); }
@media (max-width: 767px) {
    .keuangan-layout { flex-direction: column; gap: 1rem; }
    .keuangan-sidebar { width: 100%; min-width: 0; position: static; order: 2; }
    .keuangan-main { order: 1; }
}
</style>
<div class="keuangan-layout">
    {{-- Sidebar: kiri, lebar tetap, sticky --}}
    <aside class="keuangan-sidebar">
        <nav class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h2 class="font-semibold text-gray-800 text-sm">Menu Keuangan</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pembukuan & Pajak CV</p>
            </div>
            <ul class="py-2 text-sm max-h-[calc(100vh-12rem)] overflow-y-auto">
                <li>
                    <a href="{{ route('admin.keuangan.dashboard') }}" class="block px-4 py-2 {{ request()->routeIs('admin.keuangan.dashboard') ? 'bg-purple-50 text-purple-700 font-medium border-l-2 border-purple-600 -ml-px pl-[15px]' : 'text-gray-700 hover:bg-gray-100 border-l-2 border-transparent' }}">📊 Dashboard</a>
                </li>
                <li class="mt-2">
                    <div class="px-4 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Master Data</div>
                    <a href="{{ route('admin.keuangan.chart-of-accounts.index') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.chart-of-accounts.*') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Akun Perkiraan (COA)</a>
                    <a href="{{ route('admin.keuangan.master.klien-vendor') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.master.klien-vendor') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Data Klien & Vendor</a>
                    <a href="{{ route('admin.keuangan.master.karyawan') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.master.karyawan') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Data Karyawan</a>
                </li>
                <li class="mt-2">
                    <div class="px-4 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaksi</div>
                    <div id="penjualan-dropdown-wrap" class="nav-dropdown-wrap {{ request()->routeIs('admin.keuangan.transaksi.penjualan') || request()->routeIs('admin.keuangan.sale-transactions.*') ? 'open' : '' }}">
                        <button type="button" id="penjualan-dropdown-btn" class="w-full text-left px-4 py-2 pl-5 flex items-center justify-between text-gray-700 hover:bg-gray-100 border-l-2 border-transparent rounded-none" aria-expanded="{{ request()->routeIs('admin.keuangan.transaksi.penjualan') || request()->routeIs('admin.keuangan.sale-transactions.*') ? 'true' : 'false' }}">
                            <span>Penjualan</span>
                            <svg class="w-4 h-4 transition-transform nav-dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="nav-dropdown pl-0">
                            <a href="{{ route('admin.keuangan.sale-transactions.index') }}" class="block px-4 py-2 pl-6 {{ request()->routeIs('admin.keuangan.sale-transactions.*') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Transaksi Penjualan</a>
                            <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" class="block px-4 py-2 pl-6 {{ request()->routeIs('admin.keuangan.transaksi.penjualan') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Penjualan</a>
                        </div>
                    </div>
                    <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.transaksi.pembelian') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Grosir</a>
                    <a href="{{ route('admin.projects.index') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.projects.*') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Project</a>
                    <a href="{{ route('admin.keuangan.transaksi.kas-bank') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.transaksi.kas-bank') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Kas/Bank</a>
                    <a href="{{ route('admin.keuangan.transaksi.gaji') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.transaksi.gaji') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Gaji & Payroll</a>
                </li>
                <li>
                    <a href="{{ route('admin.keuangan.jurnal.index') }}" class="block px-4 py-2 {{ request()->routeIs('admin.keuangan.jurnal.*') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">📒 Jurnal Umum</a>
                </li>
                <li class="mt-2">
                    <div class="px-4 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Laporan Keuangan</div>
                    <a href="{{ route('admin.keuangan.laporan.neraca') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.laporan.neraca') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Neraca</a>
                    <a href="{{ route('admin.keuangan.laporan.laba-rugi') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.laporan.laba-rugi') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Laba Rugi</a>
                    <a href="{{ route('admin.keuangan.laporan.arus-kas') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.laporan.arus-kas') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Arus Kas</a>
                    <a href="{{ route('admin.keuangan.laporan.buku-besar') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.laporan.buku-besar') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Buku Besar</a>
                </li>
                <li class="mt-2">
                    <div class="px-4 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Menu Pajak</div>
                    <a href="{{ route('admin.keuangan.pajak.index') }}" class="block px-4 py-2 pl-5 {{ request()->routeIs('admin.keuangan.pajak.*') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Master Pajak</a>
                </li>
                <li>
                    <a href="{{ route('admin.keuangan.laporan-pajak.index') }}" class="block px-4 py-2 {{ request()->routeIs('admin.keuangan.laporan-pajak.*') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">📋 Laporan Pajak</a>
                </li>
                <li>
                    <a href="{{ route('admin.keuangan.utility.index') }}" class="block px-4 py-2 {{ request()->routeIs('admin.keuangan.utility.*') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">⚙️ Utility</a>
                </li>
            </ul>
        </nav>
    </aside>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('penjualan-dropdown-btn');
        var wrap = document.getElementById('penjualan-dropdown-wrap');
        if (btn && wrap) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                wrap.classList.toggle('open');
                btn.setAttribute('aria-expanded', wrap.classList.contains('open') ? 'true' : 'false');
            });
        }
    });
    </script>
    {{-- Main: kanan, mengisi sisa ruang --}}
    <main class="keuangan-main">
        @yield('keuangan_content')
    </main>
</div>
@endsection
