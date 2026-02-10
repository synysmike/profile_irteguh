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

    <div>
        <label for="subtotal" class="block text-sm font-medium text-gray-700 mb-2">Subtotal (DPP) Rp *</label>
        <input type="number" id="subtotal" name="subtotal" value="{{ old('subtotal', isset($purchase) && $purchase ? $purchase->subtotal : 0) }}" min="0" step="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <p class="mt-1 text-xs text-gray-500">Dasar Pengenaan Pajak</p>
    </div>

    <div>
        <label for="ppn_amount" class="block text-sm font-medium text-gray-700 mb-2">PPN Masukan (11%) Rp</label>
        <input type="number" id="ppn_amount" name="ppn_amount" value="{{ old('ppn_amount', isset($purchase) && $purchase ? $purchase->ppn_amount : 0) }}" min="0" step="1" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500">
        <p class="mt-1 text-xs text-gray-500">Otomatis dihitung 11% dari DPP</p>
    </div>

    <div>
        <label for="total_display" class="block text-sm font-medium text-gray-700 mb-2">Total (DPP + PPN) Rp</label>
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
// PPN Calculator untuk form pembelian - Global function
window.setupPPNCalculator = function() {
    const PPN_RATE = 0.11; // 11%
    
    const subtotalInput = document.getElementById('subtotal');
    const ppnInput = document.getElementById('ppn_amount');
    const totalDisplay = document.getElementById('total_display');
    
    if (!subtotalInput || !ppnInput || !totalDisplay) {
        console.log('PPN Calculator: Elements not found');
        return false;
    }

    // Cek apakah sudah di-setup (untuk menghindari duplikasi)
    if (subtotalInput.hasAttribute('data-ppn-setup')) {
        console.log('PPN Calculator: Already setup');
        return true;
    }

    function calculatePPN() {
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const ppn = Math.round(subtotal * PPN_RATE);
        const total = subtotal + ppn;

        ppnInput.value = ppn;
        totalDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        
        console.log('PPN Calculated:', { subtotal, ppn, total });
    }

    // Pasang event listener dengan berbagai event untuk memastikan terdeteksi
    subtotalInput.addEventListener('input', calculatePPN, false);
    subtotalInput.addEventListener('change', calculatePPN, false);
    subtotalInput.addEventListener('keyup', calculatePPN, false);
    subtotalInput.addEventListener('keydown', calculatePPN, false);
    
    // Mark sebagai sudah di-setup
    subtotalInput.setAttribute('data-ppn-setup', 'true');

    // Hitung nilai awal
    calculatePPN();
    
    console.log('PPN Calculator: Setup completed');
    return true;
};

// Auto-setup saat script dimuat
(function() {
    // Coba setup langsung
    if (window.setupPPNCalculator()) {
        return;
    }

    // Jika belum berhasil, coba lagi setelah delay
    setTimeout(function() {
        window.setupPPNCalculator();
    }, 100);

    setTimeout(function() {
        window.setupPPNCalculator();
    }, 300);

    setTimeout(function() {
        window.setupPPNCalculator();
    }, 500);
    
    // Juga coba setelah 1 detik (untuk form yang dimuat sangat lambat)
    setTimeout(function() {
        window.setupPPNCalculator();
    }, 1000);
})();
</script>
