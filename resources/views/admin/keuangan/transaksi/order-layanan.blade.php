@extends('admin.keuangan.layout')

@section('title', 'Order Layanan - Keuangan')

@section('keuangan_content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Order Layanan</h2>
        <p class="text-gray-600 mt-1 text-sm">Antrean draft dari checkout publik Layanan Kami (produk &amp; jasa).</p>
    </div>
    <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">Ke Kasir POS</a>
</div>

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4"><p class="text-red-700 text-sm">{{ session('error') }}</p></div>
@endif
@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4"><p class="text-green-700 text-sm">{{ session('success') }}</p></div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Draft Produk (Sale)</h3>
            <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-800">{{ $sales->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    <tr>
                        <td class="px-3 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $sale->invoice_number }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->sale_date?->format('d/m/Y') }} · {{ $sale->saleItems->count() }} item</div>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600">
                            {{ $sale->customer?->name ?? '—' }}
                            <div class="text-xs text-gray-400">{{ $sale->customer?->phone }}</div>
                        </td>
                        <td class="px-3 py-3 text-sm text-right font-medium">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right text-sm whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.sales.layanan.confirm', $sale) }}" class="inline" onsubmit="return confirm('Konfirmasi order? Stok alokasi dipotong &amp; kas diposting.')">
                                @csrf
                                <button type="submit" class="text-emerald-700 hover:text-emerald-900 font-medium mr-2">Konfirmasi</button>
                            </form>
                            <form method="POST" action="{{ route('admin.sales.layanan.reject', $sale) }}" class="inline" onsubmit="return confirm('Tolak &amp; hapus draft? Stok tidak terpengaruh.')">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500">Tidak ada draft produk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Draft Jasa (Project)</h3>
            <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-800">{{ $projects->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($projects as $project)
                    <tr>
                        <td class="px-3 py-3 text-sm">
                            <a href="{{ route('admin.projects.show', $project) }}" class="font-medium text-indigo-600 hover:text-indigo-800">{{ $project->code }}</a>
                            <div class="text-xs text-gray-600">{{ $project->title }}</div>
                            @if($project->layananTemplate)
                            <div class="text-[11px] text-gray-400">Template: {{ $project->layananTemplate->code }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600">
                            {{ $project->customer?->name ?? '—' }}
                            <div class="text-xs text-gray-400">{{ $project->customer?->phone }}</div>
                        </td>
                        <td class="px-3 py-3 text-sm text-right font-medium">Rp {{ number_format($project->total, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right text-sm whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.projects.layanan.confirm', $project) }}" class="inline" onsubmit="return confirm('Konfirmasi order jasa? Project aktif + termin default.')">
                                @csrf
                                <button type="submit" class="text-emerald-700 hover:text-emerald-900 font-medium mr-2">Konfirmasi</button>
                            </form>
                            <form method="POST" action="{{ route('admin.projects.layanan.reject', $project) }}" class="inline" onsubmit="return confirm('Tolak draft? Project dibatalkan; template etalase aman.')">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500">Tidak ada draft jasa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
