<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Name -->
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Tempat Grosir *</label>
        <input type="text" id="name" name="name" value="{{ old('name', isset($supplier) && $supplier ? $supplier->name : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Contact Person -->
    <div>
        <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">Nama Kontak</label>
        <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person', isset($supplier) && $supplier ? $supplier->contact_person : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Phone -->
    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', isset($supplier) && $supplier ? $supplier->phone : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Email -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', isset($supplier) && $supplier ? $supplier->email : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- City -->
    <div>
        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
        <input type="text" id="city" name="city" value="{{ old('city', isset($supplier) && $supplier ? $supplier->city : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Is Active -->
    <div>
        <label class="flex items-center mt-8">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($supplier) && $supplier ? $supplier->is_active : true) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Aktif</span>
        </label>
    </div>
</div>

<!-- Address -->
<div>
    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
    <textarea id="address" name="address" rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('address', isset($supplier) && $supplier ? $supplier->address : '') }}</textarea>
</div>

<!-- Notes -->
<div>
    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
    <textarea id="notes" name="notes" rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', isset($supplier) && $supplier ? $supplier->notes : '') }}</textarea>
</div>
