<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Title -->
    <div class="md:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
        <input type="text" id="title" name="title" value="{{ old('title', isset($caseStudy) && $caseStudy ? $caseStudy->title : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Category -->
    <div>
        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
        <select id="category" name="category" required
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">Pilih Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ old('category', isset($caseStudy) && $caseStudy ? $caseStudy->category : '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>

    <!-- Year -->
    <div>
        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun *</label>
        <input type="number" id="year" name="year" value="{{ old('year', isset($caseStudy) && $caseStudy ? $caseStudy->year : date('Y')) }}" min="2000" max="{{ date('Y') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Order -->
    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
        <input type="number" id="order" name="order" value="{{ old('order', isset($caseStudy) && $caseStudy ? $caseStudy->order : 0) }}" min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Featured -->
    <div>
        <label class="flex items-center mt-8">
            <input type="checkbox" name="featured" value="1" {{ old('featured', isset($caseStudy) && $caseStudy ? $caseStudy->featured : false) ? 'checked' : '' }}
                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
            <span class="ml-2 text-sm text-gray-700">Tandai sebagai Unggulan</span>
        </label>
    </div>
</div>

<!-- Client Context -->
<div>
    <label for="client_context" class="block text-sm font-medium text-gray-700 mb-2">Klien/Konteks</label>
    <textarea id="client_context" name="client_context" rows="2"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('client_context', isset($caseStudy) && $caseStudy ? $caseStudy->client_context : '') }}</textarea>
</div>

<!-- Challenge -->
<div>
    <label for="challenge" class="block text-sm font-medium text-gray-700 mb-2">Tantangan *</label>
    <textarea id="challenge" name="challenge" rows="4" required
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('challenge', isset($caseStudy) && $caseStudy ? $caseStudy->challenge : '') }}</textarea>
</div>

<!-- Solution -->
<div>
    <label for="solution" class="block text-sm font-medium text-gray-700 mb-2">Solusi *</label>
    <textarea id="solution" name="solution" rows="4" required
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('solution', isset($caseStudy) && $caseStudy ? $caseStudy->solution : '') }}</textarea>
</div>

<!-- Outcome -->
<div>
    <label for="outcome" class="block text-sm font-medium text-gray-700 mb-2">Hasil *</label>
    <textarea id="outcome" name="outcome" rows="4" required
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('outcome', isset($caseStudy) && $caseStudy ? $caseStudy->outcome : '') }}</textarea>
</div>

<!-- Excerpt -->
<div>
    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Ringkasan</label>
    <textarea id="excerpt" name="excerpt" rows="2"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('excerpt', isset($caseStudy) && $caseStudy ? $caseStudy->excerpt : '') }}</textarea>
</div>

<!-- Tags -->
<div>
    <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags (pisahkan dengan koma)</label>
    <input type="text" id="tags" name="tags" value="{{ old('tags', isset($caseStudy) && $caseStudy && $caseStudy->tags ? implode(', ', $caseStudy->tags) : '') }}"
           placeholder="Contoh: Docker, Nginx, HTTPS"
           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
</div>

<!-- Visuals -->
<div>
    <label for="visuals" class="block text-sm font-medium text-gray-700 mb-2">URL Gambar (pisahkan dengan koma)</label>
    <input type="text" id="visuals" name="visuals" value="{{ old('visuals', isset($caseStudy) && $caseStudy && $caseStudy->visuals ? implode(', ', $caseStudy->visuals) : '') }}"
           placeholder="https://example.com/image1.jpg, https://example.com/image2.jpg"
           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
</div>
