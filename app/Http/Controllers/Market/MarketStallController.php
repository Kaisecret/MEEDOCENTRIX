<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketStall;
use App\Models\MarketStallLease;
use App\Models\MarketStallLocation;
use App\Models\MarketStallRate;
use App\Models\MarketStallType;
use App\Models\MarketTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketStallController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const STALL_STATUSES = [
        'vacant' => 'Vacant',
        'occupied' => 'Occupied',
        'maintenance' => 'Maintenance',
        'inactive' => 'Inactive',
    ];

    /**
     * @var array<string, float>
     */
    private const BILLING_PERIOD_MULTIPLIERS = [
        'daily' => 1.0,
        'weekly' => 7.0,
        'monthly' => 30.0,
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $locationId = (int) $request->query('location_id', 0);
        $status = trim((string) $request->query('status', ''));
        $editingStallId = (int) $request->query('edit', 0);

        $stallQuery = $this->buildFilteredStallQuery($search, $locationId, $status);

        $stalls = $stallQuery
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $locations = MarketStallLocation::query()
            ->with('activeRate')
            ->where('is_active', true)
            ->orderBy('location_code')
            ->get();

        $stallTypes = MarketStallType::query()
            ->where('is_active', true)
            ->orderBy('type_name')
            ->get();

        $editingStall = null;
        if ($editingStallId > 0) {
            $editingStall = MarketStall::query()
                ->with(['activeLease.tenant', 'activeLease.rate'])
                ->find($editingStallId);
        }

        return view('market.stalls', [
            'stalls' => $stalls,
            'locations' => $locations,
            'stallTypes' => $stallTypes,
            'statusOptions' => self::STALL_STATUSES,
            'search' => $search,
            'selectedLocationId' => $locationId,
            'selectedStatus' => $status,
            'editingStall' => $editingStall,
            'summary' => [
                'total' => MarketStall::query()->count(),
                'occupied' => MarketStall::query()->where('stall_status', 'occupied')->count(),
                'vacant' => MarketStall::query()->where('stall_status', 'vacant')->count(),
                'maintenance' => MarketStall::query()->where('stall_status', 'maintenance')->count(),
            ],
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('q', ''));
        $locationId = (int) $request->query('location_id', 0);
        $status = trim((string) $request->query('status', ''));

        $stalls = $this->buildFilteredStallQuery($search, $locationId, $status)
            ->orderByDesc('id')
            ->get();

        $filename = 'market-stall-registry-' . now()->format('Ymd-His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($stalls, $search, $status): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderStallExcelHtml($stalls, $search, $status);
        }, $filename, $headers);
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'location_code' => ['required', 'string', 'max:50', 'unique:market_stall_locations,location_code'],
            'location_name' => ['required', 'string', 'max:120'],
            'zone' => ['nullable', 'string', 'max:120'],
            'floor_level' => ['nullable', 'string', 'max:60'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'initial_rate_amount' => ['required', 'numeric', 'min:0'],
            'effective_start_date' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($validated): void {
            $location = MarketStallLocation::query()->create([
                'location_code' => strtoupper(trim((string) $validated['location_code'])),
                'location_name' => trim((string) $validated['location_name']),
                'zone' => $validated['zone'] ? trim((string) $validated['zone']) : null,
                'floor_level' => $validated['floor_level'] ? trim((string) $validated['floor_level']) : null,
                'remarks' => $validated['remarks'] ? trim((string) $validated['remarks']) : null,
                'is_active' => true,
            ]);

            $effectiveDate = $validated['effective_start_date'] ?? now()->toDateString();
            $this->activateLocationRate(
                $location->id,
                (float) $validated['initial_rate_amount'],
                $effectiveDate
            );
        });

        return redirect()
            ->route('market.stalls')
            ->with('status', 'Market location and default rate registered.');
    }

    public function storeLocationRate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'market_stall_location_id' => ['required', 'integer', Rule::exists('market_stall_locations', 'id')],
            'rate_amount' => ['required', 'numeric', 'min:0'],
            'effective_start_date' => ['nullable', 'date'],
        ]);

        $effectiveDate = $validated['effective_start_date'] ?? now()->toDateString();

        $this->activateLocationRate(
            (int) $validated['market_stall_location_id'],
            (float) $validated['rate_amount'],
            $effectiveDate
        );

        return redirect()
            ->route('market.stalls')
            ->with('status', 'Location-based stall rate updated successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->stallRules($request));

        DB::transaction(function () use ($validated, $request): void {
            $stall = MarketStall::query()->create([
                'stall_no' => strtoupper(trim((string) $validated['stall_no'])),
                'market_stall_location_id' => (int) $validated['market_stall_location_id'],
                'market_stall_type_id' => (int) $validated['market_stall_type_id'],
                'dimension_sq_m' => $validated['dimension_sq_m'] ?? null,
                'description' => $validated['description'] ? trim((string) $validated['description']) : null,
                'stall_status' => trim((string) $validated['stall_status']),
                'is_billable' => $request->boolean('is_billable'),
            ]);

            $this->syncStallLease($stall, $validated);
        });

        return redirect()
            ->route('market.stalls')
            ->with('status', 'Market stall registered successfully.');
    }

    public function update(Request $request, MarketStall $marketStall): RedirectResponse
    {
        $validated = $request->validate($this->stallRules($request, $marketStall));

        DB::transaction(function () use ($marketStall, $validated, $request): void {
            $marketStall->update([
                'stall_no' => strtoupper(trim((string) $validated['stall_no'])),
                'market_stall_location_id' => (int) $validated['market_stall_location_id'],
                'market_stall_type_id' => (int) $validated['market_stall_type_id'],
                'dimension_sq_m' => $validated['dimension_sq_m'] ?? null,
                'description' => $validated['description'] ? trim((string) $validated['description']) : null,
                'stall_status' => trim((string) $validated['stall_status']),
                'is_billable' => $request->boolean('is_billable'),
            ]);

            $this->syncStallLease($marketStall, $validated);
        });

        return redirect()
            ->back()
            ->with('status', "Stall {$marketStall->stall_no} updated.");
    }

    public function destroy(MarketStall $marketStall): RedirectResponse
    {
        if ($marketStall->leases()->exists()) {
            $marketStall->update([
                'stall_status' => 'inactive',
                'is_billable' => false,
            ]);

            return redirect()
                ->back()
                ->with('status', "Stall {$marketStall->stall_no} has existing lease history and was set to inactive.");
        }

        $stallNo = $marketStall->stall_no;
        $marketStall->delete();

        return redirect()
            ->back()
            ->with('status', "Stall {$stallNo} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function stallRules(Request $request, ?MarketStall $marketStall = null): array
    {
        $stallNoRule = $marketStall
            ? Rule::unique('market_stalls', 'stall_no')->ignore($marketStall->id)
            : Rule::unique('market_stalls', 'stall_no');

        $isOccupied = trim((string) $request->input('stall_status')) === 'occupied';
        $activeLease = $marketStall?->activeLease()->with('tenant')->first();

        return [
            'stall_no' => ['required', 'string', 'max:60', $stallNoRule],
            'market_stall_location_id' => ['required', 'integer', Rule::exists('market_stall_locations', 'id')],
            'market_stall_type_id' => ['required', 'integer', Rule::exists('market_stall_types', 'id')],
            'dimension_sq_m' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'stall_status' => ['required', Rule::in(array_keys(self::STALL_STATUSES))],
            'is_billable' => ['nullable', 'boolean'],
            'rate_type_ids' => [Rule::requiredIf($isOccupied), 'nullable', 'array', 'min:1'],
            'rate_type_ids.*' => ['integer', Rule::exists('market_stall_types', 'id')],
            'billing_period' => ['nullable', Rule::in(array_keys(self::BILLING_PERIOD_MULTIPLIERS))],
            'billing_cycles' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'rate_multiplier' => ['nullable', 'numeric', 'min:0.01', 'max:100000'],
            'rate_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'contract_number' => ['nullable', 'string', 'max:90'],
            'lease_remarks' => ['nullable', 'string', 'max:1000'],

            'tenant_first_name' => [Rule::requiredIf($isOccupied && ! $activeLease), 'nullable', 'string', 'max:120'],
            'tenant_last_name' => [Rule::requiredIf($isOccupied && ! $activeLease), 'nullable', 'string', 'max:120'],
            'tenant_middle_name' => ['nullable', 'string', 'max:120'],
            'tenant_address' => ['nullable', 'string', 'max:255'],
            'tenant_contact_number' => ['nullable', 'string', 'max:60'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'business_type' => ['nullable', 'string', 'max:120'],
            'mpo_control_no' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncStallLease(MarketStall $stall, array $validated): void
    {
        $activeLease = $stall->activeLease()->with('tenant')->first();
        $isOccupied = ($validated['stall_status'] ?? '') === 'occupied';

        if (! $isOccupied) {
            if ($activeLease) {
                $activeLease->update([
                    'lease_status' => 'ended',
                    'end_date' => $validated['end_date'] ?? now()->toDateString(),
                    'remarks' => $validated['lease_remarks'] ? trim((string) $validated['lease_remarks']) : $activeLease->remarks,
                ]);
            }

            return;
        }

        $startDate = $validated['start_date'] ?? now()->toDateString();
        $billingPeriod = $this->normalizeBillingPeriod((string) ($validated['billing_period'] ?? 'monthly'));
        $billingCycles = max(1, (int) ($validated['billing_cycles'] ?? 1));
        $rateMultiplier = round(max((float) ($validated['rate_multiplier'] ?? 1), 0.01), 2);

        $selectedTypeIds = collect($validated['rate_type_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedTypeIds->isEmpty() && isset($validated['market_stall_type_id'])) {
            $selectedTypeIds = collect([(int) $validated['market_stall_type_id']]);
        }

        $selectedTypes = MarketStallType::query()
            ->whereIn('id', $selectedTypeIds->all())
            ->get(['id', 'type_name', 'default_rate', 'rate_notes']);

        $baseRateTotal = round((float) $selectedTypes->sum(fn (MarketStallType $type) => (float) ($type->default_rate ?? 0)), 2);
        $computedRate = round($baseRateTotal * $this->periodMultiplier($billingPeriod) * $billingCycles * $rateMultiplier, 2);
        $manualRate = round((float) ($validated['rate_amount'] ?? 0), 2);
        $finalRate = $manualRate > 0 ? $manualRate : $computedRate;

        $selectedTypeRates = $selectedTypes->map(static function (MarketStallType $type): array {
            return [
                'id' => $type->id,
                'name' => $type->type_name,
                'base_rate' => round((float) ($type->default_rate ?? 0), 2),
                'notes' => $type->rate_notes,
            ];
        })->values()->all();

        $rate = $this->resolveReferenceRate(
            (int) $validated['market_stall_location_id'],
            max($finalRate, 0),
            $startDate
        );

        $tenant = $this->upsertTenant($validated, $activeLease?->tenant);

        if ($activeLease) {
            $activeLease->update([
                'market_tenant_id' => $tenant->id,
                'market_stall_rate_id' => $rate->id,
                'selected_type_rates' => $selectedTypeRates,
                'billing_period' => $billingPeriod,
                'billing_cycles' => $billingCycles,
                'rate_multiplier' => $rateMultiplier,
                'computed_rate_amount' => $finalRate,
                'contract_number' => $validated['contract_number'] ? trim((string) $validated['contract_number']) : null,
                'start_date' => $startDate,
                'end_date' => $validated['end_date'] ?? null,
                'lease_status' => 'active',
                'remarks' => $validated['lease_remarks'] ? trim((string) $validated['lease_remarks']) : null,
            ]);

            return;
        }

        MarketStallLease::query()->create([
            'market_stall_id' => $stall->id,
            'market_tenant_id' => $tenant->id,
            'market_stall_rate_id' => $rate->id,
            'selected_type_rates' => $selectedTypeRates,
            'billing_period' => $billingPeriod,
            'billing_cycles' => $billingCycles,
            'rate_multiplier' => $rateMultiplier,
            'computed_rate_amount' => $finalRate,
            'contract_number' => $validated['contract_number'] ? trim((string) $validated['contract_number']) : null,
            'start_date' => $startDate,
            'end_date' => $validated['end_date'] ?? null,
            'lease_status' => 'active',
            'remarks' => $validated['lease_remarks'] ? trim((string) $validated['lease_remarks']) : null,
            'created_by_user_id' => Auth::id(),
        ]);
    }

    private function normalizeBillingPeriod(string $period): string
    {
        $normalized = strtolower(trim($period));
        return array_key_exists($normalized, self::BILLING_PERIOD_MULTIPLIERS)
            ? $normalized
            : 'monthly';
    }

    private function periodMultiplier(string $period): float
    {
        return self::BILLING_PERIOD_MULTIPLIERS[$this->normalizeBillingPeriod($period)] ?? self::BILLING_PERIOD_MULTIPLIERS['monthly'];
    }

    private function resolveReferenceRate(int $locationId, float $fallbackRate, string $effectiveStartDate): MarketStallRate
    {
        $activeRate = MarketStallRate::query()
            ->where('market_stall_location_id', $locationId)
            ->where('is_active', true)
            ->orderByDesc('effective_start_date')
            ->first();

        if ($activeRate) {
            return $activeRate;
        }

        return MarketStallRate::query()->create([
            'market_stall_location_id' => $locationId,
            'rate_amount' => round($fallbackRate, 2),
            'effective_start_date' => Carbon::parse($effectiveStartDate)->toDateString(),
            'effective_end_date' => null,
            'is_active' => true,
            'created_by_user_id' => Auth::id(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function upsertTenant(array $validated, ?MarketTenant $existingTenant = null): MarketTenant
    {
        $tenantPayload = [
            'first_name' => trim((string) ($validated['tenant_first_name'] ?? '')),
            'last_name' => trim((string) ($validated['tenant_last_name'] ?? '')),
            'middle_name' => $validated['tenant_middle_name'] ? trim((string) $validated['tenant_middle_name']) : null,
            'address' => $validated['tenant_address'] ? trim((string) $validated['tenant_address']) : null,
            'contact_number' => $validated['tenant_contact_number'] ? trim((string) $validated['tenant_contact_number']) : null,
            'business_name' => $validated['business_name'] ? trim((string) $validated['business_name']) : null,
            'business_type' => $validated['business_type'] ? trim((string) $validated['business_type']) : null,
            'mpo_control_no' => $validated['mpo_control_no'] ? trim((string) $validated['mpo_control_no']) : null,
        ];

        if ($existingTenant) {
            $existingTenant->update($tenantPayload);
            return $existingTenant;
        }

        return MarketTenant::query()->create($tenantPayload);
    }

    private function activateLocationRate(int $locationId, float $rateAmount, string $effectiveStartDate): MarketStallRate
    {
        $effectiveDate = Carbon::parse($effectiveStartDate)->toDateString();

        $currentActiveRate = MarketStallRate::query()
            ->where('market_stall_location_id', $locationId)
            ->where('is_active', true)
            ->orderByDesc('effective_start_date')
            ->first();

        if ($currentActiveRate && (float) $currentActiveRate->rate_amount === round($rateAmount, 2)) {
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
            'rate_amount' => round($rateAmount, 2),
            'effective_start_date' => $effectiveDate,
            'effective_end_date' => null,
            'is_active' => true,
            'created_by_user_id' => Auth::id(),
        ]);
    }

    private function buildFilteredStallQuery(string $search, int $locationId, string $status)
    {
        $stallQuery = MarketStall::query()
            ->with([
                'location.activeRate',
                'stallType',
                'activeLease.tenant',
                'activeLease.rate',
            ]);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $stallQuery->where(function ($query) use ($like): void {
                $query->where('stall_no', 'like', $like)
                    ->orWhereHas('location', function ($locationQuery) use ($like): void {
                        $locationQuery->where('location_code', 'like', $like)
                            ->orWhere('location_name', 'like', $like)
                            ->orWhere('zone', 'like', $like);
                    })
                    ->orWhereHas('activeLease.tenant', function ($tenantQuery) use ($like): void {
                        $tenantQuery->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('business_name', 'like', $like);
                    });
            });
        }

        if ($locationId > 0) {
            $stallQuery->where('market_stall_location_id', $locationId);
        }

        if (array_key_exists($status, self::STALL_STATUSES)) {
            $stallQuery->where('stall_status', $status);
        }

        return $stallQuery;
    }

    private function renderStallExcelHtml($stalls, string $search, string $status): string
    {
        $esc = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $css = '
            body { font-family: Calibri, "Segoe UI", Arial, sans-serif; color:#0f172a; }
            table { border-collapse: collapse; width: 100%; }
            .title { font-size:16pt; font-weight:bold; color:#0c3a5b; }
            .meta { font-size:10pt; color:#475569; }
            .data th {
                background:#155f8f; color:#ffffff; font-weight:bold;
                padding:6pt 8pt; border:1px solid #0c3a5b; text-align:left; font-size:10pt;
            }
            .data td {
                padding:5pt 8pt; border:1px solid #cbd5e1; font-size:10pt; vertical-align:top;
            }
            .data tr.alt td { background:#f8fafc; }
            .center { text-align:center; }
            .num { mso-number-format:"#,##0.00"; text-align:right; }
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Market Stall Registry</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Stall Registry</x:Name>
                    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style><?= $css ?></style>
</head>
<body>
<table>
    <tr><td colspan="9" class="title">Market Stall Registry Export</td></tr>
    <tr><td colspan="9" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="9" class="meta">Search: <?= $esc($search === '' ? 'All records' : $search) ?></td></tr>
    <tr><td colspan="9" class="meta">Status Filter: <?= $esc($status === '' ? 'All status' : (self::STALL_STATUSES[$status] ?? strtoupper($status))) ?></td></tr>
    <tr><td colspan="9" class="meta">Total Records: <?= number_format($stalls->count()) ?></td></tr>
    <tr><td colspan="9">&nbsp;</td></tr>
</table>

<table class="data">
    <thead>
    <tr>
        <th>Stall No.</th>
        <th>Location</th>
        <th>Type</th>
        <th>Current Tenant</th>
        <th>Dimension (sq.m)</th>
        <th>Rate</th>
        <th>Status</th>
        <th>Updated</th>
        <th>Business</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 0; foreach ($stalls as $stall): $i++; ?>
        <?php
            $lease = $stall->activeLease;
            $tenant = $lease?->tenant;
            $location = $stall->location;
            $rateAmount = (float) ($lease?->computed_rate_amount ?? $lease?->rate?->rate_amount ?? $location?->activeRate?->rate_amount ?? 0);
        ?>
        <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
            <td><?= $esc($stall->stall_no ?: '-') ?></td>
            <td><?= $esc(($location?->location_code ?: '-') . ' - ' . ($location?->location_name ?: '-')) ?></td>
            <td><?= $esc($stall->stallType?->type_name ?: '-') ?></td>
            <td><?= $esc($tenant ? ($tenant->fullName() ?: '-') : 'Vacant') ?></td>
            <td class="num"><?= $esc($stall->dimension_sq_m !== null ? number_format((float) $stall->dimension_sq_m, 2) : '-') ?></td>
            <td class="num"><?= number_format($rateAmount, 2) ?></td>
            <td class="center"><?= $esc(self::STALL_STATUSES[$stall->stall_status] ?? strtoupper((string) $stall->stall_status)) ?></td>
            <td><?= $esc(optional($stall->updated_at)->format('Y-m-d H:i')) ?></td>
            <td><?= $esc($tenant?->business_name ?: '-') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($stalls->isEmpty()): ?>
        <tr><td colspan="9" class="center">No stall records found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</body>
</html>
        <?php

        return (string) ob_get_clean();
    }
}
