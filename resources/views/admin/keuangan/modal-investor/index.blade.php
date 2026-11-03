@extends('admin.keuangan.layout')

@section('title', 'Modal Investor - Keuangan')

@section('keuangan_content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Modal Investor</h2>
        <p class="text-gray-600 mt-1">Setoran → invoice T&C → setuju investor → bayar + bukti transfer → kas masuk + jurnal Modal Investor.</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Rekening Penerima (ditampilkan di form bayar investor)</h3>
    <form method="POST" action="{{ route('admin.keuangan.modal-investor.payment-account') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
            <input type="text" name="investor_bank_name" value="{{ old('investor_bank_name', $bankName) }}" class="w-full px-3 py-2 border rounded-md" placeholder="Contoh: BCA">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. Rekening</label>
            <input type="text" name="investor_bank_account" value="{{ old('investor_bank_account', $bankAccount) }}" class="w-full px-3 py-2 border rounded-md" placeholder="1234567890">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Atas Nama</label>
            <input type="text" name="investor_bank_holder" value="{{ old('investor_bank_holder', $bankHolder) }}" class="w-full px-3 py-2 border rounded-md" placeholder="PT Contoh">
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md font-semibold">Simpan Rekening</button>
    </form>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-700">{{ session('success') }}</p>
    @if(session('invoice_link'))
    <p class="text-sm mt-2 break-all">Link invoice: <a href="{{ session('invoice_link') }}" class="text-purple-700 underline" target="_blank">{{ session('invoice_link') }}</a></p>
    @endif
</div>
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
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Setoran Modal</h3>
    <form method="POST" action="{{ route('admin.keuangan.modal-investor.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Investor *</label>
            <select name="investor_id" required class="w-full px-3 py-2 border rounded-md">
                <option value="">-- Pilih --</option>
                @foreach($investors as $inv)
                <option value="{{ $inv->id }}" {{ old('investor_id') == $inv->id ? 'selected' : '' }}>{{ $inv->name }} ({{ $inv->phone }})</option>
                @endforeach
            </select>
            <a href="{{ route('admin.keuangan.investor.index') }}" class="text-xs text-purple-600">+ Kelola investor</a>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Paket Benefit *</label>
            <select name="benefit_package_id" id="benefit_package_id" required class="w-full px-3 py-2 border rounded-md">
                <option value="">-- Pilih --</option>
                @foreach($packages as $pkg)
                <option value="{{ $pkg->id }}"
                        data-profit="{{ $pkg->profit_share_percent }}"
                        data-loss="{{ $pkg->loss_share_percent }}"
                        data-min="{{ (float) $pkg->min_deposit_amount }}"
                        data-terms="{{ e($pkg->terms_template) }}"
                        {{ old('benefit_package_id') == $pkg->id ? 'selected' : '' }}>
                    {{ $pkg->code }} — {{ $pkg->name }} (Laba {{ number_format($pkg->profit_share_percent, 0) }}% · min Rp {{ number_format($pkg->min_deposit_amount, 0, ',', '.') }})
                </option>
                @endforeach
            </select>
            <p id="benefitPreview" class="text-xs text-gray-500 mt-1"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
            <input type="date" name="injection_date" value="{{ old('injection_date', date('Y-m-d')) }}" required class="w-full px-3 py-2 border rounded-md">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Setoran (Rp) *</label>
            <input type="number" name="amount" id="amount" min="1" step="1" value="{{ old('amount') }}" required class="w-full px-3 py-2 border rounded-md">
            <p id="amountHint" class="text-xs text-gray-500 mt-1">Minimal mengikuti paket benefit. Boleh dinaikkan.</p>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Sumber Dana *</label>
            <select name="sumber_dana_id" required class="w-full px-3 py-2 border rounded-md">
                @foreach($sumberDanas as $sd)
                <option value="{{ $sd->id }}" {{ (string) old('sumber_dana_id', $defaultSumberDanaId) === (string) $sd->id ? 'selected' : '' }}>
                    {{ $sd->code }} — {{ $sd->name }}
                </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">COA ekuitas: {{ $equityAccount?->code ?? '3-3100' }} {{ $equityAccount?->name ?? 'Modal Investor' }}</p>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Syarat & Ketentuan</label>
            <textarea name="terms_content" id="terms_content" rows="5" class="w-full px-3 py-2 border rounded-md">{{ old('terms_content') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="notes" rows="2" class="w-full px-3 py-2 border rounded-md">{{ old('notes') }}</textarea>
        </div>
        <div>
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md font-semibold hover:bg-purple-700">Simpan & Buat Invoice</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Invoice</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Investor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paket</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($injections as $row)
            <tr>
                <td class="px-4 py-3 text-sm font-medium">{{ $row->invoice_number }}</td>
                <td class="px-4 py-3 text-sm">{{ $row->injection_date?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm">{{ $row->investor?->name }}</td>
                <td class="px-4 py-3 text-sm">
                    {{ $row->benefit_name }}
                    <div class="text-xs text-gray-500">Laba {{ number_format($row->profit_share_percent, 0) }}% / Rugi {{ number_format($row->loss_share_percent, 0) }}%</div>
                </td>
                <td class="px-4 py-3 text-sm text-right font-medium">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-sm">
                    @php
                        $badge = match($row->status) {
                            'approved' => 'bg-green-100 text-green-800',
                            'awaiting_payment' => 'bg-blue-100 text-blue-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'pending_approval' => 'bg-amber-100 text-amber-800',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $badge }}">{{ $row->statusLabel() }}</span>
                    @if($row->status === 'approved' && $row->paid_at)
                    <div class="text-xs text-gray-500 mt-1">Bayar {{ $row->paid_at->format('d/m/Y H:i') }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    <a href="{{ $row->publicInvoiceUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 mr-2">Invoice</a>
                    @if($row->isEditable())
                    <form method="POST" action="{{ route('admin.keuangan.modal-investor.destroy', $row) }}" class="inline" onsubmit="return confirm('Hapus setoran?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600">Hapus</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada setoran modal.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<script>
(function(){
  var sel=document.getElementById('benefit_package_id');
  var terms=document.getElementById('terms_content');
  var preview=document.getElementById('benefitPreview');
  var amount=document.getElementById('amount');
  var hint=document.getElementById('amountHint');
  var hadOldAmount={{ old('amount') !== null ? 'true' : 'false' }};
  function fmt(n){ return Number(n||0).toLocaleString('id-ID'); }
  function sync(fromChange){
    var opt=sel.options[sel.selectedIndex];
    if(!opt||!opt.value){
      preview.textContent='';
      amount.min=1;
      hint.textContent='Minimal mengikuti paket benefit. Boleh dinaikkan.';
      return;
    }
    var min=parseFloat(opt.dataset.min||0);
    if(!(min>0)) min=1;
    amount.min=min;
    preview.textContent='Share laba '+opt.dataset.profit+'% · rugi '+opt.dataset.loss+'% · min setoran Rp '+fmt(min);
    hint.textContent='Minimal Rp '+fmt(min)+'. Boleh dinaikkan di atas nominal ini.';
    if(fromChange || !hadOldAmount){
      var cur=parseFloat(amount.value||0);
      if(!amount.value || cur < min){ amount.value=min; }
    }
    if(!terms.value.trim() && opt.dataset.terms){ terms.value=opt.dataset.terms; }
  }
  sel.addEventListener('change', function(){ terms.value=''; hadOldAmount=false; sync(true); });
  amount.addEventListener('change', function(){
    var min=parseFloat(amount.min||1);
    var cur=parseFloat(amount.value||0);
    if(cur < min){ amount.value=min; }
  });
  sync(false);
})();
</script>
@endsection
