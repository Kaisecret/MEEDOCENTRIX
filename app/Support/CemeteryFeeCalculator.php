<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CemeteryFeeCalculator
{
    /**
     * @var array<string, float>|null
     */
    private static ?array $ruleAmounts = null;

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
        $burialPermitFee = $hasBurialPermit ? self::ruleAmount('permit.standard', 300.00) : 0.00;
        $forceBurialPermitFee = false;

        if ($transactionTypeCode === 'SINGLE_NICHE_PURCHASE') {
            if ($siteCode === 'SJM') {
                if ($categoryCode === 'INFANT') {
                    $baseFee = self::ruleAmount('base.single_niche.sjm.infant', 5000.00);
                } elseif (in_array($categoryCode, ['REGULAR', 'REGULAR_LARGE'], true)) {
                    $baseFee = self::ruleAmount('base.single_niche.sjm.' . strtolower($categoryCode), 10000.00);
                }
            } elseif ($siteCode === 'NMC' && in_array($categoryCode, ['COLUMBARIUM', 'INFANT'], true)) {
                $baseFee = self::ruleAmount('base.single_niche.nmc.' . strtolower($categoryCode), 5000.00);
            }
        } elseif ($transactionTypeCode === 'ADDITIONAL_BURIAL') {
            if (in_array($siteCode, ['OMC', 'NMC', 'SPMC'], true)) {
                $baseFee = self::ruleAmount('base.additional_burial.' . strtolower($siteCode), 5000.00);
                $forceBurialPermitFee = true;
            }
        } elseif ($transactionTypeCode === 'LOT_PURCHASE') {
            if ($siteCode === 'SPMC') {
                $baseFee = self::ruleAmount('base.lot_purchase.spmc', 10000.00);
                $forceBurialPermitFee = true;
            }
        } elseif ($transactionTypeCode === 'BURIAL_PERMIT') {
            $baseFee = self::ruleAmount('base.burial_permit', 300.00);
            $burialPermitFee = 0.00;
        } elseif ($transactionTypeCode === 'MAINTENANCE_FEE') {
            $baseFee = 0.00;
        } elseif ($transactionTypeCode === 'EXHUMATION') {
            $baseFee = self::ruleAmount('base.exhumation', 200.00);
            $burialPermitFee = 0.00;
        } elseif (in_array($transactionTypeCode, ['TRANSFER', 'OTHER'], true)) {
            $baseFee = self::ruleAmount('base.' . strtolower($transactionTypeCode), 300.00);
            $burialPermitFee = 0.00;
            $maintenanceFee = 0.00;
        }

        if ($forceBurialPermitFee) {
            $burialPermitFee = self::ruleAmount('permit.standard', 300.00);
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
            return self::ruleAmount('maintenance.five_year_fixed', 1500.00);
        }

        if ($maintenanceType === 'yearly') {
            $years = max((int) ($maintenanceYears ?? 0), 0);
            return round($years * self::ruleAmount('maintenance.yearly', 300.00), 2);
        }

        return 0.00;
    }

    public static function flushCache(): void
    {
        self::$ruleAmounts = null;
    }

    private static function ruleAmount(string $key, float $fallback): float
    {
        if (self::$ruleAmounts === null) {
            self::$ruleAmounts = [];

            if (Schema::hasTable('cemetery_fee_rules')) {
                self::$ruleAmounts = DB::table('cemetery_fee_rules')
                    ->where('is_active', true)
                    ->pluck('amount', 'fee_key')
                    ->map(static fn ($amount): float => round((float) $amount, 2))
                    ->all();
            }
        }

        return self::$ruleAmounts[$key] ?? $fallback;
    }
}
