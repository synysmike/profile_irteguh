@extends('admin.keuangan.layout')

@section('title', 'Benefit Investor - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Master Benefit Investor</h2>
    <p class="text-gray-600 mt-1">Paket pembagian laba/rugi dan template syarat & ketentuan.</p>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6"><p class="text-green-700">{{ session('success') }}</p></div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"><p class="text-red-700">{{ session('error') }}</p></div>
@endif
@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <ul class="list-disc list-inside text-red-700 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Paket</h3>
    <form method="POST" action="{{ route('admin.keuangan.benefit-investor.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="code" value="{{ old('code') }}" placeholder="Kode *" required class="px-4 py-2 border rounded-md">
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama paket *" required class="md:col-span-2 px-4 py-2 border rounded-md">
        <input type="number" step="0.01" min="0" max="100" name="profit_share_percent" value="{{ old('profit_share_percent', 10) }}" placeholder="% Laba" required class="px-4 py-2 border rounded-md">
        <input type="number" step="0.01" min="0" max="100" name="loss_share_percent" value="{{ old('loss_share_percent', 10) }}" placeholder="% Rugi" required class="px-4 py-2 border rounded-md">
        <input type="number" step="1" min="0" name="min_deposit_amount" value="{{ old('min_deposit_amount', 0) }}" placeholder="Min. setoran (Rp) *" required class="px-4 py-2 border rounded-md">
        <label class="flex items-center"><input type="checkbox" name="is_active" value="1" checked class="mr-2">Aktif</label>
        <textarea name="description" rows="2" placeholder="Deskripsi" class="md:col-span-3 px-4 py-2 border rounded-md">{{ old('description') }}</textarea>
        <textarea name="terms_template" rows="2" placeholder="Template T&C" class="md:col-span-3 px-4 py-2 border rounded-md">{{ old('terms_template') }}</textarea>
        <input type="number" name="sort_order" value="{{ old('sort_order', 100) }}" class="px-4 py-2 border rounded-md" placeholder="Urutan">
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md font-semibold">Simpan Paket</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">% Laba</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">% Rugi</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Min. Setoran</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dipakai</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($packages as $pkg)
            <tr>
                <td class="px-4 py-3 text-sm font-medium">{{ $pkg->code }}</td>
                <td class="px-4 py-3 text-sm">
                    {{ $pkg->name }}
                    @if($pkg->description)<div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($pkg->description, 60) }}</div>@endif
                </td>
                <td class="px-4 py-3 text-sm text-right">{{ number_format($pkg->profit_share_percent, 2, ',', '.') }}%</td>
                <td class="px-4 py-3 text-sm text-right">{{ number_format($pkg->loss_share_percent, 2, ',', '.') }}%</td>
                <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format($pkg->min_deposit_amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm text-right">{{ $pkg->capital_injections_count }}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full {{ $pkg->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    <button type="button" class="text-purple-600" onclick="openPkgEdit(this)"
                        data-url="{{ route('admin.keuangan.benefit-investor.update', $pkg) }}"
                        data-code="{{ $pkg->code }}" data-name="{{ $pkg->name }}"
                        data-profit="{{ $pkg->profit_share_percent }}" data-loss="{{ $pkg->loss_share_percent }}"
                        data-min="{{ (float) $pkg->min_deposit_amount }}"
                        data-description="{{ $pkg->description }}" data-terms="{{ $pkg->terms_template }}"
                        data-order="{{ $pkg->sort_order }}" data-active="{{ $pkg->is_active ? '1' : '0' }}">Edit</button>
                    <form method="POST" action="{{ route('admin.keuangan.benefit-investor.destroy', $pkg) }}" class="inline ml-2" onsubmit="return confirm('Hapus paket?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Belum ada paket.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div id="pkgModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-xl bg-white rounded-lg shadow-xl border overflow-hidden">
            <div class="px-5 py-4 border-b flex justify-between"><h3 class="font-semibold">Edit Paket Benefit</h3><button type="button" data-close>✕</button></div>
            <form id="pkgForm" method="POST" class="p-5 space-y-3">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="code" id="p_code" required class="px-3 py-2 border rounded-md" placeholder="Kode">
                    <input type="text" name="name" id="p_name" required class="px-3 py-2 border rounded-md" placeholder="Nama">
                    <input type="number" step="0.01" name="profit_share_percent" id="p_profit" required class="px-3 py-2 border rounded-md" placeholder="% Laba">
                    <input type="number" step="0.01" name="loss_share_percent" id="p_loss" required class="px-3 py-2 border rounded-md" placeholder="% Rugi">
                    <input type="number" step="1" min="0" name="min_deposit_amount" id="p_min" required class="col-span-2 px-3 py-2 border rounded-md" placeholder="Min. setoran (Rp)">
                </div>
                <textarea name="description" id="p_desc" rows="2" class="w-full px-3 py-2 border rounded-md" placeholder="Deskripsi"></textarea>
                <textarea name="terms_template" id="p_terms" rows="4" class="w-full px-3 py-2 border rounded-md" placeholder="T&C"></textarea>
                <input type="number" name="sort_order" id="p_order" class="w-full px-3 py-2 border rounded-md">
                <label class="flex items-center text-sm"><input type="checkbox" name="is_active" id="p_active" value="1" class="mr-2">Aktif</label>
                <div class="flex justify-end gap-2"><button type="button" data-close class="px-4 py-2 bg-gray-100 rounded-md">Batal</button><button class="px-4 py-2 bg-purple-600 text-white rounded-md">Simpan</button></div>
            </form>
        </div>
    </div>
</div>
<script>
(function(){
  var m=document.getElementById('pkgModal'), f=document.getElementById('pkgForm');
  window.openPkgEdit=function(btn){
    f.action=btn.dataset.url;
    p_code.value=btn.dataset.code; p_name.value=btn.dataset.name;
    p_profit.value=btn.dataset.profit; p_loss.value=btn.dataset.loss;
    p_min.value=btn.dataset.min||0;
    p_desc.value=btn.dataset.description||''; p_terms.value=btn.dataset.terms||'';
    p_order.value=btn.dataset.order||0; p_active.checked=btn.dataset.active==='1';
    m.classList.remove('hidden');
  };
  m.querySelectorAll('[data-close]').forEach(function(el){el.addEventListener('click',function(){m.classList.add('hidden');});});
})();
</script>
@endsection
