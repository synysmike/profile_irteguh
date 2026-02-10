<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Text -->
    <div class="md:col-span-2">
        <label for="text" class="block text-sm font-medium text-gray-700 mb-2">Teks *</label>
        <input type="text" id="text" name="text" value="{{ old('text', isset($heroText) && $heroText ? $heroText->text : '') }}" required
               placeholder="Contoh: Solusi IT & Kreatif Terintegrasi"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Order -->
    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
        <input type="number" id="order" name="order" value="{{ old('order', isset($heroText) && $heroText ? $heroText->order : 0) }}" min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Is Active -->
    <div>
        <label class="flex items-center mt-8">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($heroText) && $heroText ? $heroText->is_active : true) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Aktif</span>
        </label>
    </div>
</div>
