<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminTransactionController extends Controller
{
    /**
     * @var array<string, array<string, string>>
     */
    private const DEPARTMENTS = [
        'fishport' => ['name' => 'Fishport', 'icon' => 'fas fa-ship', 'color' => '#2563eb'],
        'market' => ['name' => 'Public Market', 'icon' => 'fas fa-store', 'color' => '#0f766e'],
        'cemetery' => ['name' => 'Cemetery', 'icon' => 'fas fa-cross', 'color' => '#7c3aed'],
        'terminal' => ['name' => 'Terminal', 'icon' => 'fas fa-bus', 'color' => '#ea580c'],
        'atrium' => ['name' => 'Atrium Hall', 'icon' => 'fas fa-building-columns', 'color' => '#0891b2'],
    ];

    /**
     * @var array<int, int>
     */
    private const PER_PAGE_OPTIONS = [25, 50, 100];

    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $rows = $this->getRows($filters['from'], $filters['to']);
        $filteredRows = $this->applyFilters($rows, $filters);

        $transactions = $this->paginateRows($filteredRows, $filters['per_page'], $request);
        $summary = $this->buildSummary($filteredRows);

        return view('admin.transactions', [
            'departments' => self::DEPARTMENTS,
            'filters' => $filters,
            'statusOptions' => [
                'all' => 'All Status',
                'paid' => 'Paid / Collected',
                'pending' => 'Pending / Active',
                'partial' => 'Partial',
                'cancelled' => 'Cancelled / Rejected',
            ],
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'transactions' => $transactions,
            'summary' => $summary,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $rows = $this->applyFilters($this->getRows($filters['from'], $filters['to']), $filters);

        $filename = 'admin-transactions-' . now()->format('Ymd-His') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($rows, $filters): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderExcelHtml($rows, $filters);
        }, $filename, $headers);
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @param array<string, mixed> $filters
     */
    private function renderExcelHtml(Collection $rows, array $filters): string
    {
        $esc = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $css = '
            body { font-family: Calibri, "Segoe UI", Arial, sans-serif; color:#0f172a; }
            table { border-collapse: collapse; width: 100%; }
            .title { font-size:18pt; font-weight:bold; color:#0c3a5b; }
            .subtitle { font-size:11pt; color:#475569; }
            .meta { font-size:10pt; color:#475569; }
            .section-title { background:#0c3a5b; color:#ffffff; font-weight:bold; padding:6pt 10pt; font-size:11pt; letter-spacing:1pt; }
            .data th { background:#155f8f; color:#ffffff; font-weight:bold; padding:6pt 8pt; border:1px solid #0c3a5b; text-align:left; font-size:10pt; }
            .data td { padding:5pt 8pt; border:1px solid #cbd5e1; font-size:10pt; vertical-align:top; }
            .data tr.alt td { background:#f8fafc; }
            .num { mso-number-format:"#,##0.00"; text-align:right; }
            .text { mso-number-format:"\@"; }
            .paid { color:#047857; font-weight:bold; }
            .pending { color:#b45309; font-weight:bold; }
            .cancelled { color:#b91c1c; font-weight:bold; }
            .partial { color:#6d28d9; font-weight:bold; }
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Admin Transactions Export</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Transactions</x:Name>
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
    <tr><td colspan="8" class="title">Master Transaction Ledger</td></tr>
    <tr><td colspan="8" class="subtitle">All departments transaction export</td></tr>
    <tr><td colspan="8" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="8" class="meta">Range: <?= $esc($filters['from']->format('F d, Y')) ?> to <?= $esc($filters['to']->format('F d, Y')) ?></td></tr>
    <tr><td colspan="8">&nbsp;</td></tr>
</table>

<table>
    <tr><td colspan="8" class="section-title">TRANSACTIONS</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th style="width:160px;">Date Time</th>
            <th style="width:120px;">Department</th>
            <th style="width:140px;">Reference</th>
            <th style="width:140px;">Type</th>
            <th style="width:280px;">Description</th>
            <th style="width:200px;">Person/Payer</th>
            <th style="width:100px;">Amount</th>
            <th style="width:110px;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($rows as $row): $i++; ?>
            <?php
                $statusClass = match ((string) $row['status_key']) {
                    'paid' => 'paid',
                    'partial' => 'partial',
                    'cancelled' => 'cancelled',
                    default => 'pending',
                };
            ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td class="text"><?= $esc($row['occurred_at']->format('m/d/Y h:i A')) ?></td>
                <td><?= $esc($row['department_name']) ?></td>
                <td class="text"><?= $esc($row['reference']) ?></td>
                <td><?= $esc($row['source']) ?></td>
                <td><?= $esc($row['description']) ?></td>
                <td><?= $esc($row['person']) ?></td>
                <td class="num"><?= number_format((float) $row['amount'], 2, '.', '') ?></td>
                <td class="<?= $statusClass ?>"><?= $esc($row['status_label']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows->isEmpty()): ?>
            <tr><td colspan="8" style="text-align:center;color:#64748b;font-style:italic;">No transactions found for the selected filters.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFilters(Request $request): array
    {
        $today = today();
        $start = $this->safeDate((string) $request->query('from'), $today->copy()->startOfMonth())->startOfDay();
        $end = $this->safeDate((string) $request->query('to'), $today->copy())->endOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end->startOfDay(), $start->endOfDay()];
        }

        $department = strtolower(trim((string) $request->query('department', 'all')));
        if ($department !== 'all' && ! array_key_exists($department, self::DEPARTMENTS)) {
            $department = 'all';
        }

        $status = strtolower(trim((string) $request->query('status', 'all')));
        if (! in_array($status, ['all', 'paid', 'pending', 'partial', 'cancelled'], true)) {
            $status = 'all';
        }

        $perPage = (int) $request->query('per_page', self::PER_PAGE_OPTIONS[0]);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        return [
            'q' => trim((string) $request->query('q', '')),
            'department' => $department,
            'status' => $status,
            'from' => $start,
            'to' => $end,
            'from_input' => $start->toDateString(),
            'to_input' => $end->toDateString(),
            'per_page' => $perPage,
        ];
    }

    private function safeDate(string $value, Carbon $fallback): Carbon
    {
        if (trim($value) === '') {
            return $fallback->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return $fallback->copy();
        }
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getRows(Carbon $from, Carbon $to): Collection
    {
        return collect()
            ->merge($this->fishportRows($from, $to))
            ->merge($this->marketRows($from, $to))
            ->merge($this->cemeteryRows($from, $to))
            ->merge($this->terminalRows($from, $to))
            ->merge($this->atriumRows($from, $to))
            ->sortByDesc(static fn (array $row): int => $row['occurred_at']->getTimestamp())
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fishportRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('fishport_payment_records')) {
            return collect();
        }

        return DB::table('fishport_payment_records as payment')
            ->leftJoin('fishport_logs as log', 'log.id', '=', 'payment.fishport_log_id')
            ->leftJoin('fishport_vessels as vessel', 'vessel.id', '=', 'log.fishport_vessel_id')
            ->select([
                'payment.id',
                'payment.payment_number',
                'payment.total_amount',
                'payment.payer_name',
                'payment.generated_at',
                'payment.created_at',
                'log.log_number',
                'log.is_paid',
                'vessel.name as vessel_name',
            ])
            ->whereRaw('DATE(COALESCE(payment.generated_at, payment.created_at)) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromBoolean((bool) ($row->is_paid ?? false));
                $description = trim('Vessel: ' . ((string) ($row->vessel_name ?: 'Unknown')) . ' | Log: ' . ((string) ($row->log_number ?: 'N/A')));

                return $this->baseRow([
                    'department_code' => 'fishport',
                    'source' => 'Fishport Payment',
                    'reference' => (string) ($row->payment_number ?: ('FPR-' . $row->id)),
                    'description' => $description,
                    'person' => (string) ($row->payer_name ?: ($row->vessel_name ?: 'N/A')),
                    'amount' => (float) $row->total_amount,
                    'occurred_at' => $row->generated_at ?? $row->created_at,
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                ]);
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function marketRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('market_payment_collections')) {
            return collect();
        }

        return DB::table('market_payment_collections as payment')
            ->leftJoin('market_stall_leases as lease', 'lease.id', '=', 'payment.market_stall_lease_id')
            ->leftJoin('market_stalls as stall', 'stall.id', '=', 'lease.market_stall_id')
            ->leftJoin('market_tenants as tenant', 'tenant.id', '=', 'lease.market_tenant_id')
            ->leftJoin('collection_dispatch_items as dispatch', 'dispatch.id', '=', 'payment.collection_dispatch_item_id')
            ->select([
                'payment.id',
                'payment.payment_number',
                'payment.amount_paid',
                'payment.payer_name',
                'payment.payment_date',
                'payment.created_at',
                'lease.contract_number',
                'stall.stall_no',
                'tenant.first_name',
                'tenant.last_name',
                'dispatch.status as dispatch_status',
            ])
            ->whereRaw('DATE(COALESCE(payment.payment_date, payment.created_at)) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $tenantName = trim(((string) ($row->first_name ?? '')) . ' ' . ((string) ($row->last_name ?? '')));
                $status = $this->normalizeStatus((string) ($row->dispatch_status ?: 'collected'));
                $description = 'Stall ' . ((string) ($row->stall_no ?: 'N/A'));
                if (! empty($row->contract_number)) {
                    $description .= ' | Contract ' . $row->contract_number;
                }

                return $this->baseRow([
                    'department_code' => 'market',
                    'source' => 'Market Collection',
                    'reference' => (string) ($row->payment_number ?: ('MPC-' . $row->id)),
                    'description' => $description,
                    'person' => (string) ($row->payer_name ?: ($tenantName !== '' ? $tenantName : 'N/A')),
                    'amount' => (float) $row->amount_paid,
                    'occurred_at' => $row->payment_date ?? $row->created_at,
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                ]);
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function cemeteryRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('cemetery_payment_collections')) {
            return collect();
        }

        return DB::table('cemetery_payment_collections as payment')
            ->leftJoin('cemetery_transactions as tx', 'tx.id', '=', 'payment.cemetery_transaction_id')
            ->select([
                'payment.id',
                'payment.payment_no',
                'payment.amount_paid',
                'payment.payment_date',
                'payment.created_at',
                'payment.payment_status',
                'tx.transaction_no',
                'tx.deceased_name',
                'tx.plot_reference',
            ])
            ->whereRaw('DATE(COALESCE(payment.payment_date, payment.created_at)) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->normalizeStatus((string) ($row->payment_status ?: 'pending'));
                $description = trim('Txn ' . ((string) ($row->transaction_no ?: 'N/A')) . ' | ' . ((string) ($row->plot_reference ?: 'No plot ref')));

                return $this->baseRow([
                    'department_code' => 'cemetery',
                    'source' => 'Cemetery Payment',
                    'reference' => (string) ($row->payment_no ?: ('CMP-' . $row->id)),
                    'description' => $description,
                    'person' => (string) ($row->deceased_name ?: 'N/A'),
                    'amount' => (float) $row->amount_paid,
                    'occurred_at' => $row->payment_date ?? $row->created_at,
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                ]);
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function terminalRows(Carbon $from, Carbon $to): Collection
    {
        $rows = collect();

        if (Schema::hasTable('terminal_parking_payments')) {
            $rows = $rows->merge(
                DB::table('terminal_parking_payments as payment')
                    ->leftJoin('terminal_parking_logs as log', 'log.id', '=', 'payment.terminal_parking_log_id')
                    ->leftJoin('terminal_vehicles as vehicle', 'vehicle.id', '=', 'log.terminal_vehicle_id')
                    ->select([
                        'payment.id',
                        'payment.or_number',
                        'payment.paid_amount',
                        'payment.payment_date',
                        'payment.payment_status',
                        'log.log_number',
                        'vehicle.plate_number',
                        'vehicle.operator_name',
                    ])
                    ->whereRaw('DATE(payment.payment_date) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
                    ->get()
                    ->map(function (object $row): array {
                        $status = $this->normalizeStatus((string) ($row->payment_status ?: 'paid'));
                        $description = trim('Parking log ' . ((string) ($row->log_number ?: 'N/A')) . ' | Plate ' . ((string) ($row->plate_number ?: 'N/A')));

                        return $this->baseRow([
                            'department_code' => 'terminal',
                            'source' => 'Terminal Parking Payment',
                            'reference' => (string) ($row->or_number ?: ('TPP-' . $row->id)),
                            'description' => $description,
                            'person' => (string) ($row->operator_name ?: ($row->plate_number ?: 'N/A')),
                            'amount' => (float) $row->paid_amount,
                            'occurred_at' => $row->payment_date,
                            'status_key' => $status['key'],
                            'status_label' => $status['label'],
                        ]);
                    })
            );
        }

        if (Schema::hasTable('terminal_quick_payments')) {
            $hasPaidAt = Schema::hasColumn('terminal_quick_payments', 'paid_at');
            $hasIsPaid = Schema::hasColumn('terminal_quick_payments', 'is_paid');
            $dateExpr = $hasPaidAt ? 'COALESCE(paid_at, payment_date, created_at)' : 'COALESCE(payment_date, created_at)';

            $rows = $rows->merge(
                DB::table('terminal_quick_payments as payment')
                    ->selectRaw("payment.id, payment.payer_name, payment.total_payment, {$dateExpr} as occurred_at, payment.remarks" . ($hasIsPaid ? ', payment.is_paid' : ''))
                    ->whereRaw("DATE({$dateExpr}) BETWEEN ? AND ?", [$from->toDateString(), $to->toDateString()])
                    ->get()
                    ->map(function (object $row) use ($hasIsPaid): array {
                        $status = $hasIsPaid
                            ? $this->statusFromBoolean((bool) ($row->is_paid ?? false))
                            : $this->normalizeStatus('paid');

                        return $this->baseRow([
                            'department_code' => 'terminal',
                            'source' => 'Terminal Quick Payment',
                            'reference' => 'TQP-' . (int) $row->id,
                            'description' => (string) ($row->remarks ?: 'Quick counter payment'),
                            'person' => (string) ($row->payer_name ?: 'N/A'),
                            'amount' => (float) $row->total_payment,
                            'occurred_at' => $row->occurred_at,
                            'status_key' => $status['key'],
                            'status_label' => $status['label'],
                        ]);
                    })
            );
        }

        return $rows->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function atriumRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('atrium_event_payments')) {
            return collect();
        }

        return DB::table('atrium_event_payments as payment')
            ->leftJoin('atrium_events as event', 'event.id', '=', 'payment.atrium_event_id')
            ->leftJoin('atrium_function_halls as hall', 'hall.id', '=', 'event.atrium_function_hall_id')
            ->select([
                'payment.id',
                'payment.or_number',
                'payment.date_of_payment',
                'payment.payment_amount',
                'payment.payment_status',
                'event.event_code',
                'event.name_contact_person',
                'event.event_details',
                'hall.name as hall_name',
            ])
            ->whereBetween('payment.date_of_payment', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->normalizeStatus((string) ($row->payment_status ?: 'partial'));
                $description = trim(((string) ($row->event_code ?: 'No event code')) . ' | ' . ((string) ($row->hall_name ?: 'Hall not set')));

                return $this->baseRow([
                    'department_code' => 'atrium',
                    'source' => 'Atrium Event Payment',
                    'reference' => (string) ($row->or_number ?: ('AEP-' . $row->id)),
                    'description' => $description,
                    'person' => (string) ($row->name_contact_person ?: 'N/A'),
                    'amount' => (float) $row->payment_amount,
                    'occurred_at' => $row->date_of_payment,
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                ]);
            });
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function baseRow(array $payload): array
    {
        $departmentCode = (string) $payload['department_code'];
        $department = self::DEPARTMENTS[$departmentCode] ?? ['name' => Str::headline($departmentCode), 'icon' => 'fas fa-circle', 'color' => '#64748b'];
        $occurredAt = $this->safeDateTime($payload['occurred_at'] ?? null, now());

        return [
            'department_code' => $departmentCode,
            'department_name' => $department['name'],
            'department_icon' => $department['icon'],
            'department_color' => $department['color'],
            'source' => (string) $payload['source'],
            'reference' => (string) $payload['reference'],
            'description' => (string) $payload['description'],
            'person' => (string) $payload['person'],
            'amount' => round((float) $payload['amount'], 2),
            'occurred_at' => $occurredAt,
            'status_key' => (string) $payload['status_key'],
            'status_label' => (string) $payload['status_label'],
            'searchable' => strtolower(
                implode(' ', [
                    (string) $payload['source'],
                    (string) $payload['reference'],
                    (string) $payload['description'],
                    (string) $payload['person'],
                    $department['name'],
                    (string) $payload['status_label'],
                ])
            ),
        ];
    }

    private function safeDateTime(mixed $value, Carbon $fallback): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value === null || $value === '') {
            return $fallback->copy();
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return $fallback->copy();
        }
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @param array<string, mixed> $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function applyFilters(Collection $rows, array $filters): Collection
    {
        $filtered = $rows;

        if ($filters['department'] !== 'all') {
            $filtered = $filtered->where('department_code', $filters['department'])->values();
        }

        if ($filters['status'] !== 'all') {
            $filtered = $filtered->where('status_key', $filters['status'])->values();
        }

        if ($filters['q'] !== '') {
            $needle = strtolower((string) $filters['q']);
            $filtered = $filtered->filter(static fn (array $row): bool => str_contains((string) $row['searchable'], $needle))->values();
        }

        return $filtered->sortByDesc(static fn (array $row): int => $row['occurred_at']->getTimestamp())->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @return array<string, mixed>
     */
    private function buildSummary(Collection $rows): array
    {
        $byDepartment = collect(self::DEPARTMENTS)->map(function (array $config, string $code) use ($rows): array {
            $departmentRows = $rows->where('department_code', $code);

            return [
                'code' => $code,
                'name' => $config['name'],
                'icon' => $config['icon'],
                'color' => $config['color'],
                'count' => $departmentRows->count(),
                'amount' => round((float) $departmentRows->sum('amount'), 2),
            ];
        })->values();

        return [
            'count' => $rows->count(),
            'amount' => round((float) $rows->sum('amount'), 2),
            'paid_count' => $rows->where('status_key', 'paid')->count(),
            'pending_count' => $rows->where('status_key', 'pending')->count(),
            'by_department' => $byDepartment,
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function paginateRows(Collection $rows, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = max((int) $request->query('page', 1), 1);
        $total = $rows->count();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => route('admin.transactions'),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * @return array{key: string, label: string}
     */
    private function normalizeStatus(string $status): array
    {
        $value = strtolower(trim($status));
        $normalized = str_replace(['-', '_'], ' ', $value);

        if (in_array($normalized, ['paid', 'collected', 'completed', 'approved', 'settled', 'success'], true)) {
            return ['key' => 'paid', 'label' => Str::headline($status)];
        }

        if (in_array($normalized, ['partial', 'partially paid'], true)) {
            return ['key' => 'partial', 'label' => Str::headline($status)];
        }

        if (in_array($normalized, ['cancelled', 'canceled', 'rejected', 'void'], true)) {
            return ['key' => 'cancelled', 'label' => Str::headline($status)];
        }

        return ['key' => 'pending', 'label' => Str::headline($status !== '' ? $status : 'pending')];
    }

    /**
     * @return array{key: string, label: string}
     */
    private function statusFromBoolean(bool $isPaid): array
    {
        return $isPaid
            ? ['key' => 'paid', 'label' => 'Paid']
            : ['key' => 'pending', 'label' => 'Pending'];
    }
}
