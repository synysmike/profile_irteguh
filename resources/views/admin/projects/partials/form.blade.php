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
        <label for="discount_id" class="block text-sm font-medium text-gray-700 mb-2">Diskon Jasa</label>
        <select id="discount_id" name="discount_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">Tanpa Diskon</option>
            @foreach(($discounts ?? collect()) as $discount)
            <option value="{{ $discount->id }}"
                    data-type="{{ $discount->type }}"
                    data-value="{{ $discount->value }}"
                    {{ (string) old('discount_id', $project->discount_id ?? '') === (string) $discount->id ? 'selected' : '' }}>
                {{ $discount->label() }}
            </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Diterapkan pada nilai jasa saja. Diskon stok diatur per alokasi.</p>
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
        @php
            $hasLetterJasa = isset($project) && $project->relationLoaded('assignmentLetters')
                ? $project->assignmentLetters->flatMap->jasaItems->isNotEmpty()
                : (isset($project) && $project->assignmentLetters()->whereHas('jasaItems')->exists());
        @endphp
        <input type="number" id="subtotal" name="subtotal" min="0" step="1" required
               value="{{ old('subtotal', isset($project) ? ($project->base_subtotal ?? $project->subtotal) : 0) }}"
               @if($hasLetterJasa) readonly @endif
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 {{ $hasLetterJasa ? 'bg-gray-50' : '' }}">
        @if($hasLetterJasa)
        <p class="mt-1 text-xs text-indigo-600">Nilai ini diisi otomatis dari item jasa surat tugas. Sesuaikan qty di halaman detail project.</p>
        @else
        <p class="mt-1 text-xs text-gray-500">Belum termasuk alokasi stok. Bisa juga diisi lewat item jasa pada surat tugas (lalu qty di detail project).</p>
        @endif
    </div>

    <div>
        <label for="discount_amount_display" class="block text-sm font-medium text-gray-700 mb-2">Nominal Diskon Jasa Rp</label>
        <input type="text" id="discount_amount_display" readonly
               class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50">
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
    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan Internal</label>
    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', $project->notes ?? '') }}</textarea>
</div>

<div class="mt-8 pt-6 border-t border-gray-200">
    <h4 class="text-sm font-semibold text-gray-800 mb-1">Portofolio Publik</h4>
    <p class="text-xs text-gray-500 mb-4">Konten ini tampil di website publik. Data keuangan/customer tidak ikut diekspos.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_portfolio_public" value="1" class="rounded border-gray-300"
                   {{ old('is_portfolio_public', $project->is_portfolio_public ?? false) ? 'checked' : '' }}>
            Tampilkan di Portofolio
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_portfolio_featured" value="1" class="rounded border-gray-300"
                   {{ old('is_portfolio_featured', $project->is_portfolio_featured ?? false) ? 'checked' : '' }}>
            Proyek Unggulan (beranda)
        </label>

        <div>
            <label for="portfolio_category" class="block text-sm font-medium text-gray-700 mb-2">Kategori Portofolio</label>
            <select id="portfolio_category" name="portfolio_category" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <option value="">— Pilih —</option>
                @foreach(\App\Models\Project::portfolioCategories() as $cat)
                <option value="{{ $cat }}" {{ old('portfolio_category', $project->portfolio_category ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="portfolio_year" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
            <input type="number" id="portfolio_year" name="portfolio_year" min="2000" max="2100"
                   value="{{ old('portfolio_year', $project->portfolio_year ?? ($project->start_date?->year ?? date('Y'))) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md">
        </div>
        <div>
            <label for="portfolio_sort_order" class="block text-sm font-medium text-gray-700 mb-2">Urutan Tampil</label>
            <input type="number" id="portfolio_sort_order" name="portfolio_sort_order" min="0"
                   value="{{ old('portfolio_sort_order', $project->portfolio_sort_order ?? 0) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md">
        </div>
        <div>
            <label for="portfolio_client_context" class="block text-sm font-medium text-gray-700 mb-2">Konteks Klien (publik)</label>
            <input type="text" id="portfolio_client_context" name="portfolio_client_context"
                   value="{{ old('portfolio_client_context', $project->portfolio_client_context ?? '') }}"
                   placeholder="Mis. Perusahaan manufaktur Jawa Timur"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md">
        </div>
        <div class="md:col-span-2">
            <label for="portfolio_tags" class="block text-sm font-medium text-gray-700 mb-2">Tags (pisahkan koma)</label>
            <input type="text" id="portfolio_tags" name="portfolio_tags"
                   value="{{ old('portfolio_tags', isset($project) && $project->portfolio_tags ? implode(', ', $project->portfolio_tags) : '') }}"
                   placeholder="network, automation, branding"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md">
        </div>
        <div class="md:col-span-2">
            <label for="portfolio_excerpt" class="block text-sm font-medium text-gray-700 mb-2">Ringkasan Kartu</label>
            <textarea id="portfolio_excerpt" name="portfolio_excerpt" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-md">{{ old('portfolio_excerpt', $project->portfolio_excerpt ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="portfolio_challenge" class="block text-sm font-medium text-gray-700 mb-2">Tantangan</label>
            <textarea id="portfolio_challenge" name="portfolio_challenge" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md">{{ old('portfolio_challenge', $project->portfolio_challenge ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="portfolio_solution" class="block text-sm font-medium text-gray-700 mb-2">Solusi</label>
            <textarea id="portfolio_solution" name="portfolio_solution" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md">{{ old('portfolio_solution', $project->portfolio_solution ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="portfolio_outcome" class="block text-sm font-medium text-gray-700 mb-2">Hasil</label>
            <textarea id="portfolio_outcome" name="portfolio_outcome" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md">{{ old('portfolio_outcome', $project->portfolio_outcome ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="portfolio_cover" class="block text-sm font-medium text-gray-700 mb-2">Cover Portofolio</label>
            @if(!empty($project?->portfolio_cover_path))
            <div class="mb-2 flex items-center gap-3">
                <img src="{{ $project->portfolioCoverUrl() }}" alt="Cover" class="h-16 w-24 object-cover rounded border">
                <label class="text-xs text-red-600 flex items-center gap-1">
                    <input type="checkbox" name="remove_portfolio_cover" value="1"> Hapus cover
                </label>
            </div>
            @endif
            <input type="file" id="portfolio_cover" name="portfolio_cover" accept="image/*"
                   class="w-full text-sm text-gray-600">
        </div>
    </div>
</div>
