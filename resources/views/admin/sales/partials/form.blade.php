<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">Klien (Customer) *</label>
        <select id="customer_id" name="customer_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">-- Pilih Customer --</option>
            @foreach($customers as $c)
            <option value="{{ $c->id }}" {{ old('customer_id', isset($sale) && $sale ? $sale->customer_id : '') == $c->id ? 'selected' : '' }}>{{ $c->name }}{{ $c->company_name ? ' (' . $c->company_name . ')' : '' }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Data dari Master Keuangan → Data Klien & Vendor</p>
    </div>

    <div>
        <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-2">No. Faktur *</label>
        <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', isset($sale) && $sale ? $sale->invoice_number : \App\Models\Sale::generateInvoiceNumber()) }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="sale_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal *</label>
        <input type="date" id="sale_date" name="sale_date" value="{{ old('sale_date', isset($sale) && $sale ? $sale->sale_date?->format('Y-m-d') : date('Y-m-d')) }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    @if(isset($sale) && $sale)
    {{-- Edit: pakai dropdown transaksi seperti sebelumnya --}}
    <div class="md:col-span-2">
        <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-medium text-gray-700">Pilih Transaksi *</label>
            <a href="{{ route('admin.keuangan.sale-transactions.index') }}" target="_blank" class="text-xs text-purple-600 hover:text-purple-800">Kelola Transaksi →</a>
        </div>
        <div class="space-y-2" id="selected-transactions">
            @php
                $selectedIds = [];
                if (isset($sale) && $sale->saleItems) {
                    foreach ($sale->saleItems as $item) {
                        $match = \App\Models\SaleTransaction::where('description', $item->description)->where('quantity', $item->quantity)->where('unit_price', $item->unit_price)->first();
                        if ($match) $selectedIds[] = $match->id;
                    }
                }
            @endphp
            @if(!empty($selectedIds))
                @foreach(\App\Models\SaleTransaction::whereIn('id', $selectedIds)->get() as $trans)
                    <div class="selected-transaction-item border border-gray-200 rounded-lg p-3 bg-gray-50 flex items-center justify-between" data-transaction-id="{{ $trans->id }}" data-subtotal="{{ $trans->subtotal }}">
                        <div class="flex-1">
                            <span class="font-medium text-sm">{{ $trans->description }}</span>
                            <span class="text-xs text-gray-500 ml-2">Qty: {{ $trans->quantity }} × Rp {{ number_format($trans->unit_price, 0, ',', '.') }} = Rp {{ number_format($trans->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <button type="button" class="btn-remove-transaction text-red-600 hover:text-red-800 text-sm ml-2">Hapus</button>
                        <input type="hidden" name="transaction_ids[]" value="{{ $trans->id }}">
                    </div>
                @endforeach
            @endif
        </div>
        <select id="transaction-select" multiple class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 mt-2" size="5" style="min-height: 120px;">
            <option value="" disabled>-- Pilih transaksi (Ctrl+klik untuk multi) --</option>
            @foreach(\App\Models\SaleTransaction::active()->fromGrosir()->with('purchase')->orderBy('description')->get() as $transaction)
                <option value="{{ $transaction->id }}" data-description="{{ $transaction->description }}" data-quantity="{{ $transaction->quantity }}" data-unit-price="{{ $transaction->unit_price }}" data-subtotal="{{ $transaction->subtotal }}" {{ in_array($transaction->id, $selectedIds) ? 'disabled' : '' }}>
                    {{ $transaction->description }} {{ $transaction->code ? '(' . $transaction->code . ')' : '' }}
                    @if($transaction->purchase) [{{ $transaction->purchase->invoice_number }}] @endif
                    - Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}
                </option>
            @endforeach
        </select>
    </div>
    @else
    {{-- Create: hanya tampilkan daftar sementara (read-only). Tambah transaksi dilakukan di halaman via modal terpisah. --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Transaksi yang akan di-invoice</label>
        <div id="pending-transactions-list" class="space-y-2 min-h-[60px] border border-dashed border-gray-300 rounded-lg p-3 bg-gray-50">
            <p class="text-sm text-gray-500 text-center py-4">Memuat daftar...</p>
        </div>
        <p class="mt-1 text-xs text-gray-500">Tambahkan transaksi dari halaman Penjualan (tombol &quot;Tambah Transaksi&quot;) lalu buat invoice di sini.</p>
    </div>
    @endif

    <div>
        <label for="subtotal" class="block text-sm font-medium text-gray-700 mb-2">Subtotal (DPP) Rp *</label>
        <input type="number" id="subtotal" name="subtotal" value="{{ old('subtotal', isset($sale) && $sale ? $sale->subtotal : 0) }}" min="0" step="1" required readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500">
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
                    {{ (string) old('tax_id', isset($sale) && $sale ? $sale->tax_id : '') === (string) $tax->id ? 'selected' : '' }}>
                {{ $tax->name }} ({{ number_format($tax->rate, 2, ',', '.') }}%) {{ $tax->calculation_type === 'deduction' ? '- Potongan' : '+ Tambahan' }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="ppn_amount" id="tax_amount_label" class="block text-sm font-medium text-gray-700 mb-2">Nominal Pajak Rp</label>
        <input type="number" id="ppn_amount" name="ppn_amount" value="{{ old('ppn_amount', isset($sale) && $sale ? $sale->ppn_amount : 0) }}" min="0" step="1" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="total_display" class="block text-sm font-medium text-gray-700 mb-2">Total Setelah Pajak Rp</label>
        <input type="text" id="total_display" value="0" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 font-semibold text-gray-900">
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
        <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', isset($sale) && $sale ? $sale->notes : '') }}</textarea>
    </div>
</div>

<script>
(function() {
    const isCreate = document.getElementById('pending-transactions-list') !== null;
    const pendingListEl = document.getElementById('pending-transactions-list');
    const subtotalInput = document.getElementById('subtotal');
    const ppnInput = document.getElementById('ppn_amount');
    const totalDisplay = document.getElementById('total_display');
    const taxSelect = document.getElementById('tax_id');
    const taxAmountLabel = document.getElementById('tax_amount_label');

    function formatRupiah(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
    }

    function calculateTaxAndTotal() {
        const subtotal = parseFloat(subtotalInput ? subtotalInput.value : 0) || 0;
        const selectedTax = taxSelect ? taxSelect.options[taxSelect.selectedIndex] : null;
        const rate = selectedTax ? (parseFloat(selectedTax.dataset.rate || 0) || 0) : 0;
        const calculation = selectedTax ? (selectedTax.dataset.calculation || 'addition') : 'addition';
        const taxName = selectedTax ? (selectedTax.dataset.name || 'Pajak') : 'Pajak';
        const taxAmount = Math.round(subtotal * rate / 100);
        const total = calculation === 'deduction'
            ? (subtotal - taxAmount)
            : (subtotal + taxAmount);

        if (ppnInput) ppnInput.value = taxAmount;
        if (totalDisplay) totalDisplay.value = formatRupiah(Math.max(total, 0));
        if (taxAmountLabel) {
            taxAmountLabel.textContent = selectedTax
                ? `Nominal ${taxName} (${rate.toFixed(2)}%) Rp`
                : 'Nominal Pajak Rp';
        }
    }

    if (isCreate && pendingListEl) {
        const PENDING_LIST_URL = '{{ route("admin.sales.pending-transactions.list") }}';

        function getSubmitBtn() {
            var form = document.getElementById('saleModal_form') || pendingListEl.closest('form');
            return form ? document.querySelector('button[type="submit"][form="' + form.id + '"]') : null;
        }

        function renderPendingList(data) {
            if (!data.items || data.items.length === 0) {
                pendingListEl.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Belum ada transaksi. Tutup modal ini, lalu gunakan tombol &quot;Tambah Transaksi&quot; di halaman untuk menambah ke daftar.</p>';
            } else {
                var html = '';
                data.items.forEach(function(it) {
                    html += '<div class="flex items-center justify-between border border-gray-200 rounded-lg p-3 bg-white">';
                    html += '<div class="flex-1"><span class="font-medium text-sm">' + (it.description || '') + '</span>';
                    html += ' <span class="text-xs text-gray-500">Qty: ' + it.quantity + ' × Rp ' + new Intl.NumberFormat('id-ID').format(it.unit_price) + ' = Rp ' + new Intl.NumberFormat('id-ID').format(it.subtotal) + '</span></div>';
                    html += '</div>';
                });
                pendingListEl.innerHTML = html;
            }
            if (subtotalInput) subtotalInput.value = Math.round(data.subtotal || 0);
            calculateTaxAndTotal();
            var submitBtn = getSubmitBtn();
            if (submitBtn) submitBtn.disabled = !data.items || data.items.length === 0;
        }

        function loadPendingList() {
            fetch(PENDING_LIST_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(renderPendingList)
                .catch(function() { renderPendingList({ items: [], subtotal: 0, ppn_amount: 0, total: 0 }); });
        }

        loadPendingList();
        window.refreshPendingInSaleModal = loadPendingList;
    } else {
        window.initSaleTransactionsForm = function() {
            var transactionSelect = document.getElementById('transaction-select');
            var selectedContainer = document.getElementById('selected-transactions');
            if (!transactionSelect || !selectedContainer || !subtotalInput || !ppnInput || !totalDisplay) return false;
            if (transactionSelect.hasAttribute('data-transactions-init')) return true;

            function getSelectedIds() {
                var ids = [];
                selectedContainer.querySelectorAll('.selected-transaction-item').forEach(function(el) {
                    var id = el.getAttribute('data-transaction-id');
                    if (id) ids.push(id);
                });
                return ids;
            }
            function calculateTotals() {
                var subtotal = 0;
                selectedContainer.querySelectorAll('.selected-transaction-item').forEach(function(el) {
                    subtotal += parseFloat(el.getAttribute('data-subtotal') || 0);
                });
                subtotalInput.value = Math.round(subtotal);
                calculateTaxAndTotal();
                var submitBtn = document.querySelector('button[type="submit"][form="' + (selectedContainer.closest('form')?.id || '') + '"]');
                if (submitBtn) submitBtn.disabled = getSelectedIds().length === 0 || subtotal <= 0;
            }
            function updateSelectOptions() {
                var selectedIds = getSelectedIds();
                transactionSelect.querySelectorAll('option').forEach(function(opt) {
                    if (opt.value) {
                        opt.disabled = selectedIds.includes(opt.value);
                        opt.style.color = opt.disabled ? '#9ca3af' : '';
                    }
                });
            }

            var newSelect = transactionSelect.cloneNode(true);
            transactionSelect.parentNode.replaceChild(newSelect, transactionSelect);
            newSelect.addEventListener('change', function() {
                var toAdd = [];
                for (var i = 0; i < this.options.length; i++) {
                    var opt = this.options[i];
                    if (!opt.value || opt.disabled) continue;
                    if (opt.selected && !getSelectedIds().includes(opt.value)) toAdd.push(opt);
                }
                toAdd.forEach(function(opt) {
                    var item = document.createElement('div');
                    item.className = 'selected-transaction-item border border-gray-200 rounded-lg p-3 bg-gray-50 flex items-center justify-between';
                    item.setAttribute('data-transaction-id', opt.value);
                    item.setAttribute('data-subtotal', opt.getAttribute('data-subtotal') || '0');
                    item.innerHTML = '<div class="flex-1"><span class="font-medium text-sm">' + opt.getAttribute('data-description') + '</span> <span class="text-xs text-gray-500">Qty: ' + opt.getAttribute('data-quantity') + ' × Rp ' + new Intl.NumberFormat('id-ID').format(opt.getAttribute('data-unit-price')) + ' = Rp ' + new Intl.NumberFormat('id-ID').format(opt.getAttribute('data-subtotal')) + '</span></div><button type="button" class="btn-remove-transaction text-red-600 hover:text-red-800 text-sm ml-2">Hapus</button><input type="hidden" name="transaction_ids[]" value="' + opt.value + '">';
                    item.querySelector('.btn-remove-transaction').onclick = function() { item.remove(); calculateTotals(); updateSelectOptions(); };
                    selectedContainer.appendChild(item);
                });
                for (var j = 0; j < newSelect.options.length; j++) newSelect.options[j].selected = false;
                calculateTotals();
                updateSelectOptions();
            });
            selectedContainer.querySelectorAll('.btn-remove-transaction').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var item = this.closest('.selected-transaction-item');
                    if (item) { item.remove(); calculateTotals(); updateSelectOptions(); }
                });
            });
            newSelect.setAttribute('data-transactions-init', 'true');
            calculateTotals();
            updateSelectOptions();
            return true;
        };
        if (document.getElementById('transaction-select')) {
            if (!window.initSaleTransactionsForm()) setTimeout(window.initSaleTransactionsForm, 100);
        }

        if (taxSelect) {
            taxSelect.addEventListener('change', calculateTaxAndTotal);
            calculateTaxAndTotal();
        }
    }

    if (taxSelect && isCreate) {
        taxSelect.addEventListener('change', calculateTaxAndTotal);
        calculateTaxAndTotal();
    }
})();
</script>
