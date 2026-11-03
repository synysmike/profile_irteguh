@extends('admin.layout')

@section('title', $title ?? 'Keuangan - Admin')

@section('content')
<style>
.keuangan-layout { display: flex; flex-direction: row; gap: 1.5rem; align-items: flex-start; }
.keuangan-sidebar { width: 15rem; min-width: 15rem; flex-shrink: 0; position: sticky; top: 4.5rem; }
.keuangan-main { flex: 1; min-width: 0; }
.keuangan-sidebar .nav-link {
    display: block;
    padding: 0.5rem 1rem;
    color: #374151;
    border-left: 2px solid transparent;
}
.keuangan-sidebar .nav-link:hover { background: #f3f4f6; }
.keuangan-sidebar .nav-link.active {
    background: #f5f3ff;
    color: #6d28d9;
    font-weight: 500;
    border-left-color: #7c3aed;
    padding-left: calc(1rem - 2px);
}
.keuangan-sidebar .nav-section {
    margin-top: 0.75rem;
    padding: 0.4rem 1rem 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #6b7280;
}
.keuangan-sidebar .nav-section:first-of-type { margin-top: 0.25rem; }
.keuangan-menu-toggle { display: none; }
.keuangan-sidebar-backdrop { display: none; }

@media (max-width: 1023px) {
    .keuangan-layout { flex-direction: column; gap: 1rem; }
    .keuangan-menu-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .keuangan-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: min(20rem, 88vw);
        min-width: 0;
        z-index: 50;
        transform: translateX(-105%);
        transition: transform 0.2s ease;
        padding: 0;
        margin: 0;
    }
    .keuangan-sidebar .keuangan-sidebar-panel {
        height: 100%;
        border-radius: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .keuangan-sidebar .keuangan-sidebar-panel ul {
        max-height: none !important;
        flex: 1;
        overflow-y: auto;
    }
    .keuangan-layout.sidebar-open .keuangan-sidebar { transform: translateX(0); }
    .keuangan-sidebar-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        z-index: 45;
    }
    .keuangan-layout.sidebar-open .keuangan-sidebar-backdrop { display: block; }
}
@media (min-width: 1024px) {
    .keuangan-sidebar-close { display: none !important; }
}
@media print {
    .keuangan-menu-toggle, .keuangan-sidebar, .keuangan-sidebar-backdrop { display: none !important; }
    .keuangan-layout { display: block !important; }
    .keuangan-main { width: 100% !important; }
}
</style>

<button type="button" id="keuangan-menu-toggle" class="keuangan-menu-toggle mb-3 no-print" aria-expanded="false" aria-controls="keuangan-sidebar">
    <span>Menu Keuangan</span>
    <span class="text-xs font-normal text-gray-500">Buka navigasi</span>
</button>

<div class="keuangan-layout" id="keuangan-layout">
    <div class="keuangan-sidebar-backdrop" id="keuangan-sidebar-backdrop" aria-hidden="true"></div>

    <aside class="keuangan-sidebar" id="keuangan-sidebar">
        <nav class="keuangan-sidebar-panel bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-start justify-between gap-2">
                <div>
                    <h2 class="font-semibold text-gray-800 text-sm">Menu Keuangan</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pembukuan & transaksi</p>
                </div>
                <button type="button" id="keuangan-sidebar-close" class="keuangan-sidebar-close p-1.5 rounded-md text-gray-500 hover:bg-gray-200" aria-label="Tutup menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <ul class="py-2 text-sm max-h-[calc(100vh-12rem)] overflow-y-auto">
                <li>
                    <a href="{{ route('admin.keuangan.dashboard') }}" class="nav-link {{ request()->routeIs('admin.keuangan.dashboard') ? 'active' : '' }}">Dashboard</a>
                </li>

                <li>
                    <div class="nav-section">Master Data</div>
                    <a href="{{ route('admin.keuangan.chart-of-accounts.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.chart-of-accounts.*') ? 'active' : '' }}">Akun Perkiraan (COA)</a>
                    <a href="{{ route('admin.keuangan.master.klien-vendor') }}" class="nav-link {{ request()->routeIs('admin.keuangan.master.klien-vendor') ? 'active' : '' }}">Klien & Vendor</a>
                    <a href="{{ route('admin.keuangan.master.karyawan') }}" class="nav-link {{ request()->routeIs('admin.keuangan.master.karyawan') ? 'active' : '' }}">Karyawan</a>
                    <a href="{{ route('admin.keuangan.pajak.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.pajak.*') ? 'active' : '' }}">Pajak</a>
                    <a href="{{ route('admin.keuangan.diskon.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.diskon.*') ? 'active' : '' }}">Diskon</a>
                    <a href="{{ route('admin.keuangan.jasa.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.jasa.*') ? 'active' : '' }}">Jasa</a>
                    <a href="{{ route('admin.keuangan.sumber-dana.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.sumber-dana.*') ? 'active' : '' }}">Sumber Dana</a>
                </li>

                <li>
                    <div class="nav-section">Investor</div>
                    <a href="{{ route('admin.keuangan.investor.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.investor.*') ? 'active' : '' }}">Master Investor</a>
                    <a href="{{ route('admin.keuangan.benefit-investor.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.benefit-investor.*') ? 'active' : '' }}">Benefit Investor</a>
                    <a href="{{ route('admin.keuangan.modal-investor.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.modal-investor.*') ? 'active' : '' }}">Modal Investor</a>
                    <a href="{{ route('admin.keuangan.dividen-investor.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.dividen-investor.*') ? 'active' : '' }}">Dividen & Pembayaran</a>
                </li>

                <li>
                    <div class="nav-section">Penjualan</div>
                    <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" class="nav-link {{ request()->routeIs('admin.keuangan.transaksi.penjualan') ? 'active' : '' }}">Kasir POS</a>
                    <a href="{{ route('admin.projects.index') }}" class="nav-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">Project</a>
                    <a href="{{ route('admin.keuangan.sale-transactions.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.sale-transactions.*') ? 'active' : '' }}">Alokasi Stok</a>
                </li>

                <li>
                    <div class="nav-section">Transaksi</div>
                    <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" class="nav-link {{ request()->routeIs('admin.keuangan.transaksi.pembelian') ? 'active' : '' }}">Grosir</a>
                    <a href="{{ route('admin.keuangan.transaksi.kas-bank') }}" class="nav-link {{ request()->routeIs('admin.keuangan.transaksi.kas-bank') ? 'active' : '' }}">Kas / Bank</a>
                    <a href="{{ route('admin.keuangan.transaksi.gaji') }}" class="nav-link {{ request()->routeIs('admin.keuangan.transaksi.gaji') ? 'active' : '' }}">Gaji & Payroll</a>
                    <a href="{{ route('admin.keuangan.jurnal.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.jurnal.*') ? 'active' : '' }}">Jurnal Umum</a>
                </li>

                <li>
                    <div class="nav-section">Laporan</div>
                    <a href="{{ route('admin.keuangan.laporan.neraca') }}" class="nav-link {{ request()->routeIs('admin.keuangan.laporan.neraca') ? 'active' : '' }}">Neraca</a>
                    <a href="{{ route('admin.keuangan.laporan.laba-rugi') }}" class="nav-link {{ request()->routeIs('admin.keuangan.laporan.laba-rugi') ? 'active' : '' }}">Laba Rugi</a>
                    <a href="{{ route('admin.keuangan.laporan.arus-kas') }}" class="nav-link {{ request()->routeIs('admin.keuangan.laporan.arus-kas') ? 'active' : '' }}">Arus Kas</a>
                    <a href="{{ route('admin.keuangan.laporan.sumber-dana') }}" class="nav-link {{ request()->routeIs('admin.keuangan.laporan.sumber-dana') ? 'active' : '' }}">Sumber Dana</a>
                    <a href="{{ route('admin.keuangan.laporan.buku-besar') }}" class="nav-link {{ request()->routeIs('admin.keuangan.laporan.buku-besar') ? 'active' : '' }}">Buku Besar</a>
                    <a href="{{ route('admin.keuangan.laporan-pajak.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.laporan-pajak.*') ? 'active' : '' }}">Laporan Pajak</a>
                </li>

                <li>
                    <div class="nav-section">Sistem</div>
                    <a href="{{ route('admin.keuangan.utility.index') }}" class="nav-link {{ request()->routeIs('admin.keuangan.utility.*') ? 'active' : '' }}">Utility</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="keuangan-main">
        @yield('keuangan_content')
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var layout = document.getElementById('keuangan-layout');
    var openBtn = document.getElementById('keuangan-menu-toggle');
    var closeBtn = document.getElementById('keuangan-sidebar-close');
    var backdrop = document.getElementById('keuangan-sidebar-backdrop');

    function setSidebarOpen(open) {
        if (!layout) return;
        layout.classList.toggle('sidebar-open', open);
        if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open && window.innerWidth < 1024 ? 'hidden' : '';
    }

    if (openBtn) openBtn.addEventListener('click', function () { setSidebarOpen(true); });
    if (closeBtn) closeBtn.addEventListener('click', function () { setSidebarOpen(false); });
    if (backdrop) backdrop.addEventListener('click', function () { setSidebarOpen(false); });
});
</script>
@endsection
