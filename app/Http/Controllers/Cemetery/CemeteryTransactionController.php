<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryCategory;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeterySite;
use App\Models\CemeteryTransaction;
use App\Models\CemeteryTransactionType;
use App\Support\CemeteryFeeCalculator;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CemeteryTransactionController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'partial' => 'Partial',
        'cancelled' => 'Cancelled',
    ];

    /**
     * @var array<string, string>
     */
    private const PAYMENT_STATUS_OPTIONS = [
        'paid' => 'Paid',
        'unpaid' => 'Unpaid',
        'partial' => 'Partial',
        'overdue' => 'Overdue',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);
        $categoryId = (int) $request->query('cemetery_category_id', 0);
        $transactionTypeId = (int) $request->query('cemetery_transaction_type_id', 0);
        $status = trim((string) $request->query('status', ''));
        $prefillOccupantRecordId = (int) $request->query('occupant_record_id', 0);
        $openCreateModal = filter_var($request->query('open_create', false), FILTER_VALIDATE_BOOL);

        $transactionQuery = $this->buildFilteredTransactionQuery($search, $siteId, $categoryId, $transactionTypeId, $status)
            ->withCount('payments')
            ->withMax('payments as latest_payment_id', 'id');

        $transactions = $transactionQuery
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $sites = CemeterySite::query()
            ->where('is_active', true)
            ->orderBy('site_name')
            ->get();

        $categories = CemeteryCategory::query()
            ->where('is_active', true)
            ->orderBy('category_name')
            ->get();

        $transactionTypes = CemeteryTransactionType::query()
            ->where('is_active', true)
            ->orderBy('type_name')
            ->get();

        $occupantRecords = CemeteryOccupantRecord::query()
            ->with(['plot:id,plot_reference', 'site:id,site_name,site_code', 'category:id,category_name,category_code'])
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $prefillData = $this->prefillData($prefillOccupantRecordId);
        if ($prefillData !== null) {
            $openCreateModal = true;
        }

        return view('cemetery.transactions', [
            'transactions' => $transactions,
            'sites' => $sites,
            'categories' => $categories,
            'transactionTypes' => $transactionTypes,
            'occupantRecords' => $occupantRecords,
            'statusOptions' => self::STATUS_OPTIONS,
            'search' => $search,
            'selectedSiteId' => $siteId,
            'selectedCategoryId' => $categoryId,
            'selectedTransactionTypeId' => $transactionTypeId,
            'selectedStatus' => $status,
            'openCreateModal' => $openCreateModal,
            'prefillData' => $prefillData,
            'nextTransactionNo' => $this->nextTransactionNo(),
            'summary' => [
                'total_transactions' => CemeteryTransaction::query()->count(),
                'today_transactions' => CemeteryTransaction::query()->whereDate('transaction_date', now()->toDateString())->count(),
                'pending_transactions' => CemeteryTransaction::query()->where('status', 'pending')->count(),
                'paid_transactions' => CemeteryTransaction::query()->where('status', 'paid')->count(),
                'total_due' => (float) CemeteryTransaction::query()->sum('amount_due'),
            ],
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('q', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);
        $categoryId = (int) $request->query('cemetery_category_id', 0);
        $transactionTypeId = (int) $request->query('cemetery_transaction_type_id', 0);
        $status = trim((string) $request->query('status', ''));

        $transactions = $this->buildFilteredTransactionQuery($search, $siteId, $categoryId, $transactionTypeId, $status)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'cemetery-transactions-' . now()->format('Ymd-His') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($transactions, $search): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderTransactionsExcelHtml($transactions, $search);
        }, $filename, $headers);
    }

    public function show(CemeteryTransaction $transaction): View
    {
        $transaction->load([
            'site:id,site_name',
            'category:id,category_name',
            'transactionType:id,type_name',
            'occupantRecord:id,record_no,cemetery_contact_id',
            'occupantRecord.contact:id,contact_person,contact_number,address',
            'payments' => static function ($query): void {
                $query
                    ->with('creator:id,name')
                    ->orderBy('payment_date')
                    ->orderBy('id');
            },
        ]);

        $amountDue = round((float) ($transaction->amount_due ?? 0), 2);
        $totalPaid = round((float) $transaction->payments->sum('amount_paid'), 2);
        $balance = round(max($amountDue - $totalPaid, 0), 2);

        return view('cemetery.transaction_show', [
            'transaction' => $transaction,
            'amountDue' => $amountDue,
            'totalPaid' => $totalPaid,
            'balance' => $balance,
            'statusLabel' => self::STATUS_OPTIONS[$transaction->status] ?? strtoupper((string) $transaction->status),
            'paymentStatusOptions' => self::PAYMENT_STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));
        $validated = $this->mergeLinkedSource($validated);
        $fees = $this->computedFees($validated);

        CemeteryTransaction::query()->create([
            'transaction_no' => strtoupper(trim((string) $validated['transaction_no'])),
            'transaction_date' => $this->resolveTransactionDate($validated['transaction_date'] ?? null),
            'cemetery_site_id' => (int) $validated['cemetery_site_id'],
            'cemetery_category_id' => (int) $validated['cemetery_category_id'],
            'cemetery_transaction_type_id' => (int) $validated['cemetery_transaction_type_id'],
            'occupant_record_id' => $validated['occupant_record_id'] ?? null,
            'service_log_id' => null,
            'deceased_name' => trim((string) $validated['deceased_name']),
            'plot_reference' => strtoupper(trim((string) $validated['plot_reference'])),
            'quantity' => $validated['quantity'] ?? null,
            'amount_due' => $fees['amount_due'],
            'total_paid' => 0,
            'remaining_balance' => $fees['amount_due'],
            'maintenance_type' => $this->maintenanceTypeFrom($validated),
            'maintenance_years' => $this->maintenanceYearsFrom($validated),
            'has_burial_permit' => $this->hasBurialPermit($validated),
            'base_fee' => $fees['base_fee'],
            'maintenance_fee' => $fees['maintenance_fee'],
            'burial_permit_fee' => $fees['burial_permit_fee'],
            'other_applicable_fee' => $fees['other_applicable_fee'],
            'remarks' => $validated['remarks'] ? trim((string) $validated['remarks']) : null,
            'status' => trim((string) $validated['status']),
            'created_by_user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('cemetery.transactions')
            ->with('status', 'Cemetery transaction added successfully.');
    }

    public function update(Request $request, CemeteryTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate($this->rules($request, $transaction));
        $validated = $this->mergeLinkedSource($validated);
        $fees = $this->computedFees($validated);

        $transaction->update([
            'transaction_no' => strtoupper(trim((string) $validated['transaction_no'])),
            'transaction_date' => $this->resolveTransactionDate($validated['transaction_date'] ?? null),
            'cemetery_site_id' => (int) $validated['cemetery_site_id'],
            'cemetery_category_id' => (int) $validated['cemetery_category_id'],
            'cemetery_transaction_type_id' => (int) $validated['cemetery_transaction_type_id'],
            'occupant_record_id' => $validated['occupant_record_id'] ?? null,
            'service_log_id' => null,
            'deceased_name' => trim((string) $validated['deceased_name']),
            'plot_reference' => strtoupper(trim((string) $validated['plot_reference'])),
            'quantity' => $validated['quantity'] ?? null,
            'amount_due' => $fees['amount_due'],
            'maintenance_type' => $this->maintenanceTypeFrom($validated),
            'maintenance_years' => $this->maintenanceYearsFrom($validated),
            'has_burial_permit' => $this->hasBurialPermit($validated),
            'base_fee' => $fees['base_fee'],
            'maintenance_fee' => $fees['maintenance_fee'],
            'burial_permit_fee' => $fees['burial_permit_fee'],
            'other_applicable_fee' => $fees['other_applicable_fee'],
            'remarks' => $validated['remarks'] ? trim((string) $validated['remarks']) : null,
            'status' => trim((string) $validated['status']),
        ]);
        $this->syncPaymentDerivedFields($transaction, true);

        return redirect()
            ->back()
            ->with('status', "Transaction {$transaction->transaction_no} updated.");
    }

    public function destroy(CemeteryTransaction $transaction): RedirectResponse
    {
        $transactionNo = $transaction->transaction_no;
        $hadPayments = $transaction->payments()->exists();
        try {
            DB::transaction(function () use ($transaction): void {
                // Keep delete behavior consistent from UI: remove linked payment rows first.
                $transaction->payments()->delete();
                $transaction->delete();
            });
        } catch (QueryException $exception) {
            return redirect()
                ->back()
                ->withErrors("Transaction {$transactionNo} cannot be deleted because it is linked to other records.");
        }

        return redirect()
            ->back()
            ->with('status', $hadPayments
                ? "Transaction {$transactionNo} and its linked payment record(s) were deleted."
                : "Transaction {$transactionNo} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request, ?CemeteryTransaction $transaction = null): array
    {
        $transactionNoRule = $transaction
            ? Rule::unique('cemetery_transactions', 'transaction_no')->ignore($transaction->id)
            : Rule::unique('cemetery_transactions', 'transaction_no');

        return [
            'transaction_no' => ['required', 'string', 'max:40', $transactionNoRule],
            'transaction_date' => ['nullable', 'date'],
            'cemetery_site_id' => ['nullable', 'integer', Rule::exists('cemetery_sites', 'id')],
            'cemetery_category_id' => ['nullable', 'integer', Rule::exists('cemetery_categories', 'id')],
            'cemetery_transaction_type_id' => ['required', 'integer', Rule::exists('cemetery_transaction_types', 'id')],
            'occupant_record_id' => ['required', 'integer', Rule::exists('cemetery_occupant_records', 'id')],
            'deceased_name' => ['nullable', 'string', 'max:190'],
            'plot_reference' => ['nullable', 'string', 'max:80'],
            'quantity' => ['nullable', 'numeric', 'min:0.01'],
            'maintenance_type' => ['nullable', Rule::in(['none', 'yearly', 'five_year_fixed'])],
            'maintenance_years' => ['nullable', 'integer', 'min:1', 'max:50'],
            'has_burial_permit' => ['nullable', 'boolean'],
            'other_applicable_fee' => ['nullable', 'numeric', 'min:0'],
            'amount_due' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'form_mode' => ['nullable', 'string', Rule::in(['create', 'edit'])],
            'form_transaction_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @param array<string, mixed> $validated
     * @return array{
     *     base_fee: float,
     *     maintenance_fee: float,
     *     burial_permit_fee: float,
     *     other_applicable_fee: float,
     *     amount_due: float
     * }
     */
    private function computedFees(array $validated): array
    {
        $site = CemeterySite::query()
            ->select(['id', 'site_code'])
            ->find((int) $validated['cemetery_site_id']);
        $category = CemeteryCategory::query()
            ->select(['id', 'category_code'])
            ->find((int) $validated['cemetery_category_id']);
        $transactionType = CemeteryTransactionType::query()
            ->select(['id', 'type_code'])
            ->find((int) $validated['cemetery_transaction_type_id']);

        if (! $site || ! $category || ! $transactionType) {
            throw ValidationException::withMessages([
                'amount_due' => 'Unable to compute fee. Please reselect cemetery, category, and transaction type.',
            ]);
        }

        $maintenanceType = $this->maintenanceTypeFrom($validated);
        $maintenanceYears = $this->maintenanceYearsFrom($validated);

        if ($maintenanceType === 'yearly' && (! is_int($maintenanceYears) || $maintenanceYears < 1)) {
            throw ValidationException::withMessages([
                'maintenance_years' => 'Years to cover is required for yearly maintenance.',
            ]);
        }

        if ($transactionType->type_code === 'MAINTENANCE_FEE' && $maintenanceType === 'none') {
            throw ValidationException::withMessages([
                'maintenance_type' => 'Maintenance type is required when transaction type is Maintenance Fee.',
            ]);
        }

        if (in_array($transactionType->type_code, ['TRANSFER', 'OTHER'], true)) {
            $otherFee = (float) ($validated['other_applicable_fee'] ?? 0);
            if ($otherFee < 0 || $otherFee > 200) {
                throw ValidationException::withMessages([
                    'other_applicable_fee' => 'For this service, additional fee must be between 0 and 200 (total range: 300 to 500).',
                ]);
            }
        }

        return CemeteryFeeCalculator::compute(
            $site->site_code,
            $category->category_code,
            $transactionType->type_code,
            $maintenanceType,
            $maintenanceYears,
            $this->hasBurialPermit($validated),
            (float) ($validated['other_applicable_fee'] ?? 0)
        );
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function maintenanceTypeFrom(array $validated): string
    {
        $value = strtolower(trim((string) ($validated['maintenance_type'] ?? 'none')));
        return in_array($value, ['none', 'yearly', 'five_year_fixed'], true) ? $value : 'none';
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function maintenanceYearsFrom(array $validated): ?int
    {
        $years = $validated['maintenance_years'] ?? null;
        if ($years === null || $years === '') {
            return null;
        }

        return (int) $years;
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function hasBurialPermit(array $validated): bool
    {
        return filter_var($validated['has_burial_permit'] ?? false, FILTER_VALIDATE_BOOL);
    }

    /**
     * @return array<string, int|string|null>|null
     */
    private function prefillData(int $occupantRecordId): ?array
    {
        if ($occupantRecordId > 0) {
            $occupant = CemeteryOccupantRecord::query()
                ->with('plot:id,plot_reference')
                ->find($occupantRecordId);

            if ($occupant) {
                return [
                    'occupant_record_id' => $occupant->id,
                    'cemetery_site_id' => (int) $occupant->cemetery_site_id,
                    'cemetery_category_id' => (int) $occupant->cemetery_category_id,
                    'deceased_name' => (string) $occupant->deceased_name,
                    'plot_reference' => (string) ($occupant->plot?->plot_reference ?? ''),
                ];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function mergeLinkedSource(array $validated): array
    {
        $occupantRecordId = (int) ($validated['occupant_record_id'] ?? 0);

        if ($occupantRecordId <= 0) {
            throw ValidationException::withMessages([
                'occupant_record_id' => 'Select an occupant record before creating a transaction.',
            ]);
        }

        $occupant = CemeteryOccupantRecord::query()
            ->with('plot:id,plot_reference')
            ->find($occupantRecordId);

        if (! $occupant || ! $occupant->plot) {
            throw ValidationException::withMessages([
                'occupant_record_id' => 'Selected occupant record is not valid or has no assigned niche/lot.',
            ]);
        }

        $validated['cemetery_site_id'] = (int) $occupant->cemetery_site_id;
        $validated['cemetery_category_id'] = (int) $occupant->cemetery_category_id;
        $validated['deceased_name'] = (string) $occupant->deceased_name;
        $validated['plot_reference'] = (string) ($occupant->plot?->plot_reference ?? '');
        $validated['occupant_record_id'] = (int) $occupant->id;

        return $validated;
    }

    private function buildFilteredTransactionQuery(
        string $search,
        int $siteId,
        int $categoryId,
        int $transactionTypeId,
        string $status
    ) {
        $transactionQuery = CemeteryTransaction::query()
            ->with(['site', 'category', 'transactionType', 'occupantRecord.contact']);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $transactionQuery->where(function ($query) use ($like): void {
                $query->where('transaction_no', 'like', $like)
                    ->orWhere('deceased_name', 'like', $like)
                    ->orWhere('plot_reference', 'like', $like);
            });
        }

        if ($siteId > 0) {
            $transactionQuery->where('cemetery_site_id', $siteId);
        }

        if ($categoryId > 0) {
            $transactionQuery->where('cemetery_category_id', $categoryId);
        }

        if ($transactionTypeId > 0) {
            $transactionQuery->where('cemetery_transaction_type_id', $transactionTypeId);
        }

        if (array_key_exists($status, self::STATUS_OPTIONS)) {
            $transactionQuery->where('status', $status);
        }

        return $transactionQuery;
    }

    private function renderTransactionsExcelHtml($transactions, string $search): string
    {
        $esc = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $searchLabel = $search === '' ? 'All records' : $search;

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
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Cemetery Transactions</title>
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
    <tr><td colspan="8" class="title">Cemetery Transactions</td></tr>
    <tr><td colspan="8" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="8" class="meta">Filter: <?= $esc($searchLabel) ?></td></tr>
    <tr><td colspan="8">&nbsp;</td></tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th>Transaction No.</th>
            <th>Date</th>
            <th>Deceased Name</th>
            <th>Niche / Lot</th>
            <th>Cemetery / Category</th>
            <th>Transaction Type</th>
            <th>Occupant Record</th>
            <th>Amount Due</th>
        </tr>
    </thead>
    <tbody>
        <?php $rowIndex = 0; foreach ($transactions as $transaction): $rowIndex++; ?>
            <tr<?= $rowIndex % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($transaction->transaction_no) ?></strong></td>
                <td><?= $esc(optional($transaction->transaction_date)->format('Y-m-d h:i A') ?: '-') ?></td>
                <td><?= $esc($transaction->deceased_name ?: '-') ?></td>
                <td><?= $esc($transaction->plot_reference ?: '-') ?></td>
                <td><?= $esc(($transaction->site?->site_name ?: '-') . ' / ' . ($transaction->category?->category_name ?: '-')) ?></td>
                <td><?= $esc($transaction->transactionType?->type_name ?: '-') ?></td>
                <td><?= $esc($transaction->occupantRecord?->record_no ?: '-') ?></td>
                <td><?= $esc('PHP ' . number_format((float) $transaction->amount_due, 2)) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($transactions->isEmpty()): ?>
            <tr><td colspan="8">No transactions found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>
        <?php

        return (string) ob_get_clean();
    }

    private function nextTransactionNo(): string
    {
        $latestNo = (string) CemeteryTransaction::query()
            ->orderByDesc('id')
            ->value('transaction_no');

        if (preg_match('/(\d+)$/', $latestNo, $matches) === 1) {
            $next = (int) $matches[1] + 1;
            return 'CTX-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        return 'CTX-0001';
    }

    private function resolveTransactionDate(mixed $transactionDate): string
    {
        $value = trim((string) ($transactionDate ?? ''));
        if ($value === '') {
            return now()->format('Y-m-d H:i:s');
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    private function syncPaymentDerivedFields(CemeteryTransaction $transaction, bool $preserveManualStatus = false): void
    {
        $amountDue = round((float) $transaction->amount_due, 2);
        $totalPaid = round((float) $transaction->payments()->sum('amount_paid'), 2);
        $remaining = round(max($amountDue - $totalPaid, 0), 2);

        $transaction->total_paid = $totalPaid;
        $transaction->remaining_balance = $remaining;

        if (! $preserveManualStatus && $transaction->status !== 'cancelled') {
            if ($amountDue <= 0 || $totalPaid >= $amountDue) {
                $transaction->status = 'paid';
            } elseif ($totalPaid > 0) {
                $transaction->status = 'partial';
            } else {
                $transaction->status = 'pending';
            }
        }

        $transaction->save();
    }
}
