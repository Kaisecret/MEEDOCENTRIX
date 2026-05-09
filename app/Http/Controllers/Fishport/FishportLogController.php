<?php

namespace App\Http\Controllers\Fishport;

use App\Http\Controllers\Controller;
use App\Models\FishportCommodity;
use App\Models\FishportCommodityClassification;
use App\Models\FishportLog;
use App\Models\FishportOrigin;
use App\Models\FishportPaymentType;
use App\Models\FishportUnit;
use App\Models\FishportVessel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FishportLogController extends Controller
{
    /**
     * Vessel logs listing page with date tab filters.
     */
    public function vesselLogs(Request $request): View
    {
        $period = (string) $request->query('period', 'today');
        if (! in_array($period, ['all', 'today', 'week', 'month', 'custom'], true)) {
            $period = 'today';
        }

        $search = trim((string) $request->query('q', ''));
        $date = trim((string) $request->query('date', $request->query('from', '')));
        $hasValidDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;

        $baseQuery = FishportLog::query();
        if ($search !== '') {
            $likeSearch = '%' . $search . '%';
            $baseQuery->where(function ($query) use ($likeSearch): void {
                $query->where('log_number', 'like', $likeSearch)
                    ->orWhere('arr_dep', 'like', $likeSearch)
                    ->orWhereHas('paymentRecord', static fn ($paymentRecordQuery) => $paymentRecordQuery->where('payment_number', 'like', $likeSearch))
                    ->orWhereHas('vessel', static fn ($vesselQuery) => $vesselQuery->where('name', 'like', $likeSearch))
                    ->orWhereHas('origin', static fn ($originQuery) => $originQuery->where('name', 'like', $likeSearch))
                    ->orWhereHas('user', static fn ($userQuery) => $userQuery->where('name', 'like', $likeSearch));
            });
        }

        $todayStart = Carbon::today();
        $todayEnd = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $applyPeriod = static function ($query, string $activePeriod) use (
            $todayStart,
            $todayEnd,
            $weekStart,
            $weekEnd,
            $monthStart,
            $monthEnd,
            $hasValidDate,
            $date
        ): void {
            if ($activePeriod === 'today') {
                $query->whereBetween('log_date', [$todayStart->toDateString(), $todayEnd->toDateString()]);
                return;
            }

            if ($activePeriod === 'week') {
                $query->whereBetween('log_date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
                return;
            }

            if ($activePeriod === 'month') {
                $query->whereBetween('log_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                return;
            }

            if ($activePeriod === 'custom') {
                if ($hasValidDate) {
                    $query->whereDate('log_date', '=', $date);
                }
            }
        };

        $countQuery = clone $baseQuery;
        $counts = [
            'all' => (clone $countQuery)->count(),
            'today' => (function () use ($countQuery, $applyPeriod): int {
                $query = clone $countQuery;
                $applyPeriod($query, 'today');
                return $query->count();
            })(),
            'week' => (function () use ($countQuery, $applyPeriod): int {
                $query = clone $countQuery;
                $applyPeriod($query, 'week');
                return $query->count();
            })(),
            'month' => (function () use ($countQuery, $applyPeriod): int {
                $query = clone $countQuery;
                $applyPeriod($query, 'month');
                return $query->count();
            })(),
            'custom' => (function () use ($countQuery, $applyPeriod): int {
                $query = clone $countQuery;
                $applyPeriod($query, 'custom');
                return $query->count();
            })(),
        ];

        $logsQuery = clone $baseQuery;
        $applyPeriod($logsQuery, $period);

        $logs = $logsQuery
            ->with([
                'vessel:id,name',
                'origin:id,name',
                'user:id,name',
                $this->paymentRecordSelectColumns(),
            ])
            ->orderByDesc('log_date')
            ->orderByDesc('log_time')
            ->orderByDesc('id')
            ->cursorPaginate(15)
            ->withQueryString();

        $vessels = FishportVessel::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $todayLogsByVesselMovement = FishportLog::query()
            ->whereDate('log_date', Carbon::today()->toDateString())
            ->get(['fishport_vessel_id', 'arr_dep'])
            ->groupBy('fishport_vessel_id')
            ->map(static fn ($rows) => $rows->pluck('arr_dep')->unique()->values()->all());

        $origins = FishportOrigin::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('fishport.vessel_logs', [
            'logs' => $logs,
            'period' => $period,
            'search' => $search,
            'date' => $hasValidDate ? $date : '',
            'counts' => $counts,
            'rangeLabel' => match ($period) {
                'today' => 'Today',
                'week' => 'This Week',
                'month' => 'This Month',
                'custom' => $hasValidDate ? ('Custom Date: ' . Carbon::parse($date)->format('M d, Y')) : 'Custom Date',
                default => 'All Dates',
            },
            'vessels' => $vessels,
            'todayLogsByVesselMovement' => $todayLogsByVesselMovement,
            'origins' => $origins,
        ]);
    }

    public function storeVesselLog(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'log_date' => ['required', 'date'],
            'log_time' => ['required', 'date_format:H:i'],
            'arr_dep' => ['required', Rule::in(['ARR', 'DEP'])],
            'vessel_id' => [
                'required',
                Rule::exists('fishport_vessels', 'id')->where(static fn ($query) => $query->where('is_active', true)),
            ],
            'origin_id' => [
                'nullable',
                Rule::exists('fishport_origins', 'id')->where(static fn ($query) => $query->where('is_active', true)),
            ],
            'origin_name' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $resolvedOriginId = $this->resolveOriginIdForLogEntry($validated);

        $duplicateExists = FishportLog::query()
            ->whereDate('log_date', $validated['log_date'])
            ->where('fishport_vessel_id', (int) $validated['vessel_id'])
            ->where('arr_dep', (string) $validated['arr_dep'])
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'arr_dep' => 'This vessel already has this movement logged for the selected date.',
            ]);
        }

        $log = new FishportLog();
        $log->fill([
            'log_number' => $this->generateLogNumber(),
            'log_date' => $validated['log_date'],
            'log_time' => $validated['log_time'],
            'arr_dep' => $validated['arr_dep'],
            'fishport_vessel_id' => (int) $validated['vessel_id'],
            'fishport_origin_id' => $resolvedOriginId,
            'user_id' => Auth::id(),
            'remarks' => $validated['remarks'] ?: null,
        ]);
        $log->save();
        $this->syncPaymentRecord($log, $this->baseHeaderFeeTotal());
        $log->load('paymentRecord:id,fishport_log_id,payment_number');

        return redirect()
            ->route('fishport.vessel_logs')
            ->with('status', "Vessel log {$log->log_number} saved with payment no. {$log->paymentRecord?->payment_number}.");
    }

    public function updateVesselLog(Request $request, FishportLog $fishportLog): RedirectResponse
    {
        $validated = $request->validate([
            'log_date' => ['required', 'date'],
            'log_time' => ['required', 'date_format:H:i'],
            'arr_dep' => ['required', Rule::in(['ARR', 'DEP'])],
            'vessel_id' => [
                'required',
                Rule::exists('fishport_vessels', 'id')->where(static fn ($query) => $query->where('is_active', true)),
            ],
            'origin_id' => [
                'nullable',
                Rule::exists('fishport_origins', 'id')->where(static fn ($query) => $query->where('is_active', true)),
            ],
            'origin_name' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $resolvedOriginId = $this->resolveOriginIdForLogEntry($validated);

        $duplicateExists = FishportLog::query()
            ->whereDate('log_date', $validated['log_date'])
            ->where('fishport_vessel_id', (int) $validated['vessel_id'])
            ->where('arr_dep', (string) $validated['arr_dep'])
            ->whereKeyNot($fishportLog->id)
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'arr_dep' => 'This vessel already has this movement logged for the selected date.',
            ]);
        }

        $fishportLog->update([
            'log_date' => $validated['log_date'],
            'log_time' => $validated['log_time'],
            'arr_dep' => $validated['arr_dep'],
            'fishport_vessel_id' => (int) $validated['vessel_id'],
            'fishport_origin_id' => $resolvedOriginId,
            'remarks' => $validated['remarks'] ?: null,
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('fishport.vessel_logs')
            ->with('status', "Vessel log {$fishportLog->log_number} updated.");
    }

    public function index(Request $request): View
    {
        return $this->renderRecordsPage(null, $request);
    }

    public function edit(Request $request, FishportLog $fishportLog): View
    {
        $fishportLog->load([
            'items.commodity.classification:id,name',
            'items.unit:id,name',
            'payments.paymentType:id,code,name',
            $this->paymentRecordSelectColumns(),
        ]);

        return $this->renderRecordsPage($fishportLog, $request);
    }

    public function store(Request $request): RedirectResponse
    {
        [$validated, $items, $payments] = $this->validatedPayload($request, true);
        $shouldPrint = $request->boolean('print_receipt');
        $sourceLogId = (int) ($validated['source_log_id'] ?? 0);

        $savedLog = DB::transaction(function () use ($validated, $items, $payments, $sourceLogId): FishportLog {
            $log = null;

            if ($sourceLogId <= 0) {
                throw ValidationException::withMessages([
                    'source_log_id' => 'Please select a logged vessel entry first.',
                ]);
            }

            $log = FishportLog::query()->whereKey($sourceLogId)->lockForUpdate()->first();
            if (! $log) {
                throw ValidationException::withMessages([
                    'source_log_id' => 'Selected vessel log was not found.',
                ]);
            }

            $sourceVesselIsActive = FishportVessel::query()
                ->whereKey((int) $log->fishport_vessel_id)
                ->where('is_active', true)
                ->exists();
            if (! $sourceVesselIsActive) {
                throw ValidationException::withMessages([
                    'source_log_id' => 'The vessel linked to this log is inactive and cannot be used for transaction entry.',
                ]);
            }

            if (! $this->isLogInsideCalendarDay($log)) {
                throw ValidationException::withMessages([
                    'source_log_id' => 'Selected vessel log is outside today\'s date range (00:00 to 23:59). Please select a log from today.',
                ]);
            }

            // Keep core linkage fields from selected source log.
            // ARR/DEP is intentionally taken from the transaction form so changing it here
            // will update the linked vessel log on save.
            $validated['log_date'] = optional($log->log_date)->format('Y-m-d');
            $validated['log_time'] = substr((string) $log->log_time, 0, 5);
            $validated['vessel_id'] = (int) $log->fishport_vessel_id;
            $validated['origin_id'] = (int) $log->fishport_origin_id;
            $validated['remarks'] = $log->remarks;

            $this->persistLog($log, $validated, $items, $payments);

            return $log;
        });

        $savedLog->load([
            'vessel:id,name',
            'origin:id,name',
            'user:id,name',
            'payments.paymentType:id,name',
            $this->paymentRecordSelectColumns(),
        ]);

        $redirect = redirect()
            ->route('fishport.records')
            ->with('status', "Payment {$savedLog->paymentRecord?->payment_number} saved for log {$savedLog->log_number}.");

        if ($shouldPrint) {
            $redirect->with('print_receipt_data', $this->buildReceiptPayload($savedLog));
        }

        return $redirect;
    }

    public function update(Request $request, FishportLog $fishportLog): RedirectResponse
    {
        [$validated, $items, $payments] = $this->validatedPayload($request, false, (bool) $fishportLog->is_paid);
        $shouldPrint = $request->boolean('print_receipt');

        DB::transaction(function () use ($fishportLog, $validated, $items, $payments): void {
            $this->persistLog($fishportLog, $validated, $items, $payments);
        });

        $fishportLog->load([
            'vessel:id,name',
            'origin:id,name',
            'user:id,name',
            'payments.paymentType:id,name',
            $this->paymentRecordSelectColumns(),
        ]);

        $redirect = redirect()
            ->route('fishport.records')
            ->with('status', "Payment {$fishportLog->paymentRecord?->payment_number} for log {$fishportLog->log_number} updated successfully.");

        if ($shouldPrint) {
            $redirect->with('print_receipt_data', $this->buildReceiptPayload($fishportLog));
        }

        return $redirect;
    }

    public function destroy(FishportLog $fishportLog): RedirectResponse
    {
        $logNumber = $fishportLog->log_number;
        $fishportLog->delete();

        return redirect()
            ->back()
            ->with('status', "Fishport log {$logNumber} deleted.");
    }

    public function markPaid(Request $request, FishportLog $fishportLog): RedirectResponse
    {
        if ($fishportLog->is_paid) {
            return redirect()
                ->back()
                ->with('status', "Fishport log {$fishportLog->log_number} is already marked as paid.");
        }

        if (! $this->hasPayerNameColumn()) {
            return redirect()
                ->back()
                ->with('status', 'Payment update required: database is missing payer_name column. Please run migrations first.');
        }

        $validated = $request->validate([
            'payer_name' => ['required', 'string', 'max:150'],
        ]);

        $fishportLog->update([
            'is_paid' => true,
            'paid_at' => now(),
            'paid_by_user_id' => Auth::id(),
        ]);

        $fishportLog->loadMissing('paymentRecord');
        $fishportLog->paymentRecord()->updateOrCreate(
            [],
            [
                'payment_number' => $fishportLog->paymentRecord?->payment_number ?: $this->formatPaymentNumberFromLogId((int) $fishportLog->id),
                'total_amount' => round((float) ($fishportLog->paymentRecord?->total_amount ?? $fishportLog->payments()->sum('total')), 2),
                'payer_name' => trim((string) $validated['payer_name']),
                'generated_by_user_id' => $fishportLog->paymentRecord?->generated_by_user_id ?: Auth::id(),
                'generated_at' => $fishportLog->paymentRecord?->generated_at ?: now(),
            ]
        );

        return redirect()
            ->back()
            ->with('status', "Fishport log {$fishportLog->log_number} marked as paid by {$validated['payer_name']}.");
    }

    public function cancelPayment(FishportLog $fishportLog): RedirectResponse
    {
        if (! $fishportLog->is_paid) {
            return redirect()
                ->back()
                ->with('status', "Fishport log {$fishportLog->log_number} is already in Not Paid status.");
        }

        $fishportLog->update([
            'is_paid' => false,
            'paid_at' => null,
            'paid_by_user_id' => null,
        ]);

        if ($fishportLog->paymentRecord && $this->hasPayerNameColumn()) {
            $fishportLog->paymentRecord->update(['payer_name' => null]);
        }

        return redirect()
            ->back()
            ->with('status', "Payment for {$fishportLog->log_number} was cancelled and moved to Not Paid.");
    }

    public function downloadReceipt(FishportLog $fishportLog): View
    {
        $fishportLog->load([
            'vessel:id,name',
            'origin:id,name',
            'user:id,name',
            'payments.paymentType:id,name',
            $this->paymentRecordSelectColumns(),
        ]);

        $payload = $this->buildReceiptPayload($fishportLog);
        return view('fishport.receipt', [
            'receipt' => $payload,
            'pdfUrl' => route('fishport.records.receipt.pdf', $fishportLog),
        ]);
    }

    public function downloadReceiptPdf(FishportLog $fishportLog)
    {
        $fishportLog->load([
            'vessel:id,name',
            'origin:id,name',
            'user:id,name',
            'payments.paymentType:id,name',
            $this->paymentRecordSelectColumns(),
        ]);

        $payload = $this->buildReceiptPayload($fishportLog);
        $filename = ($payload['payment_number'] ?? $fishportLog->log_number ?? 'receipt') . '.pdf';

        return Pdf::loadView('fishport.receipt_pdf', [
            'receipt' => $payload,
        ])->download($filename);
    }

    private function renderRecordsPage(?FishportLog $editingLog = null, ?Request $request = null): View
    {
        $request ??= request();
        [$windowStart, $windowEnd] = $this->currentCalendarDayWindow();

        $pendingLogs = FishportLog::query()
            ->with([
                'vessel:id,name',
                'origin:id,name',
                $this->paymentRecordSelectColumns(),
            ])
            ->whereHas('vessel', static fn ($query) => $query->where('is_active', true))
            ->whereRaw(
                "TIMESTAMP(log_date, COALESCE(log_time, '00:00:00')) >= ? AND TIMESTAMP(log_date, COALESCE(log_time, '00:00:00')) < ?",
                [$windowStart->format('Y-m-d H:i:s'), $windowEnd->format('Y-m-d H:i:s')]
            )
            ->orderByDesc('log_date')
            ->orderByDesc('log_time')
            ->orderByDesc('id')
            ->limit(120)
            ->get();

        $loggedVesselIds = $pendingLogs
            ->pluck('fishport_vessel_id')
            ->filter()
            ->unique()
            ->values();

        $vessels = FishportVessel::query()
            ->where('is_active', true)
            ->whereIn('id', $loggedVesselIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($editingLog && ! $vessels->contains('id', $editingLog->fishport_vessel_id)) {
            $editingVessel = FishportVessel::query()
                ->whereKey($editingLog->fishport_vessel_id)
                ->get(['id', 'name']);
            $vessels = $editingVessel->merge($vessels)->unique('id')->sortBy('name')->values();
        }

        $origins = FishportOrigin::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $units = FishportUnit::query()->orderBy('name')->get(['id', 'name']);
        $commodities = FishportCommodity::query()
            ->where('is_active', true)
            ->with(['classification:id,name', 'defaultUnit:id,name'])
            ->orderBy('name')
            ->get();
        $paymentTypes = FishportPaymentType::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'default_fee']);

        $savedStatusFilter = in_array((string) $request->query('saved_status', 'all'), ['all', 'paid', 'not_paid'], true)
            ? (string) $request->query('saved_status', 'all')
            : 'all';
        $savedSearchQuery = trim((string) $request->query('saved_search', ''));
        $savedFromDate = (string) $request->query('saved_from', '');
        $savedToDate = (string) $request->query('saved_to', '');
        $hasValidFromDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $savedFromDate) === 1;
        $hasValidToDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $savedToDate) === 1;

        $savedLogsBaseQuery = FishportLog::query();

        if ($savedSearchQuery !== '') {
            $likeSearch = '%' . $savedSearchQuery . '%';
            $savedLogsBaseQuery->where(function ($query) use ($likeSearch): void {
                $query->where('log_number', 'like', $likeSearch)
                    ->orWhere('arr_dep', 'like', $likeSearch)
                    ->orWhereHas('paymentRecord', static fn ($paymentRecordQuery) => $paymentRecordQuery->where('payment_number', 'like', $likeSearch))
                    ->orWhereHas('vessel', static fn ($vesselQuery) => $vesselQuery->where('name', 'like', $likeSearch))
                    ->orWhereHas('origin', static fn ($originQuery) => $originQuery->where('name', 'like', $likeSearch));
            });
        }

        if ($hasValidFromDate) {
            $savedLogsBaseQuery->whereDate('log_date', '>=', $savedFromDate);
        }

        if ($hasValidToDate) {
            $savedLogsBaseQuery->whereDate('log_date', '<=', $savedToDate);
        }

        $savedCounts = [
            'all' => (clone $savedLogsBaseQuery)->count(),
            'paid' => (clone $savedLogsBaseQuery)->where('is_paid', true)->count(),
            'not_paid' => (clone $savedLogsBaseQuery)->where('is_paid', false)->count(),
        ];

        $filteredLogsQuery = clone $savedLogsBaseQuery;
        if ($savedStatusFilter === 'paid') {
            $filteredLogsQuery->where('is_paid', true);
        } elseif ($savedStatusFilter === 'not_paid') {
            $filteredLogsQuery->where('is_paid', false);
        }

        $logs = $filteredLogsQuery
            ->with([
                'vessel:id,name',
                'origin:id,name',
                'user:id,name',
                'paidBy:id,name',
                $this->paymentRecordSelectColumns(),
                'items.commodity:id,name,classification_id',
                'items.commodity.classification:id,name',
                'items.unit:id,name',
                'payments.paymentType:id,name',
            ])
            ->orderByDesc('id')
            ->cursorPaginate(15)
            ->withQueryString();

        $headerFeeTypeMap = $paymentTypes
            ->whereIn('code', ['ENTRANCE', 'DOCKING'])
            ->keyBy('code');

        $savedLogLookup = collect($logs->items())->mapWithKeys(function (FishportLog $log) use ($headerFeeTypeMap): array {
            $items = $log->items->map(static fn ($item) => [
                'commodity' => $item->commodity?->name ?? '-',
                'classification' => $item->commodity?->classification?->name ?? '-',
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit?->name ?? '-',
                'conversion' => (float) $item->unit_conversion,
                'volume' => (float) $item->volume,
            ])->values()->all();

            $payments = $log->payments->map(static fn ($payment) => [
                'item' => $payment->paymentType?->name ?? 'Charge',
                'fee' => (float) $payment->fee,
                'quantity' => (float) $payment->quantity,
                'total' => (float) $payment->total,
            ])->values()->all();

            // Log-first entries can be paid without encoded payment rows yet.
            // Show default Entrance + Docking in details instead of empty payment table.
            if (count($payments) === 0) {
                foreach (['ENTRANCE', 'DOCKING'] as $feeCode) {
                    $type = $headerFeeTypeMap->get($feeCode);
                    if (! $type) {
                        continue;
                    }

                    $fee = round((float) ($type->default_fee ?? 0), 2);
                    $payments[] = [
                        'item' => (string) ($type->name ?? 'Charge'),
                        'fee' => $fee,
                        'quantity' => 1.0,
                        'total' => $fee,
                    ];
                }
            }

            $actualGrandTotal = (float) $log->payments->sum('total');
            $grandTotal = $actualGrandTotal > 0
                ? $actualGrandTotal
                : (float) ($log->paymentRecord?->total_amount ?? 0);

            return [
                (string) $log->id => [
                    'id' => $log->id,
                    'log_number' => $log->log_number,
                    'payment_number' => $log->paymentRecord?->payment_number ?? '-',
                    'log_date' => optional($log->log_date)->format('m/d/Y'),
                    'log_time' => substr((string) $log->log_time, 0, 5),
                    'arr_dep' => $log->arr_dep,
                    'vessel' => $log->vessel?->name ?? '-',
                    'origin' => $log->origin?->name ?? '-',
                    'remarks' => $log->remarks ?? '',
                    'encoder' => $this->resolveUiEncoderName($log),
                    'is_paid' => (bool) $log->is_paid,
                    'paid_label' => $log->is_paid ? 'Paid' : 'Not Paid',
                    'payer_name' => $log->paymentRecord?->payer_name ?? '-',
                    'paid_at' => optional($log->paid_at)->format('m/d/Y h:i A'),
                    'paid_by' => $log->paidBy?->name ?? '-',
                    'items' => $items,
                    'payments' => $payments,
                    'line_count' => count($items),
                    'total_volume' => (float) $log->items->sum('volume'),
                    'grand_total' => $grandTotal,
                ],
            ];
        })->all();

        $commodityLookup = $commodities->map(fn (FishportCommodity $commodity) => [
            'id' => $commodity->id,
            'name' => $commodity->name,
            'classification' => $commodity->classification?->name ?? 'Marine',
            'default_unit_id' => $commodity->default_unit_id,
            'default_unit_name' => $commodity->defaultUnit?->name ?? '',
            'default_conversion' => (float) $commodity->default_conversion,
        ])->values();

        $editingItems = $editingLog?->items->map(fn ($item) => [
            'commodity_id' => $item->fishport_commodity_id,
            'quantity' => (float) $item->quantity,
            'unit_id' => $item->unit_id,
            'unit_conversion' => (float) $item->unit_conversion,
        ])->values() ?? collect();

        $editingPayments = $editingLog?->payments->map(fn ($payment) => [
            'payment_type_id' => $payment->fishport_payment_type_id,
            'fee' => (float) $payment->fee,
            'quantity' => (float) $payment->quantity,
            'total' => (float) $payment->total,
        ])->values() ?? collect();

        $baseFees = $paymentTypes
            ->mapWithKeys(fn ($type) => [$type->code => (float) $type->default_fee])
            ->all();

        $savedFilteredCount = $savedCounts[$savedStatusFilter] ?? 0;
        $savedHasFilters = $request->query('saved_tab') === 'saved'
            || $savedStatusFilter !== 'all'
            || $savedSearchQuery !== ''
            || $hasValidFromDate
            || $hasValidToDate
            || $request->has('cursor');

        $pendingLogLookup = $pendingLogs->map(function (FishportLog $log): array {
            return [
                'id' => $log->id,
                'log_number' => $log->log_number,
                'payment_number' => $log->paymentRecord?->payment_number ?? $this->formatPaymentNumberFromLogId((int) $log->id),
                'log_date' => optional($log->log_date)->format('Y-m-d'),
                'log_time' => substr((string) $log->log_time, 0, 5),
                'arr_dep' => $log->arr_dep,
                'vessel_id' => $log->fishport_vessel_id,
                'vessel_name' => $log->vessel?->name ?? '-',
                'origin_id' => $log->fishport_origin_id,
                'origin_name' => $log->origin?->name ?? '-',
                'remarks' => $log->remarks ?? '',
            ];
        })->values();

        return view('fishport.transactions', [
            'vessels' => $vessels,
            'origins' => $origins,
            'units' => $units,
            'commodities' => $commodities,
            'paymentTypes' => $paymentTypes,
            'logs' => $logs,
            'commodityLookup' => $commodityLookup,
            'editingLog' => $editingLog,
            'editingItems' => $editingItems,
            'editingPayments' => $editingPayments,
            'baseFees' => $baseFees,
            'savedLogLookup' => $savedLogLookup,
            'savedStatusFilter' => $savedStatusFilter,
            'savedSearchQuery' => $savedSearchQuery,
            'savedFromDate' => $hasValidFromDate ? $savedFromDate : '',
            'savedToDate' => $hasValidToDate ? $savedToDate : '',
            'savedCounts' => $savedCounts,
            'savedFilteredCount' => $savedFilteredCount,
            'savedHasFilters' => $savedHasFilters,
            'pendingLogs' => $pendingLogLookup,
        ]);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<int, array<string, float|int>>, 2: array<int, array<string, float|int>>}
     */
    private function validatedPayload(Request $request, bool $requireSourceLog = false, bool $allowEmptyLineItems = false): array
    {
        $validated = $request->validate([
            'log_date' => ['required', 'date'],
            'log_time' => ['required', 'date_format:H:i'],
            'arr_dep' => ['required', Rule::in(['ARR', 'DEP'])],
            'vessel_id' => [
                'required',
                Rule::exists('fishport_vessels', 'id')->where(static fn ($query) => $query->where('is_active', true)),
            ],
            'origin_id' => ['required', Rule::exists('fishport_origins', 'id')],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'print_receipt' => ['nullable', 'boolean'],
            'source_log_id' => [$requireSourceLog ? 'required' : 'nullable', Rule::exists('fishport_logs', 'id')],
            'items_payload' => ['required', 'string'],
            'payments_payload' => ['required', 'string'],
        ]);

        $items = json_decode((string) $validated['items_payload'], true);
        $payments = json_decode((string) $validated['payments_payload'], true);

        if (! is_array($items) || ! is_array($payments)) {
            throw ValidationException::withMessages([
                'items_payload' => 'Invalid item or payment payload format.',
            ]);
        }

        $itemArrayRules = ['required', 'array'];
        if (! $allowEmptyLineItems) {
            $itemArrayRules[] = 'min:1';
        }

        $itemsValidator = Validator::make(
            ['items' => $items],
            [
                'items' => $itemArrayRules,
                'items.*.commodity_id' => ['nullable', Rule::exists('fishport_commodities', 'id')],
                'items.*.commodity_name' => ['nullable', 'string', 'max:150'],
                'items.*.classification' => ['nullable', 'string', 'max:80'],
                'items.*.unit_id' => ['required', Rule::exists('fishport_units', 'id')],
                'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
                'items.*.unit_conversion' => ['required', 'numeric', 'min:0.0001'],
            ]
        );

        if ($itemsValidator->fails()) {
            throw ValidationException::withMessages($itemsValidator->errors()->toArray());
        }

        foreach ($items as $index => $item) {
            $commodityId = (int) ($item['commodity_id'] ?? 0);
            $commodityName = trim((string) ($item['commodity_name'] ?? ''));
            if ($commodityId <= 0 && $commodityName === '') {
                throw ValidationException::withMessages([
                    "items.{$index}.commodity_name" => 'Commodity name is required.',
                ]);
            }
        }

        $paymentArrayRules = ['required', 'array'];
        if (! $allowEmptyLineItems) {
            $paymentArrayRules[] = 'min:1';
        }

        $paymentsValidator = Validator::make(
            ['payments' => $payments],
            [
                'payments' => $paymentArrayRules,
                'payments.*.payment_type_id' => ['required', Rule::exists('fishport_payment_types', 'id')],
                'payments.*.fee' => ['nullable', 'numeric', 'min:0'],
                'payments.*.quantity' => ['required', 'numeric', 'min:0'],
            ]
        );

        if ($paymentsValidator->fails()) {
            throw ValidationException::withMessages($paymentsValidator->errors()->toArray());
        }

        $paymentTypeIds = array_map(
            static fn (array $payment): int => (int) ($payment['payment_type_id'] ?? 0),
            $payments
        );

        if (count($paymentTypeIds) !== count(array_unique($paymentTypeIds))) {
            throw ValidationException::withMessages([
                'payments_payload' => 'Payment item types should be unique per transaction.',
            ]);
        }

        $adminFeeMap = FishportPaymentType::query()
            ->whereIn('id', $paymentTypeIds)
            ->pluck('default_fee', 'id');

        $normalizedItems = array_map(function (array $item): array {
            $quantity = round((float) $item['quantity'], 2);
            $unitConversion = round((float) $item['unit_conversion'], 4);
            $volume = round($quantity * $unitConversion, 4);
            $resolvedCommodityId = $this->resolveCommodityIdForPayloadItem($item);

            return [
                'fishport_commodity_id' => $resolvedCommodityId,
                'unit_id' => (int) $item['unit_id'],
                'quantity' => $quantity,
                'unit_conversion' => $unitConversion,
                'volume' => $volume,
            ];
        }, $items);

        $normalizedPayments = array_map(function (array $payment) use ($adminFeeMap): array {
            $typeId = (int) $payment['payment_type_id'];
            $fee = round((float) ($adminFeeMap[$typeId] ?? 0), 2);
            $quantity = round((float) $payment['quantity'], 2);
            $total = round($fee * $quantity, 2);

            return [
                'fishport_payment_type_id' => $typeId,
                'fee' => $fee,
                'quantity' => $quantity,
                'total' => $total,
            ];
        }, $payments);

        return [$validated, $normalizedItems, $normalizedPayments];
    }

    private function resolveCommodityIdForPayloadItem(array $item): int
    {
        $commodityId = (int) ($item['commodity_id'] ?? 0);
        if ($commodityId > 0) {
            return $commodityId;
        }

        $commodityName = trim((string) ($item['commodity_name'] ?? ''));
        if ($commodityName === '') {
            throw ValidationException::withMessages([
                'items_payload' => 'Commodity name is required.',
            ]);
        }

        $existingCommodity = FishportCommodity::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($commodityName)])
            ->first();

        if ($existingCommodity) {
            return (int) $existingCommodity->id;
        }

        $classificationLabel = $this->normalizeCommodityClassificationLabel((string) ($item['classification'] ?? ''));
        $classification = FishportCommodityClassification::query()->firstOrCreate([
            'name' => $classificationLabel,
        ]);

        $newCommodity = FishportCommodity::query()->create([
            'name' => $commodityName,
            'classification_id' => (int) $classification->id,
            'default_unit_id' => (int) ($item['unit_id'] ?? 0) ?: null,
            'default_conversion' => round((float) ($item['unit_conversion'] ?? 1), 4),
            'is_active' => true,
        ]);

        return (int) $newCommodity->id;
    }

    private function normalizeCommodityClassificationLabel(string $value): string
    {
        $normalized = strtolower(trim($value));
        if (str_contains($normalized, 'ice')) {
            return 'Ice';
        }

        return 'Marine';
    }

    private function resolveOriginIdForLogEntry(array $validated): int
    {
        $originId = (int) ($validated['origin_id'] ?? 0);
        if ($originId > 0) {
            return $originId;
        }

        $originName = trim((string) ($validated['origin_name'] ?? ''));
        if ($originName === '') {
            throw ValidationException::withMessages([
                'origin_id' => 'Please select an origin or type a custom origin.',
            ]);
        }

        $existingOrigin = FishportOrigin::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($originName)])
            ->first();

        if ($existingOrigin) {
            if (! $existingOrigin->is_active) {
                $existingOrigin->update(['is_active' => true]);
            }
            return (int) $existingOrigin->id;
        }

        $newOrigin = FishportOrigin::query()->create([
            'name' => $originName,
            'is_active' => true,
        ]);

        return (int) $newOrigin->id;
    }

    /**
     * @param  array<int, array<string, float|int>>  $items
     * @param  array<int, array<string, float|int>>  $payments
     */
    private function persistLog(FishportLog $log, array $validated, array $items, array $payments): void
    {
        $log->fill([
            'log_date' => $validated['log_date'],
            'log_time' => $validated['log_time'],
            'arr_dep' => $validated['arr_dep'],
            'fishport_vessel_id' => (int) $validated['vessel_id'],
            'fishport_origin_id' => (int) $validated['origin_id'],
            'user_id' => Auth::id(),
            'remarks' => $validated['remarks'] ?: null,
        ]);
        $log->save();

        $log->items()->delete();
        $log->payments()->delete();

        $log->items()->createMany($items);
        $log->payments()->createMany($payments);
        $this->syncPaymentRecord($log);
    }

    private function generateLogNumber(): string
    {
        $nextId = ((int) FishportLog::max('id')) + 1;

        return 'FP-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
    }

    private function syncPaymentRecord(FishportLog $log, ?float $forcedTotalAmount = null): void
    {
        $log->loadMissing('paymentRecord');

        $existingRecord = $log->paymentRecord;
        $totalAmount = $forcedTotalAmount !== null
            ? round((float) $forcedTotalAmount, 2)
            : round((float) $log->payments()->sum('total'), 2);

        $log->paymentRecord()->updateOrCreate(
            [],
            [
                'payment_number' => $existingRecord?->payment_number ?: $this->formatPaymentNumberFromLogId((int) $log->id),
                'total_amount' => $totalAmount,
                'generated_by_user_id' => $existingRecord?->generated_by_user_id ?: Auth::id(),
                'generated_at' => $existingRecord?->generated_at ?: now(),
            ]
        );
    }

    private function formatPaymentNumberFromLogId(int $logId): string
    {
        return 'FP-PAY-' . str_pad((string) $logId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function currentOperationalWindow(?Carbon $referenceMoment = null): array
    {
        $moment = $referenceMoment?->copy() ?? now();
        $start = $this->operationalDayStartFor($moment);
        $end = $start->copy()->addDay();

        return [$start, $end];
    }

    private function operationalDayStartFor(Carbon $moment): Carbon
    {
        $startHour = $this->operationalDayStartHour();
        $start = $moment->copy()->setTime($startHour, 0, 0);

        if ($moment->lt($start)) {
            $start->subDay();
        }

        return $start;
    }

    private function operationalDayStartHour(): int
    {
        $rawHour = (int) env('FISHPORT_OPERATIONAL_DAY_START_HOUR', 0);
        return max(0, min(23, $rawHour));
    }

    private function logDateTime(FishportLog $log): ?Carbon
    {
        if (! $log->log_date) {
            return null;
        }

        $datePart = optional($log->log_date)->format('Y-m-d');
        if (! $datePart) {
            return null;
        }

        $rawTime = trim((string) $log->log_time);
        $timePart = $rawTime === '' ? '00:00:00' : substr($rawTime, 0, 8);
        if (strlen($timePart) === 5) {
            $timePart .= ':00';
        }

        return Carbon::parse("{$datePart} {$timePart}");
    }

    private function isLogInsideOperationalWindow(FishportLog $log, ?Carbon $referenceMoment = null): bool
    {
        $logDateTime = $this->logDateTime($log);
        if (! $logDateTime) {
            return false;
        }

        [$windowStart, $windowEnd] = $this->currentOperationalWindow($referenceMoment);

        return $logDateTime->greaterThanOrEqualTo($windowStart) && $logDateTime->lessThan($windowEnd);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function currentCalendarDayWindow(?Carbon $referenceMoment = null): array
    {
        $moment = $referenceMoment?->copy() ?? now();
        $start = $moment->copy()->startOfDay();
        $end = $start->copy()->addDay();

        return [$start, $end];
    }

    private function isLogInsideCalendarDay(FishportLog $log, ?Carbon $referenceMoment = null): bool
    {
        $logDateTime = $this->logDateTime($log);
        if (! $logDateTime) {
            return false;
        }

        [$windowStart, $windowEnd] = $this->currentCalendarDayWindow($referenceMoment);

        return $logDateTime->greaterThanOrEqualTo($windowStart) && $logDateTime->lessThan($windowEnd);
    }

    private function baseHeaderFeeTotal(): float
    {
        return (float) FishportPaymentType::query()
            ->whereIn('code', ['ENTRANCE', 'DOCKING'])
            ->sum('default_fee');
    }

    private function hasPayerNameColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('fishport_payment_records', 'payer_name');
        }

        return $hasColumn;
    }

    private function paymentRecordSelectColumns(): string
    {
        $columns = ['id', 'fishport_log_id', 'payment_number', 'total_amount'];

        if ($this->hasPayerNameColumn()) {
            $columns[] = 'payer_name';
        }

        return 'paymentRecord:' . implode(',', $columns);
    }

    private function buildReceiptPayload(FishportLog $log): array
    {
        $charges = $log->payments->map(static fn ($payment) => [
            'item' => $payment->paymentType?->name ?? 'Charge',
            'qty' => (float) $payment->quantity,
            'total' => (float) $payment->total,
        ])->values()->all();

        // If this is a log-first entry with no payment lines yet, show default header fees
        // so receipt still reflects Entrance + Docking charges.
        if (count($charges) === 0) {
            $headerFees = FishportPaymentType::query()
                ->whereIn('code', ['ENTRANCE', 'DOCKING'])
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['name', 'default_fee']);

            $charges = $headerFees->map(static fn ($feeType) => [
                'item' => $feeType->name ?? 'Charge',
                'qty' => 1.0,
                'total' => round((float) ($feeType->default_fee ?? 0), 2),
            ])->values()->all();
        }

        $paymentNumber = $log->paymentRecord?->payment_number ?: $this->formatPaymentNumberFromLogId((int) $log->id);

        $dateText = optional($log->log_date)->format('m/d/y');
        $timeText = substr((string) $log->log_time, 0, 5);
        $cashierName = $this->resolveReceiptCashierName($log);

        $actualTotals = (float) $log->payments->sum('total');
        $fallbackChargesTotal = (float) collect($charges)->sum(static fn ($line) => (float) ($line['total'] ?? 0));
        $computedTotal = $actualTotals > 0
            ? $actualTotals
            : (float) ($log->paymentRecord?->total_amount ?? $fallbackChargesTotal);

        return [
            'business_name' => 'Fishport Data Management',
            'address' => 'San Jose, Antique',
            'tin' => 'N/A',
            'reference' => $paymentNumber,
            'payment_number' => $paymentNumber,
            'log_number' => $log->log_number,
            'date' => trim("{$dateText} {$timeText}"),
            'cashier' => $cashierName,
            'payer_name' => $log->paymentRecord?->payer_name ?? '-',
            'vessel' => $log->vessel?->name ?? '-',
            'origin' => $log->origin?->name ?? '-',
            'arr_dep' => $log->arr_dep,
            'charges' => $charges,
            'subtotal' => $computedTotal,
            'total_due' => $computedTotal,
        ];
    }

    private function resolveReceiptCashierName(FishportLog $log): string
    {
        $authUser = Auth::user();
        $authName = $this->formatReceiptUserName($authUser);
        $logUserName = $this->formatReceiptUserName($log->user);

        if ($authName !== null && strcasecmp($authName, 'System Admin') !== 0) {
            return $authName;
        }

        if ($logUserName !== null && strcasecmp($logUserName, 'System Admin') !== 0) {
            return $logUserName;
        }

        return $authName ?? $logUserName ?? 'Fishport Staff';
    }

    private function resolveUiEncoderName(FishportLog $log): string
    {
        $candidates = [
            $this->formatReceiptUserName($log->user),
            $this->formatReceiptUserName($log->paidBy),
            $this->formatReceiptUserName(Auth::user()),
        ];

        foreach ($candidates as $name) {
            if ($name !== null && strcasecmp($name, 'System Admin') !== 0) {
                return $name;
            }
        }

        return 'Fishport Personnel';
    }

    private function formatReceiptUserName($user): ?string
    {
        if (! $user) {
            return null;
        }

        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $username = trim((string) ($user->username ?? ''));
        if ($username !== '') {
            return $username;
        }

        return null;
    }

    private function buildReceiptHtml(array $data): string
    {
        $items = $data['charges'] ?? [];
        $lineRows = '';

        foreach ($items as $line) {
            $item = htmlspecialchars((string) ($line['item'] ?? 'Charge'), ENT_QUOTES, 'UTF-8');
            $qty = number_format((float) ($line['qty'] ?? 0), 2, '.', '');
            $total = number_format((float) ($line['total'] ?? 0), 2, '.', '');
            $lineRows .= "<tr><td>{$item}</td><td style=\"text-align:center;\">{$qty}</td><td style=\"text-align:right;\">{$total}</td></tr>";
        }

        if ($lineRows === '') {
            $lineRows = '<tr><td colspan="3" style="text-align:center;">No charges</td></tr>';
        }

        $businessName = htmlspecialchars((string) ($data['business_name'] ?? 'FISHPORT'), ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars((string) ($data['address'] ?? ''), ENT_QUOTES, 'UTF-8');
        $tin = htmlspecialchars((string) ($data['tin'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
        $reference = htmlspecialchars((string) ($data['payment_number'] ?? $data['reference'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $logNumber = htmlspecialchars((string) ($data['log_number'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars((string) ($data['date'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $cashier = htmlspecialchars((string) ($data['cashier'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $payerName = htmlspecialchars((string) ($data['payer_name'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $vessel = htmlspecialchars((string) ($data['vessel'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $origin = htmlspecialchars((string) ($data['origin'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $arrDep = htmlspecialchars((string) ($data['arr_dep'] ?? '-'), ENT_QUOTES, 'UTF-8');
        $subtotal = number_format((float) ($data['subtotal'] ?? 0), 2, '.', '');
        $totalDue = number_format((float) ($data['total_due'] ?? 0), 2, '.', '');

        return <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>{$reference}</title>
<style>
body{font-family:"Courier New",monospace;background:#f5f5f5;margin:0;padding:20px;color:#111}
.r{max-width:360px;margin:0 auto;background:#fff;padding:16px;border:1px solid #ddd}
h1{font-size:34px;letter-spacing:.03em;text-align:center;margin:0 0 8px}
.m{text-align:center;line-height:1.35;margin-bottom:10px}
.hr{border-top:2px dashed #222;margin:10px 0}
table{width:100%;border-collapse:collapse}
th,td{padding:4px 0;font-size:27px}
.s td{padding-top:8px}
.t{font-size:44px;font-weight:700}
</style>
</head>
<body>
<div class="r">
<h1>{$businessName}</h1>
<div class="m">{$address}<br>TIN: {$tin}</div>
<div>Payment No: {$reference}</div>
<div>Log No: {$logNumber}</div>
<div>Date: {$date}</div>
<div>Handled By: {$cashier}</div>
<div>Payer: {$payerName}</div>
<div>Vessel: {$vessel}</div>
<div>Origin: {$origin} ({$arrDep})</div>
<div class="hr"></div>
<table>
<thead><tr><th style="text-align:left;">Item</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Total</th></tr></thead>
<tbody>{$lineRows}</tbody>
</table>
<div class="hr"></div>
<table class="s">
<tr><td>Subtotal:</td><td style="text-align:right;">{$subtotal}</td></tr>
<tr><td class="t">Total Due:</td><td class="t" style="text-align:right;">{$totalDue}</td></tr>
</table>
<div class="hr"></div>
<div class="m" style="margin-top:12px">Thank you!<br>Please come again.</div>
</div>
</body>
</html>
HTML;
    }
}
