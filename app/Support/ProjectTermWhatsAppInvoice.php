<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectPaymentTerm;
use App\Models\Setting;

class ProjectTermWhatsAppInvoice
{
    /**
     * Build unpaid (pre-posting) invoice text for a project payment term.
     * Status is always "Belum Lunas".
     */
    public static function buildMessage(Project $project, ProjectPaymentTerm $term): string
    {
        $project->loadMissing(['customer', 'saleTransactions.purchase', 'tax']);

        $appName = Setting::appName();
        $letterhead = SaleWhatsAppInvoice::letterheadPlainText();
        $businessPhone = SaleWhatsAppInvoice::BUSINESS_PHONE;

        $customer = $project->customer;
        $customerLine = trim(($customer?->name ?? '—') . ' ' . ($customer?->phone ?? ''));

        $nota = $project->code . '-T' . $term->term_number;
        $percentage = (float) $term->percentage;
        $factor = $percentage > 0 ? ($percentage / 100) : 1.0;
        $termLabel = $term->label . ' (' . number_format($percentage, 2, ',', '.') . '%)';

        $lines = [];
        $lines[] = 'FAKTUR ELEKTRONIK TRANSAKSI REGULER';
        $lines[] = strtoupper($appName);
        if ($letterhead !== '') {
            $lines[] = $letterhead;
        }
        $lines[] = $businessPhone;
        $lines[] = '';
        $lines[] = 'Nomor Nota :';
        $lines[] = $nota;
        $lines[] = '';
        $lines[] = 'Pelanggan Yth :';
        $lines[] = $customerLine;
        $lines[] = '';
        $lines[] = 'Tanggal : ' . now()->format('d/m/Y');
        $lines[] = 'Project : ' . $project->code . ' — ' . $project->title;
        $lines[] = 'Termin : ' . $termLabel;
        if ($term->due_date) {
            $lines[] = 'Jatuh Tempo : ' . $term->due_date->format('d/m/Y');
        }
        $lines[] = '';
        $lines[] = '======================';
        $lines[] = 'Detail pesanan:';

        $jasaGross = (float) ($project->base_subtotal ?? 0);
        $jasaDiscount = (float) ($project->discount_amount ?? 0);
        $jasaNet = max(0, $jasaGross - $jasaDiscount);
        $termDiscount = 0.0;

        if ($jasaNet > 0 || $jasaGross > 0) {
            $netPortion = round($jasaNet * $factor, 2);
            $discPortion = round($jasaDiscount * $factor, 2);
            $grossPortion = round($netPortion + $discPortion, 2);
            $termDiscount += $discPortion;
            $lines[] = '';
            $lines[] = 'Item:';
            $lines[] = '✅ Jasa project: ' . $project->title . ', 1 PCS';
            $lines[] = '@ ' . SaleWhatsAppInvoice::formatMoney($grossPortion) . ', Total ' . SaleWhatsAppInvoice::formatMoney($netPortion);
            if ($discPortion > 0) {
                $lines[] = 'Diskon : -' . SaleWhatsAppInvoice::formatMoney($discPortion)
                    . ($project->discount_name ? ' (' . $project->discount_name . ')' : '');
            }
            $lines[] = 'Ket : ' . $project->code . ' · ' . $termLabel;
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

            $lines[] = '';
            $lines[] = 'Item:';
            $lines[] = '✅ ' . $allocation->description . ', ' . number_format($qty, 0, ',', '.') . ' PCS';
            $lines[] = '@ ' . SaleWhatsAppInvoice::formatMoney($unitPrice) . ', Total ' . SaleWhatsAppInvoice::formatMoney($netPortion);
            if ($discPortion > 0) {
                $lines[] = 'Diskon : -' . SaleWhatsAppInvoice::formatMoney($discPortion)
                    . ($allocation->discount_name ? ' (' . $allocation->discount_name . ')' : '');
            }
            $lines[] = 'Ket : ' . $ket;
        }

        if ($jasaGross <= 0 && $project->saleTransactions->isEmpty()) {
            $lines[] = '';
            $lines[] = 'Item:';
            $lines[] = '✅ ' . $project->title . ' (' . $term->label . '), 1 PCS';
            $lines[] = '@ ' . SaleWhatsAppInvoice::formatMoney($term->subtotal_amount) . ', Total ' . SaleWhatsAppInvoice::formatMoney($term->subtotal_amount);
            $lines[] = 'Ket : ' . $project->code;
        }

        $lines[] = '';
        $lines[] = '==============';
        $lines[] = 'Detail biaya :';
        if ($termDiscount > 0) {
            $lines[] = 'Subtotal kotor : ' . SaleWhatsAppInvoice::formatMoney((float) $term->subtotal_amount + $termDiscount);
            $lines[] = 'Diskon : -' . SaleWhatsAppInvoice::formatMoney($termDiscount);
        }
        $lines[] = 'Subtotal (DPP) : ' . SaleWhatsAppInvoice::formatMoney($term->subtotal_amount);

        if ((float) $term->tax_amount > 0) {
            $taxLabel = $project->tax_name ?: 'Pajak';
            if ($project->tax_rate) {
                $taxLabel .= ' (' . number_format((float) $project->tax_rate, 2, ',', '.') . '%)';
            }
            $sign = $project->tax_calculation_type === 'deduction' ? '-' : '';
            $lines[] = $taxLabel . ' : ' . $sign . SaleWhatsAppInvoice::formatMoney($term->tax_amount);
        }

        $lines[] = 'Total tagihan : ' . SaleWhatsAppInvoice::formatMoney($term->amount);
        $lines[] = 'Grand total : ' . SaleWhatsAppInvoice::formatMoney($term->amount);
        $lines[] = '';
        $lines[] = 'Pembayaran:';
        $lines[] = 'Sisa tagihan : ' . SaleWhatsAppInvoice::formatMoney($term->amount);
        $lines[] = '';
        $lines[] = 'Status: Belum Lunas';
        $lines[] = '';
        $lines[] = '=================';
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
