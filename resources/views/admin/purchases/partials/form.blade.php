<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">Vendor (Supplier) *</label>
        <select id="supplier_id" name="supplier_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">-- Pilih Supplier --</option>
            @foreach($suppliers as $s)
            <option value="{{ $s->id }}" {{ old('supplier_id', isset($purchase) && $purchase ? $purchase->supplier_id : '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Data dari Master Keuangan → Data Klien & Vendor</p>
    </div>

    <div>
        <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-2">No. Faktur *</label>
        <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', isset($purchase) && $purchase ? $purchase->invoice_number : \App\Models\Purchase::generateInvoiceNumber()) }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal *</label>
        <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', isset($purchase) && $purchase ? $purchase->purchase_date?->format('Y-m-d') : date('Y-m-d')) }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Nama Barang *</label>
        <input type="text" id="description" name="description" value="{{ old('description', isset($purchase) && $purchase ? $purchase->description : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
               placeholder="Contoh: Beras Premium 5kg">
        <p class="mt-1 text-xs text-gray-500">Barang ini akan tersedia untuk transaksi penjualan</p>
    </div>

    <div>
        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
        <input type="number" id="quantity" name="quantity" value="{{ old('quantity', isset($purchase) && $purchase ? $purchase->quantity : 1) }}" min="1" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        @if(isset($purchase) && $purchase && $purchase->allocatedQuantity() > 0)
        <p class="mt-1 text-xs text-amber-600">Min. {{ $purchase->allocatedQuantity() }} unit (sudah dialokasikan ke penjualan)</p>
        @endif
    </div>

    <div>
        <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Beli per Unit (Rp) *</label>
        <input type="number" id="unit_price" name="unit_price" value="{{ old('unit_price', isset($purchase) && $purchase ? $purchase->unit_price : 0) }}" min="0" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="subtotal" class="block text-sm font-medium text-gray-700 mb-2">Subtotal (DPP) Rp</label>
        <input type="number" id="subtotal" name="subtotal" value="{{ old('subtotal', isset($purchase) && $purchase ? $purchase->subtotal : 0) }}" min="0" step="1" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500">
        <p class="mt-1 text-xs text-gray-500">Otomatis: Qty × Harga Beli</p>
    </div>

    <div>
        <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-2">Pajak</label>
        <select id="tax_id" name="tax_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">Tanpa Pajak</option>
            @foreach(($taxes ?? collect()) as $tax)
            <option value="{{ $tax->id }}"
                    data-rate="{{ $tax->rate }}"
                    data-calculation="{{ $tax->calculation_type }}"
                    data-name="{{ $tax->name }}"
                    {{ (string) old('tax_id', isset($purchase) && $purchase ? $purchase->tax_id : '') === (string) $tax->id ? 'selected' : '' }}>
                {{ $tax->name }} ({{ number_format($tax->rate, 2, ',', '.') }}%) {{ $tax->calculation_type === 'deduction' ? '- Potongan' : '+ Tambahan' }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="ppn_amount" id="tax_amount_label" class="block text-sm font-medium text-gray-700 mb-2">Nominal Pajak Rp</label>
        <input type="number" id="ppn_amount" name="ppn_amount" value="{{ old('ppn_amount', isset($purchase) && $purchase ? $purchase->ppn_amount : 0) }}" min="0" step="1" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="total_display" class="block text-sm font-medium text-gray-700 mb-2">Total Setelah Pajak Rp</label>
        <input type="text" id="total_display" value="0" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 font-semibold text-gray-900">
        <p class="mt-1 text-xs text-gray-500">Total otomatis terhitung</p>
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
        <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', isset($purchase) && $purchase ? $purchase->notes : '') }}</textarea>
    </div>
</div>

<script>
window.setupPPNCalculator = function() {
    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unit_price');
    const subtotalInput = document.getElementById('subtotal');
    const ppnInput = document.getElementById('ppn_amount');
    const totalDisplay = document.getElementById('total_display');
    const taxSelect = document.getElementById('tax_id');
    const taxAmountLabel = document.getElementById('tax_amount_label');

    if (!subtotalInput || !ppnInput || !totalDisplay) {
        return false;
    }

    if (subtotalInput.hasAttribute('data-ppn-setup')) {
        return true;
    }

    function calculateTotals() {
        const qty = parseFloat(quantityInput?.value || 0) || 0;
        const unitPrice = parseFloat(unitPriceInput?.value || 0) || 0;
        const subtotal = Math.round(qty * unitPrice);
        const selectedTax = taxSelect ? taxSelect.options[taxSelect.selectedIndex] : null;
        const rate = selectedTax ? (parseFloat(selectedTax.dataset.rate || 0) || 0) : 0;
        const calculation = selectedTax ? (selectedTax.dataset.calculation || 'addition') : 'addition';
        const taxName = selectedTax ? (selectedTax.dataset.name || 'Pajak') : 'Pajak';
        const ppn = Math.round(subtotal * rate / 100);
        const total = calculation === 'deduction' ? (subtotal - ppn) : (subtotal + ppn);

        subtotalInput.value = subtotal;
        ppnInput.value = ppn;
        totalDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(total, 0));
        if (taxAmountLabel) {
            taxAmountLabel.textContent = selectedTax
                ? `Nominal ${taxName} (${rate.toFixed(2)}%) Rp`
                : 'Nominal Pajak Rp';
        }
    }

    [quantityInput, unitPriceInput, subtotalInput].forEach(function(el) {
        if (!el) return;
        ['input', 'change', 'keyup'].forEach(function(evt) {
            el.addEventListener(evt, calculateTotals, false);
        });
    });
    if (taxSelect) {
        taxSelect.addEventListener('change', calculateTotals, false);
    }

    subtotalInput.setAttribute('data-ppn-setup', 'true');
    calculateTotals();
    return true;
};

(function() {
    if (window.setupPPNCalculator()) return;
    [100, 300, 500, 1000].forEach(function(ms) {
        setTimeout(window.setupPPNCalculator, ms);
    });
})();
</script>
