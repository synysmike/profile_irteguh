<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectPaymentTerm;
use App\Models\Setting;

class ProjectTermWhatsAppInvoice
{
    /**
     * Structured billing data for an unpaid project payment term.
     * Shared by WhatsApp message and printable HTML invoice.
     *
     * @return array{
     *     nota: string,
     *     term_label: string,
     *     percentage: float,
     *     items: list<array{description: string, quantity: int, unit_price: float, discount_amount: float, discount_name: ?string, subtotal: float, notes: string}>,
     *     discount_total: float,
     *     subtotal: float,
     *     tax_amount: float,
     *     tax_name: ?string,
     *     tax_rate: ?float,
     *     tax_calculation_type: ?string,
     *     total: float,
     *     status: string
     * }
     */
    public static function billingPayload(Project $project, ProjectPaymentTerm $term): array
    {
        $project->loadMissing(['customer', 'saleTransactions.purchase', 'tax', 'assignmentLetters.jasaItems']);

        $percentage = (float) $term->percentage;
        $factor = $percentage > 0 ? ($percentage / 100) : 1.0;
        $termLabel = $term->label . ' (' . number_format($percentage, 2, ',', '.') . '%)';
        $nota = $project->code . '-T' . $term->term_number;
        $targetDpp = round((float) $term->subtotal_amount, 2);

        $items = [];
        $termDiscount = 0.0;

        foreach (\App\Services\ProjectFinanceService::buildTermJasaLines($project, $factor, $termLabel) as $line) {
            $termDiscount += (float) $line['discount_amount'];
            $items[] = [
                'description' => $line['description'],
                'quantity' => (int) $line['quantity'],
                'unit_price' => (float) $line['unit_price'],
                'discount_amount' => (float) $line['discount_amount'],
                'discount_name' => $line['discount_name'],
                'subtotal' => (float) $line['subtotal'],
                'notes' => $line['notes'],
            ];
        }

        foreach ($project->saleTransactions as $allocation) {
            $netPortion = round((float) $allocation->subtotal * $factor, 2);
            $discPortion = round((float) ($allocation->discount_amount ?? 0) * $factor, 2);
            $grossPortion = round($netPortion + $discPortion, 2);
            $qty = max(1, (int) $allocation->quantity);
            $unitPrice = round($grossPortion / $qty, 2);
            $termDiscount += $discPortion;

            $ket = $project->code . ' · Alokasi stok · ' . $termLabel;
            if ($allocation->purchase?->invoice_number) {
                $ket .= ' · Grosir ' . $allocation->purchase->invoice_number;
            }

            $items[] = [
                'description' => $allocation->description,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_amount' => $discPortion,
                'discount_name' => $discPortion > 0 ? ($allocation->discount_name ?: 'Diskon') : null,
                'subtotal' => $netPortion,
                'notes' => $ket,
            ];
        }

        if ($items === []) {
            $items[] = [
                'description' => $project->title . ' (' . $term->label . ')',
                'quantity' => 1,
                'unit_price' => $targetDpp,
                'discount_amount' => 0.0,
                'discount_name' => null,
                'subtotal' => $targetDpp,
                'notes' => $project->code,
            ];
        } else {
            // Align last line DPP with term subtotal (same as paid invoice builder).
            $sum = 0.0;
            $last = count($items) - 1;
            foreach ($items as $i => $item) {
                if ($i < $last) {
                    $sum += $item['subtotal'];
                }
            }
            $items[$last]['subtotal'] = round($targetDpp - $sum, 2);
            $items[$last]['unit_price'] = round(
                ($items[$last]['subtotal'] + $items[$last]['discount_amount'])
                / max(1, $items[$last]['quantity']),
                2
            );
        }

        return [
            'nota' => $nota,
            'term_label' => $termLabel,
            'percentage' => $percentage,
            'items' => $items,
            'discount_total' => $termDiscount,
            'subtotal' => $targetDpp,
            'tax_amount' => (float) $term->tax_amount,
            'tax_name' => $project->tax_name,
            'tax_rate' => $project->tax_rate !== null ? (float) $project->tax_rate : null,
            'tax_calculation_type' => $project->tax_calculation_type,
            'total' => (float) $term->amount,
            'status' => 'Belum Lunas',
        ];
    }

    /**
     * Build unpaid (pre-posting) invoice text for a project payment term.
     * Status is always "Belum Lunas".
     */
    public static function buildMessage(Project $project, ProjectPaymentTerm $term): string
    {
        $payload = static::billingPayload($project, $term);

        $appName = Setting::appName();
        $letterhead = SaleWhatsAppInvoice::letterheadPlainText();
        $businessPhone = SaleWhatsAppInvoice::BUSINESS_PHONE;

        $customer = $project->customer;
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
        $lines[] = $payload['nota'];
        $lines[] = '';
        $lines[] = 'Pelanggan Yth :';
        $lines[] = $customerLine;
        $lines[] = '';
        $lines[] = 'Tanggal : ' . now()->format('d/m/Y');
        $lines[] = 'Project : ' . $project->code . ' — ' . $project->title;
        $lines[] = 'Termin : ' . $payload['term_label'];
        if ($term->due_date) {
            $lines[] = 'Jatuh Tempo : ' . $term->due_date->format('d/m/Y');
        }
        $lines[] = '';
        $lines[] = '======================';
        $lines[] = 'Detail pesanan:';

        foreach ($payload['items'] as $item) {
            $lines[] = '';
            $lines[] = 'Item:';
            $lines[] = '✅ ' . $item['description'];
            $lines[] = 'Qty : ' . number_format($item['quantity'], 0, ',', '.') . ' PCS';
            $lines[] = 'Harga : ' . SaleWhatsAppInvoice::formatMoney($item['unit_price']);
            if ($item['discount_amount'] > 0) {
                $lines[] = 'Diskon : -' . SaleWhatsAppInvoice::formatMoney($item['discount_amount'])
                    . ($item['discount_name'] ? ' (' . $item['discount_name'] . ')' : '');
            }
            $lines[] = 'Total : ' . SaleWhatsAppInvoice::formatMoney($item['subtotal']);
            if (! empty($item['notes'])) {
                $lines[] = 'Ket : ' . $item['notes'];
            }
        }

        $lines[] = '';
        $lines[] = '==============';
        $lines[] = 'Detail biaya :';
        if ($payload['discount_total'] > 0) {
            $lines[] = 'Subtotal kotor : ' . SaleWhatsAppInvoice::formatMoney($payload['subtotal'] + $payload['discount_total']);
            $lines[] = 'Diskon : -' . SaleWhatsAppInvoice::formatMoney($payload['discount_total']);
        }
        $lines[] = 'Subtotal (DPP) : ' . SaleWhatsAppInvoice::formatMoney($payload['subtotal']);

        if ($payload['tax_amount'] > 0) {
            $taxLabel = $payload['tax_name'] ?: 'Pajak';
            if ($payload['tax_rate']) {
                $taxLabel .= ' (' . number_format($payload['tax_rate'], 2, ',', '.') . '%)';
            }
            $sign = $payload['tax_calculation_type'] === 'deduction' ? '-' : '';
            $lines[] = $taxLabel . ' : ' . $sign . SaleWhatsAppInvoice::formatMoney($payload['tax_amount']);
        }

        $lines[] = 'Total tagihan : ' . SaleWhatsAppInvoice::formatMoney($payload['total']);
        $lines[] = 'Grand total : ' . SaleWhatsAppInvoice::formatMoney($payload['total']);
        $lines[] = '';
        $lines[] = 'Pembayaran:';
        $lines[] = 'Sisa tagihan : ' . SaleWhatsAppInvoice::formatMoney($payload['total']);
        $lines[] = '';
        $lines[] = 'Status: ' . $payload['status'];
        $lines[] = '';
        $lines[] = '=================';
        $lines[] = 'Link invoice:';
        $lines[] = $term->publicInvoiceUrl();
        $lines[] = '(Buka link, lalu masukkan nomor HP Anda untuk melihat invoice)';
        $lines[] = '';
        $lines[] = 'Link ulasan project:';
        $lines[] = $project->publicReviewUrl();
        $lines[] = '(Verifikasi nomor HP yang sama, lalu isi rating & komentar)';
        $lines[] = '';
        $lines[] = 'Catatan: Invoice ini belum diposting ke keuangan. Lakukan pembayaran sesuai tagihan.';
        $lines[] = '';
        $lines[] = 'Terima kasih';

        return implode("\n", $lines);
    }

    public static function destinationPhone(Project $project): ?string
    {
        return SaleWhatsAppInvoice::normalizePhone($project->customer?->phone);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function shareUrl(Project $project, ProjectPaymentTerm $term): string
    {
        if ($term->status === 'paid' || $term->sale_id) {
            throw new \InvalidArgumentException('Termin sudah diposting. Gunakan kirim WA dari invoice penjualan.');
        }

        $destination = static::destinationPhone($project);
        if (! $destination) {
            throw new \InvalidArgumentException('Customer project belum memiliki nomor HP.');
        }

        return 'https://wa.me/' . $destination . '?text=' . rawurlencode(static::buildMessage($project, $term));
    }
}
