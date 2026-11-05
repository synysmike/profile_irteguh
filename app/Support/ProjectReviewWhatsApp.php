<?php

namespace App\Support;

use App\Models\Project;
use App\Models\Setting;

class ProjectReviewWhatsApp
{
    public static function destinationPhone(Project $project): ?string
    {
        return PhoneNumber::normalize($project->customer?->phone);
    }

    public static function buildMessage(Project $project): string
    {
        $project->loadMissing('customer');

        $appName = Setting::appName();
        $customerName = $project->customer?->name ?: 'Bapak/Ibu';
        $status = $project->statusLabel();
        $progress = (int) $project->progress_percent;
        $reviewUrl = $project->publicReviewUrl();

        $lines = [];
        $lines[] = 'Assalamu\'alaikum / Salam,' ;
        $lines[] = '';
        $lines[] = 'Yth. ' . $customerName . ',';
        $lines[] = '';
        $lines[] = 'Terima kasih telah mempercayakan project kepada ' . $appName . '.';
        $lines[] = 'Mohon kesediaannya untuk memberikan ulasan singkat terkait project berikut:';
        $lines[] = '';
        $lines[] = 'Kode : ' . $project->code;
        $lines[] = 'Judul : ' . $project->title;
        $lines[] = 'Status : ' . $status . ' (' . $progress . '%)';
        if ($project->start_date) {
            $lines[] = 'Mulai : ' . $project->start_date->format('d/m/Y');
        }
        if ($project->due_date) {
            $lines[] = 'Target : ' . $project->due_date->format('d/m/Y');
        }
        $lines[] = '';
        $lines[] = 'Link ulasan:';
        $lines[] = $reviewUrl;
        $lines[] = '';
        $lines[] = 'Cara mengisi:';
        $lines[] = '1. Buka link di atas';
        $lines[] = '2. Masukkan nomor HP yang terdaftar pada project';
        $lines[] = '3. Isi rating, komentar, dan lampiran foto/video (opsional)';
        $lines[] = '';
        $lines[] = 'Ulasan Anda sangat membantu peningkatan layanan kami.';
        $lines[] = '';
        $lines[] = 'Hormat kami,';
        $lines[] = $appName;

        return implode("\n", $lines);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function shareUrl(Project $project, ?string $customMessage = null): string
    {
        $destination = static::destinationPhone($project);
        if (! $destination) {
            throw new \InvalidArgumentException('Customer project belum memiliki nomor HP.');
        }

        $message = $customMessage !== null && trim($customMessage) !== ''
            ? $customMessage
            : static::buildMessage($project);

        return 'https://wa.me/' . $destination . '?text=' . rawurlencode($message);
    }
}
