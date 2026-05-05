<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketPaymentCollection;
use App\Models\MarketTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class MarketTenantController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $tenantQuery = $this->buildFilteredTenantQuery($search);

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

    public function csv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('q', ''));

        $tenants = $this->buildFilteredTenantQuery($search)
            ->orderByDesc('updated_at')
            ->get();

        $filename = 'market-tenant-directory-' . now()->format('Ymd-His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($tenants, $search): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderTenantExcelHtml($tenants, $search);
        }, $filename, $headers);
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

    private function buildFilteredTenantQuery(string $search)
    {
        $tenantQuery = MarketTenant::query()
            ->with([
                'activeLease.stall.location',
            ]);

        if ($search === '') {
            return $tenantQuery;
        }

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

        return $tenantQuery;
    }

    private function renderTenantExcelHtml($tenants, string $search): string
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
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Market Tenant Directory</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Tenant Directory</x:Name>
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
    <tr><td colspan="8" class="title">Market Tenant Directory Export</td></tr>
    <tr><td colspan="8" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="8" class="meta">Search: <?= $esc($search === '' ? 'All records' : $search) ?></td></tr>
    <tr><td colspan="8" class="meta">Total Records: <?= number_format($tenants->count()) ?></td></tr>
    <tr><td colspan="8">&nbsp;</td></tr>
</table>

<table class="data">
    <thead>
    <tr>
        <th>Tenant ID</th>
        <th>Tenant / Lessee</th>
        <th>MPO Control No.</th>
        <th>Business</th>
        <th>Contact</th>
        <th>Active Stall</th>
        <th>Lease Status</th>
        <th>Updated</th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 0; foreach ($tenants as $tenant): $i++; ?>
        <?php
            $lease = $tenant->activeLease;
            $stall = $lease?->stall;
            $location = $stall?->location;
            $tenantIdLabel = 'TNT-' . str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT);
            $stallLabel = $stall ? (($stall->stall_no ?: '-') . ' / ' . (($location?->location_code ?: '-') . ' - ' . ($location?->location_name ?: '-'))) : 'No active stall';
        ?>
        <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
            <td><?= $esc($tenantIdLabel) ?></td>
            <td><?= $esc($tenant->fullName() ?: '-') ?></td>
            <td><?= $esc($tenant->mpo_control_no ?: '-') ?></td>
            <td><?= $esc(($tenant->business_name ?: '-') . ' / ' . ($tenant->business_type ?: '-')) ?></td>
            <td><?= $esc(($tenant->contact_number ?: '-') . ' / ' . ($tenant->address ?: '-')) ?></td>
            <td><?= $esc($stallLabel) ?></td>
            <td class="center"><?= $lease ? 'ACTIVE' : 'INACTIVE' ?></td>
            <td><?= $esc(optional($tenant->updated_at)->format('Y-m-d H:i')) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($tenants->isEmpty()): ?>
        <tr><td colspan="8" class="center">No tenant records found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</body>
</html>
        <?php

        return (string) ob_get_clean();
    }
}
