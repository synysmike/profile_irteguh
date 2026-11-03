<?php

namespace App\Services;

use App\Models\SumberDana;

class SumberDanaResolver
{
    public const KEY_PENJUALAN = 'penjualan';
    public const KEY_GROSIR = 'grosir';
    public const KEY_PROJECT_TERMIN = 'project_termin';
    public const KEY_MODAL_INVESTOR = 'modal_investor';
    public const KEY_DIVIDEN_INVESTOR = 'dividen_investor';

    public function idBySystemKey(string $key): ?int
    {
        $sumber = SumberDana::findBySystemKey($key);

        return $sumber?->id;
    }

    public function penjualanId(): ?int
    {
        return $this->idBySystemKey(self::KEY_PENJUALAN);
    }

    public function grosirId(): ?int
    {
        return $this->idBySystemKey(self::KEY_GROSIR);
    }

    public function projectTerminId(): ?int
    {
        return $this->idBySystemKey(self::KEY_PROJECT_TERMIN);
    }

    public function modalInvestorId(): ?int
    {
        return $this->idBySystemKey(self::KEY_MODAL_INVESTOR);
    }

    public function dividenInvestorId(): ?int
    {
        return $this->idBySystemKey(self::KEY_DIVIDEN_INVESTOR);
    }
}
