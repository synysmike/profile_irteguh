<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ulasan Faktur {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            background: linear-gradient(160deg, #f8fafc 0%, #ede9fe 50%, #f1f5f9 100%);
            color: #1f2937;
        }
        .wrap { max-width: 560px; margin: 0 auto; }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
            padding: 28px 24px;
            border: 1px solid #e5e7eb;
            margin-bottom: 16px;
        }
        h1 { margin: 0 0 6px; font-size: 22px; }
        .meta { font-size: 13px; color: #6b7280; line-height: 1.5; }
        .alert { border-radius: 10px; padding: 10px 12px; font-size: 13px; margin-bottom: 14px; }
        .alert-ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 0 0 6px; color: #374151; }
        input[type="text"], input[type="file"], textarea, select {
            width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 10px;
            font-size: 15px; margin-bottom: 14px;
        }
        input[type="file"] { padding: 10px; font-size: 13px; }
        textarea { min-height: 120px; resize: vertical; }
        .hint { font-size: 12px; color: #6b7280; margin: -8px 0 14px; }
        .stars { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
        .stars label.star {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 999px;
            font-size: 13px; cursor: pointer; background: #f9fafb;
        }
        .stars input { margin: 0; }
        button, .btn {
            width: 100%; padding: 12px 16px; background: #7c3aed; color: #fff; border: none;
            border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer;
        }
        button:hover, .btn:hover { background: #6d28d9; }
        .existing {
            background: #f9fafb; border-radius: 12px; padding: 14px; margin-bottom: 16px;
            border: 1px solid #e5e7eb;
        }
        .rating-display { color: #f59e0b; font-size: 18px; letter-spacing: 1px; }
        .media-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px; }
        .media-item {
            position: relative; border: 1px solid #e5e7eb; border-radius: 10px;
            overflow: hidden; background: #111827;
            aspect-ratio: 4/3;
        }
        .media-item img, .media-item video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .media-item label.check {
            position: absolute; left: 6px; bottom: 6px; background: rgba(0,0,0,.65); color: #fff;
            font-size: 11px; padding: 4px 8px; border-radius: 6px; display: inline-flex; gap: 4px; align-items: center;
        }
        .links { text-align: center; font-size: 13px; color: #6b7280; }
        .links a { color: #7c3aed; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Ulasan Pembelian</h1>
        <div class="meta">
            <div><strong>{{ $sale->invoice_number }}</strong></div>
            <div>Tanggal: {{ $sale->sale_date?->format('d/m/Y') ?? '—' }}</div>
            <div>Pelanggan: {{ $customer?->name }}</div>
            <div>Total: Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="card">
        @if(session('success'))
        <div class="alert alert-ok">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-err">
            <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        @if($existing)
        <div class="existing">
            <div class="meta" style="margin-bottom:6px;">Ulasan Anda saat ini</div>
            <div class="rating-display">{{ $existing->stars() }} ({{ $existing->rating }}/5)</div>
            @if($existing->comment)
            <p style="margin:8px 0 0;font-size:14px;color:#374151;white-space:pre-wrap;">{{ $existing->comment }}</p>
            @endif
            @if($existing->media->count())
            <div class="media-grid">
                @foreach($existing->media as $media)
                <div class="media-item">
                    @if($media->isVideo())
                    <video src="{{ $media->url() }}" controls></video>
                    @else
                    <img src="{{ $media->url() }}" alt="{{ $media->original_name }}">
                    @endif
                </div>
                @endforeach
            </div>
            @endif
            <div class="meta" style="margin-top:8px;">Dikirim {{ $existing->submitted_at?->format('d/m/Y H:i') }} · Anda bisa memperbarui di bawah.</div>
        </div>
        @endif

        <form method="POST" action="{{ route('sale.review.store', $sale) }}" enctype="multipart/form-data">
            @csrf
            <label>Rating *</label>
            <div class="stars">
                @for($i = 5; $i >= 1; $i--)
                <label class="star">
                    <input type="radio" name="rating" value="{{ $i }}"
                           {{ (string) old('rating', $existing?->rating) === (string) $i ? 'checked' : '' }}
                           {{ old('rating', $existing?->rating) ? '' : ($i === 5 ? 'checked' : '') }}
                           required>
                    {{ $i }} ★
                </label>
                @endfor
            </div>

            <label for="reviewer_name">Nama tampil</label>
            <input type="text" id="reviewer_name" name="reviewer_name"
                   value="{{ old('reviewer_name', $existing?->reviewer_name ?: $customer?->name) }}"
                   maxlength="120" placeholder="Nama Anda">

            <label for="comment">Ulasan</label>
            <textarea id="comment" name="comment" maxlength="2000"
                      placeholder="Ceritakan pengalaman Anda terhadap pembelian / layanan kami…">{{ old('comment', $existing?->comment) }}</textarea>

            @if($existing && $existing->media->count())
            <label>Lampiran saat ini</label>
            <div class="media-grid" style="margin-bottom:14px;">
                @foreach($existing->media as $media)
                <div class="media-item">
                    @if($media->isVideo())
                    <video src="{{ $media->url() }}" muted></video>
                    @else
                    <img src="{{ $media->url() }}" alt="{{ $media->original_name }}">
                    @endif
                    <label class="check">
                        <input type="checkbox" name="remove_media[]" value="{{ $media->id }}"> Hapus
                    </label>
                </div>
                @endforeach
            </div>
            @endif

            @php $slots = 8 - ($existing?->media->count() ?? 0); @endphp
            @if($slots > 0)
            <label for="media">Lampirkan foto/video</label>
            <input type="file" id="media" name="media[]" multiple
                   accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime">
            <p class="hint">JPG/PNG/WEBP atau MP4/WEBM/MOV · max 50MB/file · sisa slot: {{ $slots }} (total max 8)</p>
            @else
            <p class="hint">Batas 8 lampiran tercapai. Centang “Hapus” pada file lama untuk menambah yang baru.</p>
            @endif

            <button type="submit">{{ $existing ? 'Perbarui Ulasan' : 'Kirim Ulasan' }}</button>
        </form>
    </div>

    <div class="links">
        <a href="{{ $sale->publicInvoiceUrl() }}">Lihat Invoice</a>
        ·
        <a href="{{ route('home') }}">Beranda</a>
    </div>
</div>
</body>
</html>
