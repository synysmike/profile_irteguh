@extends('admin.layout')

@section('title', 'Logo Situs - Admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Logo, Nama & Korp Surat</h2>
        <p class="text-gray-600 mt-1">Unggah logo, atur nama aplikasi, dan kostumisasi korp surat untuk semua dokumen cetak.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <div class="mb-6 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Nama Aplikasi</h3>
            <p class="text-sm text-gray-600 mb-3">Nama ini ditampilkan di bawah logo pada hasil cetak invoice (penjualan & pembelian).</p>
            <form method="POST" action="{{ route('admin.site-logo.update-app-name') }}">
                @csrf
                @method('PUT')
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Aplikasi</label>
                        <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $appName) }}" maxlength="120" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Contoh: Ir Teguh Solution">
                        @error('app_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-medium">
                        Simpan Nama
                    </button>
                </div>
            </form>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Logo Saat Ini</h3>
            @if($logoUrl)
            <div class="flex items-center gap-4 flex-wrap">
                <span class="site-logo-wrap site-logo-wrap--preview border border-gray-200 rounded p-2 bg-white">
                    <img src="{{ $logoUrl }}" alt="Logo Situs" class="site-logo" width="200" height="64">
                </span>
                <div>
                    <p class="text-sm text-gray-600">Logo ditampilkan di navbar landing page dan header cetak.</p>
                    <form method="POST" action="{{ route('admin.site-logo.destroy') }}" class="mt-2" onsubmit="return confirm('Hapus logo? Situs akan menampilkan nama teks saja.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Hapus Logo</button>
                    </form>
                </div>
            </div>
            @else
            <p class="text-gray-500 text-sm">Belum ada logo. Unggah logo di bawah.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.site-logo.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">Unggah Logo Baru</label>
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF, WebP. Maks. 2 MB.</p>
                @error('logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-medium">
                    Simpan Logo
                </button>
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition font-medium">
                    Batal
                </a>
            </div>
        </form>

        <div class="mt-8 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Korp Surat (Letterhead)</h3>
            <p class="text-sm text-gray-600 mb-3">
                Teks korp (alamat, telepon, email, dll.) yang tampil di header semua dokumen cetak:
                invoice penjualan, invoice grosir, dan surat tugas. Logo otomatis memakai logo situs di atas.
            </p>

            @if($logoUrl)
            <div class="mb-4 flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                <img src="{{ $logoUrl }}" alt="Logo" style="max-height:48px; width:auto;">
                <div class="text-sm text-gray-600">Logo situs akan selalu ditampilkan di samping korp surat.</div>
            </div>
            @else
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                Belum ada logo situs. Unggah logo di atas agar korp surat lebih lengkap saat dicetak.
            </div>
            @endif

            <form method="POST" action="{{ route('admin.site-logo.update-letterhead') }}" id="letterheadForm">
                @csrf
                @method('PUT')
                <div id="letterhead-editor" class="bg-white rounded-lg border border-gray-300 overflow-hidden mb-2"></div>
                <input type="hidden" name="letterhead_html" id="letterhead_html" value="{{ old('letterhead_html', $letterheadHtml) }}">
                @error('letterhead_html')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mb-4">Contoh: alamat lengkap, nomor telepon, email, website, NPWP.</p>
                <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-medium">
                    Simpan Korp Surat
                </button>
            </form>
        </div>

        <div class="mt-8 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Ukuran di Landing Page</h3>
            <p class="text-sm text-gray-600 mb-4">Atur lebar dan tinggi tampilan logo di navbar halaman depan (public). Klik <strong>Simpan Ukuran</strong> untuk menyimpan perubahan.</p>
            <form method="POST" action="{{ route('admin.site-logo.update-size') }}" id="logo-size-form">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="logo_landing_lock_ratio" value="1" id="logo_lock_ratio"
                            {{ old('logo_landing_lock_ratio', $logoLandingLockRatio) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm font-medium text-gray-700">Kunci rasio 1:1</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Aktifkan agar lebar dan tinggi selalu sama (persegi).</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="logo_landing_width" class="block text-sm font-medium text-gray-700 mb-1">Lebar (px)</label>
                        <input type="number" name="logo_landing_width" id="logo_landing_width" value="{{ old('logo_landing_width', $logoLandingWidth) }}"
                            min="40" max="400" step="1" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500"><span id="width-hint">40–400 px</span></p>
                        @error('logo_landing_width')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="logo_landing_height" class="block text-sm font-medium text-gray-700 mb-1">Tinggi (px)</label>
                        <input type="number" name="logo_landing_height" id="logo_landing_height" value="{{ old('logo_landing_height', $logoLandingHeight) }}"
                            min="20" max="120" step="1" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500"><span id="height-hint">20–120 px</span></p>
                        @error('logo_landing_height')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" id="btn-save-size" class="px-5 py-2.5 bg-green-600 text-white rounded-md hover:bg-green-700 transition font-medium shadow-sm">
                        Simpan Ukuran
                    </button>
                    <span class="text-xs text-gray-500">Perubahan baru tersimpan setelah tombol diklik.</span>
                </div>
            </form>
        </div>

        <script>
        (function() {
            var lockCheck = document.getElementById('logo_lock_ratio');
            var widthInput = document.getElementById('logo_landing_width');
            var heightInput = document.getElementById('logo_landing_height');
            var widthHint = document.getElementById('width-hint');
            var heightHint = document.getElementById('height-hint');
            var form = document.getElementById('logo-size-form');
            var btnSave = document.getElementById('btn-save-size');

            function clamp1to1(v) {
                return Math.max(40, Math.min(120, parseInt(v, 10) || 40));
            }

            function syncFromWidth() {
                if (!lockCheck.checked) return;
                var v = clamp1to1(widthInput.value);
                widthInput.value = v;
                heightInput.value = v;
                heightInput.min = 40;
                heightInput.max = 120;
            }

            function syncFromHeight() {
                if (!lockCheck.checked) return;
                var v = clamp1to1(heightInput.value);
                heightInput.value = v;
                widthInput.value = v;
                widthInput.min = 40;
                widthInput.max = 120;
            }

            function updateHints() {
                if (lockCheck.checked) {
                    widthHint.textContent = '40–120 px (rasio 1:1)';
                    heightHint.textContent = '40–120 px (rasio 1:1)';
                    widthInput.max = 120;
                    heightInput.min = 40;
                    heightInput.max = 120;
                } else {
                    widthHint.textContent = '40–400 px';
                    heightHint.textContent = '20–120 px';
                    widthInput.max = 400;
                    heightInput.min = 20;
                    heightInput.max = 120;
                }
            }

            lockCheck.addEventListener('change', function() {
                updateHints();
                if (lockCheck.checked) {
                    syncFromWidth();
                }
            });

            widthInput.addEventListener('input', syncFromWidth);
            widthInput.addEventListener('change', syncFromWidth);
            heightInput.addEventListener('input', syncFromHeight);
            heightInput.addEventListener('change', syncFromHeight);

            updateHints();
            if (lockCheck.checked) {
                syncFromWidth();
            }

            form.addEventListener('submit', function() {
                btnSave.disabled = true;
                btnSave.textContent = 'Menyimpan...';
            });
        })();
        </script>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    #letterhead-editor .ql-toolbar.ql-snow {
        border: none;
        border-bottom: 1px solid #e5e7eb;
        background: #fafafa;
        padding: 8px 10px;
    }
    #letterhead-editor .ql-container.ql-snow {
        border: none;
        min-height: 160px;
        font-size: 14px;
    }
    #letterhead-editor .ql-editor {
        min-height: 160px;
        line-height: 1.55;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hidden = document.getElementById('letterhead_html');
    const form = document.getElementById('letterheadForm');
    if (!hidden || !form || typeof Quill === 'undefined') return;

    const quill = new Quill('#letterhead-editor', {
        theme: 'snow',
        placeholder: 'Contoh: Jl. Contoh No. 1, Surabaya | Telp: 031-xxxx | Email: info@contoh.com',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    if (hidden.value) {
        quill.root.innerHTML = hidden.value;
    }

    form.addEventListener('submit', function () {
        hidden.value = quill.root.innerHTML;
        if (!hidden.value || hidden.value === '<p><br></p>') {
            hidden.value = '';
        }
    });
});
</script>
@endpush
