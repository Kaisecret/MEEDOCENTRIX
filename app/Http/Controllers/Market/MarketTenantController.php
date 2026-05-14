<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketDueLog;
use App\Models\MarketPaymentCollection;
use App\Models\MarketStallLease;
use App\Models\MarketTenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
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

        $billingTimeline = $this->buildBillingTimelineSummary($activeLease);

        return view('market.vendor_edit', [
            'tenant' => $marketTenant,
            'activeLease' => $activeLease,
            'leaseHistory' => $leaseHistory,
            'paymentHistory' => $paymentHistory,
            'billingTimeline' => $billingTimeline,
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

    public function finalNoticePdf(MarketTenant $marketTenant)
    {
        $activeLease = $marketTenant->activeLease()
            ->with(['stall.location', 'rate'])
            ->first();

        $payload = $this->buildFinalNoticePayload($marketTenant, $activeLease);
        $filename = 'market-final-notice-' . str_pad((string) $marketTenant->id, 4, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd-His') . '.pdf';

        return Pdf::loadView('market.final_notice_pdf', $payload)
            ->setPaper('a4', 'portrait')
            ->download($filename);
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

    /**
     * @return array{
     *     has_active_lease: bool,
     *     period: string,
     *     period_label: string,
     *     cycles: int,
     *     interval_days: int,
     *     interval_label: string,
     *     start_date_label: string,
     *     first_due_label: string,
     *     next_due_label: string,
     *     days_since_start: int,
     *     due_today: bool,
     *     expected_cycles: int,
     *     paid_cycles: int,
     *     unpaid_cycles: int,
     *     unpaid_overdue_cycles: int,
     *     unpaid_preview: array<int, array{date:string,status:string,age:string,is_today:bool}>,
     *     unpaid_all: array<int, array{date:string,status:string,age:string,is_today:bool}>,
     *     unpaid_remaining: int
     * }
     */
    private function buildBillingTimelineSummary($activeLease): array
    {
        if (! $activeLease) {
            return [
                'has_active_lease' => false,
                'period' => 'monthly',
                'period_label' => 'Monthly',
                'cycles' => 1,
                'interval_days' => 30,
                'interval_label' => 'Every 30 days',
                'start_date_label' => '-',
                'first_due_label' => '-',
                'next_due_label' => '-',
                'days_since_start' => 0,
                'due_today' => false,
                'expected_cycles' => 0,
                'paid_cycles' => 0,
                'unpaid_cycles' => 0,
                'unpaid_overdue_cycles' => 0,
                'unpaid_preview' => [],
                'unpaid_all' => [],
                'unpaid_remaining' => 0,
            ];
        }

        $period = strtolower((string) ($activeLease->billing_period ?? 'monthly'));
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'monthly';
        }

        $cycles = max(1, (int) ($activeLease->billing_cycles ?? 1));
        $intervalDays = match ($period) {
            'daily' => 1 * $cycles,
            'weekly' => 7 * $cycles,
            default => 30 * $cycles,
        };

        $startAt = $activeLease->start_date instanceof Carbon
            ? $activeLease->start_date->copy()->startOfDay()
            : ($activeLease->created_at ? $activeLease->created_at->copy()->startOfDay() : null);

        if (! $startAt) {
            return [
                'has_active_lease' => true,
                'period' => $period,
                'period_label' => ucfirst($period),
                'cycles' => $cycles,
                'interval_days' => $intervalDays,
                'interval_label' => $this->formatIntervalLabel($period, $cycles, $intervalDays),
                'start_date_label' => '-',
                'first_due_label' => '-',
                'next_due_label' => '-',
                'days_since_start' => 0,
                'due_today' => false,
                'expected_cycles' => 0,
                'paid_cycles' => 0,
                'unpaid_cycles' => 0,
                'unpaid_overdue_cycles' => 0,
                'unpaid_preview' => [],
                'unpaid_all' => [],
                'unpaid_remaining' => 0,
            ];
        }

        $today = now()->startOfDay();
        $firstDueAt = $startAt->copy()->addDays($intervalDays);
        $dueDateKeys = [];
        $nextDueAt = $firstDueAt->copy();

        if ($today->gte($firstDueAt)) {
            $cursor = $firstDueAt->copy();
            $guard = 0;

            while ($cursor->lte($today) && $guard < 4000) {
                $dueDateKeys[] = $cursor->toDateString();
                $cursor->addDays($intervalDays);
                $guard++;
            }

            $nextDueAt = $cursor;
        }

        $dueLogsByDate = collect();
        if (Schema::hasTable('market_due_logs')) {
            $dueLogsByDate = MarketDueLog::query()
                ->where('market_stall_lease_id', (int) $activeLease->id)
                ->whereDate('due_date', '>=', $firstDueAt->toDateString())
                ->whereDate('due_date', '<=', $today->toDateString())
                ->get(['due_date', 'status'])
                ->keyBy(static fn (MarketDueLog $log): string => $log->due_date?->toDateString() ?? '');
        }

        $unpaidRows = [];
        $paidCycles = 0;
        $unpaidOverdueCycles = 0;
        $todayKey = $today->toDateString();

        foreach ($dueDateKeys as $dueDateKey) {
            $statusKey = strtolower((string) ($dueLogsByDate->get($dueDateKey)?->status ?? 'due'));
            $isPaid = $statusKey === 'paid';
            if ($isPaid) {
                $paidCycles++;
                continue;
            }

            $dueAt = Carbon::parse($dueDateKey)->startOfDay();
            $isToday = $dueDateKey === $todayKey;
            if (! $isToday) {
                $unpaidOverdueCycles++;
            }

            $ageLabel = $isToday
                ? 'Due today'
                : $today->diffInDays($dueAt) . ' day(s) overdue';

            $unpaidRows[] = [
                'date' => $dueAt->format('M d, Y'),
                'status' => $this->formatDueStatusLabel($statusKey),
                'age' => $ageLabel,
                'is_today' => $isToday,
                'sort_key' => $dueAt->toDateString(),
            ];
        }

        usort($unpaidRows, static function (array $left, array $right): int {
            return strcmp((string) $right['sort_key'], (string) $left['sort_key']);
        });

        $unpaidAll = array_map(static function (array $item): array {
            unset($item['sort_key']);
            return $item;
        }, $unpaidRows);
        $unpaidPreview = array_slice($unpaidAll, 0, 3);
        $unpaidRemaining = max(0, count($unpaidAll) - count($unpaidPreview));

        return [
            'has_active_lease' => true,
            'period' => $period,
            'period_label' => ucfirst($period),
            'cycles' => $cycles,
            'interval_days' => $intervalDays,
            'interval_label' => $this->formatIntervalLabel($period, $cycles, $intervalDays),
            'start_date_label' => $startAt->format('M d, Y'),
            'first_due_label' => $firstDueAt->format('M d, Y'),
            'next_due_label' => $nextDueAt->format('M d, Y'),
            'days_since_start' => max(0, $startAt->diffInDays($today)),
            'due_today' => in_array($todayKey, $dueDateKeys, true),
            'expected_cycles' => count($dueDateKeys),
            'paid_cycles' => $paidCycles,
            'unpaid_cycles' => count($unpaidRows),
            'unpaid_overdue_cycles' => $unpaidOverdueCycles,
            'unpaid_preview' => $unpaidPreview,
            'unpaid_all' => $unpaidAll,
            'unpaid_remaining' => $unpaidRemaining,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function buildFinalNoticePayload(MarketTenant $tenant, ?MarketStallLease $activeLease): array
    {
        $today = now()->startOfDay();
        $tenantName = trim((string) ($tenant->fullName() ?: 'N/A'));
        $address = trim((string) ($tenant->address ?: '-'));
        $tenantIdLabel = 'TNT-' . str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT);
        $stallNo = $activeLease?->stall?->stall_no ?: '-';
        $locationCode = (string) ($activeLease?->stall?->location?->location_code ?: '-');
        $contractNo = (string) ($activeLease?->contract_number ?: '-');
        $billingPeriod = strtolower((string) ($activeLease?->billing_period ?: 'monthly'));
        if (! in_array($billingPeriod, ['daily', 'weekly', 'monthly'], true)) {
            $billingPeriod = 'monthly';
        }

        $rateAmount = round((float) ($activeLease?->computed_rate_amount ?? $activeLease?->rate?->rate_amount ?? 0), 2);
        $dueRows = collect();

        if ($activeLease) {
            $dueRows = $this->buildUnpaidDueRowsForNotice($activeLease, $today, $rateAmount);
        }

        $grandTotal = (float) $dueRows->sum('amount_due');
        $statementMonth = $today->copy()->format('F Y');

        return [
            'generatedAt' => now(),
            'today' => $today,
            'tenant' => $tenant,
            'activeLease' => $activeLease,
            'tenantName' => $tenantName,
            'tenantIdLabel' => $tenantIdLabel,
            'address' => $address,
            'stallNo' => $stallNo,
            'locationCode' => $locationCode,
            'contractNo' => $contractNo,
            'billingPeriod' => ucfirst($billingPeriod),
            'billingCycles' => max(1, (int) ($activeLease?->billing_cycles ?? 1)),
            'rateAmount' => $rateAmount,
            'statementMonth' => $statementMonth,
            'dueRows' => $dueRows,
            'grandTotal' => $grandTotal,
        ];
    }

    /**
     * @return Collection<int, array<string,mixed>>
     */
    private function buildUnpaidDueRowsForNotice(MarketStallLease $lease, Carbon $today, float $rateAmount): Collection
    {
        $period = strtolower((string) ($lease->billing_period ?? 'monthly'));
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'monthly';
        }
        $cycles = max(1, (int) ($lease->billing_cycles ?? 1));
        $intervalDays = match ($period) {
            'daily' => 1 * $cycles,
            'weekly' => 7 * $cycles,
            default => 30 * $cycles,
        };

        $startAt = $lease->start_date instanceof Carbon
            ? $lease->start_date->copy()->startOfDay()
            : ($lease->created_at ? $lease->created_at->copy()->startOfDay() : null);
        if (! $startAt) {
            return collect();
        }

        $firstDueAt = $startAt->copy()->addDays($intervalDays);
        if ($today->lt($firstDueAt)) {
            return collect();
        }

        $dueKeys = [];
        $cursor = $firstDueAt->copy();
        $guard = 0;
        while ($cursor->lte($today) && $guard < 4000) {
            $dueKeys[] = $cursor->toDateString();
            $cursor->addDays($intervalDays);
            $guard++;
        }

        $dueLogsByDate = collect();
        if (Schema::hasTable('market_due_logs')) {
            $dueLogsByDate = MarketDueLog::query()
                ->where('market_stall_lease_id', (int) $lease->id)
                ->whereDate('due_date', '>=', $firstDueAt->toDateString())
                ->whereDate('due_date', '<=', $today->toDateString())
                ->get(['due_date', 'status'])
                ->keyBy(static fn (MarketDueLog $log): string => $log->due_date?->toDateString() ?? '');
        }

        $rows = collect();
        foreach ($dueKeys as $idx => $dueDateKey) {
            $status = strtolower((string) ($dueLogsByDate->get($dueDateKey)?->status ?? 'due'));
            if ($status === 'paid') {
                continue;
            }

            $dueAt = Carbon::parse($dueDateKey)->startOfDay();
            $daysUnpaid = max(0, $dueAt->diffInDays($today));
            $unpaidRent = $rateAmount;
            $surcharge = round($unpaidRent * 0.25, 2);
            $penaltyMonths = $daysUnpaid > 0 ? max(1, (int) ceil($daysUnpaid / 30)) : 0;
            $penalty = round($unpaidRent * 0.02 * $penaltyMonths, 2);
            $amountDue = round($unpaidRent + $surcharge + $penalty, 2);

            $rows->push([
                'posting_id' => 'DUE-' . str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT),
                'billing_period' => $dueAt->format('M d, Y'),
                'due_date_sort' => $dueAt->toDateString(),
                'rate' => $unpaidRent,
                'days_unpaid' => $daysUnpaid,
                'unpaid_rent' => $unpaidRent,
                'surcharge' => $surcharge,
                'penalty' => $penalty,
                'amount_due' => $amountDue,
                'status' => $status,
            ]);
        }

        return $rows
            ->sortByDesc('due_date_sort')
            ->map(static function (array $row): array {
                unset($row['due_date_sort']);
                return $row;
            })
            ->values();
    }

    private function formatIntervalLabel(string $period, int $cycles, int $intervalDays): string
    {
        return match ($period) {
            'daily' => 'Every ' . $cycles . ' day(s)',
            'weekly' => 'Every ' . (7 * $cycles) . ' day(s) (' . $cycles . ' week cycle)',
            default => 'Every ' . $intervalDays . ' day(s) (' . $cycles . ' month cycle)',
        };
    }

    private function formatDueStatusLabel(string $statusKey): string
    {
        return match ($statusKey) {
            'paid' => 'Paid',
            'sent' => 'Sent to collector',
            'awaiting_confirmation' => 'Awaiting confirmation',
            'missed' => 'Missed',
            default => 'Unpaid',
        };
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
