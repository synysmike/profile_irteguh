@php
    $selectedPurchaseId = old('purchase_id', isset($transaction) && $transaction ? $transaction->purchase_id : '');
    $saleTransactionId = isset($transaction) && $transaction ? $transaction->id : '';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="purchase_id" class="block text-sm font-medium text-gray-700 mb-2">Barang dari Grosir *</label>
        <select id="purchase_id" name="purchase_id" required
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                data-details-url="{{ route('admin.keuangan.sale-transactions.purchase-details', ['purchaseId' => '__ID__']) }}"
                data-exclude-id="{{ $saleTransactionId }}">
            <option value="">-- Pilih Barang Grosir --</option>
            @forelse(($purchases ?? collect()) as $purchase)
            <option value="{{ $purchase->id }}"
                    data-description="{{ $purchase->displayDescription() }}"
                    data-remaining="{{ $purchase->remainingQuantity($saleTransactionId ?: null) }}"
                    data-cost="{{ (float) $purchase->unit_price }}"
                    {{ (string) $selectedPurchaseId === (string) $purchase->id ? 'selected' : '' }}>
                {{ $purchase->invoice_number }} — {{ $purchase->displayDescription() }}
                (stok: {{ $purchase->remainingQuantity($saleTransactionId ?: null) }})
            </option>
            @empty
            <option value="" disabled>Belum ada data grosir dengan stok tersedia</option>
            @endforelse
        </select>
        <p class="mt-1 text-xs text-gray-500">Opsional jika jual langsung dari Kasir POS. Alokasi ini muncul di tab &quot;Alokasi Siap Invoice&quot;.</p>
        <p id="purchase-info" class="mt-2 text-xs text-purple-700 hidden"></p>
    </div>

    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode Transaksi (Opsional)</label>
        <input type="text" id="code" name="code" value="{{ old('code', isset($transaction) && $transaction ? $transaction->code : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
               placeholder="Contoh: TRX-001">
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Item *</label>
        <input type="text" id="description" name="description" value="{{ old('description', isset($transaction) && $transaction ? $transaction->description : '') }}" required readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500">
        <p class="mt-1 text-xs text-gray-500">Diisi otomatis dari data grosir</p>
    </div>

    <div>
        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
        <input type="number" id="quantity" name="quantity" value="{{ old('quantity', isset($transaction) && $transaction ? $transaction->quantity : 1) }}" min="1" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <p id="quantity-hint" class="mt-1 text-xs text-gray-500">Maksimal sesuai stok grosir tersisa</p>
    </div>

    <div>
        <label for="cost_unit_price_display" class="block text-sm font-medium text-gray-700 mb-2">Harga Grosir (Rp)</label>
        <input type="text" id="cost_unit_price_display" value="—" disabled readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
        <p class="mt-1 text-xs text-gray-500">Harga beli dari data grosir (otomatis)</p>
    </div>

    <div>
        <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Jual per Unit (Rp) *</label>
        <input type="number" id="unit_price" name="unit_price" value="{{ old('unit_price', isset($transaction) && $transaction ? $transaction->unit_price : 0) }}" min="0" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="subtotal_display" class="block text-sm font-medium text-gray-700 mb-2">Subtotal Jual (Rp)</label>
        <input type="text" id="subtotal_display" value="0" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 font-semibold text-gray-900">
        <p class="mt-1 text-xs text-gray-500">Otomatis: Qty × Harga Jual</p>
    </div>

    <div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($transaction) && $transaction ? $transaction->is_active : true) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                   onchange="this.previousElementSibling.value = this.checked ? '1' : '0'">
            <span class="text-sm font-medium text-gray-700">Aktif</span>
        </label>
        <p class="mt-1 text-xs text-gray-500">Transaksi aktif dapat dipilih saat membuat penjualan</p>
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
        <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', isset($transaction) && $transaction ? $transaction->notes : '') }}</textarea>
    </div>
</div>
