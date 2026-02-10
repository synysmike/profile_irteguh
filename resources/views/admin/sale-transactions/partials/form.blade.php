<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode Transaksi (Opsional)</label>
        <input type="text" id="code" name="code" value="{{ old('code', isset($transaction) && $transaction ? $transaction->code : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
               placeholder="Contoh: TRX-001">
        <p class="mt-1 text-xs text-gray-500">Kode unik untuk identifikasi transaksi</p>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Item *</label>
        <input type="text" id="description" name="description" value="{{ old('description', isset($transaction) && $transaction ? $transaction->description : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
               placeholder="Nama barang/jasa">
    </div>

    <div>
        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
        <input type="number" id="quantity" name="quantity" value="{{ old('quantity', isset($transaction) && $transaction ? $transaction->quantity : 1) }}" min="1" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Satuan (Rp) *</label>
        <input type="number" id="unit_price" name="unit_price" value="{{ old('unit_price', isset($transaction) && $transaction ? $transaction->unit_price : 0) }}" min="0" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="subtotal_display" class="block text-sm font-medium text-gray-700 mb-2">Subtotal (Rp)</label>
        <input type="text" id="subtotal_display" value="0" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 font-semibold text-gray-900">
        <p class="mt-1 text-xs text-gray-500">Otomatis dihitung: Qty × Harga Satuan</p>
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
    function initSubtotalCalculator() {
        const quantityInput = document.getElementById('quantity');
        const unitPriceInput = document.getElementById('unit_price');
        const subtotalDisplay = document.getElementById('subtotal_display');

        if (!quantityInput || !unitPriceInput || !subtotalDisplay) {
            return false;
        }

        // Check if already initialized
        if (quantityInput.hasAttribute('data-subtotal-init')) {
            return true;
        }

        function calculateSubtotal() {
            const qty = parseFloat(quantityInput.value || 0);
            const price = parseFloat(unitPriceInput.value || 0);
            const subtotal = qty * price;
            
            subtotalDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        }

        // Add event listeners
        quantityInput.addEventListener('input', calculateSubtotal);
        quantityInput.addEventListener('change', calculateSubtotal);
        unitPriceInput.addEventListener('input', calculateSubtotal);
        unitPriceInput.addEventListener('change', calculateSubtotal);

        // Mark as initialized
        quantityInput.setAttribute('data-subtotal-init', 'true');
        unitPriceInput.setAttribute('data-subtotal-init', 'true');

        // Initial calculation
        calculateSubtotal();
        return true;
    }

    // Try to initialize immediately
    if (!initSubtotalCalculator()) {
        // Retry after a short delay if elements not ready
        setTimeout(function() {
            initSubtotalCalculator();
        }, 100);
    }

    // Make function available globally for re-initialization
    window.initSubtotalCalculator = initSubtotalCalculator;
})();
</script>
