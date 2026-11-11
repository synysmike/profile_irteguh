@extends('admin.layout')

@section('title', 'Tema Beranda - Admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tema Beranda</h2>
            <p class="text-gray-600 mt-1 text-sm">Atur copy hero, warna aksen, overlay slide, dan section yang tampil di beranda publik.</p>
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

    <form method="POST" action="{{ route('admin.home-theme.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Copy Hero</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="home_kicker">Kicker (eyebrow)</label>
                <input type="text" name="home_kicker" id="home_kicker" value="{{ old('home_kicker', $theme['kicker']) }}" maxlength="120" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="home_tagline">Tagline</label>
                <input type="text" name="home_tagline" id="home_tagline" value="{{ old('home_tagline', $theme['tagline']) }}" maxlength="160" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="home_description">Deskripsi singkat</label>
                <textarea name="home_description" id="home_description" rows="3" maxlength="500" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('home_description', $theme['description']) }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_cta_primary_label">CTA utama — label</label>
                    <input type="text" name="home_cta_primary_label" id="home_cta_primary_label" value="{{ old('home_cta_primary_label', $theme['cta_primary_label']) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_cta_primary_url">CTA utama — URL</label>
                    <input type="text" name="home_cta_primary_url" id="home_cta_primary_url" value="{{ old('home_cta_primary_url', $theme['cta_primary_url']) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="/portfolio">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_cta_secondary_label">CTA sekunder — label</label>
                    <input type="text" name="home_cta_secondary_label" id="home_cta_secondary_label" value="{{ old('home_cta_secondary_label', $theme['cta_secondary_label']) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_cta_secondary_url">CTA sekunder — URL</label>
                    <input type="text" name="home_cta_secondary_url" id="home_cta_secondary_url" value="{{ old('home_cta_secondary_url', $theme['cta_secondary_url']) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="/contact">
                </div>
            </div>
            <p class="text-xs text-gray-500">Teks typing hero tetap dikelola di menu <a href="{{ route('admin.hero-texts.index') }}" class="text-purple-600 hover:underline">Teks Hero</a>. Background slide di <a href="{{ route('admin.slides.index') }}" class="text-purple-600 hover:underline">Slide</a>.</p>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Warna & Overlay</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="theme_accent">Aksen (tombol)</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="theme_accent_picker" value="{{ old('theme_accent', $theme['accent']) }}" class="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                               oninput="document.getElementById('theme_accent').value=this.value">
                        <input type="text" name="theme_accent" id="theme_accent" value="{{ old('theme_accent', $theme['accent']) }}" required
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md font-mono text-sm"
                               oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('theme_accent_picker').value=this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="theme_accent_hover">Aksen hover</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="theme_accent_hover_picker" value="{{ old('theme_accent_hover', $theme['accent_hover']) }}" class="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                               oninput="document.getElementById('theme_accent_hover').value=this.value">
                        <input type="text" name="theme_accent_hover" id="theme_accent_hover" value="{{ old('theme_accent_hover', $theme['accent_hover']) }}" required
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md font-mono text-sm"
                               oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('theme_accent_hover_picker').value=this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="theme_gradient_from">Gradient fallback — from</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="theme_gradient_from_picker" value="{{ old('theme_gradient_from', $theme['gradient_from']) }}" class="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                               oninput="document.getElementById('theme_gradient_from').value=this.value">
                        <input type="text" name="theme_gradient_from" id="theme_gradient_from" value="{{ old('theme_gradient_from', $theme['gradient_from']) }}" required
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md font-mono text-sm"
                               oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('theme_gradient_from_picker').value=this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="theme_gradient_via">Gradient fallback — via</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="theme_gradient_via_picker" value="{{ old('theme_gradient_via', $theme['gradient_via']) }}" class="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                               oninput="document.getElementById('theme_gradient_via').value=this.value">
                        <input type="text" name="theme_gradient_via" id="theme_gradient_via" value="{{ old('theme_gradient_via', $theme['gradient_via']) }}" required
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md font-mono text-sm"
                               oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('theme_gradient_via_picker').value=this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="theme_gradient_to">Gradient fallback — to</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" id="theme_gradient_to_picker" value="{{ old('theme_gradient_to', $theme['gradient_to']) }}" class="h-10 w-12 border border-gray-300 rounded cursor-pointer"
                               oninput="document.getElementById('theme_gradient_to').value=this.value">
                        <input type="text" name="theme_gradient_to" id="theme_gradient_to" value="{{ old('theme_gradient_to', $theme['gradient_to']) }}" required
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md font-mono text-sm"
                               oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('theme_gradient_to_picker').value=this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_overlay_opacity">Overlay slide (0.15–0.75)</label>
                    <input type="number" step="0.05" min="0.15" max="0.75" name="home_overlay_opacity" id="home_overlay_opacity"
                           value="{{ old('home_overlay_opacity', $theme['overlay_opacity']) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <p class="text-xs text-gray-500 mt-1">Semakin tinggi semakin gelap overlay di atas foto slide.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Section Beranda</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="home_show_categories" value="1" class="rounded border-gray-300"
                           {{ old('home_show_categories', $theme['show_categories'] ? '1' : '0') == '1' ? 'checked' : '' }}>
                    Tampilkan kategori portofolio
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="home_show_featured" value="1" class="rounded border-gray-300"
                           {{ old('home_show_featured', $theme['show_featured'] ? '1' : '0') == '1' ? 'checked' : '' }}>
                    Tampilkan proyek unggulan
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="home_show_services" value="1" class="rounded border-gray-300"
                           {{ old('home_show_services', $theme['show_services'] ? '1' : '0') == '1' ? 'checked' : '' }}>
                    Tampilkan preview layanan
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="home_show_team" value="1" class="rounded border-gray-300"
                           {{ old('home_show_team', $theme['show_team'] ? '1' : '0') == '1' ? 'checked' : '' }}>
                    Tampilkan tim
                </label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_categories_heading">Judul kategori</label>
                    <input type="text" name="home_categories_heading" id="home_categories_heading" value="{{ old('home_categories_heading', $theme['categories_heading']) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_featured_heading">Judul proyek unggulan</label>
                    <input type="text" name="home_featured_heading" id="home_featured_heading" value="{{ old('home_featured_heading', $theme['featured_heading']) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_services_heading">Judul preview layanan</label>
                    <input type="text" name="home_services_heading" id="home_services_heading" value="{{ old('home_services_heading', $theme['services_heading']) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="home_team_heading">Judul tim</label>
                    <input type="text" name="home_team_heading" id="home_team_heading" value="{{ old('home_team_heading', $theme['team_heading']) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
            <p class="text-xs text-gray-500">Kartu preview layanan diambil otomatis dari master <a href="{{ route('admin.services.index') }}" class="text-purple-600 hover:underline">Layanan</a> yang aktif.</p>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Footer</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="home_footer_tagline">Tagline footer</label>
                <input type="text" name="home_footer_tagline" id="home_footer_tagline" value="{{ old('home_footer_tagline', $theme['footer_tagline']) }}" maxlength="255" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <p class="text-xs text-gray-500">Alamat footer memakai Setting kontak (<a href="{{ route('admin.contact.index') }}" class="text-purple-600 hover:underline">Kontak</a>).</p>
        </div>

        <div class="flex flex-wrap justify-between gap-2">
            <a href="{{ route('admin.home-seo.edit') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">SEO Beranda →</a>
            <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-semibold">Simpan Tema</button>
        </div>
    </form>
</div>
@endsection
