<?php

namespace App\Support;

use App\Models\Sale;
use App\Models\Setting;

class SaleWhatsAppInvoice
{
    /**
     * Business contact shown inside the message body (not the chat destination).
     */
    public const BUSINESS_PHONE = '6281332444088';

    public static function formatMoney(float|int|string $amount): string
    {
        return 'Rp' . number_format((float) $amount, 0, ',', '.');
    }

    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }

    /**
     * Destination WhatsApp number — always from invoice customer phone.
     */
    public static function destinationPhone(Sale $sale): ?string
    {
        return static::normalizePhone($sale->customer?->phone);
    }

    public static function letterheadPlainText(): string
    {
        $html = Setting::letterheadHtml();
        if ($html === '') {
            return Setting::contactAddress();
        }

        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $html)));
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $text) ?: [])));

        return implode("\n", $lines);
    }

    public static function buildMessage(Sale $sale): string
    {
        $sale->loadMissing(['customer', 'saleItems', 'cashTransaction', 'project']);

        $appName = Setting::appName();
        $letterhead = static::letterheadPlainText();
        $businessPhone = self::BUSINESS_PHONE;

        $customer = $sale->customer;
        $customerLine = trim(($customer?->name ?? '—') . ' ' . ($customer?->phone ?? ''));

        $lines = [];
        $lines[] = 'FAKTUR ELEKTRONIK TRANSAKSI REGULER';
        $lines[] = strtoupper($appName);
        if ($letterhead !== '') {
            $lines[] = $letterhead;
        }
        $lines[] = $businessPhone;
        $lines[] = '';
        $lines[] = 'Nomor Nota :';
        $lines[] = $sale->invoice_number;
        $lines[] = '';
        $lines[] = 'Pelanggan Yth :';
        $lines[] = $customerLine;
        $lines[] = '';
        $lines[] = 'Tanggal : ' . ($sale->sale_date?->format('d/m/Y') ?? '—');
        if ($sale->project) {
            $lines[] = 'Project : ' . $sale->project->code . ' — ' . $sale->project->title;
        }
        $lines[] = '';
        $lines[] = '======================';
        $lines[] = 'Detail pesanan:';

        foreach ($sale->saleItems as $item) {
            $lines[] = '';
            $lines[] = 'Item:';
            $lines[] = '✅ ' . $item->description . ', ' . number_format((int) $item->quantity, 0, ',', '.') . ' PCS';
            $lines[] = '@ ' . static::formatMoney($item->unit_price) . ', Total ' . static::formatMoney($item->subtotal);
            $lines[] = 'Ket : ' . trim((string) ($item->notes ?? ''));
        }

        $lines[] = '';
        $lines[] = '==============';
        $lines[] = 'Detail biaya :';
        $lines[] = 'Subtotal (DPP) : ' . static::formatMoney($sale->subtotal);

        if ((float) $sale->ppn_amount > 0) {
            $taxLabel = $sale->tax_name ?: 'Pajak';
            if ($sale->tax_rate) {
                $taxLabel .= ' (' . number_format((float) $sale->tax_rate, 2, ',', '.') . '%)';
            }
            $sign = $sale->tax_calculation_type === 'deduction' ? '-' : '';
            $lines[] = $taxLabel . ' : ' . $sign . static::formatMoney($sale->ppn_amount);
        }

        $lines[] = 'Total tagihan : ' . static::formatMoney($sale->total);
        $lines[] = 'Grand total : ' . static::formatMoney($sale->total);
        $lines[] = '';
        $lines[] = 'Pembayaran:';

        if ($sale->cashTransaction) {
            $lines[] = 'Sisa tagihan : ' . static::formatMoney(0);
            $lines[] = '';
            $lines[] = 'Status: Lunas';
        } else {
            $lines[] = 'Sisa tagihan : ' . static::formatMoney($sale->total);
            $lines[] = '';
            $lines[] = 'Status: Belum lunas';
        }

        if ($sale->notes) {
            $lines[] = '';
            $lines[] = 'Catatan: ' . $sale->notes;
        }

        $lines[] = '';
        $lines[] = '=================';
        $lines[] = 'Link invoice:';
        $lines[] = route('admin.sales.invoice', $sale->id);
        $lines[] = '';
        $lines[] = 'Terima kasih';

        return implode("\n", $lines);
    }

    /**
     * @throws \InvalidArgumentException when customer phone is missing
     */
    public static function shareUrl(Sale $sale): string
    {
        $destination = static::destinationPhone($sale);
        if (! $destination) {
            throw new \InvalidArgumentException('Nomor WhatsApp customer tidak ditemukan pada invoice ini.');
        }

        return 'https://wa.me/' . $destination . '?text=' . rawurlencode(static::buildMessage($sale));
    }
}
