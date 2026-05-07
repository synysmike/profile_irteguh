@php
    $types = \App\Models\ChartOfAccount::types();
@endphp
<div class="space-y-4">
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Akun *</label>
        <input type="text" name="code" id="code" value="{{ old('code', $account->code ?? '') }}" required maxlength="20"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500 @error('code') border-red-500 @enderror"
            placeholder="Contoh: 1001">
        @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Akun *</label>
        <input type="text" name="name" id="name" value="{{ old('name', $account->name ?? '') }}" required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500 @error('name') border-red-500 @enderror"
            placeholder="Contoh: Kas Kecil">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
        <select name="type" id="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500 @error('type') border-red-500 @enderror">
            @foreach($types as $key => $label)
            <option value="{{ $key }}" {{ old('type', $account->type ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Induk (opsional)</label>
        <select name="parent_id" id="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
            <option value="">— Tanpa induk —</option>
            @foreach($parents ?? [] as $p)
            @if(!isset($account) || $p->id != $account->id)
            <option value="{{ $p->id }}" {{ old('parent_id', $account->parent_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->code }} - {{ $p->name }}</option>
            @endif
            @endforeach
        </select>
        @error('parent_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
        <input type="number" name="order" id="order" value="{{ old('order', $account->order ?? 0) }}" min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
        @error('order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', isset($account) ? $account->is_active : true) ? 'checked' : '' }}
            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
        <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
    </div>
</div>
