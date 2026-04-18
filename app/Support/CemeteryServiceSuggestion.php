<?php

namespace App\Support;

class CemeteryServiceSuggestion
{
    /**
     * @return array{transaction_type_code: ?string, amount_due: float}
     */
    public static function resolve(string $serviceTypeCode, string $siteCode): array
    {
        $code = strtoupper(trim($serviceTypeCode));
        $site = strtoupper(trim($siteCode));

        if ($code === 'INTERMENT') {
            return match ($site) {
                'SJM' => ['transaction_type_code' => 'SINGLE_NICHE_PURCHASE', 'amount_due' => 10000.00],
                'NMC' => ['transaction_type_code' => 'SINGLE_NICHE_PURCHASE', 'amount_due' => 5000.00],
                'SPMC' => ['transaction_type_code' => 'LOT_PURCHASE', 'amount_due' => 10300.00],
                'OMC' => ['transaction_type_code' => 'ADDITIONAL_BURIAL', 'amount_due' => 5300.00],
                default => ['transaction_type_code' => null, 'amount_due' => 0.00],
            };
        }

        if ($code === 'BURIAL') {
            if (in_array($site, ['OMC', 'NMC', 'SPMC'], true)) {
                return ['transaction_type_code' => 'ADDITIONAL_BURIAL', 'amount_due' => 5300.00];
            }
            return ['transaction_type_code' => 'BURIAL_PERMIT', 'amount_due' => 300.00];
        }

        if ($code === 'RENEWAL' || $code === 'MAINTENANCE_UPDATE') {
            return ['transaction_type_code' => 'MAINTENANCE_FEE', 'amount_due' => 1500.00];
        }

        if ($code === 'EXHUMATION') {
            return ['transaction_type_code' => 'EXHUMATION', 'amount_due' => 200.00];
        }

        if ($code === 'TRANSFER') {
            return ['transaction_type_code' => 'TRANSFER', 'amount_due' => 300.00];
        }

        if ($code === 'RECORD_CORRECTION') {
            return ['transaction_type_code' => 'OTHER', 'amount_due' => 300.00];
        }

        return ['transaction_type_code' => null, 'amount_due' => 0.00];
    }
}
