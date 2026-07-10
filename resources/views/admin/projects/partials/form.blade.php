<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Project *</label>
        <input type="text" id="title" name="title" value="{{ old('title', $project->title ?? '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">Customer *</label>
        <select id="customer_id" name="customer_id" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">-- Pilih Customer --</option>
            @foreach($customers as $customer)
            <option value="{{ $customer->id }}" {{ (string) old('customer_id', $project->customer_id ?? '') === (string) $customer->id ? 'selected' : '' }}>
                {{ $customer->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-2">Pajak</label>
        <select id="tax_id" name="tax_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">Tanpa Pajak</option>
            @foreach($taxes as $tax)
            <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}" data-calculation="{{ $tax->calculation_type }}" data-name="{{ $tax->name }}"
                {{ (string) old('tax_id', $project->tax_id ?? '') === (string) $tax->id ? 'selected' : '' }}>
                {{ $tax->name }} ({{ number_format($tax->rate, 2, ',', '.') }}%) {{ $tax->calculation_type === 'deduction' ? '- Potongan' : '+ Tambahan' }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status Progress *</label>
        <select id="status" name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @foreach(\App\Models\Project::statusLabels() as $value => $label)
            <option value="{{ $value }}" {{ old('status', $project->status ?? 'pending') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="progress_percent" class="block text-sm font-medium text-gray-700 mb-2">Progress (%)</label>
        <input type="number" id="progress_percent" name="progress_percent" min="0" max="100"
               value="{{ old('progress_percent', $project->progress_percent ?? 0) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="subtotal" class="block text-sm font-medium text-gray-700 mb-2">Nilai Kontrak (DPP) Rp *</label>
        <input type="number" id="subtotal" name="subtotal" min="0" step="1" required
               value="{{ old('subtotal', isset($project) ? $project->subtotal : 0) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="tax_amount_display" class="block text-sm font-medium text-gray-700 mb-2">Nominal Pajak Rp</label>
        <input type="text" id="tax_amount_display" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50">
    </div>

    <div>
        <label for="total_display" class="block text-sm font-medium text-gray-700 mb-2">Total Setelah Pajak Rp</label>
        <input type="text" id="total_display" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 font-semibold">
    </div>

    <div>
        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran *</label>
        <select id="payment_method" name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="full" {{ old('payment_method', $project->payment_method ?? 'full') === 'full' ? 'selected' : '' }}>Langsung Lunas</option>
            <option value="installment" {{ old('payment_method', $project->payment_method ?? '') === 'installment' ? 'selected' : '' }}>Beberapa Termin</option>
        </select>
    </div>

    <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', isset($project) && $project->start_date ? $project->start_date->format('Y-m-d') : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Target Selesai</label>
        <input type="date" id="due_date" name="due_date" value="{{ old('due_date', isset($project) && $project->due_date ? $project->due_date->format('Y-m-d') : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
        <textarea id="description" name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', $project->description ?? '') }}</textarea>
    </div>
</div>

<div id="installment-section" class="mt-6 {{ old('payment_method', $project->payment_method ?? 'full') === 'installment' ? '' : 'hidden' }}">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-800">Jadwal Termin Pembayaran</h4>
        <button type="button" id="btn-add-term" class="text-sm text-purple-600 hover:text-purple-800 font-medium">+ Tambah Termin</button>
    </div>
    <p class="text-xs text-gray-500 mb-3">Total persentase harus 100%. Setiap termin akan terhubung ke invoice penjualan & kas saat ditandai lunas.</p>
    <div id="terms-container" class="space-y-3">
        @php
            $terms = old('terms', isset($project) ? $project->paymentTerms->map(fn($t) => [
                'label' => $t->label,
                'percentage' => $t->percentage,
                'due_date' => $t->due_date?->format('Y-m-d'),
            ])->toArray() : [['label' => 'DP', 'percentage' => 50, 'due_date' => ''], ['label' => 'Pelunasan', 'percentage' => 50, 'due_date' => '']]);
        @endphp
        @foreach($terms as $index => $term)
        <div class="term-row grid grid-cols-1 md:grid-cols-4 gap-3 items-end border border-gray-200 rounded-lg p-3 bg-gray-50">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Label Termin</label>
                <input type="text" name="terms[{{ $index }}][label]" value="{{ $term['label'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Persentase (%)</label>
                <input type="number" name="terms[{{ $index }}][percentage]" value="{{ $term['percentage'] ?? '' }}" min="0" max="100" step="0.01" class="term-percentage w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Jatuh Tempo</label>
                <input type="date" name="terms[{{ $index }}][due_date]" value="{{ $term['due_date'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <button type="button" class="btn-remove-term w-full px-3 py-2 bg-red-50 text-red-600 rounded-md text-sm hover:bg-red-100">Hapus</button>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-2 text-sm text-gray-600">Total persentase: <span id="terms-percent-total" class="font-semibold">0</span>%</div>
</div>

<div class="mt-6">
    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', $project->notes ?? '') }}</textarea>
</div>

<script>
window.initProjectForm = function() {
    const subtotalInput = document.getElementById('subtotal');
    const taxSelect = document.getElementById('tax_id');
    const taxAmountDisplay = document.getElementById('tax_amount_display');
    const totalDisplay = document.getElementById('total_display');
    const paymentMethod = document.getElementById('payment_method');
    const installmentSection = document.getElementById('installment-section');
    const termsContainer = document.getElementById('terms-container');
    const percentTotal = document.getElementById('terms-percent-total');
    const btnAddTerm = document.getElementById('btn-add-term');

    if (!subtotalInput) return;

    function formatRupiah(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, Math.round(n)));
    }

    function calculateTotals() {
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const selected = taxSelect ? taxSelect.options[taxSelect.selectedIndex] : null;
        const rate = selected && selected.value ? parseFloat(selected.dataset.rate || 0) : 0;
        const calculation = selected ? (selected.dataset.calculation || 'addition') : 'addition';
        const taxAmount = Math.round(subtotal * rate / 100);
        const total = calculation === 'deduction' ? (subtotal - taxAmount) : (subtotal + taxAmount);
        if (taxAmountDisplay) taxAmountDisplay.value = formatRupiah(taxAmount);
        if (totalDisplay) totalDisplay.value = formatRupiah(total);
    }

    function updatePercentTotal() {
        let sum = 0;
        document.querySelectorAll('.term-percentage').forEach(function(el) {
            sum += parseFloat(el.value) || 0;
        });
        if (percentTotal) {
            percentTotal.textContent = sum.toFixed(2);
            percentTotal.className = Math.abs(sum - 100) < 0.01 ? 'font-semibold text-green-600' : 'font-semibold text-red-600';
        }
    }

    function bindTermRow(row) {
        const removeBtn = row.querySelector('.btn-remove-term');
        const percentInput = row.querySelector('.term-percentage');
        if (removeBtn) {
            removeBtn.onclick = function() {
                if (document.querySelectorAll('.term-row').length <= 1) return;
                row.remove();
                reindexTerms();
                updatePercentTotal();
            };
        }
        if (percentInput) percentInput.addEventListener('input', updatePercentTotal);
    }

    function reindexTerms() {
        document.querySelectorAll('.term-row').forEach(function(row, index) {
            row.querySelectorAll('input').forEach(function(input) {
                const name = input.getAttribute('name');
                if (!name) return;
                input.setAttribute('name', name.replace(/terms\[\d+\]/, 'terms[' + index + ']'));
            });
        });
    }

    if (subtotalInput.dataset.projectInit !== '1') {
        subtotalInput.addEventListener('input', calculateTotals);
        if (taxSelect) taxSelect.addEventListener('change', calculateTotals);
        if (paymentMethod) {
            paymentMethod.addEventListener('change', function() {
                if (this.value === 'installment') {
                    installmentSection.classList.remove('hidden');
                } else {
                    installmentSection.classList.add('hidden');
                }
            });
        }
        if (btnAddTerm) {
            btnAddTerm.addEventListener('click', function() {
                const index = document.querySelectorAll('.term-row').length;
                const row = document.createElement('div');
                row.className = 'term-row grid grid-cols-1 md:grid-cols-4 gap-3 items-end border border-gray-200 rounded-lg p-3 bg-gray-50';
                row.innerHTML = '<div><label class="block text-xs text-gray-600 mb-1">Label Termin</label><input type="text" name="terms[' + index + '][label]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"></div><div><label class="block text-xs text-gray-600 mb-1">Persentase (%)</label><input type="number" name="terms[' + index + '][percentage]" min="0" max="100" step="0.01" class="term-percentage w-full px-3 py-2 border border-gray-300 rounded-md text-sm"></div><div><label class="block text-xs text-gray-600 mb-1">Jatuh Tempo</label><input type="date" name="terms[' + index + '][due_date]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"></div><div><button type="button" class="btn-remove-term w-full px-3 py-2 bg-red-50 text-red-600 rounded-md text-sm hover:bg-red-100">Hapus</button></div>';
                termsContainer.appendChild(row);
                bindTermRow(row);
                updatePercentTotal();
            });
        }
        subtotalInput.dataset.projectInit = '1';
    }

    document.querySelectorAll('.term-row').forEach(bindTermRow);
    calculateTotals();
    updatePercentTotal();
};
</script>
