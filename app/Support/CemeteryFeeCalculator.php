<?php

namespace App\Support;

class CemeteryFeeCalculator
{
    /**
     * @return array{
     *     base_fee: float,
     *     maintenance_fee: float,
     *     burial_permit_fee: float,
     *     other_applicable_fee: float,
     *     amount_due: float
     * }
     */
    public static function compute(
        string $siteCode,
        string $categoryCode,
        string $transactionTypeCode,
        string $maintenanceType,
        ?int $maintenanceYears,
        bool $hasBurialPermit,
        float $otherApplicableFee
    ): array {
        $siteCode = strtoupper(trim($siteCode));
        $categoryCode = strtoupper(trim($categoryCode));
        $transactionTypeCode = strtoupper(trim($transactionTypeCode));
        $maintenanceType = strtolower(trim($maintenanceType));
        $otherApplicableFee = max(round($otherApplicableFee, 2), 0.00);

        $baseFee = 0.00;
        $maintenanceFee = self::maintenanceFee($maintenanceType, $maintenanceYears);
        $burialPermitFee = $hasBurialPermit ? 300.00 : 0.00;

        if ($transactionTypeCode === 'SINGLE_NICHE_PURCHASE') {
            if ($siteCode === 'SJM') {
                if ($categoryCode === 'INFANT') {
                    $baseFee = 5000.00;
                } elseif (in_array($categoryCode, ['REGULAR', 'REGULAR_LARGE'], true)) {
                    $baseFee = 10000.00;
                }
            } elseif ($siteCode === 'NMC' && in_array($categoryCode, ['COLUMBARIUM', 'INFANT'], true)) {
                $baseFee = 5000.00;
            }
        } elseif ($transactionTypeCode === 'ADDITIONAL_BURIAL') {
            if (in_array($siteCode, ['OMC', 'NMC', 'SPMC'], true)) {
                $baseFee = 5000.00;
            }
        } elseif ($transactionTypeCode === 'LOT_PURCHASE') {
            if ($siteCode === 'SPMC') {
                $baseFee = 10000.00;
            }
        } elseif ($transactionTypeCode === 'BURIAL_PERMIT') {
            $baseFee = 300.00;
            $burialPermitFee = 0.00;
        } elseif ($transactionTypeCode === 'MAINTENANCE_FEE') {
            $baseFee = 0.00;
        }

        $amountDue = round($baseFee + $maintenanceFee + $burialPermitFee + $otherApplicableFee, 2);

        return [
            'base_fee' => round($baseFee, 2),
            'maintenance_fee' => round($maintenanceFee, 2),
            'burial_permit_fee' => round($burialPermitFee, 2),
            'other_applicable_fee' => $otherApplicableFee,
            'amount_due' => $amountDue,
        ];
    }

    private static function maintenanceFee(string $maintenanceType, ?int $maintenanceYears): float
    {
        if ($maintenanceType === 'five_year_fixed') {
            return 1500.00;
        }

        if ($maintenanceType === 'yearly') {
            $years = max((int) ($maintenanceYears ?? 0), 0);
            return round($years * 300.00, 2);
        }

        return 0.00;
    }
}
