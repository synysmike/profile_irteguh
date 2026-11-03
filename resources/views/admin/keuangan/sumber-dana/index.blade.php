@extends('admin.keuangan.layout')

@section('title', 'Master Sumber Dana - Keuangan')

@section('keuangan_content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Master Sumber Dana</h2>
        <p class="text-gray-600 mt-1">Klasifikasi kas masuk dan pemakaian dana untuk transaksi kas/bank.</p>
    </div>
    <a href="{{ route('admin.keuangan.laporan.sumber-dana') }}" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50">
        Lihat Laporan →
    </a>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6"><p class="text-green-700">{{ session('success') }}</p></div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"><p class="text-red-700">{{ session('error') }}</p></div>
@endif
@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <ul class="list-disc list-inside text-red-700 text-sm">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Sumber Dana</h3>
    <form method="POST" action="{{ route('admin.keuangan.sumber-dana.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="code" value="{{ old('code') }}" placeholder="Kode (contoh: SD-CUSTOM)" required
               class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama klasifikasi" required
               class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <select name="flow_type" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @foreach(\App\Models\SumberDana::flowTypes() as $value => $label)
            <option value="{{ $value }}" {{ old('flow_type', 'both') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <input type="number" name="sort_order" value="{{ old('sort_order', 100) }}" min="0" placeholder="Urutan"
               class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <label class="flex items-center px-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="mr-2">
            Aktif
        </label>
        <textarea name="description" rows="2" placeholder="Deskripsi (opsional)"
                  class="md:col-span-5 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description') }}</textarea>
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
            Simpan
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Arah</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sistem</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dipakai</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($sumberDanas as $item)
            <tr>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->code }}</td>
                <td class="px-4 py-3 text-sm text-gray-800">
                    {{ $item->name }}
                    @if($item->description)
                    <div class="text-xs text-gray-500 mt-0.5">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    @if($item->flow_type === 'masuk')
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Masuk</span>
                    @elseif($item->flow_type === 'keluar')
                    <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Keluar</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">Masuk & Keluar</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    @if($item->system_key)
                    <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $item->system_key }}</code>
                    @else
                    —
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $item->cash_transactions_count }}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    <button type="button"
                            class="text-purple-600 hover:text-purple-800 font-medium"
                            data-sumber-edit
                            data-update-url="{{ route('admin.keuangan.sumber-dana.update', $item) }}"
                            data-code="{{ $item->code }}"
                            data-name="{{ $item->name }}"
                            data-flow-type="{{ $item->flow_type }}"
                            data-sort-order="{{ $item->sort_order }}"
                            data-description="{{ $item->description }}"
                            data-is-active="{{ $item->is_active ? '1' : '0' }}"
                            data-system-key="{{ $item->system_key }}">
                        Edit
                    </button>
                    @unless($item->isSystem())
                    <form method="POST" action="{{ route('admin.keuangan.sumber-dana.destroy', $item) }}" class="inline-block ml-3" onsubmit="return confirm('Hapus sumber dana ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                    </form>
                    @endunless
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada sumber dana.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- Edit Modal --}}
<div id="sumberDanaEditModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/40" data-sumber-edit-close></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="sumberDanaEditTitle">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 id="sumberDanaEditTitle" class="text-lg font-semibold text-gray-800">Edit Sumber Dana</h3>
                    <p id="sumberDanaEditSubtitle" class="text-xs text-gray-500 mt-0.5"></p>
                </div>
                <button type="button" class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100" data-sumber-edit-close aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="sumberDanaEditForm" method="POST" action="#" class="p-5 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_code" class="block text-sm font-medium text-gray-700 mb-1">Kode *</label>
                        <input type="text" id="edit_code" name="code" required maxlength="30"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label for="edit_flow_type" class="block text-sm font-medium text-gray-700 mb-1">Arah *</label>
                        <select id="edit_flow_type" name="flow_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @foreach(\App\Models\SumberDana::flowTypes() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                    <input type="text" id="edit_name" name="name" required maxlength="120"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_sort_order" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input type="number" id="edit_sort_order" name="sort_order" min="0" max="9999"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center text-sm text-gray-700">
                            <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            Aktif
                        </label>
                    </div>
                </div>
                <div>
                    <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="edit_description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" data-sumber-edit-close class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-semibold">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('sumberDanaEditModal');
    var form = document.getElementById('sumberDanaEditForm');
    if (!modal || !form) return;

    function openModal(btn) {
        form.action = btn.getAttribute('data-update-url');
        document.getElementById('edit_code').value = btn.getAttribute('data-code') || '';
        document.getElementById('edit_name').value = btn.getAttribute('data-name') || '';
        document.getElementById('edit_flow_type').value = btn.getAttribute('data-flow-type') || 'both';
        document.getElementById('edit_sort_order').value = btn.getAttribute('data-sort-order') || '0';
        document.getElementById('edit_description').value = btn.getAttribute('data-description') || '';
        document.getElementById('edit_is_active').checked = btn.getAttribute('data-is-active') === '1';

        var systemKey = btn.getAttribute('data-system-key') || '';
        var subtitle = document.getElementById('sumberDanaEditSubtitle');
        subtitle.textContent = systemKey
            ? 'Klasifikasi sistem: ' + systemKey
            : 'Klasifikasi kustom';

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        document.getElementById('edit_name').focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelectorAll('[data-sumber-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () { openModal(btn); });
    });

    modal.querySelectorAll('[data-sumber-edit-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
</script>
@endsection
