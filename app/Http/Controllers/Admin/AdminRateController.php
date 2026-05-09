<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtriumFunctionHall;
use App\Models\CemeteryFeeRule;
use App\Models\FishportCommodity;
use App\Models\FishportCommodityClassification;
use App\Models\FishportPaymentType;
use App\Models\FishportUnit;
use App\Models\MarketStallLocation;
use App\Models\MarketStallRate;
use App\Models\MarketStallType;
use App\Models\TerminalRouteFare;
use App\Models\TerminalVehicleType;
use App\Support\AppNotificationService;
use App\Support\CemeteryFeeCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

        $fishportCommodityClassifications = FishportCommodityClassification::query()
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

        $terminalRouteFares = TerminalRouteFare::query()
            ->orderBy('vehicle_kind')
            ->orderBy('fare_amount')
            ->orderBy('sort_order')
            ->orderBy('route_name')
            ->get(['id', 'code', 'vehicle_kind', 'route_name', 'fare_amount', 'sort_order', 'is_active']);

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
                $terminalRouteFares->map(fn (TerminalRouteFare $route): array => [
                    'amount' => (float) $route->fare_amount,
                    'active' => (bool) $route->is_active,
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
            'fishportCommodityClassifications' => $fishportCommodityClassifications,
            'marketStallTypes' => $marketStallTypes,
            'marketLocations' => $marketLocations,
            'terminalRouteFares' => $terminalRouteFares,
            'atriumFunctionHalls' => $atriumFunctionHalls,
            'cemeteryFeeRules' => $cemeteryFeeRules,
            'lastUpdatedAt' => collect([
                FishportPaymentType::query()->max('updated_at'),
                MarketStallType::query()->max('updated_at'),
                CemeteryFeeRule::query()->max('updated_at'),
                TerminalRouteFare::query()->max('updated_at'),
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
            'delete_fishport_payment_type_ids' => ['nullable', 'array'],
            'delete_fishport_payment_type_ids.*' => ['integer', Rule::exists('fishport_payment_types', 'id')],
            'delete_fishport_commodity_ids' => ['nullable', 'array'],
            'delete_fishport_commodity_ids.*' => ['integer', Rule::exists('fishport_commodities', 'id')],
            'fishport_commodities' => ['required', 'array', 'min:1'],
            'fishport_commodities.*.id' => ['required', 'integer', Rule::exists('fishport_commodities', 'id')],
            'fishport_commodities.*.default_unit_id' => ['required', 'integer', Rule::exists('fishport_units', 'id')],
            'fishport_commodities.*.default_conversion' => ['required', 'numeric', 'min:0.0001'],
            'fishport_commodities.*.is_active' => ['nullable', 'boolean'],
            'delete_market_stall_type_ids' => ['nullable', 'array'],
            'delete_market_stall_type_ids.*' => ['integer', Rule::exists('market_stall_types', 'id')],
            'market_stall_types' => ['required', 'array', 'min:1'],
            'market_stall_types.*.id' => ['required', 'integer', Rule::exists('market_stall_types', 'id')],
            'market_stall_types.*.default_rate' => ['required', 'numeric', 'min:0'],
            'market_stall_types.*.rate_notes' => ['nullable', 'string', 'max:1000'],
            'market_stall_types.*.is_active' => ['nullable', 'boolean'],
            'delete_market_location_rate_ids' => ['nullable', 'array'],
            'delete_market_location_rate_ids.*' => ['integer', Rule::exists('market_stall_locations', 'id')],
            'market_location_rates' => ['required', 'array', 'min:1'],
            'market_location_rates.*.id' => ['required', 'integer', Rule::exists('market_stall_locations', 'id')],
            'market_location_rates.*.rate_amount' => ['required', 'numeric', 'min:0'],
            'market_location_rates.*.effective_start_date' => ['nullable', 'date'],
            'market_location_rates.*.is_active' => ['nullable', 'boolean'],
            'delete_terminal_vehicle_type_ids' => ['nullable', 'array'],
            'delete_terminal_vehicle_type_ids.*' => ['integer', Rule::exists('terminal_vehicle_types', 'id')],
            'terminal_vehicle_types' => ['nullable', 'array'],
            'terminal_vehicle_types.*.id' => ['required', 'integer', Rule::exists('terminal_vehicle_types', 'id')],
            'terminal_vehicle_types.*.parking_fee_per_hour' => ['required', 'numeric', 'min:0'],
            'terminal_vehicle_types.*.description' => ['nullable', 'string', 'max:1000'],
            'terminal_vehicle_types.*.is_active' => ['nullable', 'boolean'],
            'delete_terminal_route_fare_ids' => ['nullable', 'array'],
            'delete_terminal_route_fare_ids.*' => ['integer', Rule::exists('terminal_route_fares', 'id')],
            'terminal_route_fares' => ['nullable', 'array'],
            'terminal_route_fares.*.id' => ['required', 'integer', Rule::exists('terminal_route_fares', 'id')],
            'terminal_route_fares.*.vehicle_kind' => ['required', 'string', 'max:80'],
            'terminal_route_fares.*.route_name' => ['required', 'string', 'max:150'],
            'terminal_route_fares.*.fare_amount' => ['required', 'numeric', 'min:0'],
            'terminal_route_fares.*.is_active' => ['nullable', 'boolean'],
            'delete_atrium_function_hall_ids' => ['nullable', 'array'],
            'delete_atrium_function_hall_ids.*' => ['integer', Rule::exists('atrium_function_halls', 'id')],
            'atrium_function_halls' => ['required', 'array', 'min:1'],
            'atrium_function_halls.*.id' => ['required', 'integer', Rule::exists('atrium_function_halls', 'id')],
            'atrium_function_halls.*.hourly_rate' => ['required', 'numeric', 'min:0'],
            'atrium_function_halls.*.capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'atrium_function_halls.*.description' => ['nullable', 'string', 'max:1000'],
            'atrium_function_halls.*.is_active' => ['nullable', 'boolean'],
            'delete_cemetery_fee_rule_ids' => ['nullable', 'array'],
            'delete_cemetery_fee_rule_ids.*' => ['integer', Rule::exists('cemetery_fee_rules', 'id')],
            'cemetery_fee_rules' => ['required', 'array', 'min:1'],
            'cemetery_fee_rules.*.id' => ['required', 'integer', Rule::exists('cemetery_fee_rules', 'id')],
            'cemetery_fee_rules.*.amount' => ['required', 'numeric', 'min:0'],
            'cemetery_fee_rules.*.is_active' => ['nullable', 'boolean'],
        ]);

        $newFishportPaymentTypes = $this->validatedNewFishportPaymentTypes($request);
        $newFishportCommodities = $this->validatedNewFishportCommodities($request);
        $newMarketStallTypes = $this->validatedNewMarketStallTypes($request);
        $newMarketLocations = $this->validatedNewMarketLocations($request);
        $newTerminalVehicleTypes = $this->validatedNewTerminalVehicleTypes($request);
        $newTerminalRouteFares = $this->validatedNewTerminalRouteFares($request);
        $newAtriumFunctionHalls = $this->validatedNewAtriumFunctionHalls($request);
        $newCemeteryFeeRules = $this->validatedNewCemeteryFeeRules($request);
        $deleteFishportPaymentTypeIds = $this->normalizedDeleteIds((array) ($validated['delete_fishport_payment_type_ids'] ?? []));
        $deleteFishportCommodityIds = $this->normalizedDeleteIds((array) ($validated['delete_fishport_commodity_ids'] ?? []));
        $deleteMarketStallTypeIds = $this->normalizedDeleteIds((array) ($validated['delete_market_stall_type_ids'] ?? []));
        $deleteMarketLocationRateIds = $this->normalizedDeleteIds((array) ($validated['delete_market_location_rate_ids'] ?? []));
        $deleteTerminalVehicleTypeIds = $this->normalizedDeleteIds((array) ($validated['delete_terminal_vehicle_type_ids'] ?? []));
        $deleteTerminalRouteFareIds = $this->normalizedDeleteIds((array) ($validated['delete_terminal_route_fare_ids'] ?? []));
        $deleteAtriumFunctionHallIds = $this->normalizedDeleteIds((array) ($validated['delete_atrium_function_hall_ids'] ?? []));
        $deleteCemeteryFeeRuleIds = $this->normalizedDeleteIds((array) ($validated['delete_cemetery_fee_rule_ids'] ?? []));

        $deletedCounts = [
            'fishport_payment_types' => 0,
            'fishport_commodities' => 0,
            'market_stall_types' => 0,
            'market_location_rates' => 0,
            'terminal_vehicle_types' => 0,
            'terminal_route_fares' => 0,
            'atrium_function_halls' => 0,
            'cemetery_fee_rules' => 0,
        ];
        $blockedCounts = [
            'fishport_payment_types' => 0,
            'fishport_commodities' => 0,
            'market_stall_types' => 0,
            'market_location_rates' => 0,
            'terminal_vehicle_types' => 0,
            'terminal_route_fares' => 0,
            'atrium_function_halls' => 0,
            'cemetery_fee_rules' => 0,
        ];

        DB::transaction(function () use (
            $validated,
            $newFishportPaymentTypes,
            $newFishportCommodities,
            $newMarketStallTypes,
            $newMarketLocations,
            $newTerminalVehicleTypes,
            $newTerminalRouteFares,
            $newAtriumFunctionHalls,
            $newCemeteryFeeRules,
            $deleteFishportPaymentTypeIds,
            $deleteFishportCommodityIds,
            $deleteMarketStallTypeIds,
            $deleteMarketLocationRateIds,
            $deleteTerminalVehicleTypeIds,
            $deleteTerminalRouteFareIds,
            $deleteAtriumFunctionHallIds,
            $deleteCemeteryFeeRuleIds,
            &$deletedCounts,
            &$blockedCounts
        ): void {
            $skipFishportPaymentTypeIds = $deleteFishportPaymentTypeIds;
            $skipFishportCommodityIds = $deleteFishportCommodityIds;
            $skipMarketStallTypeIds = $deleteMarketStallTypeIds;
            $skipMarketLocationRateIds = $deleteMarketLocationRateIds;
            $skipTerminalVehicleTypeIds = $deleteTerminalVehicleTypeIds;
            $skipTerminalRouteFareIds = $deleteTerminalRouteFareIds;
            $skipAtriumFunctionHallIds = $deleteAtriumFunctionHallIds;
            $skipCemeteryFeeRuleIds = $deleteCemeteryFeeRuleIds;

            if ($deleteFishportPaymentTypeIds !== []) {
                $blockedDeleteIds = FishportPaymentType::query()
                    ->whereIn('id', $deleteFishportPaymentTypeIds)
                    ->whereHas('logPayments')
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $blockedCounts['fishport_payment_types'] = count($blockedDeleteIds);
                $deletableIds = array_values(array_diff($deleteFishportPaymentTypeIds, $blockedDeleteIds));

                if ($deletableIds !== []) {
                    $deletedCounts['fishport_payment_types'] = FishportPaymentType::query()
                        ->whereIn('id', $deletableIds)
                        ->delete();
                }
            }

            if ($deleteFishportCommodityIds !== []) {
                $blockedDeleteIds = FishportCommodity::query()
                    ->whereIn('id', $deleteFishportCommodityIds)
                    ->whereHas('logItems')
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $blockedCounts['fishport_commodities'] = count($blockedDeleteIds);
                $deletableIds = array_values(array_diff($deleteFishportCommodityIds, $blockedDeleteIds));

                if ($deletableIds !== []) {
                    $deletedCounts['fishport_commodities'] = FishportCommodity::query()
                        ->whereIn('id', $deletableIds)
                        ->delete();
                }
            }

            if ($deleteMarketStallTypeIds !== []) {
                $blockedDeleteIds = MarketStallType::query()
                    ->whereIn('id', $deleteMarketStallTypeIds)
                    ->whereHas('stalls')
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $blockedCounts['market_stall_types'] = count($blockedDeleteIds);
                $deletableIds = array_values(array_diff($deleteMarketStallTypeIds, $blockedDeleteIds));

                if ($deletableIds !== []) {
                    $deletedCounts['market_stall_types'] = MarketStallType::query()
                        ->whereIn('id', $deletableIds)
                        ->delete();
                }
            }

            if ($deleteMarketLocationRateIds !== []) {
                $blockedDeleteIds = MarketStallLocation::query()
                    ->whereIn('id', $deleteMarketLocationRateIds)
                    ->where(function ($query): void {
                        $query->whereHas('stalls')
                            ->orWhereHas('rates.leases');
                    })
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $blockedCounts['market_location_rates'] = count($blockedDeleteIds);
                $deletableIds = array_values(array_diff($deleteMarketLocationRateIds, $blockedDeleteIds));

                if ($deletableIds !== []) {
                    $deletedCounts['market_location_rates'] = MarketStallLocation::query()
                        ->whereIn('id', $deletableIds)
                        ->delete();
                }
            }

            if ($deleteTerminalVehicleTypeIds !== []) {
                $blockedDeleteIds = TerminalVehicleType::query()
                    ->whereIn('id', $deleteTerminalVehicleTypeIds)
                    ->whereHas('vehicles')
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $blockedCounts['terminal_vehicle_types'] = count($blockedDeleteIds);
                $deletableIds = array_values(array_diff($deleteTerminalVehicleTypeIds, $blockedDeleteIds));

                if ($deletableIds !== []) {
                    $deletedCounts['terminal_vehicle_types'] = TerminalVehicleType::query()
                        ->whereIn('id', $deletableIds)
                        ->delete();
                }
            }

            if ($deleteTerminalRouteFareIds !== []) {
                $deletedCounts['terminal_route_fares'] = TerminalRouteFare::query()
                    ->whereIn('id', $deleteTerminalRouteFareIds)
                    ->delete();
            }

            if ($deleteAtriumFunctionHallIds !== []) {
                $blockedDeleteIds = AtriumFunctionHall::query()
                    ->whereIn('id', $deleteAtriumFunctionHallIds)
                    ->whereHas('events')
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $blockedCounts['atrium_function_halls'] = count($blockedDeleteIds);
                $deletableIds = array_values(array_diff($deleteAtriumFunctionHallIds, $blockedDeleteIds));

                if ($deletableIds !== []) {
                    $deletedCounts['atrium_function_halls'] = AtriumFunctionHall::query()
                        ->whereIn('id', $deletableIds)
                        ->delete();
                }
            }

            if ($deleteCemeteryFeeRuleIds !== []) {
                $deletedCounts['cemetery_fee_rules'] = CemeteryFeeRule::query()
                    ->whereIn('id', $deleteCemeteryFeeRuleIds)
                    ->delete();
            }

            foreach ($validated['fishport_payment_types'] as $row) {
                if (in_array((int) $row['id'], $skipFishportPaymentTypeIds, true)) {
                    continue;
                }

                FishportPaymentType::query()->whereKey((int) $row['id'])->update([
                    'default_fee' => round((float) $row['default_fee'], 2),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['fishport_commodities'] as $row) {
                if (in_array((int) $row['id'], $skipFishportCommodityIds, true)) {
                    continue;
                }

                FishportCommodity::query()->whereKey((int) $row['id'])->update([
                    'default_unit_id' => (int) $row['default_unit_id'],
                    'default_conversion' => round((float) $row['default_conversion'], 4),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['market_stall_types'] as $row) {
                if (in_array((int) $row['id'], $skipMarketStallTypeIds, true)) {
                    continue;
                }

                MarketStallType::query()->whereKey((int) $row['id'])->update([
                    'default_rate' => round((float) $row['default_rate'], 2),
                    'rate_notes' => trim((string) ($row['rate_notes'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['market_location_rates'] as $row) {
                $locationId = (int) $row['id'];
                if (in_array($locationId, $skipMarketLocationRateIds, true)) {
                    continue;
                }

                MarketStallLocation::query()->whereKey($locationId)->update([
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);

                $this->activateLocationRate(
                    $locationId,
                    (float) $row['rate_amount'],
                    (string) ($row['effective_start_date'] ?? now()->toDateString())
                );
            }

            foreach (($validated['terminal_vehicle_types'] ?? []) as $row) {
                if (in_array((int) $row['id'], $skipTerminalVehicleTypeIds, true)) {
                    continue;
                }

                TerminalVehicleType::query()->whereKey((int) $row['id'])->update([
                    'parking_fee_per_hour' => round((float) $row['parking_fee_per_hour'], 2),
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach (($validated['terminal_route_fares'] ?? []) as $row) {
                if (in_array((int) $row['id'], $skipTerminalRouteFareIds, true)) {
                    continue;
                }

                TerminalRouteFare::query()->whereKey((int) $row['id'])->update([
                    'vehicle_kind' => trim((string) $row['vehicle_kind']),
                    'route_name' => trim((string) $row['route_name']),
                    'fare_amount' => round((float) $row['fare_amount'], 2),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['atrium_function_halls'] as $row) {
                if (in_array((int) $row['id'], $skipAtriumFunctionHallIds, true)) {
                    continue;
                }

                AtriumFunctionHall::query()->whereKey((int) $row['id'])->update([
                    'capacity' => isset($row['capacity']) ? (int) $row['capacity'] : null,
                    'hourly_rate' => round((float) $row['hourly_rate'], 2),
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]);
            }

            foreach ($validated['cemetery_fee_rules'] as $row) {
                if (in_array((int) $row['id'], $skipCemeteryFeeRuleIds, true)) {
                    continue;
                }

                CemeteryFeeRule::query()->whereKey((int) $row['id'])->update([
                    'amount' => round((float) $row['amount'], 2),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                    'updated_by_user_id' => Auth::id(),
                ]);
            }

            foreach ($newFishportPaymentTypes as $row) {
                FishportPaymentType::query()->create([
                    'code' => strtoupper(trim((string) $row['code'])),
                    'name' => trim((string) $row['name']),
                    'default_fee' => round((float) $row['default_fee'], 2),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);
            }

            foreach ($newFishportCommodities as $row) {
                FishportCommodity::query()->create([
                    'name' => trim((string) $row['name']),
                    'classification_id' => (int) $row['classification_id'],
                    'default_unit_id' => (int) $row['default_unit_id'],
                    'default_conversion' => round((float) $row['default_conversion'], 4),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);
            }

            foreach ($newMarketStallTypes as $row) {
                MarketStallType::query()->create([
                    'type_name' => trim((string) $row['type_name']),
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'default_rate' => round((float) $row['default_rate'], 2),
                    'rate_notes' => trim((string) ($row['rate_notes'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);
            }

            foreach ($newMarketLocations as $row) {
                $location = MarketStallLocation::query()->create([
                    'location_code' => strtoupper(trim((string) $row['location_code'])),
                    'location_name' => trim((string) $row['location_name']),
                    'zone' => trim((string) ($row['zone'] ?? '')) ?: null,
                    'floor_level' => trim((string) ($row['floor_level'] ?? '')) ?: null,
                    'remarks' => trim((string) ($row['remarks'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);

                $this->activateLocationRate(
                    (int) $location->id,
                    (float) $row['rate_amount'],
                    (string) ($row['effective_start_date'] ?? now()->toDateString())
                );
            }

            foreach ($newTerminalVehicleTypes as $row) {
                TerminalVehicleType::query()->create([
                    'code' => strtoupper(trim((string) $row['code'])),
                    'name' => trim((string) $row['name']),
                    'parking_fee_per_hour' => round((float) $row['parking_fee_per_hour'], 2),
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);
            }

            $maxRouteSortOrder = (int) TerminalRouteFare::query()->max('sort_order');
            foreach ($newTerminalRouteFares as $row) {
                $maxRouteSortOrder += 10;

                TerminalRouteFare::query()->create([
                    'code' => strtolower(trim((string) $row['code'])),
                    'vehicle_kind' => trim((string) $row['vehicle_kind']),
                    'route_name' => trim((string) $row['route_name']),
                    'fare_amount' => round((float) $row['fare_amount'], 2),
                    'sort_order' => $maxRouteSortOrder,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);
            }

            foreach ($newAtriumFunctionHalls as $row) {
                AtriumFunctionHall::query()->create([
                    'code' => strtoupper(trim((string) $row['code'])),
                    'name' => trim((string) $row['name']),
                    'capacity' => isset($row['capacity']) && $row['capacity'] !== '' ? (int) $row['capacity'] : null,
                    'hourly_rate' => round((float) $row['hourly_rate'], 2),
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);
            }

            $maxSortOrder = (int) CemeteryFeeRule::query()->max('sort_order');
            foreach ($newCemeteryFeeRules as $row) {
                $maxSortOrder += 10;

                CemeteryFeeRule::query()->create([
                    'fee_key' => strtolower(trim((string) $row['fee_key'])),
                    'label' => trim((string) $row['label']),
                    'description' => trim((string) ($row['description'] ?? '')) ?: null,
                    'amount' => round((float) $row['amount'], 2),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'sort_order' => isset($row['sort_order']) && $row['sort_order'] !== ''
                        ? (int) $row['sort_order']
                        : $maxSortOrder,
                    'updated_by_user_id' => Auth::id(),
                ]);
            }
        });

        CemeteryFeeCalculator::flushCache();
        $statusMessage = $this->buildStatusMessage($deletedCounts, $blockedCounts);

        AppNotificationService::notifyRateAndFeeUpdated(
            changeSummary: $statusMessage,
            createdByUserId: Auth::id(),
            actorName: (string) ($request->user()?->name ?? 'Administrator')
        );

        return redirect()
            ->route('admin.rates')
            ->with('status', $statusMessage);
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedNewFishportPaymentTypes(Request $request): array
    {
        $rows = $this->normalizeNewRows((array) $request->input('new_fishport_payment_types', []), [
            'code', 'name', 'default_fee', 'is_active',
        ]);

        if ($rows === []) {
            return [];
        }

        return Validator::make(
            ['rows' => $rows],
            [
                'rows' => ['array'],
                'rows.*.code' => ['required', 'string', 'max:60', 'unique:fishport_payment_types,code'],
                'rows.*.name' => ['required', 'string', 'max:150', 'unique:fishport_payment_types,name'],
                'rows.*.default_fee' => ['required', 'numeric', 'min:0'],
                'rows.*.is_active' => ['nullable', 'boolean'],
            ]
        )->validated()['rows'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedNewFishportCommodities(Request $request): array
    {
        $rows = $this->normalizeNewRows((array) $request->input('new_fishport_commodities', []), [
            'name', 'classification_id', 'default_unit_id', 'default_conversion', 'is_active',
        ]);

        if ($rows === []) {
            return [];
        }

        return Validator::make(
            ['rows' => $rows],
            [
                'rows' => ['array'],
                'rows.*.name' => ['required', 'string', 'max:150', 'unique:fishport_commodities,name'],
                'rows.*.classification_id' => ['required', 'integer', Rule::exists('fishport_commodity_classifications', 'id')],
                'rows.*.default_unit_id' => ['required', 'integer', Rule::exists('fishport_units', 'id')],
                'rows.*.default_conversion' => ['required', 'numeric', 'min:0.0001'],
                'rows.*.is_active' => ['nullable', 'boolean'],
            ]
        )->validated()['rows'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedNewMarketStallTypes(Request $request): array
    {
        $rows = $this->normalizeNewRows((array) $request->input('new_market_stall_types', []), [
            'type_name', 'description', 'default_rate', 'rate_notes', 'is_active',
        ]);

        if ($rows === []) {
            return [];
        }

        return Validator::make(
            ['rows' => $rows],
            [
                'rows' => ['array'],
                'rows.*.type_name' => ['required', 'string', 'max:100', 'unique:market_stall_types,type_name'],
                'rows.*.description' => ['nullable', 'string', 'max:255'],
                'rows.*.default_rate' => ['required', 'numeric', 'min:0'],
                'rows.*.rate_notes' => ['nullable', 'string', 'max:1000'],
                'rows.*.is_active' => ['nullable', 'boolean'],
            ]
        )->validated()['rows'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedNewMarketLocations(Request $request): array
    {
        $rows = $this->normalizeNewRows((array) $request->input('new_market_location_rates', []), [
            'location_code', 'location_name', 'zone', 'floor_level', 'remarks', 'rate_amount', 'effective_start_date', 'is_active',
        ]);

        if ($rows === []) {
            return [];
        }

        return Validator::make(
            ['rows' => $rows],
            [
                'rows' => ['array'],
                'rows.*.location_code' => ['required', 'string', 'max:50', 'unique:market_stall_locations,location_code'],
                'rows.*.location_name' => ['required', 'string', 'max:120'],
                'rows.*.zone' => ['nullable', 'string', 'max:120'],
                'rows.*.floor_level' => ['nullable', 'string', 'max:60'],
                'rows.*.remarks' => ['nullable', 'string', 'max:1000'],
                'rows.*.rate_amount' => ['required', 'numeric', 'min:0'],
                'rows.*.effective_start_date' => ['nullable', 'date'],
                'rows.*.is_active' => ['nullable', 'boolean'],
            ]
        )->validated()['rows'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedNewTerminalVehicleTypes(Request $request): array
    {
        $rows = $this->normalizeNewRows((array) $request->input('new_terminal_vehicle_types', []), [
            'code', 'name', 'parking_fee_per_hour', 'description', 'is_active',
        ]);

        if ($rows === []) {
            return [];
        }

        return Validator::make(
            ['rows' => $rows],
            [
                'rows' => ['array'],
                'rows.*.code' => ['required', 'string', 'max:30', 'unique:terminal_vehicle_types,code'],
                'rows.*.name' => ['required', 'string', 'max:120'],
                'rows.*.parking_fee_per_hour' => ['required', 'numeric', 'min:0'],
                'rows.*.description' => ['nullable', 'string', 'max:1000'],
                'rows.*.is_active' => ['nullable', 'boolean'],
            ]
        )->validated()['rows'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedNewTerminalRouteFares(Request $request): array
    {
        $rows = $this->normalizeNewRows((array) $request->input('new_terminal_route_fares', []), [
            'code', 'vehicle_kind', 'route_name', 'fare_amount', 'is_active',
        ]);

        if ($rows === []) {
            return [];
        }

        return Validator::make(
            ['rows' => $rows],
            [
                'rows' => ['array'],
                'rows.*.code' => ['required', 'string', 'max:80', 'unique:terminal_route_fares,code'],
                'rows.*.vehicle_kind' => ['required', 'string', 'max:80'],
                'rows.*.route_name' => ['required', 'string', 'max:150'],
                'rows.*.fare_amount' => ['required', 'numeric', 'min:0'],
                'rows.*.is_active' => ['nullable', 'boolean'],
            ]
        )->validated()['rows'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedNewAtriumFunctionHalls(Request $request): array
    {
        $rows = $this->normalizeNewRows((array) $request->input('new_atrium_function_halls', []), [
            'code', 'name', 'capacity', 'hourly_rate', 'description', 'is_active',
        ]);

        if ($rows === []) {
            return [];
        }

        return Validator::make(
            ['rows' => $rows],
            [
                'rows' => ['array'],
                'rows.*.code' => ['required', 'string', 'max:30', 'unique:atrium_function_halls,code'],
                'rows.*.name' => ['required', 'string', 'max:150'],
                'rows.*.capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
                'rows.*.hourly_rate' => ['required', 'numeric', 'min:0'],
                'rows.*.description' => ['nullable', 'string', 'max:1000'],
                'rows.*.is_active' => ['nullable', 'boolean'],
            ]
        )->validated()['rows'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedNewCemeteryFeeRules(Request $request): array
    {
        $rows = $this->normalizeNewRows((array) $request->input('new_cemetery_fee_rules', []), [
            'fee_key', 'label', 'description', 'amount', 'sort_order', 'is_active',
        ]);

        if ($rows === []) {
            return [];
        }

        return Validator::make(
            ['rows' => $rows],
            [
                'rows' => ['array'],
                'rows.*.fee_key' => ['required', 'string', 'max:100', 'unique:cemetery_fee_rules,fee_key'],
                'rows.*.label' => ['required', 'string', 'max:160'],
                'rows.*.description' => ['nullable', 'string', 'max:1000'],
                'rows.*.amount' => ['required', 'numeric', 'min:0'],
                'rows.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
                'rows.*.is_active' => ['nullable', 'boolean'],
            ]
        )->validated()['rows'] ?? [];
    }

    /**
     * @param array<int|string, mixed> $rows
     * @param array<int, string> $keys
     * @return array<int, array<string, mixed>>
     */
    private function normalizeNewRows(array $rows, array $keys): array
    {
        return collect($rows)
            ->map(static function ($row) use ($keys): array {
                $safeRow = is_array($row) ? $row : [];
                $normalized = [];

                foreach ($keys as $key) {
                    $value = $safeRow[$key] ?? null;
                    if (is_string($value)) {
                        $value = trim($value);
                    }
                    $normalized[$key] = $value;
                }

                return $normalized;
            })
            ->filter(static function (array $row): bool {
                foreach ($row as $value) {
                    if ($value !== null && $value !== '') {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();
    }

    /**
     * @param array<string, int> $ids
     * @return array<int, int>
     */
    private function normalizedDeleteIds(array $ids): array
    {
        return collect($ids)
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<string, int> $deletedCounts
     * @param array<string, int> $blockedCounts
     */
    private function buildStatusMessage(array $deletedCounts, array $blockedCounts): string
    {
        $message = 'Rate and fee settings updated across all departments.';
        $labels = [
            'fishport_payment_types' => 'Fishport payment type(s)',
            'fishport_commodities' => 'Fishport commodit(ies)',
            'market_stall_types' => 'Market stall type(s)',
            'market_location_rates' => 'Market location rate row(s)',
            'terminal_vehicle_types' => 'Terminal vehicle type(s)',
            'terminal_route_fares' => 'Terminal route/operator fare row(s)',
            'atrium_function_halls' => 'Atrium hall(s)',
            'cemetery_fee_rules' => 'Cemetery fee rule(s)',
        ];

        $deletedParts = [];
        foreach ($labels as $key => $label) {
            $count = $deletedCounts[$key] ?? 0;
            if ($count > 0) {
                $deletedParts[] = $count . ' ' . $label;
            }
        }
        if ($deletedParts !== []) {
            $message .= ' Deleted: ' . implode(', ', $deletedParts) . '.';
        }

        $blockedParts = [];
        foreach ($labels as $key => $label) {
            $count = $blockedCounts[$key] ?? 0;
            if ($count > 0) {
                $blockedParts[] = $count . ' ' . $label;
            }
        }
        if ($blockedParts !== []) {
            $message .= ' Not deleted because already in use: ' . implode(', ', $blockedParts) . '.';
        }

        return $message;
    }
}
