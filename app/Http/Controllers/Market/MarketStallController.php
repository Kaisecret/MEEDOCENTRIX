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

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $locationId = (int) $request->query('location_id', 0);
        $status = trim((string) $request->query('status', ''));
        $editingStallId = (int) $request->query('edit', 0);

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
            'rate_amount' => ['required', 'numeric', 'min:0'],
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
        $rate = $this->activateLocationRate(
            (int) $validated['market_stall_location_id'],
            (float) $validated['rate_amount'],
            $startDate
        );

        $tenant = $this->upsertTenant($validated, $activeLease?->tenant);

        if ($activeLease) {
            $activeLease->update([
                'market_tenant_id' => $tenant->id,
                'market_stall_rate_id' => $rate->id,
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
            'contract_number' => $validated['contract_number'] ? trim((string) $validated['contract_number']) : null,
            'start_date' => $startDate,
            'end_date' => $validated['end_date'] ?? null,
            'lease_status' => 'active',
            'remarks' => $validated['lease_remarks'] ? trim((string) $validated['lease_remarks']) : null,
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
}
