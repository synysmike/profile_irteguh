<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Modal Investor {{ $injection->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111; margin: 0; padding: 24px; background: #f3f4f6; }
        .sheet { max-width: 800px; margin: 0 auto; background: #fff; padding: 28px; border: 1px solid #e5e7eb; }
        .letterhead-wrap { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 2px solid #333; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        .meta { font-size: 13px; color: #4b5563; line-height: 1.5; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 20px 0; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .box h3 { margin: 0 0 8px; font-size: 13px; color: #6b7280; text-transform: uppercase; }
        .amount { font-size: 28px; font-weight: 700; margin: 8px 0; }
        .terms { white-space: pre-wrap; font-size: 13px; line-height: 1.55; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .ok { background: #dcfce7; color: #166534; }
        .wait { background: #fef3c7; color: #92400e; }
        .pay { background: #dbeafe; color: #1e40af; }
        .bad { background: #fee2e2; color: #991b1b; }
        .actions { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { border: 0; border-radius: 6px; padding: 10px 16px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-ok { background: #16a34a; color: #fff; }
        .btn-bad { background: #dc2626; color: #fff; }
        .btn-pay { background: #2563eb; color: #fff; }
        .alert { padding: 10px 12px; border-radius: 6px; margin-bottom: 14px; font-size: 14px; }
        .alert-ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .pay-form label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; }
        .pay-form input { width: 100%; box-sizing: border-box; padding: 9px 10px; border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 12px; }
        .bank-line { font-size: 14px; line-height: 1.6; }
        @media print { body { background: #fff; padding: 0; } .actions, .no-print { display: none !important; } .sheet { border: 0; } }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="sheet">
    @if(session('success'))
    <div class="alert alert-ok no-print">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-err no-print">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="letterhead-wrap">
        @include('components.letterhead')
    </div>

    <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;">
        <div>
            <div class="meta">Dokumen Setoran Modal Investor</div>
            <h1>{{ $injection->invoice_number }}</h1>
            <div class="meta">
                Tanggal: {{ $injection->injection_date?->format('d/m/Y') }}<br>
                Status:
                @if($injection->status === 'approved')
                <span class="status ok">Lunas / Modal Masuk</span>
                @elseif($injection->status === 'awaiting_payment')
                <span class="status pay">Menunggu Pembayaran</span>
                @elseif($injection->status === 'rejected')
                <span class="status bad">Ditolak</span>
                @else
                <span class="status wait">Menunggu Persetujuan</span>
                @endif
            </div>
        </div>
        <div class="amount">Rp {{ number_format($injection->amount, 0, ',', '.') }}</div>
    </div>

    <div class="grid">
        <div class="box">
            <h3>Investor</h3>
            <div><strong>{{ $injection->investor?->name }}</strong></div>
            <div class="meta">{{ $injection->investor?->phone }}</div>
            <div class="meta">{{ $injection->investor?->email }}</div>
            <div class="meta">{{ $injection->investor?->address }}</div>
        </div>
        <div class="box">
            <h3>Paket Benefit (Snapshot)</h3>
            <div><strong>{{ $injection->benefit_name }}</strong></div>
            <div class="meta">Share laba: {{ number_format($injection->profit_share_percent, 2, ',', '.') }}%</div>
            <div class="meta">Share rugi: {{ number_format($injection->loss_share_percent, 2, ',', '.') }}%</div>
        </div>
    </div>

    <h3 style="font-size:15px;margin:0 0 8px;">Syarat & Ketentuan</h3>
    <div class="terms">{{ $injection->terms_content }}</div>

    @if($injection->status === 'pending_approval')
    <div class="actions no-print">
        <form method="POST" action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('invoice.investor.approve', now()->addDays(30), ['injection' => $injection->id]) }}" onsubmit="return confirm('Menyetujui syarat & ketentuan setoran ini?')">
            @csrf
            <button type="submit" class="btn btn-ok">Saya Setuju</button>
        </form>
        <form method="POST" action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('invoice.investor.reject', now()->addDays(30), ['injection' => $injection->id]) }}" onsubmit="return confirm('Tolak dokumen ini?')">
            @csrf
            <input type="text" name="rejection_note" placeholder="Alasan penolakan (opsional)" style="padding:8px;border:1px solid #d1d5db;border-radius:6px;margin-right:8px;">
            <button type="submit" class="btn btn-bad">Tolak</button>
        </form>
    </div>

    @elseif($injection->status === 'awaiting_payment')
    <div class="no-print" style="margin-top:20px;">
        <div class="box" style="margin-bottom:14px;background:#eff6ff;border-color:#bfdbfe;">
            <h3>Instruksi Pembayaran</h3>
            <div class="bank-line">Transfer tepat <strong>Rp {{ number_format($injection->amount, 0, ',', '.') }}</strong> ke rekening berikut:</div>
            @if($bankName || $bankAccount || $bankHolder)
            <div class="bank-line" style="margin-top:8px;">
                @if($bankName)<div>Bank: <strong>{{ $bankName }}</strong></div>@endif
                @if($bankAccount)<div>No. Rekening: <strong>{{ $bankAccount }}</strong></div>@endif
                @if($bankHolder)<div>Atas Nama: <strong>{{ $bankHolder }}</strong></div>@endif
            </div>
            @else
            <div class="meta" style="margin-top:8px;">Rekening penerima belum diatur admin. Hubungi admin untuk nomor rekening.</div>
            @endif
            <div class="meta" style="margin-top:8px;">Berita transfer disarankan: {{ $injection->invoice_number }}</div>
        </div>

        <div class="box pay-form">
            <h3>Konfirmasi & Unggah Bukti Transfer</h3>
            <p class="meta" style="margin:0 0 12px;">Setelah transfer, unggah bukti. Dana akan dicatat sebagai kas masuk dari akun investor.</p>
            <form method="POST" enctype="multipart/form-data" action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('invoice.investor.pay', now()->addDays(30), ['injection' => $injection->id]) }}">
                @csrf
                <div class="grid" style="margin:0;">
                    <div>
                        <label for="payment_date">Tanggal Transfer *</label>
                        <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>
                    <div>
                        <label for="payment_reference">No. Referensi / Berita Transfer</label>
                        <input type="text" id="payment_reference" name="payment_reference" value="{{ old('payment_reference', $injection->invoice_number) }}" maxlength="120">
                    </div>
                    <div>
                        <label for="payment_sender_name">Nama Pengirim</label>
                        <input type="text" id="payment_sender_name" name="payment_sender_name" value="{{ old('payment_sender_name', $injection->investor?->name) }}" maxlength="120">
                    </div>
                    <div>
                        <label for="payment_proof">Bukti Transfer (JPG/PNG/PDF, max 5MB) *</label>
                        <input type="file" id="payment_proof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf,.webp,image/*,application/pdf" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-pay">Kirim Bukti Transfer</button>
            </form>
        </div>
    </div>

    @elseif($injection->status === 'approved')
    <div style="margin-top:16px;" class="box">
        <h3>Pembayaran Diterima</h3>
        <div class="meta">
            Disetujui: {{ $injection->approved_at?->format('d/m/Y H:i') }}<br>
            Dibayar: {{ $injection->paid_at?->format('d/m/Y H:i') ?? $injection->approved_at?->format('d/m/Y H:i') }}<br>
            @if($injection->payment_date)Tanggal transfer: {{ $injection->payment_date->format('d/m/Y') }}<br>@endif
            @if($injection->payment_reference)Referensi: {{ $injection->payment_reference }}<br>@endif
            @if($injection->payment_sender_name)Pengirim: {{ $injection->payment_sender_name }}<br>@endif
            Kas masuk dan jurnal Modal Investor telah dicatat.
        </div>
    </div>

    @elseif($injection->status === 'rejected')
    <p class="meta" style="margin-top:16px;">Ditolak pada {{ $injection->rejected_at?->format('d/m/Y H:i') }}. {{ $injection->rejection_note }}</p>
    @endif
</div>
</body>
</html>
