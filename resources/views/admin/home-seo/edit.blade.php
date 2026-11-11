@extends('admin.layout')

@section('title', 'SEO Beranda - Admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">SEO Beranda</h2>
            <p class="text-gray-600 mt-1 text-sm">Atur title, description, keywords, robots, dan gambar Open Graph khusus halaman beranda.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Lihat Beranda ↗</a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.home-seo.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Meta Dasar</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="seo_home_title">Title (≤70)</label>
                <input type="text" name="seo_home_title" id="seo_home_title" maxlength="70" required
                       value="{{ old('seo_home_title', $seo['title']) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md"
                       oninput="document.getElementById('title-count').textContent=this.value.length">
                <p class="text-xs text-gray-500 mt-1"><span id="title-count">{{ strlen(old('seo_home_title', $seo['title'])) }}</span>/70 · tampil di tab browser & hasil Google</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="seo_home_description">Description (≤160)</label>
                <textarea name="seo_home_description" id="seo_home_description" rows="3" maxlength="160" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md"
                          oninput="document.getElementById('desc-count').textContent=this.value.length">{{ old('seo_home_description', $seo['description']) }}</textarea>
                <p class="text-xs text-gray-500 mt-1"><span id="desc-count">{{ strlen(old('seo_home_description', $seo['description'])) }}</span>/160 · cuplikan di Google & share preview</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="seo_home_keywords">Keywords (opsional)</label>
                <input type="text" name="seo_home_keywords" id="seo_home_keywords" maxlength="255"
                       value="{{ old('seo_home_keywords', $seo['keywords']) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md"
                       placeholder="IT, otomasi, infrastruktur, desain">
                <p class="text-xs text-gray-500 mt-1">Pisahkan dengan koma. Bukan faktor ranking utama, tetapi berguna untuk konsistensi.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="seo_home_robots">Robots</label>
                <select name="seo_home_robots" id="seo_home_robots" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @foreach(['index,follow' => 'index, follow (direkomendasikan)', 'noindex,follow' => 'noindex, follow', 'index,nofollow' => 'index, nofollow', 'noindex,nofollow' => 'noindex, nofollow'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('seo_home_robots', $seo['robots']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Gambar Open Graph</h3>
            <p class="text-sm text-gray-600">Dipakai saat beranda dibagikan ke WhatsApp / Facebook / X. Ideal 1200×630. Jika kosong, memakai logo situs.</p>
            @if($seo['og_image_url'])
            <div class="flex items-start gap-4">
                <img src="{{ $seo['og_image_url'] }}" alt="OG image" class="w-48 h-24 object-cover rounded border border-gray-200 bg-gray-50">
                <label class="flex items-center gap-2 text-sm text-gray-700 mt-1">
                    <input type="checkbox" name="remove_og_image" value="1" class="rounded border-gray-300">
                    Hapus gambar OG
                </label>
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="seo_home_og_image">Unggah gambar baru</label>
                <input type="file" name="seo_home_og_image" id="seo_home_og_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full text-sm text-gray-600">
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 text-sm text-gray-600">
            <p class="font-medium text-gray-800 mb-1">Preview singkat</p>
            <p class="text-blue-700 font-medium truncate" id="preview-title">{{ old('seo_home_title', $seo['title']) }}</p>
            <p class="text-green-700 text-xs truncate">{{ url('/') }}</p>
            <p class="text-gray-600 mt-1" id="preview-desc">{{ old('seo_home_description', $seo['description']) }}</p>
        </div>

        <div class="flex flex-wrap justify-between gap-2">
            <a href="{{ route('admin.home-theme.edit') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">← Tema Beranda</a>
            <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-semibold">Simpan SEO</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('seo_home_title')?.addEventListener('input', function () {
    document.getElementById('preview-title').textContent = this.value;
});
document.getElementById('seo_home_description')?.addEventListener('input', function () {
    document.getElementById('preview-desc').textContent = this.value;
});
</script>
@endpush
