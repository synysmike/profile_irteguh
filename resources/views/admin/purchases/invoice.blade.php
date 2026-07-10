<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Grosir {{ $purchase->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; max-width: 800px; margin: 0 auto; padding: 24px; }
        .no-print { margin-bottom: 16px; }
        .btn-print { display: inline-block; padding: 10px 20px; background: #7c3aed; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn-print:hover { background: #6d28d9; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; padding-bottom: 20px; border-bottom: 2px solid #333; }
        .company { flex: 1; }
        .company-brand { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .company-logo { max-height: 48px; width: auto; display: block; }
        .company-name { font-size: 18px; font-weight: bold; color: #111; margin: 0; }
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
            .header { border-bottom-color: #000; }
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
        <a href="{{ route('admin.keuangan.transaksi.pembelian') }}" style="margin-left: 8px; color: #6b7280; font-size: 14px;">← Kembali ke Daftar Grosir</a>
    </div>

    <div class="header">
        <div class="company">
            <div class="company-brand">
                @php $logoUrl = \App\Models\Setting::logoPath(); @endphp
                @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::appName() }}" class="company-logo" style="max-height: 48px; width: auto;">
                @endif
                <div class="company-name">{{ \App\Models\Setting::appName() }}</div>
            </div>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;">Grosir / Faktur Masuk</p>
        </div>
        <div class="invoice-title">
            <h1>INVOICE GROSIR</h1>
            <div class="invoice-meta">
                <strong>No. Faktur:</strong> {{ $purchase->invoice_number }}<br>
                <strong>Tanggal:</strong> {{ $purchase->purchase_date?->locale('id')->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Pemasok (Supplier)</h3>
            <p><strong>{{ $purchase->supplier?->name ?? '—' }}</strong></p>
            @if($purchase->supplier?->address)
            <p>{{ $purchase->supplier->address }}</p>
            @endif
            @if($purchase->supplier?->city)
            <p>{{ $purchase->supplier->city }}</p>
            @endif
            @if($purchase->supplier?->phone)
            <p>Telp: {{ $purchase->supplier->phone }}</p>
            @endif
            @if($purchase->supplier?->email)
            <p>Email: {{ $purchase->supplier->email }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Barang</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga Beli</th>
                <th class="text-right">Subtotal (DPP)</th>
                <th class="text-right">Pajak</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $purchase->displayDescription() }}</td>
                <td class="text-right">{{ number_format($purchase->quantity ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($purchase->unit_price ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($purchase->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">{{ $purchase->tax_calculation_type === 'deduction' ? '-' : '' }}Rp {{ number_format($purchase->ppn_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
            </tr>
            @if($purchase->tax_name)
            <tr>
                <td colspan="6" class="text-xs text-gray-600">
                    Pajak diterapkan: {{ $purchase->tax_name }}
                    @if($purchase->tax_rate)
                        ({{ number_format($purchase->tax_rate, 2, ',', '.') }}%)
                    @endif
                    {{ $purchase->tax_calculation_type === 'deduction' ? '- Potongan' : '+ Tambahan' }}
                </td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="5" class="text-right">Total Pembayaran</td>
                <td class="text-right">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if($purchase->notes)
    <div class="notes">
        <strong>Catatan:</strong> {{ $purchase->notes }}
    </div>
    @endif

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-title">Penerima</div>
            <div class="signature-name">{{ $purchase->supplier?->name ?? '-' }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Penerbit</div>
            <div class="signature-name">{{ \App\Models\Setting::appName() }}</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini dicetak dari sistem Keuangan. {{ $purchase->invoice_number }} — {{ now()->locale('id')->translatedFormat('d F Y H:i') }}
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
