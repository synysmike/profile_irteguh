@extends('admin.keuangan.layout')

@section('title', 'Master Investor - Keuangan')

@section('keuangan_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Master Investor</h2>
    <p class="text-gray-600 mt-1">Data investor, nomor rekening, setoran modal, dan dokumen.</p>
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
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Investor</h3>
    <form method="POST" action="{{ route('admin.keuangan.investor.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama *" required class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-md">
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="No. HP *" required class="px-4 py-2 border border-gray-300 rounded-md">
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" class="px-4 py-2 border border-gray-300 rounded-md">
        <label class="flex items-center px-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="mr-2">Aktif</label>
        <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank" class="px-4 py-2 border border-gray-300 rounded-md">
        <input type="text" name="bank_account" value="{{ old('bank_account') }}" placeholder="No. Rekening" class="px-4 py-2 border border-gray-300 rounded-md">
        <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="Atas Nama Rekening" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-md">
        <textarea name="address" rows="2" placeholder="Alamat" class="md:col-span-3 px-4 py-2 border border-gray-300 rounded-md">{{ old('address') }}</textarea>
        <textarea name="notes" rows="2" placeholder="Catatan" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-md">{{ old('notes') }}</textarea>
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md font-semibold hover:bg-purple-700">Simpan</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">HP</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Rekening</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Setoran</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dokumen</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($investors as $item)
            <tr>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->phone ?: '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    @if($item->bank_account)
                    <div class="font-medium text-gray-800">{{ $item->bank_account }}</div>
                    <div class="text-xs text-gray-500">{{ $item->bank_name ?: '—' }}@if($item->bank_account_name) · a/n {{ $item->bank_account_name }}@endif</div>
                    @else
                    <span class="text-amber-600 text-xs">Belum diisi</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->email ?: '—' }}</td>
                <td class="px-4 py-3 text-sm text-right">{{ $item->capital_injections_count }}</td>
                <td class="px-4 py-3 text-sm text-right">{{ $item->documents_count }}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    <button type="button" class="text-purple-600 hover:text-purple-800"
                        onclick="openInvestorEdit(this)"
                        data-url="{{ route('admin.keuangan.investor.update', $item) }}"
                        data-name="{{ $item->name }}"
                        data-phone="{{ $item->phone }}"
                        data-email="{{ $item->email }}"
                        data-bank-name="{{ $item->bank_name }}"
                        data-bank-account="{{ $item->bank_account }}"
                        data-bank-account-name="{{ $item->bank_account_name }}"
                        data-address="{{ $item->address }}"
                        data-notes="{{ $item->notes }}"
                        data-active="{{ $item->is_active ? '1' : '0' }}">Edit</button>
                    <form method="POST" action="{{ route('admin.keuangan.investor.destroy', $item) }}" class="inline ml-2" onsubmit="return confirm('Hapus investor ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                    </form>
                </td>
            </tr>
            <tr class="bg-gray-50">
                <td colspan="8" class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.keuangan.investor.documents.store', $item) }}" enctype="multipart/form-data" class="flex flex-wrap gap-2 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Unggah dokumen</label>
                            <input type="text" name="title" placeholder="Judul dokumen" required class="px-3 py-1.5 border rounded-md text-sm">
                        </div>
                        <input type="file" name="document" required class="text-sm">
                        <button type="submit" class="px-3 py-1.5 bg-purple-600 text-white rounded-md text-sm">Upload</button>
                    </form>
                    @if($item->documents_count > 0)
                    <div class="mt-2 text-xs text-gray-500">{{ $item->documents_count }} dokumen tersimpan (lihat di portal investor).</div>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Belum ada investor.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div id="investorEditModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-lg shadow-xl border overflow-hidden">
            <div class="px-5 py-4 border-b flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Edit Investor</h3>
                <button type="button" data-close class="text-gray-500">✕</button>
            </div>
            <form id="investorEditForm" method="POST" class="p-5 space-y-3">
                @csrf @method('PUT')
                <input type="text" name="name" id="ie_name" required class="w-full px-3 py-2 border rounded-md" placeholder="Nama">
                <input type="text" name="phone" id="ie_phone" required class="w-full px-3 py-2 border rounded-md" placeholder="HP">
                <input type="email" name="email" id="ie_email" class="w-full px-3 py-2 border rounded-md" placeholder="Email">
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="bank_name" id="ie_bank_name" class="px-3 py-2 border rounded-md" placeholder="Bank">
                    <input type="text" name="bank_account" id="ie_bank_account" class="px-3 py-2 border rounded-md" placeholder="No. Rekening">
                </div>
                <input type="text" name="bank_account_name" id="ie_bank_account_name" class="w-full px-3 py-2 border rounded-md" placeholder="Atas Nama Rekening">
                <textarea name="address" id="ie_address" rows="2" class="w-full px-3 py-2 border rounded-md" placeholder="Alamat"></textarea>
                <textarea name="notes" id="ie_notes" rows="2" class="w-full px-3 py-2 border rounded-md" placeholder="Catatan"></textarea>
                <label class="flex items-center text-sm"><input type="checkbox" name="is_active" id="ie_active" value="1" class="mr-2">Aktif</label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" data-close class="px-4 py-2 bg-gray-100 rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function(){
  var m=document.getElementById('investorEditModal');
  var f=document.getElementById('investorEditForm');
  window.openInvestorEdit=function(btn){
    f.action=btn.dataset.url;
    ie_name.value=btn.dataset.name||'';
    ie_phone.value=btn.dataset.phone||'';
    ie_email.value=btn.dataset.email||'';
    ie_bank_name.value=btn.dataset.bankName||'';
    ie_bank_account.value=btn.dataset.bankAccount||'';
    ie_bank_account_name.value=btn.dataset.bankAccountName||'';
    ie_address.value=btn.dataset.address||'';
    ie_notes.value=btn.dataset.notes||'';
    ie_active.checked=btn.dataset.active==='1';
    m.classList.remove('hidden');
  };
  m.querySelectorAll('[data-close]').forEach(function(el){el.addEventListener('click',function(){m.classList.add('hidden');});});
})();
</script>
@endsection
