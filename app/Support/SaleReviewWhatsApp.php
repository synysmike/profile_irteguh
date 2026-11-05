<?php

namespace App\Support;

use App\Models\Sale;
use App\Models\Setting;

class SaleReviewWhatsApp
{
    public static function destinationPhone(Sale $sale): ?string
    {
        return PhoneNumber::normalize($sale->customer?->phone);
    }

    public static function buildMessage(Sale $sale): string
    {
        $sale->loadMissing('customer');

        $appName = Setting::appName();
        $customerName = $sale->customer?->name ?: 'Bapak/Ibu';
        $reviewUrl = $sale->publicReviewUrl();
        $invoiceUrl = $sale->publicInvoiceUrl();

        $lines = [];
        $lines[] = 'Assalamu\'alaikum / Salam,';
        $lines[] = '';
        $lines[] = 'Yth. ' . $customerName . ',';
        $lines[] = '';
        $lines[] = 'Terima kasih telah berbelanja / bertransaksi dengan ' . $appName . '.';
        $lines[] = 'Mohon kesediaannya untuk memberikan ulasan singkat terkait pembelian berikut:';
        $lines[] = '';
        $lines[] = 'No. Faktur : ' . $sale->invoice_number;
        $lines[] = 'Tanggal : ' . ($sale->sale_date?->format('d/m/Y') ?? '—');
        $lines[] = 'Total : Rp' . number_format((float) $sale->total, 0, ',', '.');
        $lines[] = '';
        $lines[] = 'Link ulasan:';
        $lines[] = $reviewUrl;
        $lines[] = '';
        $lines[] = 'Cara mengisi:';
        $lines[] = '1. Buka link di atas';
        $lines[] = '2. Masukkan nomor HP yang terdaftar pada faktur';
        $lines[] = '3. Isi rating, komentar, dan lampiran foto/video (opsional)';
        $lines[] = '';
        $lines[] = 'Link invoice (jika diperlukan):';
        $lines[] = $invoiceUrl;
        $lines[] = '';
        $lines[] = 'Ulasan Anda sangat membantu peningkatan layanan kami.';
        $lines[] = '';
        $lines[] = 'Hormat kami,';
        $lines[] = $appName;

        return implode("\n", $lines);
    }
}
