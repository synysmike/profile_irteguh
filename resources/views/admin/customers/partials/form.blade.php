<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Name -->
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
        <input type="text" id="name" name="name" value="{{ old('name', isset($customer) && $customer ? $customer->name : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Customer Type -->
    <div>
        <label for="customer_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Customer *</label>
        <select id="customer_type" name="customer_type" required
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="individual" {{ old('customer_type', isset($customer) && $customer ? $customer->customer_type : 'individual') == 'individual' ? 'selected' : '' }}>Individu</option>
            <option value="company" {{ old('customer_type', isset($customer) && $customer ? $customer->customer_type : 'individual') == 'company' ? 'selected' : '' }}>Perusahaan</option>
        </select>
    </div>

    <!-- Company Name -->
    <div id="company_name_field" style="display: {{ (isset($customer) && $customer && $customer->customer_type == 'company') || old('customer_type') == 'company' ? 'block' : 'none' }};">
        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Perusahaan</label>
        <input type="text" id="company_name" name="company_name" value="{{ old('company_name', isset($customer) && $customer ? $customer->company_name : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Email -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', isset($customer) && $customer ? $customer->email : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Phone -->
    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', isset($customer) && $customer ? $customer->phone : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- City -->
    <div>
        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
        <input type="text" id="city" name="city" value="{{ old('city', isset($customer) && $customer ? $customer->city : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Is Active -->
    <div>
        <label class="flex items-center mt-8">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($customer) && $customer ? $customer->is_active : true) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Aktif</span>
        </label>
    </div>
</div>

<!-- Address -->
<div>
    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
    <textarea id="address" name="address" rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('address', isset($customer) && $customer ? $customer->address : '') }}</textarea>
</div>

<!-- Notes -->
<div>
    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
    <textarea id="notes" name="notes" rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', isset($customer) && $customer ? $customer->notes : '') }}</textarea>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerTypeSelect = document.getElementById('customer_type');
    const companyField = document.getElementById('company_name_field');
    
    if (customerTypeSelect && companyField) {
        customerTypeSelect.addEventListener('change', function() {
            if (this.value === 'company') {
                companyField.style.display = 'block';
            } else {
                companyField.style.display = 'none';
                document.getElementById('company_name').value = '';
            }
        });
    }
});
</script>
