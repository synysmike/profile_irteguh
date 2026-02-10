<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Title -->
    <div class="md:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Layanan *</label>
        <input type="text" id="title" name="title" value="{{ old('title', isset($service) && $service ? $service->title : '') }}" required
               placeholder="Contoh: Infrastruktur IT"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Icon (emoji or short text) -->
    <div>
        <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">Icon (emoji)</label>
        <input type="text" id="icon" name="icon" value="{{ old('icon', isset($service) && $service ? $service->icon : '') }}" maxlength="50"
               placeholder="Contoh: 🖥️ atau ⚙️"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        <p class="mt-1 text-xs text-gray-500">Satu emoji atau karakter singkat</p>
    </div>

    <!-- Order -->
    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
        <input type="number" id="order" name="order" value="{{ old('order', isset($service) && $service ? $service->order : 0) }}" min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Features (bullet points, one per line) -->
    <div class="md:col-span-2">
        <label for="features" class="block text-sm font-medium text-gray-700 mb-2">Daftar poin (satu baris = satu poin)</label>
        <textarea id="features" name="features" rows="8" placeholder="Deployment & containerisasi Docker&#10;Manajemen & perawatan server&#10;Setup infrastruktur cloud"
                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('features', isset($service) && $service && $service->features ? implode("\n", $service->features) : '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Tulis setiap poin di baris baru. Bullet (•) akan ditambahkan otomatis di tampilan.</p>
    </div>

    <!-- Is Active -->
    <div class="md:col-span-2">
        <label class="flex items-center">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($service) && $service ? $service->is_active : true) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Tampilkan di halaman Layanan</span>
        </label>
    </div>
</div>
