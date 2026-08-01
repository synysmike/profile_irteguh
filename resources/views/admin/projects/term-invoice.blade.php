<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Penagihan {{ $payload['nota'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; max-width: 800px; margin: 0 auto; padding: 24px; }
        .no-print { margin-bottom: 16px; }
        .btn-print { display: inline-block; padding: 10px 20px; background: #7c3aed; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; text-decoration: none; }
        .btn-print:hover { background: #6d28d9; }
        .btn-wa { display: inline-block; padding: 10px 20px; background: #16a34a; color: #fff; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; margin-left: 8px; }
        .btn-wa:hover { background: #15803d; }
        .letterhead-wrap { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 2px solid #333; }
        .letterhead-content p { margin: 0 0 2px 0; text-align: center; }
        .letterhead-content p:last-child { margin-bottom: 0; }
        .letterhead-content { text-align: center; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; gap: 16px; }
        .invoice-type { font-size: 13px; color: #6b7280; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 22px; margin: 0 0 8px 0; color: #111; }
        .invoice-meta { font-size: 13px; color: #666; }
        .badge-unpaid { display: inline-block; margin-top: 8px; padding: 4px 10px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 600; }
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
        @media (max-width: 640px) {
            .header, .info-grid, .signature-grid { grid-template-columns: 1fr; display: grid; }
            .invoice-title { text-align: left; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print();">Cetak / Simpan PDF</button>
        @if(empty($isPublic) && auth()->check())
            @if($project->customer?->phone)
            <a href="{{ route('admin.projects.whatsapp-term', [$project, $term]) }}" target="_blank" rel="noopener" class="btn-wa">Kirim WA</a>
            @endif
            <a href="{{ route('admin.projects.show', $project) }}" style="margin-left: 8px; color: #6b7280; font-size: 14px;">← Kembali ke Project</a>
        @endif
    </div>

    <div class="letterhead-wrap">
        @include('components.letterhead')
    </div>

    <div class="header">
        <div>
            <div class="invoice-type">Project / Penagihan Termin</div>
            <span class="badge-unpaid">{{ $payload['status'] }}</span>
        </div>
        <div class="invoice-title">
            <h1>INVOICE PENAGIHAN</h1>
            <div class="invoice-meta">
                <strong>No. Nota:</strong> {{ $payload['nota'] }}<br>
                <strong>Tanggal:</strong> {{ now()->locale('id')->translatedFormat('d F Y') }}<br>
                <strong>Termin:</strong> {{ $payload['term_label'] }}
                @if($term->due_date)
                <br><strong>Jatuh Tempo:</strong> {{ $term->due_date->locale('id')->translatedFormat('d F Y') }}
                @endif
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Pelanggan (Customer)</h3>
            <p><strong>{{ $project->customer?->name ?? '—' }}</strong></p>
            @if($project->customer?->company_name)
            <p>{{ $project->customer->company_name }}</p>
            @endif
            @if($project->customer?->address)
            <p>{{ $project->customer->address }}</p>
            @endif
            @if($project->customer?->city)
            <p>{{ $project->customer->city }}</p>
            @endif
            @if($project->customer?->phone)
            <p>Telp: {{ $project->customer->phone }}</p>
            @endif
            @if($project->customer?->email)
            <p>Email: {{ $project->customer->email }}</p>
            @endif
        </div>
        <div class="info-box">
            <h3>Project</h3>
            <p><strong>{{ $project->code }}</strong></p>
            <p>{{ $project->title }}</p>
            @if($project->description)
            <p style="margin-top: 8px; font-size: 13px; color: #666;">{{ \Illuminate\Support\Str::limit(strip_tags($project->description), 160) }}</p>
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
            @foreach($payload['items'] as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item['description'] }}</strong>
                    @if($item['notes'])
                    <br><span style="font-size: 12px; color: #666;">{{ $item['notes'] }}</span>
                    @endif
                </td>
                <td class="text-right">{{ number_format($item['quantity'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #b91c1c;">
                    @if($item['discount_amount'] > 0)
                    - Rp {{ number_format($item['discount_amount'], 0, ',', '.') }}
                    @if($item['discount_name'])
                    <br><span style="font-size: 11px; color: #666;">{{ $item['discount_name'] }}</span>
                    @endif
                    @else
                    —
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach

            @if($payload['discount_total'] > 0)
            <tr style="border-top: 2px solid #333;">
                <td colspan="5" class="text-right">Subtotal kotor</td>
                <td class="text-right">Rp {{ number_format($payload['subtotal'] + $payload['discount_total'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right" style="color: #b91c1c;">Diskon</td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($payload['discount_total'], 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr @if($payload['discount_total'] <= 0) style="border-top: 2px solid #333;" @endif>
                <td colspan="5" class="text-right" style="font-weight: bold;">Subtotal (DPP)</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($payload['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @if($payload['tax_amount'] > 0)
            <tr>
                <td colspan="5" class="text-right">
                    {{ $payload['tax_name'] ?: 'Pajak' }}
                    @if($payload['tax_rate'])
                        ({{ number_format($payload['tax_rate'], 2, ',', '.') }}%)
                    @endif
                    {{ $payload['tax_calculation_type'] === 'deduction' ? '- Potongan' : '+ Tambahan' }}
                </td>
                <td class="text-right">{{ $payload['tax_calculation_type'] === 'deduction' ? '-' : '' }}Rp {{ number_format($payload['tax_amount'], 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="5" class="text-right" style="font-size: 16px;">Total Tagihan</td>
                <td class="text-right" style="font-size: 16px;">Rp {{ number_format($payload['total'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right">Sisa tagihan</td>
                <td class="text-right">Rp {{ number_format($payload['total'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="notes">
        <strong>Catatan:</strong> Invoice penagihan termin — belum diposting ke keuangan. Status: {{ $payload['status'] }}.
        Lakukan pembayaran sesuai tagihan. Setelah pembayaran diterima, gunakan “Posting ke Keuangan” pada detail project.
    </div>

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-title">Penerima</div>
            <div class="signature-name">{{ $project->customer?->name ?? '-' }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Penerbit</div>
            <div class="signature-name">{{ \App\Models\Setting::appName() }}</div>
        </div>
    </div>

    <div class="footer">
        Dokumen penagihan project {{ $project->code }} — {{ $payload['nota'] }} — {{ now()->locale('id')->translatedFormat('d F Y H:i') }}
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
