<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Penjualan {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; max-width: 800px; margin: 0 auto; padding: 24px; }
        .no-print { margin-bottom: 16px; }
        .btn-print { display: inline-block; padding: 10px 20px; background: #7c3aed; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn-print:hover { background: #6d28d9; }
        .letterhead-wrap { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 2px solid #333; }
        .letterhead-content p { margin: 0 0 2px 0; text-align: center; }
        .letterhead-content p:last-child { margin-bottom: 0; }
        .letterhead-content { text-align: center; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
        .invoice-type { font-size: 13px; color: #6b7280; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 24px; margin: 0 0 8px 0; color: #111; }
        .invoice-meta { font-size: 13px; color: #666; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        .info-box { padding: 12px; background: #f9fafb; border-radius: 8px; }
        .info-box h3 { margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; color: #6b7280; }
        .info-box p { margin: 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f3f4f6; font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 15px; background: #f9fafb; }
        .notes { padding: 12px; background: #f9fafb; border-radius: 8px; margin-top: 24px; font-size: 13px; color: #666; }
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; margin-top: 48px; }
        .signature-box { text-align: center; }
        .signature-title { font-size: 12px; text-transform: uppercase; color: #6b7280; margin-bottom: 64px; }
        .signature-name { font-weight: 600; border-top: 1px solid #111; padding-top: 8px; display: inline-block; min-width: 220px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
        @media print {
            body { padding: 0 0 56px 0; }
            .no-print { display: none !important; }
            .letterhead-wrap { border-bottom-color: #000; }
            table { page-break-inside: avoid; }
            .footer {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                margin-top: 0;
                padding: 8px 24px 0;
                background: #fff;
                border-top: 1px solid #e5e7eb;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print();">🖨 Cetak Invoice</button>
        @if($sale->project_id && $sale->project)
        <a href="{{ $sale->project->publicReviewUrl() }}"
           style="margin-left: 8px; display: inline-block; padding: 10px 16px; background: #059669; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none;">
            ⭐ Beri Ulasan Project
        </a>
        @endif
        @if(empty($isPublic) && auth()->check())
        <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" style="margin-left: 8px; color: #6b7280; font-size: 14px;">← Kembali ke Daftar Penjualan</a>
        @endif
    </div>

    <div class="letterhead-wrap">
        @include('components.letterhead')
    </div>

    <div class="header">
        <div class="invoice-type">Penjualan / Faktur Keluaran</div>
        <div class="invoice-title">
            <h1>INVOICE PENJUALAN</h1>
            <div class="invoice-meta">
                <strong>No. Faktur:</strong> {{ $sale->invoice_number }}<br>
                <strong>Tanggal:</strong> {{ $sale->sale_date?->locale('id')->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Pelanggan (Customer)</h3>
            <p><strong>{{ $sale->customer?->name ?? '—' }}</strong></p>
            @if($sale->customer?->company_name)
            <p>{{ $sale->customer->company_name }}</p>
            @endif
            @if($sale->customer?->address)
            <p>{{ $sale->customer->address }}</p>
            @endif
            @if($sale->customer?->city)
            <p>{{ $sale->customer->city }}</p>
            @endif
            @if($sale->customer?->phone)
            <p>Telp: {{ $sale->customer->phone }}</p>
            @endif
            @if($sale->customer?->email)
            <p>Email: {{ $sale->customer->email }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Deskripsi Item</th>
                <th class="text-right" style="width: 8%;">Qty</th>
                <th class="text-right" style="width: 14%;">Harga Satuan</th>
                <th class="text-right" style="width: 14%;">Diskon</th>
                <th class="text-right" style="width: 14%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->saleItems as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->description }}</strong>
                    @if($item->notes)
                    <br><span style="font-size: 12px; color: #666;">{{ $item->notes }}</span>
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #b91c1c;">
                    @if((float) ($item->discount_amount ?? 0) > 0)
                    - Rp {{ number_format($item->discount_amount, 0, ',', '.') }}
                    @if($item->discount_name)
                    <br><span style="font-size: 11px; color: #666;">{{ $item->discount_name }}</span>
                    @endif
                    @else
                    —
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #999;">Tidak ada item transaksi</td>
            </tr>
            @endforelse
            @php
                $invoiceDiscount = (float) ($sale->discount_amount ?? $sale->saleItems->sum('discount_amount'));
                $grossSubtotal = (float) $sale->subtotal + $invoiceDiscount;
            @endphp
            @if($invoiceDiscount > 0)
            <tr style="border-top: 2px solid #333;">
                <td colspan="5" class="text-right">Subtotal kotor</td>
                <td class="text-right">Rp {{ number_format($grossSubtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right" style="color: #b91c1c;">
                    Diskon
                    @if($sale->discount_name)
                        ({{ $sale->discount_name }})
                    @endif
                </td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($invoiceDiscount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr @if($invoiceDiscount <= 0) style="border-top: 2px solid #333;" @endif>
                <td colspan="5" class="text-right" style="font-weight: bold;">Subtotal (DPP)</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($sale->ppn_amount > 0)
                <tr>
                    <td colspan="5" class="text-right">
                        {{ $sale->tax_name ?: 'Pajak' }}
                        @if($sale->tax_rate)
                            ({{ number_format($sale->tax_rate, 2, ',', '.') }}%)
                        @endif
                        {{ $sale->tax_calculation_type === 'deduction' ? '- Potongan' : '+ Tambahan' }}
                    </td>
                    <td class="text-right">{{ $sale->tax_calculation_type === 'deduction' ? '-' : '' }}Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="5" class="text-right" style="font-size: 16px;">Total Pembayaran</td>
                <td class="text-right" style="font-size: 16px;">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if($sale->notes)
    <div class="notes">
        <strong>Catatan:</strong> {{ $sale->notes }}
    </div>
    @endif

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-title">Penerima</div>
            <div class="signature-name">{{ $sale->customer?->name ?? '-' }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Penerbit</div>
            <div class="signature-name">{{ \App\Models\Setting::appName() }}</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dicetak dari sistem Keuangan. {{ $sale->invoice_number }} — {{ now()->locale('id')->translatedFormat('d F Y H:i') }}
    </div>

    <script>
        window.onload = function() {
            if (window.location.search.indexOf('print=1') !== -1) {
                window.print();
            }
        };
    </script>
</body>
</html>
