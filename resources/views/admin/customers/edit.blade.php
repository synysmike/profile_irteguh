@extends('admin.layout')

@section('title', 'Edit Customer - Admin')

@section('content')
        <div class="mb-8">
            <a href="{{ route('admin.customers.index') }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali</a>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Edit Customer</h2>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 max-w-3xl">
            <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $customer->name) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Customer Type -->
                    <div>
                        <label for="customer_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Customer *</label>
                        <select id="customer_type" name="customer_type_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @foreach($customerTypes as $type)
                                <option value="{{ $type->id }}" data-legacy-key="{{ $type->resolveLegacyKey() }}" {{ (string) old('customer_type_id', $customer->customer_type_id) === (string) $type->id ? 'selected' : '' }}>
                                    {{ $type->category?->name ? $type->category->name . ' — ' : '' }}{{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_type_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Company Name -->
                    <div id="company_name_field" style="display: {{ old('customer_type', $customer->customer_type) == 'company' ? 'block' : 'none' }};">
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Perusahaan</label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $customer->company_name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $customer->email) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $customer->city) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Is Active -->
                    <div>
                        <label class="flex items-center mt-8">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <textarea id="address" name="address" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('address', $customer->address) }}</textarea>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('notes', $customer->notes) }}</textarea>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                        Update
                    </button>
                    <a href="{{ route('admin.customers.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        <script>
            document.getElementById('customer_type').addEventListener('change', function() {
                const companyField = document.getElementById('company_name_field');
                const selectedOption = this.options[this.selectedIndex];
                const legacyKey = selectedOption ? selectedOption.dataset.legacyKey : 'individual';
                if (legacyKey === 'company') {
                    companyField.style.display = 'block';
                } else {
                    companyField.style.display = 'none';
                    document.getElementById('company_name').value = '';
                }
            });

            document.getElementById('customer_type').dispatchEvent(new Event('change'));
        </script>
@endsection
