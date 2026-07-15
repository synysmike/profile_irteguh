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
    <h4 class="text-sm font-semibold text-gray-800 mb-1">Jadwal Termin Pembayaran</h4>
    <p class="text-xs text-gray-500 mb-4">DP diisi terpisah. Termin tambahan adalah cicilan setelah DP. Total semua persentase harus 100%.</p>

    @php
        $allTerms = isset($project) ? $project->paymentTerms : collect();
        $dpTerm = $allTerms->first(fn ($t) => strcasecmp((string) $t->label, 'DP') === 0)
            ?? $allTerms->sortBy('term_number')->first();
        $extraTerms = $allTerms->when($dpTerm, fn ($c) => $c->where('id', '!=', $dpTerm->id))->values();

        $dpPaid = $dpTerm && $dpTerm->status === 'paid';
        $dpLabel = old('dp.label', $dpTerm->label ?? 'DP');
        $dpPercentage = old('dp.percentage', $dpTerm->percentage ?? 50);
        $dpDueDate = old('dp.due_date', $dpTerm && $dpTerm->due_date ? $dpTerm->due_date->format('Y-m-d') : '');

        $paidExtra = $extraTerms->where('status', 'paid')->values();
        $editableExtra = old('terms', $extraTerms
            ->where('status', '!=', 'paid')
            ->values()
            ->map(fn ($t) => [
                'label' => $t->label,
                'percentage' => $t->percentage,
                'due_date' => $t->due_date?->format('Y-m-d'),
            ])->toArray()
        );

        if (!isset($project) && !old('terms')) {
            $editableExtra = [
                ['label' => 'Pelunasan', 'percentage' => 50, 'due_date' => ''],
            ];
        }

        // DP always editable in form — only paid *additional* terms are locked in the total.
        $paidLockedPercent = round((float) $paidExtra->sum('percentage'), 2);
    @endphp

    {{-- DP (selalu bisa diedit, termasuk jika sudah lunas) --}}
    <div id="dp-section" class="mb-4 border border-purple-200 rounded-lg p-4 bg-purple-50/60">
        <div class="flex items-center justify-between mb-3">
            <h5 class="text-sm font-semibold text-purple-900">Down Payment (DP)</h5>
            @if($dpPaid)
            <span class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded">Sudah lunas — tetap bisa diedit</span>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Label</label>
                <input type="text" name="dp[label]" id="dp_label" value="{{ $dpLabel }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Persentase (%)</label>
                <input type="number" name="dp[percentage]" id="dp_percentage" value="{{ $dpPercentage }}"
                       min="0" max="100" step="0.01"
                       class="term-percentage-dp w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Jatuh Tempo</label>
                <input type="date" name="dp[due_date]" id="dp_due_date" value="{{ $dpDueDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white">
            </div>
        </div>
        @if($dpPaid)
        <input type="hidden" name="dp[term_id]" value="{{ $dpTerm->id }}">
        <p class="text-xs text-amber-700 mt-2">Mengubah DP yang sudah lunas akan memperbarui data termin (invoice terkait tidak diubah otomatis).</p>
        @endif
    </div>

    @if($paidExtra->isNotEmpty())
    <div class="mb-3 space-y-2">
        <p class="text-xs font-medium text-gray-700">Termin tambahan sudah lunas</p>
        @foreach($paidExtra as $paid)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-center border border-green-200 rounded-lg p-3 bg-green-50">
            <div class="text-sm text-gray-800 font-medium">{{ $paid->label }}</div>
            <div class="text-sm text-gray-700">{{ number_format((float) $paid->percentage, 2, ',', '.') }}%</div>
            <div class="text-sm text-gray-500">{{ $paid->due_date?->format('d/m/Y') ?: '—' }}</div>
            <div class="text-xs font-semibold text-green-700">Lunas</div>
        </div>
        @endforeach
    </div>
    @endif

    <input type="hidden" id="paid-terms-percent" value="{{ $paidLockedPercent }}">

    {{-- Termin tambahan --}}
    <div class="flex items-center justify-between mb-2">
        <h5 class="text-sm font-semibold text-gray-800">Termin Tambahan</h5>
        <button type="button" id="btn-add-term" class="text-sm text-purple-600 hover:text-purple-800 font-medium">+ Tambah Termin</button>
    </div>
    <div id="terms-container" class="space-y-3" data-paid-percent="{{ $paidLockedPercent }}">
        @forelse($editableExtra as $index => $term)
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
        @empty
        <p id="terms-empty-hint" class="text-xs text-gray-500">Belum ada termin tambahan. Klik “+ Tambah Termin” bila perlu.</p>
        @endforelse
    </div>

    <div class="mt-3 text-sm text-gray-600">
        Total persentase (DP + termin):
        <span id="terms-percent-total" class="font-semibold">0</span>%
    </div>
</div>

<div class="mt-6">
    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', $project->notes ?? '') }}</textarea>
</div>
