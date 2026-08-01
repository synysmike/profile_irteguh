@php
    $letter = $letter ?? null;
    $jasas = $jasas ?? collect();
    $assigneeRows = old('assignees', isset($letter)
        ? $letter->assignees->map(fn ($a) => [
            'name' => $a->name,
            'gender' => $a->gender,
            'ktp' => $a->ktp,
            'phone' => $a->phone,
        ])->toArray()
        : [['name' => '', 'gender' => 'L', 'ktp' => '', 'phone' => '']]
    );
    if (empty($assigneeRows)) {
        $assigneeRows = [['name' => '', 'gender' => 'L', 'ktp' => '', 'phone' => '']];
    }

    $jasaRows = old('jasa_items', isset($letter)
        ? $letter->jasaItems->map(fn ($item) => [
            'jasa_id' => $item->jasa_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
        ])->toArray()
        : []
    );

    $jasaCatalog = $jasas->map(function ($j) {
        return [
            'id' => $j->id,
            'name' => $j->name,
            'unit_price' => (float) $j->unit_price,
            'label' => $j->label(),
        ];
    })->values();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="letter_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Surat *</label>
            <input type="date" id="letter_date" name="letter_date" required
                   value="{{ old('letter_date', isset($letter) && $letter->letter_date ? $letter->letter_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @error('letter_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
            <input type="text" id="subject" name="subject"
                   value="{{ old('subject', $letter->subject ?? '') }}"
                   placeholder="Contoh: Pelaksanaan pekerjaan di lokasi klien"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @error('subject')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="task_description" class="block text-sm font-medium text-gray-700 mb-2">Uraian Tugas</label>
        <textarea id="task_description" name="task_description" rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                  placeholder="Jelaskan tugas yang harus dikerjakan">{{ old('task_description', $letter->task_description ?? '') }}</textarea>
        @error('task_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="border border-purple-100 rounded-lg p-4 bg-purple-50/50">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <div>
                <h4 class="text-sm font-semibold text-purple-900">Yang Bertugas *</h4>
                <p class="text-xs text-gray-500 mt-0.5">Tambahkan satu atau lebih orang yang ditugaskan.</p>
            </div>
            <button type="button" id="btn-add-assignee" class="text-sm text-purple-700 hover:text-purple-900 font-medium">
                + Tambah Orang
            </button>
        </div>
        @error('assignees')<p class="text-red-600 text-sm mb-3">{{ $message }}</p>@enderror

        <div id="assignees-container" class="space-y-3">
            @foreach($assigneeRows as $index => $row)
            <div class="assignee-row border border-purple-100 rounded-lg p-3 bg-white">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide assignee-label">Orang {{ $index + 1 }}</span>
                    <button type="button" class="btn-remove-assignee text-xs text-red-600 hover:text-red-800">Hapus</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="assignees[{{ $index }}][name]" value="{{ $row['name'] ?? '' }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Jenis Kelamin *</label>
                        <select name="assignees[{{ $index }}][gender]" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            @foreach(\App\Models\AssignmentLetter::genderLabels() as $value => $label)
                            <option value="{{ $value }}" {{ ($row['gender'] ?? 'L') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Nomor HP *</label>
                        <input type="text" name="assignees[{{ $index }}][phone]" value="{{ $row['phone'] ?? '' }}" required
                               placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-600 mb-1">Nomor KTP *</label>
                        <input type="text" name="assignees[{{ $index }}][ktp]" value="{{ $row['ktp'] ?? '' }}" required
                               placeholder="16 digit NIK" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="border border-indigo-100 rounded-lg p-4 bg-indigo-50/40">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <div>
                <h4 class="text-sm font-semibold text-indigo-900">Item Jasa</h4>
                <p class="text-xs text-gray-500 mt-0.5">Pilih jasa dari master. Qty bisa disesuaikan lagi di detail project.</p>
            </div>
            <button type="button" id="btn-add-jasa" class="text-sm text-indigo-700 hover:text-indigo-900 font-medium">
                + Tambah Jasa
            </button>
        </div>
        @error('jasa_items')<p class="text-red-600 text-sm mb-3">{{ $message }}</p>@enderror
        @if($jasas->isEmpty())
        <p class="text-sm text-amber-700 bg-amber-50 border border-amber-100 rounded-md px-3 py-2 mb-3">
            Belum ada master jasa aktif.
            <a href="{{ route('admin.keuangan.jasa.index') }}" class="underline font-medium" target="_blank">Tambah di Master Jasa</a>
        </p>
        @endif

        <div id="jasa-items-container" class="space-y-3">
            @foreach($jasaRows as $index => $row)
            <div class="jasa-item-row border border-indigo-100 rounded-lg p-3 bg-white">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide jasa-item-label">Jasa {{ $index + 1 }}</span>
                    <button type="button" class="btn-remove-jasa text-xs text-red-600 hover:text-red-800">Hapus</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                    <div class="md:col-span-3">
                        <label class="block text-xs text-gray-600 mb-1">Jasa *</label>
                        <select name="jasa_items[{{ $index }}][jasa_id]" class="jasa-select w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                            <option value="">-- Pilih jasa --</option>
                            @foreach($jasas as $jasa)
                            <option value="{{ $jasa->id }}"
                                    data-name="{{ $jasa->name }}"
                                    data-price="{{ $jasa->unit_price }}"
                                    {{ (string) ($row['jasa_id'] ?? '') === (string) $jasa->id ? 'selected' : '' }}>
                                {{ $jasa->label() }}
                            </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="jasa_items[{{ $index }}][description]" class="jasa-description" value="{{ $row['description'] ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Qty *</label>
                        <input type="number" name="jasa_items[{{ $index }}][quantity]" min="1" step="1" required
                               value="{{ $row['quantity'] ?? 1 }}"
                               class="jasa-qty w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Harga (Rp) *</label>
                        <input type="number" name="jasa_items[{{ $index }}][unit_price]" min="0" step="0.01" required
                               value="{{ $row['unit_price'] ?? 0 }}"
                               class="jasa-price w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Subtotal</label>
                        <input type="text" readonly class="jasa-subtotal w-full px-3 py-2 border border-gray-200 rounded-md text-sm bg-gray-50" value="">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-3 flex justify-end text-sm font-semibold text-gray-800">
            Total jasa: <span id="jasa-items-total" class="ml-2">Rp 0</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai Tugas</label>
            <input type="date" id="start_date" name="start_date"
                   value="{{ old('start_date', isset($letter) && $letter->start_date ? $letter->start_date->format('Y-m-d') : '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @error('start_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai Tugas</label>
            <input type="date" id="end_date" name="end_date"
                   value="{{ old('end_date', isset($letter) && $letter->end_date ? $letter->end_date->format('Y-m-d') : '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @error('end_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
        <textarea id="notes" name="notes" rows="2"
                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', $letter->notes ?? '') }}</textarea>
        @error('notes')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<script>
(function () {
    const container = document.getElementById('assignees-container');
    const btnAdd = document.getElementById('btn-add-assignee');
    const genderOptions = @json(\App\Models\AssignmentLetter::genderLabels());
    const jasaCatalog = @json($jasaCatalog);

    function formatRp(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
    }

    function reindexAssignees() {
        if (!container) return;
        container.querySelectorAll('.assignee-row').forEach(function (row, index) {
            const label = row.querySelector('.assignee-label');
            if (label) label.textContent = 'Orang ' + (index + 1);
            row.querySelectorAll('input, select').forEach(function (input) {
                const name = input.getAttribute('name');
                if (!name) return;
                input.setAttribute('name', name.replace(/assignees\[\d+\]/, 'assignees[' + index + ']'));
            });
        });
    }

    function createAssigneeRow(index) {
        const genderHtml = Object.keys(genderOptions).map(function (value) {
            return '<option value="' + value + '"' + (value === 'L' ? ' selected' : '') + '>' + genderOptions[value] + '</option>';
        }).join('');

        const row = document.createElement('div');
        row.className = 'assignee-row border border-purple-100 rounded-lg p-3 bg-white';
        row.innerHTML =
            '<div class="flex items-center justify-between mb-3">' +
                '<span class="text-xs font-semibold text-gray-500 uppercase tracking-wide assignee-label">Orang ' + (index + 1) + '</span>' +
                '<button type="button" class="btn-remove-assignee text-xs text-red-600 hover:text-red-800">Hapus</button>' +
            '</div>' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' +
                '<div class="md:col-span-2">' +
                    '<label class="block text-xs text-gray-600 mb-1">Nama *</label>' +
                    '<input type="text" name="assignees[' + index + '][name]" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs text-gray-600 mb-1">Jenis Kelamin *</label>' +
                    '<select name="assignees[' + index + '][gender]" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' + genderHtml + '</select>' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs text-gray-600 mb-1">Nomor HP *</label>' +
                    '<input type="text" name="assignees[' + index + '][phone]" required placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' +
                '</div>' +
                '<div class="md:col-span-2">' +
                    '<label class="block text-xs text-gray-600 mb-1">Nomor KTP *</label>' +
                    '<input type="text" name="assignees[' + index + '][ktp]" required placeholder="16 digit NIK" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' +
                '</div>' +
            '</div>';
        return row;
    }

    if (container && btnAdd) {
        btnAdd.addEventListener('click', function (e) {
            e.preventDefault();
            const index = container.querySelectorAll('.assignee-row').length;
            container.appendChild(createAssigneeRow(index));
        });

        container.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-assignee');
            if (!btn) return;
            e.preventDefault();
            const rows = container.querySelectorAll('.assignee-row');
            if (rows.length <= 1) {
                alert('Minimal 1 orang yang bertugas harus ada.');
                return;
            }
            const row = btn.closest('.assignee-row');
            if (row) row.remove();
            reindexAssignees();
        });
    }

    const jasaContainer = document.getElementById('jasa-items-container');
    const btnAddJasa = document.getElementById('btn-add-jasa');
    const jasaTotalEl = document.getElementById('jasa-items-total');

    function jasaOptionsHtml(selectedId) {
        return '<option value="">-- Pilih jasa --</option>' + jasaCatalog.map(function (j) {
            return '<option value="' + j.id + '" data-name="' + String(j.name).replace(/"/g, '&quot;') + '" data-price="' + j.unit_price + '"' +
                (String(selectedId) === String(j.id) ? ' selected' : '') + '>' + j.label + '</option>';
        }).join('');
    }

    function updateJasaRow(row) {
        const qty = row.querySelector('.jasa-qty');
        const price = row.querySelector('.jasa-price');
        const subtotal = row.querySelector('.jasa-subtotal');
        if (!qty || !price || !subtotal) return;
        const q = parseFloat(qty.value) || 0;
        const p = parseFloat(price.value) || 0;
        subtotal.value = formatRp(q * p);
    }

    function updateJasaTotals() {
        let total = 0;
        if (jasaContainer) {
            jasaContainer.querySelectorAll('.jasa-item-row').forEach(function (row) {
                updateJasaRow(row);
                const qty = parseFloat(row.querySelector('.jasa-qty')?.value) || 0;
                const price = parseFloat(row.querySelector('.jasa-price')?.value) || 0;
                total += qty * price;
            });
        }
        if (jasaTotalEl) jasaTotalEl.textContent = formatRp(total);
    }

    function reindexJasaItems() {
        if (!jasaContainer) return;
        jasaContainer.querySelectorAll('.jasa-item-row').forEach(function (row, index) {
            const label = row.querySelector('.jasa-item-label');
            if (label) label.textContent = 'Jasa ' + (index + 1);
            row.querySelectorAll('input, select').forEach(function (input) {
                const name = input.getAttribute('name');
                if (!name) return;
                input.setAttribute('name', name.replace(/jasa_items\[\d+\]/, 'jasa_items[' + index + ']'));
            });
        });
    }

    function createJasaRow(index) {
        const row = document.createElement('div');
        row.className = 'jasa-item-row border border-indigo-100 rounded-lg p-3 bg-white';
        row.innerHTML =
            '<div class="flex items-center justify-between mb-3">' +
                '<span class="text-xs font-semibold text-gray-500 uppercase tracking-wide jasa-item-label">Jasa ' + (index + 1) + '</span>' +
                '<button type="button" class="btn-remove-jasa text-xs text-red-600 hover:text-red-800">Hapus</button>' +
            '</div>' +
            '<div class="grid grid-cols-1 md:grid-cols-6 gap-3">' +
                '<div class="md:col-span-3">' +
                    '<label class="block text-xs text-gray-600 mb-1">Jasa *</label>' +
                    '<select name="jasa_items[' + index + '][jasa_id]" class="jasa-select w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>' + jasaOptionsHtml('') + '</select>' +
                    '<input type="hidden" name="jasa_items[' + index + '][description]" class="jasa-description" value="">' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs text-gray-600 mb-1">Qty *</label>' +
                    '<input type="number" name="jasa_items[' + index + '][quantity]" min="1" step="1" value="1" required class="jasa-qty w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs text-gray-600 mb-1">Harga (Rp) *</label>' +
                    '<input type="number" name="jasa_items[' + index + '][unit_price]" min="0" step="0.01" value="0" required class="jasa-price w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs text-gray-600 mb-1">Subtotal</label>' +
                    '<input type="text" readonly class="jasa-subtotal w-full px-3 py-2 border border-gray-200 rounded-md text-sm bg-gray-50" value="">' +
                '</div>' +
            '</div>';
        return row;
    }

    if (jasaContainer && btnAddJasa) {
        btnAddJasa.addEventListener('click', function (e) {
            e.preventDefault();
            if (!jasaCatalog.length) {
                alert('Belum ada master jasa aktif. Tambahkan di Master Jasa terlebih dahulu.');
                return;
            }
            const index = jasaContainer.querySelectorAll('.jasa-item-row').length;
            jasaContainer.appendChild(createJasaRow(index));
            updateJasaTotals();
        });

        jasaContainer.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-jasa');
            if (!btn) return;
            e.preventDefault();
            const row = btn.closest('.jasa-item-row');
            if (row) row.remove();
            reindexJasaItems();
            updateJasaTotals();
        });

        jasaContainer.addEventListener('change', function (e) {
            const select = e.target.closest('.jasa-select');
            if (select) {
                const row = select.closest('.jasa-item-row');
                const opt = select.options[select.selectedIndex];
                const price = row?.querySelector('.jasa-price');
                const desc = row?.querySelector('.jasa-description');
                if (opt && opt.value) {
                    if (price) price.value = opt.dataset.price || 0;
                    if (desc) desc.value = opt.dataset.name || '';
                }
            }
            updateJasaTotals();
        });

        jasaContainer.addEventListener('input', function (e) {
            if (e.target.matches('.jasa-qty, .jasa-price')) {
                updateJasaTotals();
            }
        });

        updateJasaTotals();
    }
})();
</script>
