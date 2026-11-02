@php
    $selectedTypeId = old('customer_type_id', ($customerTypes ?? collect())->first()?->id);
    $selectedType = ($customerTypes ?? collect())->firstWhere('id', (int) $selectedTypeId);
    $legacyType = $selectedType?->resolveLegacyKey() ?? 'individual';
@endphp

<div class="space-y-4">
    <p class="text-sm text-gray-500">Isi data singkat. Detail lain bisa dilengkapi nanti di menu Customer.</p>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
               placeholder="Nama pelanggan / kontak">
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon / WhatsApp *</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
               placeholder="Boleh paste dari WA, mis. +62 813-xxxx"
               inputmode="tel" autocomplete="tel">
        <p class="mt-1 text-xs text-gray-500">Diperlukan untuk invoice & kirim WA. Otomatis dinormalisasi saat simpan.</p>
    </div>

    <div>
        <label for="customer_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe *</label>
        <select id="customer_type" name="customer_type_id" required
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @foreach(($customerTypes ?? collect()) as $type)
            <option value="{{ $type->id }}"
                    data-legacy-key="{{ $type->resolveLegacyKey() }}"
                    {{ (string) $selectedTypeId === (string) $type->id ? 'selected' : '' }}>
                {{ $type->category?->name ? $type->category->name . ' — ' : '' }}{{ $type->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div id="company_name_field" style="display: {{ $legacyType === 'company' ? 'block' : 'none' }};">
        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Perusahaan</label>
        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
               placeholder="Opsional">
    </div>

    <input type="hidden" name="is_active" value="1">
</div>

<script>
window.initQuickCustomerForm = function () {
    const customerTypeSelect = document.getElementById('customer_type');
    const companyField = document.getElementById('company_name_field');
    const companyInput = document.getElementById('company_name');
    if (!customerTypeSelect || !companyField) return;

    const toggleCompanyField = function () {
        const selectedOption = customerTypeSelect.options[customerTypeSelect.selectedIndex];
        const legacyKey = selectedOption ? selectedOption.dataset.legacyKey : 'individual';
        if (legacyKey === 'company') {
            companyField.style.display = 'block';
        } else {
            companyField.style.display = 'none';
            if (companyInput) companyInput.value = '';
        }
    };

    customerTypeSelect.removeEventListener('change', toggleCompanyField);
    customerTypeSelect.addEventListener('change', toggleCompanyField);
    toggleCompanyField();
};

window.initQuickCustomerForm();
</script>
