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
        <label for="subtotal" class="block text-sm font-medium text-gray-700 mb-2">Nilai Jasa / DPP Dasar Rp *</label>
        <input type="number" id="subtotal" name="subtotal" min="0" step="1" required
               value="{{ old('subtotal', isset($project) ? ($project->base_subtotal ?? $project->subtotal) : 0) }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <p class="mt-1 text-xs text-gray-500">Belum termasuk alokasi stok. Stok dilampirkan di halaman detail project dan otomatis diakumulasi ke total.</p>
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

<div class="mt-6">
    @include('admin.projects.partials.terms-fields')
</div>

<div class="mt-6">
    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', $project->notes ?? '') }}</textarea>
</div>
