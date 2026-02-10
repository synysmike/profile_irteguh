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
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .header { border-bottom-color: #000; }
            table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print();">🖨 Cetak Invoice</button>
        <a href="{{ route('admin.keuangan.transaksi.penjualan') }}" style="margin-left: 8px; color: #6b7280; font-size: 14px;">← Kembali ke Daftar Penjualan</a>
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
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;">Penjualan / Faktur Keluaran</p>
        </div>
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
        <div class="info-box">
            <h3>Diterbitkan oleh</h3>
            <p>{{ auth()->user()?->name ?? \App\Models\Setting::appName() }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Deskripsi Item</th>
                <th class="text-right" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 15%;">Harga Satuan</th>
                <th class="text-right" style="width: 15%;">Subtotal</th>
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
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 20px; color: #999;">Tidak ada item transaksi</td>
            </tr>
            @endforelse
            <tr style="border-top: 2px solid #333;">
                <td colspan="4" class="text-right" style="font-weight: bold;">Subtotal (DPP)</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">PPN (11%)</td>
                <td class="text-right">Rp {{ number_format($sale->ppn_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right" style="font-size: 16px;">Total Pembayaran</td>
                <td class="text-right" style="font-size: 16px;">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if($sale->notes)
    <div class="notes">
        <strong>Catatan:</strong> {{ $sale->notes }}
    </div>
    @endif

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
