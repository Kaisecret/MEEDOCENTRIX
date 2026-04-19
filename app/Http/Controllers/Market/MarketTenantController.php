<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketPaymentCollection;
use App\Models\MarketTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketTenantController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $tenantQuery = MarketTenant::query()
            ->with([
                'activeLease.stall.location',
            ]);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $tenantQuery->where(function ($query) use ($like): void {
                $query->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('business_name', 'like', $like)
                    ->orWhere('business_type', 'like', $like)
                    ->orWhere('contact_number', 'like', $like)
                    ->orWhere('mpo_control_no', 'like', $like)
                    ->orWhereHas('activeLease.stall', function ($stallQuery) use ($like): void {
                        $stallQuery->where('stall_no', 'like', $like)
                            ->orWhereHas('location', function ($locationQuery) use ($like): void {
                                $locationQuery->where('location_code', 'like', $like)
                                    ->orWhere('location_name', 'like', $like);
                            });
                    });
            });
        }

        $tenants = $tenantQuery
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $totalTenants = (int) MarketTenant::query()->count();
        $activeTenants = (int) MarketTenant::query()
            ->whereHas('activeLease')
            ->count();

        return view('market.vendors', [
            'tenants' => $tenants,
            'search' => $search,
            'summary' => [
                'total' => $totalTenants,
                'active' => $activeTenants,
                'inactive' => max(0, $totalTenants - $activeTenants),
            ],
        ]);
    }

    public function edit(MarketTenant $marketTenant): View
    {
        $activeLease = $marketTenant->activeLease()
            ->with(['stall.location', 'rate'])
            ->first();

        $leaseHistory = $marketTenant->leases()
            ->with(['stall.location'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $totalLeases = (int) $marketTenant->leases()->count();
        $activeLeaseCount = (int) $marketTenant->leases()->where('lease_status', 'active')->count();
        $paymentHistory = MarketPaymentCollection::query()
            ->with([
                'lease.stall.location',
                'dispatchItem.dispatch.collector:id,name',
                'generatedBy:id,name',
            ])
            ->whereHas('lease', static function ($leaseQuery) use ($marketTenant): void {
                $leaseQuery->where('market_tenant_id', $marketTenant->id);
            })
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $totalPaymentCount = (int) MarketPaymentCollection::query()
            ->whereHas('lease', static function ($leaseQuery) use ($marketTenant): void {
                $leaseQuery->where('market_tenant_id', $marketTenant->id);
            })
            ->count();

        $totalPaid = (float) MarketPaymentCollection::query()
            ->whereHas('lease', static function ($leaseQuery) use ($marketTenant): void {
                $leaseQuery->where('market_tenant_id', $marketTenant->id);
            })
            ->sum('amount_paid');

        return view('market.vendor_edit', [
            'tenant' => $marketTenant,
            'activeLease' => $activeLease,
            'leaseHistory' => $leaseHistory,
            'paymentHistory' => $paymentHistory,
            'leaseSummary' => [
                'total' => $totalLeases,
                'active' => $activeLeaseCount,
                'inactive' => max(0, $totalLeases - $activeLeaseCount),
            ],
            'paymentSummary' => [
                'count' => $totalPaymentCount,
                'total_paid' => $totalPaid,
            ],
        ]);
    }

    public function update(Request $request, MarketTenant $marketTenant): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120', 'regex:/\\S/'],
            'last_name' => ['required', 'string', 'max:120', 'regex:/\\S/'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:60'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'business_type' => ['nullable', 'string', 'max:120'],
            'mpo_control_no' => ['nullable', 'string', 'max:120'],
        ]);

        $marketTenant->update($this->normalizeTenantPayload($validated));

        return redirect()
            ->route('market.vendors.edit', $marketTenant)
            ->with('status', 'Tenant record updated. Connected market tabs now show the latest tenant data.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, string|null>
     */
    private function normalizeTenantPayload(array $validated): array
    {
        $normalizeNullable = static function ($value): ?string {
            $text = trim((string) ($value ?? ''));
            return $text === '' ? null : $text;
        };

        return [
            'first_name' => trim((string) $validated['first_name']),
            'last_name' => trim((string) $validated['last_name']),
            'middle_name' => $normalizeNullable($validated['middle_name'] ?? null),
            'address' => $normalizeNullable($validated['address'] ?? null),
            'contact_number' => $normalizeNullable($validated['contact_number'] ?? null),
            'business_name' => $normalizeNullable($validated['business_name'] ?? null),
            'business_type' => $normalizeNullable($validated['business_type'] ?? null),
            'mpo_control_no' => $normalizeNullable($validated['mpo_control_no'] ?? null),
        ];
    }
}
