@php
    $selectedPurchaseId = old('purchase_id', isset($transaction) && $transaction ? $transaction->purchase_id : '');
    $saleTransactionId = isset($transaction) && $transaction ? $transaction->id : '';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="purchase_id" class="block text-sm font-medium text-gray-700 mb-2">Barang dari Grosir *</label>
        <select id="purchase_id" name="purchase_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
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
        <p class="mt-1 text-xs text-gray-500">Transaksi penjualan harus berasal dari barang grosir yang tercatat</p>
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
        <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Jual per Unit (Rp) *</label>
        <input type="number" id="unit_price" name="unit_price" value="{{ old('unit_price', isset($transaction) && $transaction ? $transaction->unit_price : 0) }}" min="0" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <p id="cost-hint" class="mt-1 text-xs text-gray-500">Harga beli grosir: —</p>
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

<script>
(function() {
    const purchaseSelect = document.getElementById('purchase_id');
    const descriptionInput = document.getElementById('description');
    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unit_price');
    const subtotalDisplay = document.getElementById('subtotal_display');
    const purchaseInfo = document.getElementById('purchase-info');
    const quantityHint = document.getElementById('quantity-hint');
    const costHint = document.getElementById('cost-hint');
    const excludeId = @json($saleTransactionId ?: null);
    const detailsUrlTemplate = @json(route('admin.keuangan.sale-transactions.purchase-details', ['purchaseId' => '__ID__']));

    if (!purchaseSelect) return;

    let maxQuantity = null;

    function formatRp(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
    }

    function calculateSubtotal() {
        const qty = parseFloat(quantityInput?.value || 0) || 0;
        const price = parseFloat(unitPriceInput?.value || 0) || 0;
        if (subtotalDisplay) {
            subtotalDisplay.value = formatRp(qty * price);
        }
    }

    function applyPurchaseOption(option) {
        if (!option || !option.value) {
            maxQuantity = null;
            if (purchaseInfo) {
                purchaseInfo.classList.add('hidden');
                purchaseInfo.textContent = '';
            }
            if (costHint) costHint.textContent = 'Harga beli grosir: —';
            if (quantityHint) quantityHint.textContent = 'Maksimal sesuai stok grosir tersisa';
            return;
        }

        const description = option.dataset.description || '';
        const remaining = parseInt(option.dataset.remaining || '0', 10);
        const cost = parseFloat(option.dataset.cost || '0') || 0;

        maxQuantity = remaining;
        if (descriptionInput) descriptionInput.value = description;
        if (quantityInput) {
            quantityInput.max = remaining > 0 ? remaining : 1;
            if ((parseInt(quantityInput.value, 10) || 0) > remaining) {
                quantityInput.value = remaining > 0 ? remaining : 1;
            }
        }
        if (costHint) costHint.textContent = 'Harga beli grosir: ' + formatRp(cost);
        if (quantityHint) quantityHint.textContent = 'Stok tersisa: ' + remaining + ' unit';
        if (purchaseInfo) {
            purchaseInfo.textContent = 'Supplier: ' + (option.textContent.split('—')[0] || '').trim();
            purchaseInfo.classList.remove('hidden');
        }
        calculateSubtotal();
    }

    async function refreshPurchaseDetails(purchaseId) {
        if (!purchaseId) return;
        let url = detailsUrlTemplate.replace('__ID__', purchaseId);
        if (excludeId) {
            url += '?exclude_sale_transaction_id=' + encodeURIComponent(excludeId);
        }
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) return;
            const data = await response.json();
            maxQuantity = data.remaining_quantity;
            if (descriptionInput) descriptionInput.value = data.description;
            if (quantityInput) {
                quantityInput.max = data.remaining_quantity > 0 ? data.remaining_quantity : 1;
            }
            if (costHint) costHint.textContent = 'Harga beli grosir: ' + formatRp(data.cost_unit_price);
            if (quantityHint) quantityHint.textContent = 'Stok tersisa: ' + data.remaining_quantity + ' unit';
            if (purchaseInfo) {
                purchaseInfo.textContent = data.invoice_number + ' • ' + (data.supplier || '—') + ' • ' + (data.purchase_date || '');
                purchaseInfo.classList.remove('hidden');
            }
            calculateSubtotal();
        } catch (e) {}
    }

    purchaseSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        applyPurchaseOption(option);
        if (option?.value) {
            refreshPurchaseDetails(option.value);
        }
    });

    [quantityInput, unitPriceInput].forEach(function(input) {
        if (!input) return;
        input.addEventListener('input', calculateSubtotal);
        input.addEventListener('change', function() {
            if (maxQuantity !== null && (parseInt(input.value, 10) || 0) > maxQuantity) {
                input.value = maxQuantity;
            }
            calculateSubtotal();
        });
    });

    const initialOption = purchaseSelect.options[purchaseSelect.selectedIndex];
    if (initialOption?.value) {
        applyPurchaseOption(initialOption);
        refreshPurchaseDetails(initialOption.value);
    } else {
        calculateSubtotal();
    }

    window.initSubtotalCalculator = calculateSubtotal;
})();
</script>
