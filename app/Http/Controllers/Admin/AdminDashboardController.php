<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * @var array<string, array<string, string>>
     */
    private const DEPARTMENTS = [
        'fishport' => [
            'name' => 'Fishport',
            'short_name' => 'Fishport',
            'icon' => 'fas fa-ship',
            'color' => '#2563eb',
            'surface' => '#eff6ff',
            'description' => 'Landing, docking, unloading, and fishery-related payments.',
        ],
        'market' => [
            'name' => 'Public Market',
            'short_name' => 'Market',
            'icon' => 'fas fa-store',
            'color' => '#0f766e',
            'surface' => '#ecfdf5',
            'description' => 'Stall lease, tenant, and market payment collections.',
        ],
        'cemetery' => [
            'name' => 'Cemetery',
            'short_name' => 'Cemetery',
            'icon' => 'fas fa-cross',
            'color' => '#7c3aed',
            'surface' => '#f5f3ff',
            'description' => 'Burial, service, maintenance, and cemetery transaction payments.',
        ],
        'terminal' => [
            'name' => 'Transport Terminal',
            'short_name' => 'Terminal',
            'icon' => 'fas fa-bus',
            'color' => '#ea580c',
            'surface' => '#fff7ed',
            'description' => 'Parking, quick payment, and terminal operation revenue.',
        ],
        'atrium' => [
            'name' => 'Atrium Hall',
            'short_name' => 'Atrium',
            'icon' => 'fas fa-building-columns',
            'color' => '#0891b2',
            'surface' => '#ecfeff',
            'description' => 'Function hall booking and event payment revenue.',
        ],
    ];

    public function index(Request $request): View
    {
        return $this->renderDashboard($request, 'overview');
    }

    public function all(Request $request): View
    {
        return $this->renderDashboard($request, 'all');
    }

    public function department(Request $request, string $department): View
    {
        abort_unless(array_key_exists($department, self::DEPARTMENTS), 404);

        return $this->renderDashboard($request, 'department', $department);
    }

    private function renderDashboard(Request $request, string $mode, ?string $selectedDepartment = null): View
    {
        $filters = $this->resolveFilters($request);
        $currentRows = $this->getRevenueRows($filters['start_date'], $filters['end_date']);
        $previousRows = $this->getRevenueRows($filters['previous_start_date'], $filters['previous_end_date']);
        $visibleRows = $selectedDepartment
            ? $currentRows->where('department_code', $selectedDepartment)->values()
            : $currentRows;
        $visiblePreviousRows = $selectedDepartment
            ? $previousRows->where('department_code', $selectedDepartment)->values()
            : $previousRows;

        $departmentSummaries = $this->buildDepartmentSummaries($currentRows, $previousRows, $filters);
        $summaryCards = $this->buildSummaryCards($visibleRows, $visiblePreviousRows, $departmentSummaries, $filters);
        $chartData = $this->buildChartData($visibleRows, $currentRows, $departmentSummaries, $filters);
        $recentTransactions = $visibleRows
            ->sortByDesc(fn (array $row): int => $row['occurred_at']->getTimestamp())
            ->take(12)
            ->values();

        return view('admin.dashboard', [
            'mode' => $mode,
            'selectedDepartment' => $selectedDepartment,
            'selectedDepartmentConfig' => $selectedDepartment ? self::DEPARTMENTS[$selectedDepartment] : null,
            'departments' => self::DEPARTMENTS,
            'filters' => $filters,
            'summaryCards' => $summaryCards,
            'departmentSummaries' => $departmentSummaries,
            'chartData' => $chartData,
            'recentTransactions' => $recentTransactions,
            'lastUpdatedAt' => now(),
            'hasAnyRevenueData' => $currentRows->isNotEmpty(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFilters(Request $request): array
    {
        $periodOptions = [
            'day' => 'Day',
            'week' => 'Week',
            'month' => 'Month',
            'custom' => 'Custom Range',
        ];

        $period = (string) $request->query('period', 'month');
        if (! array_key_exists($period, $periodOptions)) {
            $period = 'month';
        }

        $today = today();
        $startDate = $today->copy()->startOfMonth();
        $endDate = $today->copy()->endOfMonth();

        if ($period === 'day') {
            $startDate = $today->copy();
            $endDate = $today->copy();
        } elseif ($period === 'week') {
            $startDate = $today->copy()->startOfWeek();
            $endDate = $today->copy()->endOfWeek();
        } elseif ($period === 'custom') {
            $startDate = $this->safeDate((string) $request->query('start_date'), $today->copy()->startOfMonth());
            $endDate = $this->safeDate((string) $request->query('end_date'), $today);

            if ($endDate->lt($startDate)) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            if ($startDate->diffInDays($endDate) > 365) {
                $endDate = $startDate->copy()->addDays(365);
            }
        }

        $days = (int) $startDate->diffInDays($endDate) + 1;
        $previousEndDate = $startDate->copy()->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($days - 1);

        return [
            'period_options' => $periodOptions,
            'period' => $period,
            'period_label' => $periodOptions[$period],
            'start_date' => $startDate->copy()->startOfDay(),
            'end_date' => $endDate->copy()->endOfDay(),
            'start_date_input' => $startDate->toDateString(),
            'end_date_input' => $endDate->toDateString(),
            'previous_start_date' => $previousStartDate->copy()->startOfDay(),
            'previous_end_date' => $previousEndDate->copy()->endOfDay(),
            'days' => $days,
            'range_label' => $this->formatRangeLabel($startDate, $endDate),
        ];
    }

    private function safeDate(string $value, Carbon $fallback): Carbon
    {
        try {
            return $value !== '' ? Carbon::parse($value)->startOfDay() : $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function formatRangeLabel(Carbon $startDate, Carbon $endDate): string
    {
        if ($startDate->isSameDay($endDate)) {
            return $startDate->format('F j, Y');
        }

        if ($startDate->isSameYear($endDate)) {
            return $startDate->format('F j') . ' to ' . $endDate->format('F j, Y');
        }

        return $startDate->format('F j, Y') . ' to ' . $endDate->format('F j, Y');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getRevenueRows(Carbon $startDate, Carbon $endDate): Collection
    {
        return collect()
            ->merge($this->fishportRevenueRows($startDate, $endDate))
            ->merge($this->marketRevenueRows($startDate, $endDate))
            ->merge($this->cemeteryRevenueRows($startDate, $endDate))
            ->merge($this->terminalRevenueRows($startDate, $endDate))
            ->merge($this->atriumRevenueRows($startDate, $endDate))
            ->filter(static fn (array $row): bool => (float) $row['amount'] > 0)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fishportRevenueRows(Carbon $startDate, Carbon $endDate): Collection
    {
        if (! Schema::hasTable('fishport_payment_records')) {
            return collect();
        }

        return DB::table('fishport_payment_records')
            ->selectRaw("'fishport' as department_code, total_amount as amount, COALESCE(generated_at, created_at) as occurred_at, payment_number as reference")
            ->whereRaw('DATE(COALESCE(generated_at, created_at)) BETWEEN ? AND ?', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('total_amount', '>', 0)
            ->get()
            ->map(fn (object $row): array => $this->normalizeRevenueRow($row, 'Payment Record'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function marketRevenueRows(Carbon $startDate, Carbon $endDate): Collection
    {
        if (! Schema::hasTable('market_payment_collections')) {
            return collect();
        }

        return DB::table('market_payment_collections')
            ->selectRaw("'market' as department_code, amount_paid as amount, COALESCE(payment_date, created_at) as occurred_at, payment_number as reference")
            ->whereRaw('DATE(COALESCE(payment_date, created_at)) BETWEEN ? AND ?', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('amount_paid', '>', 0)
            ->get()
            ->map(fn (object $row): array => $this->normalizeRevenueRow($row, 'Market Payment'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function cemeteryRevenueRows(Carbon $startDate, Carbon $endDate): Collection
    {
        if (! Schema::hasTable('cemetery_payment_collections')) {
            return collect();
        }

        return DB::table('cemetery_payment_collections')
            ->selectRaw("'cemetery' as department_code, amount_paid as amount, COALESCE(payment_date, created_at) as occurred_at, payment_no as reference")
            ->whereRaw('DATE(COALESCE(payment_date, created_at)) BETWEEN ? AND ?', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('amount_paid', '>', 0)
            ->get()
            ->map(fn (object $row): array => $this->normalizeRevenueRow($row, 'Cemetery Payment'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function terminalRevenueRows(Carbon $startDate, Carbon $endDate): Collection
    {
        $rows = collect();

        if (Schema::hasTable('terminal_parking_payments')) {
            $rows = $rows->merge(
                DB::table('terminal_parking_payments')
                    ->selectRaw("'terminal' as department_code, paid_amount as amount, payment_date as occurred_at, or_number as reference")
                    ->whereRaw('DATE(payment_date) BETWEEN ? AND ?', [$startDate->toDateString(), $endDate->toDateString()])
                    ->where('paid_amount', '>', 0)
                    ->get()
                    ->map(fn (object $row): array => $this->normalizeRevenueRow($row, 'Parking Payment'))
            );
        }

        if (Schema::hasTable('terminal_quick_payments')) {
            $quickPaymentDateExpression = Schema::hasColumn('terminal_quick_payments', 'paid_at')
                ? 'COALESCE(paid_at, payment_date, created_at)'
                : 'COALESCE(payment_date, created_at)';

            $query = DB::table('terminal_quick_payments')
                ->selectRaw("'terminal' as department_code, total_payment as amount, {$quickPaymentDateExpression} as occurred_at, payer_name as reference")
                ->whereRaw("DATE({$quickPaymentDateExpression}) BETWEEN ? AND ?", [$startDate->toDateString(), $endDate->toDateString()])
                ->where('total_payment', '>', 0);

            if (Schema::hasColumn('terminal_quick_payments', 'is_paid')) {
                $query->where('is_paid', true);
            }

            $rows = $rows->merge(
                $query->get()->map(fn (object $row): array => $this->normalizeRevenueRow($row, 'Quick Payment'))
            );
        }

        return $rows->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function atriumRevenueRows(Carbon $startDate, Carbon $endDate): Collection
    {
        if (! Schema::hasTable('atrium_event_payments')) {
            return collect();
        }

        return DB::table('atrium_event_payments')
            ->selectRaw("'atrium' as department_code, payment_amount as amount, date_of_payment as occurred_at, or_number as reference")
            ->whereBetween('date_of_payment', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_amount', '>', 0)
            ->get()
            ->map(fn (object $row): array => $this->normalizeRevenueRow($row, 'Atrium Payment'));
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeRevenueRow(object $row, string $fallbackReference): array
    {
        $departmentCode = (string) $row->department_code;
        $occurredAt = Carbon::parse($row->occurred_at);

        return [
            'department_code' => $departmentCode,
            'department_name' => self::DEPARTMENTS[$departmentCode]['name'] ?? Str::headline($departmentCode),
            'amount' => round((float) $row->amount, 2),
            'occurred_at' => $occurredAt,
            'date_key' => $occurredAt->toDateString(),
            'reference' => trim((string) ($row->reference ?? '')) ?: $fallbackReference,
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $currentRows
     * @param Collection<int, array<string, mixed>> $previousRows
     * @return Collection<int, array<string, mixed>>
     */
    private function buildDepartmentSummaries(Collection $currentRows, Collection $previousRows, array $filters): Collection
    {
        $totalRevenue = (float) $currentRows->sum('amount');

        return collect(self::DEPARTMENTS)
            ->map(function (array $config, string $code) use ($currentRows, $previousRows, $filters, $totalRevenue): array {
                $departmentRows = $currentRows->where('department_code', $code)->values();
                $previousDepartmentRows = $previousRows->where('department_code', $code)->values();
                $revenue = round((float) $departmentRows->sum('amount'), 2);
                $previousRevenue = round((float) $previousDepartmentRows->sum('amount'), 2);
                $dailyTotals = $departmentRows
                    ->groupBy('date_key')
                    ->map(static fn (Collection $rows): float => round((float) $rows->sum('amount'), 2));
                $bestDayKey = $dailyTotals->sortDesc()->keys()->first();
                $bestDayRevenue = $bestDayKey ? (float) $dailyTotals[$bestDayKey] : 0.0;
                $latestPaymentAt = $departmentRows
                    ->sortByDesc(fn (array $row): int => $row['occurred_at']->getTimestamp())
                    ->first()['occurred_at'] ?? null;
                $growth = $this->growthPercentage($revenue, $previousRevenue);
                $share = $totalRevenue > 0 ? round(($revenue / $totalRevenue) * 100, 1) : 0.0;

                return [
                    'code' => $code,
                    'name' => $config['name'],
                    'short_name' => $config['short_name'],
                    'icon' => $config['icon'],
                    'color' => $config['color'],
                    'surface' => $config['surface'],
                    'description' => $config['description'],
                    'revenue' => $revenue,
                    'previous_revenue' => $previousRevenue,
                    'growth_percentage' => $growth,
                    'average_daily_revenue' => round($revenue / max((int) $filters['days'], 1), 2),
                    'transaction_count' => $departmentRows->count(),
                    'share_percentage' => $share,
                    'best_day_label' => $bestDayKey ? Carbon::parse($bestDayKey)->format('M d') : 'No revenue',
                    'best_day_revenue' => $bestDayRevenue,
                    'latest_payment_label' => $latestPaymentAt instanceof Carbon ? $latestPaymentAt->format('M d, Y h:i A') : 'No recent payment',
                    'insights' => $this->buildDepartmentInsights($revenue, $growth, $share, $bestDayKey, $bestDayRevenue),
                ];
            })
            ->sortByDesc('revenue')
            ->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $visibleRows
     * @param Collection<int, array<string, mixed>> $visiblePreviousRows
     * @param Collection<int, array<string, mixed>> $departmentSummaries
     * @return array<string, mixed>
     */
    private function buildSummaryCards(Collection $visibleRows, Collection $visiblePreviousRows, Collection $departmentSummaries, array $filters): array
    {
        $totalRevenue = round((float) $visibleRows->sum('amount'), 2);
        $previousRevenue = round((float) $visiblePreviousRows->sum('amount'), 2);
        $bestDepartment = $departmentSummaries->sortByDesc('revenue')->first();
        $lowestDepartment = $departmentSummaries->sortBy('revenue')->first();

        return [
            'total_revenue' => $totalRevenue,
            'growth_percentage' => $this->growthPercentage($totalRevenue, $previousRevenue),
            'best_department' => $bestDepartment,
            'lowest_department' => $lowestDepartment,
            'average_daily_revenue' => round($totalRevenue / max((int) $filters['days'], 1), 2),
            'active_departments' => $departmentSummaries->filter(static fn (array $row): bool => (float) $row['revenue'] > 0)->count(),
            'transaction_count' => $visibleRows->count(),
        ];
    }

    private function growthPercentage(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @return array<int, string>
     */
    private function buildDepartmentInsights(float $revenue, float $growth, float $share, ?string $bestDayKey, float $bestDayRevenue): array
    {
        if ($revenue <= 0) {
            return [
                'No collected revenue in the selected period.',
                'Check pending collections or widen the date filter for more context.',
            ];
        }

        $growthText = $growth >= 0
            ? 'Revenue increased by ' . number_format($growth, 1) . '% versus the previous period.'
            : 'Revenue decreased by ' . number_format(abs($growth), 1) . '% versus the previous period.';

        $bestDayText = $bestDayKey
            ? 'Best day was ' . Carbon::parse($bestDayKey)->format('F j') . ' with PHP ' . number_format($bestDayRevenue, 2) . '.'
            : 'No daily peak available for this period.';

        return [
            $growthText,
            'Department share is ' . number_format($share, 1) . '% of total selected revenue.',
            $bestDayText,
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $visibleRows
     * @param Collection<int, array<string, mixed>> $departmentSummaries
     * @return array<string, mixed>
     */
    private function buildChartData(Collection $visibleRows, Collection $allRows, Collection $departmentSummaries, array $filters): array
    {
        $dateKeys = $this->dateKeys($filters['start_date'], $filters['end_date']);
        $dailyTotals = $visibleRows
            ->groupBy('date_key')
            ->map(static fn (Collection $rows): float => round((float) $rows->sum('amount'), 2));

        $trendValues = collect($dateKeys)
            ->map(static fn (string $dateKey): float => (float) ($dailyTotals[$dateKey] ?? 0))
            ->all();

        $labels = collect($dateKeys)
            ->map(static fn (string $dateKey): string => Carbon::parse($dateKey)->format('M d'))
            ->all();

        $departmentRows = collect(self::DEPARTMENTS)
            ->keys()
            ->map(fn (string $code): ?array => $departmentSummaries->firstWhere('code', $code))
            ->filter()
            ->values();

        $shareValues = $departmentRows->pluck('revenue')->map(static fn ($value): float => (float) $value)->all();
        $shareLabels = $departmentRows->pluck('short_name')->all();
        $shareColors = $departmentRows->pluck('color')->all();

        if (array_sum($shareValues) <= 0) {
            $shareLabels = ['No revenue'];
            $shareValues = [1.0];
            $shareColors = ['#cbd5e1'];
        }

        return [
            'trend' => [
                'labels' => $labels,
                'values' => $trendValues,
            ],
            'departmentBar' => [
                'labels' => $departmentRows->pluck('short_name')->all(),
                'values' => $departmentRows->pluck('revenue')->map(static fn ($value): float => (float) $value)->all(),
                'colors' => $departmentRows->pluck('color')->all(),
            ],
            'share' => [
                'labels' => $shareLabels,
                'values' => $shareValues,
                'colors' => $shareColors,
            ],
            'departmentTrends' => $this->buildDepartmentTrendCharts($allRows, $departmentSummaries, $filters),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function dateKeys(Carbon $startDate, Carbon $endDate): array
    {
        $keys = [];
        $cursor = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $keys[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $keys;
    }

    /**
     * @param Collection<int, array<string, mixed>> $departmentSummaries
     * @return array<string, array<string, mixed>>
     */
    private function buildDepartmentTrendCharts(Collection $allRows, Collection $departmentSummaries, array $filters): array
    {
        $dateKeys = $this->dateKeys($filters['start_date'], $filters['end_date']);
        $charts = [];

        foreach ($departmentSummaries as $department) {
            $dailyTotals = $allRows
                ->where('department_code', $department['code'])
                ->groupBy('date_key')
                ->map(static fn (Collection $rows): float => round((float) $rows->sum('amount'), 2));

            $charts[$department['code']] = [
                'labels' => collect($dateKeys)->map(static fn (string $dateKey): string => Carbon::parse($dateKey)->format('M d'))->all(),
                'values' => collect($dateKeys)->map(static fn (string $dateKey): float => (float) ($dailyTotals[$dateKey] ?? 0))->all(),
                'color' => $department['color'],
            ];
        }

        return $charts;
    }
}
