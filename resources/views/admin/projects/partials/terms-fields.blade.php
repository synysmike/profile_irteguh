{{-- Shared installment / terms fields. Expects $project (nullable on create). --}}
@php
    $allTerms = isset($project) ? $project->paymentTerms : collect();
    $dpTerm = $allTerms->first(fn ($t) => strcasecmp((string) $t->label, 'DP') === 0)
        ?? $allTerms->sortBy('term_number')->first();
    $extraTerms = $allTerms->when($dpTerm, fn ($c) => $c->where('id', '!=', $dpTerm->id))->values();

    $termsBaseTotal = isset($project) ? (float) $project->total : 0;
    $termsStockSubtotal = isset($project) ? (float) $project->stockSubtotal() : 0;

    $dpPaid = $dpTerm && $dpTerm->status === 'paid';
    $dpLabel = old('dp.label', $dpTerm->label ?? 'DP');
    $dpPercentage = old('dp.percentage', $dpTerm->percentage ?? 50);
    $dpDueDate = old('dp.due_date', $dpTerm && $dpTerm->due_date ? $dpTerm->due_date->format('Y-m-d') : '');
    $dpAmount = old('dp.amount');
    if ($dpAmount === null) {
        $dpAmount = isset($dpTerm) ? (float) $dpTerm->amount : null;
        if ($dpAmount === null) {
            $dpAmount = $termsBaseTotal > 0 ? round($termsBaseTotal * (float) $dpPercentage / 100) : 0;
        }
    }

    $paidExtra = $extraTerms->where('status', 'paid')->values();
    $editableExtra = old('terms', $extraTerms
        ->where('status', '!=', 'paid')
        ->values()
        ->map(fn ($t) => [
            'label' => $t->label,
            'percentage' => $t->percentage,
            'amount' => $t->amount,
            'due_date' => $t->due_date?->format('Y-m-d'),
        ])->toArray()
    );

    if (!isset($project) && !old('terms')) {
        $editableExtra = [
            ['label' => 'Pelunasan', 'percentage' => 50, 'amount' => $termsBaseTotal > 0 ? round($termsBaseTotal * 0.5) : 0, 'due_date' => ''],
        ];
    }

    $paidLockedPercent = round((float) $paidExtra->sum('percentage'), 2);
    $paidLockedAmount = round((float) $paidExtra->sum('amount'), 0);
    $paymentMethod = old('payment_method', isset($project) ? ($project->payment_method ?? 'full') : 'full');
@endphp

<div>
    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran *</label>
    <select id="payment_method" name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <option value="full" {{ $paymentMethod === 'full' ? 'selected' : '' }}>Langsung Lunas</option>
        <option value="installment" {{ $paymentMethod === 'installment' ? 'selected' : '' }}>Beberapa Termin</option>
    </select>
</div>

<div id="installment-section" class="mt-6 {{ $paymentMethod === 'installment' ? '' : 'hidden' }}">
    <h4 class="text-sm font-semibold text-gray-800 mb-1">Jadwal Termin Pembayaran</h4>
    <p class="text-xs text-gray-500 mb-4">
        Isi persentase atau nominal — salah satu akan menghitung yang lain otomatis dari total project
        (<span id="terms-base-total-label">Rp {{ number_format($termsBaseTotal, 0, ',', '.') }}</span>).
        Total persentase harus 100%.
    </p>

    <input type="hidden" id="terms-base-total" value="{{ $termsBaseTotal }}" data-stock="{{ $termsStockSubtotal }}">
    <input type="hidden" id="paid-terms-percent" value="{{ $paidLockedPercent }}">
    <input type="hidden" id="paid-terms-amount" value="{{ $paidLockedAmount }}">

    <div id="dp-section" class="mb-4 border border-purple-200 rounded-lg p-4 bg-purple-50/60">
        <div class="flex items-center justify-between mb-3">
            <h5 class="text-sm font-semibold text-purple-900">Down Payment (DP)</h5>
            @if($dpPaid)
            <span class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded">Sudah lunas — tetap bisa diedit</span>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
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
                <label class="block text-xs text-gray-600 mb-1">Nominal (Rp)</label>
                <input type="number" id="dp_amount" value="{{ (int) round((float) $dpAmount) }}"
                       min="0" step="1"
                       class="term-amount-dp w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-white"
                       inputmode="numeric">
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
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-center border border-green-200 rounded-lg p-3 bg-green-50">
            <div class="text-sm text-gray-800 font-medium">{{ $paid->label }}</div>
            <div class="text-sm text-gray-700">{{ number_format((float) $paid->percentage, 2, ',', '.') }}%</div>
            <div class="text-sm text-gray-700">Rp {{ number_format((float) $paid->amount, 0, ',', '.') }}</div>
            <div class="text-sm text-gray-500">{{ $paid->due_date?->format('d/m/Y') ?: '—' }}</div>
            <div class="text-xs font-semibold text-green-700">Lunas</div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="flex items-center justify-between mb-2">
        <h5 class="text-sm font-semibold text-gray-800">Termin Tambahan</h5>
        <button type="button" id="btn-add-term" class="text-sm text-purple-600 hover:text-purple-800 font-medium">+ Tambah Termin</button>
    </div>
    <div id="terms-container" class="space-y-3" data-paid-percent="{{ $paidLockedPercent }}" data-paid-amount="{{ $paidLockedAmount }}">
        @forelse($editableExtra as $index => $term)
        @php
            $rowPct = (float) ($term['percentage'] ?? 0);
            $rowAmount = $term['amount'] ?? ($termsBaseTotal > 0 ? round($termsBaseTotal * $rowPct / 100) : 0);
        @endphp
        <div class="term-row grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end border border-gray-200 rounded-lg p-3 bg-gray-50">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Label Termin</label>
                <input type="text" name="terms[{{ $index }}][label]" value="{{ $term['label'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Persentase (%)</label>
                <input type="number" name="terms[{{ $index }}][percentage]" value="{{ $term['percentage'] ?? '' }}" min="0" max="100" step="0.01" class="term-percentage w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Nominal (Rp)</label>
                <input type="number" value="{{ (int) round((float) $rowAmount) }}" min="0" step="1" class="term-amount w-full px-3 py-2 border border-gray-300 rounded-md text-sm" inputmode="numeric">
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

    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600">
        <div>
            Total persentase:
            <span id="terms-percent-total" class="font-semibold">0</span>%
        </div>
        <div>
            Total nominal:
            <span id="terms-amount-total" class="font-semibold">Rp 0</span>
        </div>
    </div>
</div>
