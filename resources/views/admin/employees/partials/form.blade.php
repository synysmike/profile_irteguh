<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
        <input type="text" id="name" name="name" value="{{ old('name', isset($employee) && $employee ? $employee->name : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="nip" class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
        <input type="text" id="nip" name="nip" value="{{ old('nip', isset($employee) && $employee ? $employee->nip : '') }}" maxlength="50"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Posisi / Jabatan</label>
        <input type="text" id="position" name="position" value="{{ old('position', isset($employee) && $employee ? $employee->position : '') }}"
               placeholder="Contoh: Staff IT"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Departemen</label>
        <input type="text" id="department" name="department" value="{{ old('department', isset($employee) && $employee ? $employee->department : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="basic_salary" class="block text-sm font-medium text-gray-700 mb-2">Gaji Pokok (Rp)</label>
        <input type="number" id="basic_salary" name="basic_salary" value="{{ old('basic_salary', isset($employee) && $employee ? $employee->basic_salary : 0) }}" min="0" step="1"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="npwp" class="block text-sm font-medium text-gray-700 mb-2">NPWP</label>
        <input type="text" id="npwp" name="npwp" value="{{ old('npwp', isset($employee) && $employee ? $employee->npwp : '') }}" maxlength="20"
               placeholder="15 atau 16 digit"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Bank</label>
        <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', isset($employee) && $employee ? $employee->bank_name : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="bank_account" class="block text-sm font-medium text-gray-700 mb-2">Nomor Rekening</label>
        <input type="text" id="bank_account" name="bank_account" value="{{ old('bank_account', isset($employee) && $employee ? $employee->bank_account : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', isset($employee) && $employee ? $employee->email : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', isset($employee) && $employee ? $employee->phone : '') }}"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
        <input type="number" id="order" name="order" value="{{ old('order', isset($employee) && $employee ? $employee->order : 0) }}" min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <div>
        <label class="flex items-center mt-8">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($employee) && $employee ? $employee->is_active : true) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Aktif</span>
        </label>
    </div>
</div>
