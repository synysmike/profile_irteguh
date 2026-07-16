@php
    $letter = $letter ?? null;
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
    if (!container || !btnAdd) return;

    const genderOptions = @json(\App\Models\AssignmentLetter::genderLabels());

    function reindexAssignees() {
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
})();
</script>
