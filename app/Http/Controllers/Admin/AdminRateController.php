<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtriumFunctionHall;
use App\Models\CemeteryFeeRule;
use App\Models\FishportCommodity;
use App\Models\FishportPaymentType;
use App\Models\FishportUnit;
use App\Models\MarketStallLocation;
use App\Models\MarketStallRate;
use App\Models\MarketStallType;
use App\Models\TerminalVehicleType;
use App\Support\CemeteryFeeCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRateController extends Controller
{
    /**
     * @var array<string, array<string, string>>
     */
    private const DEPARTMENTS = [
        'fishport' => ['name' => 'Fishport', 'icon' => 'fas fa-ship', 'accent' => '#2563eb'],
        'market' => ['name' => 'Public Market', 'icon' => 'fas fa-store', 'accent' => '#0f766e'],
        'cemetery' => ['name' => 'Cemetery', 'icon' => 'fas fa-cross', 'accent' => '#7c3aed'],
        'terminal' => ['name' => 'Terminal', 'icon' => 'fas fa-bus', 'accent' => '#ea580c'],
        'atrium' => ['name' => 'Atrium', 'icon' => 'fas fa-building-columns', 'accent' => '#0891b2'],
    ];

    public function index(): View
    {
        $fishportPaymentTypes = FishportPaymentType::query()
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'default_fee', 'is_active']);

        $fishportCommodities = FishportCommodity::query()
            ->with(['classification:id,name', 'defaultUnit:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'classification_id', 'default_unit_id', 'default_conversion', 'is_active']);

        $fishportUnits = FishportUnit::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $marketStallTypes = MarketStallType::query()
            ->orderBy('type_name')
            ->get(['id', 'type_name', 'description', 'default_rate', 'rate_notes', 'is_active']);

        $marketLocations = MarketStallLocation::query()
            ->with([
                'activeRate' => static function ($query): void {
                    $query->select([
                        'market_stall_rates.id',
                        'market_stall_rates.market_stall_location_id',
                        'market_stall_rates.rate_amount',
                        'market_stall_rates.effective_start_date',
                    ]);
                },
            ])
            ->orderBy('location_code')
            ->get(['id', 'location_code', 'location_name', 'zone', 'floor_level', 'is_active']);

        $terminalVehicleTypes = TerminalVehicleType::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'parking_fee_per_hour', 'description', 'is_active']);

        $atriumFunctionHalls = AtriumFunctionHall::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'capacity', 'hourly_rate', 'description', 'is_active']);

        $cemeteryFeeRules = CemeteryFeeRule::query()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'fee_key', 'label', 'description', 'amount', 'is_active', 'sort_order']);

        $departmentStats = [
            'fishport' => $this->stats(
                $fishportPaymentTypes->map(fn (FishportPaymentType $type): array => [
                    'amount' => (float) $type->default_fee,
                    'active' => (bool) $type->is_active,
                ])
            ),
            'market' => $this->stats(
                $marketStallTypes->map(fn (MarketStallType $type): array => [
                    'amount' => (float) $type->default_rate,
                    'active' => (bool) $type->is_active,
                ])->merge($marketLocations->map(fn (MarketStallLocation $location): array => [
                    'amount' => (float) ($location->activeRate?->rate_amount ?? 0),
                    'active' => (bool) $location->is_active,
                ]))
            ),
            'cemetery' => $this->stats(
                $cemeteryFeeRules->map(fn (CemeteryFeeRule $rule): array => [
                    'amount' => (float) $rule->amount,
                    'active' => (bool) $rule->is_active,
                ])
            ),
            'terminal' => $this->stats(
                $terminalVehicleTypes->map(fn (TerminalVehicleType $type): array => [
                    'amount' => (float) $type->parking_fee_per_hour,
                    'active' => (bool) $type->is_active,
                ])
            ),
            'atrium' => $this->stats(
                $atriumFunctionHalls->map(fn (AtriumFunctionHall $hall): array => [
                    'amount' => (float) $hall->hourly_rate,
                    'active' => (bool) $hall->is_active,
                ])
            ),
        ];

        return view('admin.rates', [
            'departments' => self::DEPARTMENTS,
            'departmentStats' => $departmentStats,
            'fishportPaymentTypes' => $fishportPaymentTypes,
            'fishportCommodities' => $fishportCommodities,
            'fishportUnits' => $fishportUnits,
            'marketStallTypes' => $marketStallTypes,
            'marketLocations' => $marketLocations,
            'terminalVehicleTypes' => $terminalVehicleTypes,
            'atriumFunctionHalls' => $atriumFunctionHalls,
            'cemeteryFeeRules' => $cemeteryFeeRules,
            'lastUpdatedAt' => collect([
                FishportPaymentType::query()->max('updated_at'),
                MarketStallType::query()->max('updated_at'),
                CemeteryFeeRule::query()->max('updated_at'),
                TerminalVehicleType::query()->max('updated_at'),
                AtriumFunctionHall::query()->max('updated_at'),
            ])->filter()->max(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fishport_payment_types' => ['required', 'array', 'min:1'],
            'fishport_payment_types.*.id' => ['required', 'integer', Rule::exists('fishport_payment_types', 'id')],
            'fishport_payment_types.*.default_fee' => ['required', 'numeric', 'min:0'],
            'fishport_payment_types.*.is_active' => ['nullable', 'boolean'],
            'fishport_commodities' => ['required', 'array', 'min:1'],
            'fishport_commodities.*.id' => ['required', 'integer', Rule::exists('fishport_commodities', 'id')],
            'fishport_commodities.*.default_unit_id' => ['required', 'integer', Rule::exists('fishport_units', 'id')],
            'fishport_commodities.*.default_conversion' => ['required', 'numeric', 'min:0.0001'],
            'fishport_commodities.*.is_active' => ['nullable', 'boolean'],
            'market_stall_types' => ['required', 'array', 'min:1'],
            'market_stall_types.*.id' => ['required', 'integer', Rule::exists('market_stall_types', 'id')],
            'market_stall_types.*.default_rate' => ['required', 'numeric', 'min:0'],
            'market_stall_types.*.rate_notes' => ['nullable', 'string', 'max:1000'],
            'market_stall_types.*.is_active' => ['nullable', 'boolean'],
            'market_location_rates' => ['required', 'array', 'min:1'],
            'market_location_rates.*.id' => ['required', 'integer', Rule::exists('market_stall_locations', 'id')],
            'market_location_rates.*.rate_amount' => ['required', 'numeric', 'min:0'],
            'market_location_rates.*.effective_start_date' => ['nullable', 'date'],
            'market_location_rates.*.is_active' => ['nullable', 'boolean'],
            'terminal_vehicle_types' => ['required', 'array', 'min:1'],
            'terminal_vehicle_types.*.id' => ['required', 'integer', Rule::exists('terminal_vehicle_types', 'id')],
            'terminal_vehicle_types.*.parking_fee_per_hour' => ['required', 'numeric', 'min:0'],
            'terminal_vehicle_types.*.description' => ['nullable', 'string', 'max:1000'],
            'terminal_vehicle_types.*.is_active' => ['nullable', 'boolean'],
            'atrium_function_halls' => ['required', 'array', 'min:1'],
            'atrium_function_halls.*.id' => ['required', 'integer', Rule::exists('atrium_function_halls', 'id')],
            'atrium_function_halls.*.hourly_rate' => ['required', 'numeric', 'min:0'],
            'atrium_function_halls.*.capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'atrium_function_halls.*.description' => ['nullable', 'string', 'max:1000'],
            'atrium_function_halls.*.is_active' => ['nullable', 'boolean'],
            'cemetery_fee_rules' => ['required', 'array', 'min:1'],
            'cemetery_fee_rules.*.id' => ['required', 'integer', Rule::exists('cemetery_fee_rules', 'id')],
            'cemetery_fee_rules.*.amount' => ['required', 'numeric', 'min:0'],
            'cemetery_fee_rules.*.is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['fishport_payment_types'] as $row) {
                FishportPaymentType::query()->whereKey((int) $row['id'])->update([
                    'default_fee' => round((float) $row['default_fee'], 2),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['fishport_commodities'] as $row) {
                FishportCommodity::query()->whereKey((int) $row['id'])->update([
                    'default_unit_id' => (int) $row['default_unit_id'],
                    'default_conversion' => round((float) $row['default_conversion'], 4),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['market_stall_types'] as $row) {
                MarketStallType::query()->whereKey((int) $row['id'])->update([
                    'default_rate' => round((float) $row['default_rate'], 2),
                    'rate_notes' => trim((string) ($row['rate_notes'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['market_location_rates'] as $row) {
                $locationId = (int) $row['id'];
                MarketStallLocation::query()->whereKey($locationId)->update([
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);

                $this->activateLocationRate(
                    $locationId,
                    (float) $row['rate_amount'],
                    (string) ($row['effective_start_date'] ?? now()->toDateString())
                );
            }

            foreach ($validated['terminal_vehicle_types'] as $row) {
                TerminalVehicleType::query()->whereKey((int) $row['id'])->update([
                    'parking_fee_per_hour' => round((float) $row['parking_fee_per_hour'], 2),
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['atrium_function_halls'] as $row) {
                AtriumFunctionHall::query()->whereKey((int) $row['id'])->update([
                    'capacity' => isset($row['capacity']) ? (int) $row['capacity'] : null,
                    'hourly_rate' => round((float) $row['hourly_rate'], 2),
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['cemetery_fee_rules'] as $row) {
                CemeteryFeeRule::query()->whereKey((int) $row['id'])->update([
                    'amount' => round((float) $row['amount'], 2),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                    'updated_by_user_id' => Auth::id(),
                ]);
            }
        });

        CemeteryFeeCalculator::flushCache();

        return redirect()
            ->route('admin.rates')
            ->with('status', 'Rate and fee settings updated across all departments.');
    }

    /**
     * @param Collection<int, array{amount: float, active: bool}> $rows
     * @return array{items: int, active: int, inactive: int, average: float, highest: float, total_reference: float}
     */
    private function stats(Collection $rows): array
    {
        $amounts = $rows->pluck('amount')->map(static fn ($amount): float => (float) $amount);

        return [
            'items' => $rows->count(),
            'active' => $rows->where('active', true)->count(),
            'inactive' => $rows->where('active', false)->count(),
            'average' => round((float) $amounts->avg(), 2),
            'highest' => round((float) $amounts->max(), 2),
            'total_reference' => round((float) $amounts->sum(), 2),
        ];
    }

    private function activateLocationRate(int $locationId, float $rateAmount, string $effectiveStartDate): MarketStallRate
    {
        $effectiveDate = Carbon::parse($effectiveStartDate ?: now()->toDateString())->toDateString();
        $roundedRate = round($rateAmount, 2);

        $currentActiveRate = MarketStallRate::query()
            ->where('market_stall_location_id', $locationId)
            ->where('is_active', true)
            ->orderByDesc('effective_start_date')
            ->first();

        if ($currentActiveRate && (float) $currentActiveRate->rate_amount === $roundedRate) {
            return $currentActiveRate;
        }

        if ($currentActiveRate) {
            $currentStart = Carbon::parse((string) $currentActiveRate->effective_start_date);
            $computedEnd = Carbon::parse($effectiveDate)->subDay();
            if ($computedEnd->lt($currentStart)) {
                $computedEnd = $currentStart;
            }

            $currentActiveRate->update([
                'is_active' => false,
                'effective_end_date' => $computedEnd->toDateString(),
            ]);
        }

        return MarketStallRate::query()->create([
            'market_stall_location_id' => $locationId,
            'rate_amount' => $roundedRate,
            'effective_start_date' => $effectiveDate,
            'effective_end_date' => null,
            'is_active' => true,
            'created_by_user_id' => Auth::id(),
        ]);
    }
}
