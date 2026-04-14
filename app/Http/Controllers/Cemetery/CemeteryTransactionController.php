<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryCategory;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeterySite;
use App\Models\CemeteryServiceLog;
use App\Models\CemeteryTransaction;
use App\Models\CemeteryTransactionType;
use App\Support\CemeteryFeeCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);
        $categoryId = (int) $request->query('cemetery_category_id', 0);
        $transactionTypeId = (int) $request->query('cemetery_transaction_type_id', 0);
        $status = trim((string) $request->query('status', ''));
        $prefillOccupantRecordId = (int) $request->query('occupant_record_id', 0);
        $prefillServiceLogId = (int) $request->query('service_log_id', 0);
        $openCreateModal = filter_var($request->query('open_create', false), FILTER_VALIDATE_BOOL);

        $transactionQuery = CemeteryTransaction::query()
            ->with(['site', 'category', 'transactionType', 'occupantRecord', 'serviceLog']);

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

        $transactions = $transactionQuery
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(12)
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
            ->with(['plot:id,plot_reference', 'site:id,site_name', 'category:id,category_name'])
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $serviceLogs = CemeteryServiceLog::query()
            ->with(['site:id,site_name', 'serviceType:id,type_name'])
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $serviceLinkMap = $this->buildServiceLinkMap($serviceLogs);

        $prefillData = $this->prefillData($prefillOccupantRecordId, $prefillServiceLogId, $serviceLinkMap);
        if ($prefillData !== null) {
            $openCreateModal = true;
        }

        return view('cemetery.transactions', [
            'transactions' => $transactions,
            'sites' => $sites,
            'categories' => $categories,
            'transactionTypes' => $transactionTypes,
            'occupantRecords' => $occupantRecords,
            'serviceLogs' => $serviceLogs,
            'serviceLinkMap' => $serviceLinkMap,
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));
        $validated = $this->mergeLinkedSource($validated);
        $fees = $this->computedFees($validated);

        CemeteryTransaction::query()->create([
            'transaction_no' => strtoupper(trim((string) $validated['transaction_no'])),
            'transaction_date' => (string) $validated['transaction_date'],
            'cemetery_site_id' => (int) $validated['cemetery_site_id'],
            'cemetery_category_id' => (int) $validated['cemetery_category_id'],
            'cemetery_transaction_type_id' => (int) $validated['cemetery_transaction_type_id'],
            'occupant_record_id' => $validated['occupant_record_id'] ?? null,
            'service_log_id' => $validated['service_log_id'] ?? null,
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
            'transaction_date' => (string) $validated['transaction_date'],
            'cemetery_site_id' => (int) $validated['cemetery_site_id'],
            'cemetery_category_id' => (int) $validated['cemetery_category_id'],
            'cemetery_transaction_type_id' => (int) $validated['cemetery_transaction_type_id'],
            'occupant_record_id' => $validated['occupant_record_id'] ?? null,
            'service_log_id' => $validated['service_log_id'] ?? null,
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

        return redirect()
            ->back()
            ->with('status', "Transaction {$transaction->transaction_no} updated.");
    }

    public function destroy(CemeteryTransaction $transaction): RedirectResponse
    {
        $transactionNo = $transaction->transaction_no;
        $transaction->delete();

        return redirect()
            ->back()
            ->with('status', "Transaction {$transactionNo} deleted.");
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
            'transaction_date' => ['required', 'date'],
            'cemetery_site_id' => ['required', 'integer', Rule::exists('cemetery_sites', 'id')],
            'cemetery_category_id' => ['required', 'integer', Rule::exists('cemetery_categories', 'id')],
            'cemetery_transaction_type_id' => ['required', 'integer', Rule::exists('cemetery_transaction_types', 'id')],
            'occupant_record_id' => ['nullable', 'integer', Rule::exists('cemetery_occupant_records', 'id')],
            'service_log_id' => ['nullable', 'integer', Rule::exists('cemetery_service_logs', 'id')],
            'deceased_name' => ['required', 'string', 'max:190'],
            'plot_reference' => ['required', 'string', 'max:80'],
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
     * @param array<int, CemeteryServiceLog>|\Illuminate\Support\Collection<int, CemeteryServiceLog> $serviceLogs
     * @return array<int, array{occupant_record_id: int|null, category_id: int|null}>
     */
    private function buildServiceLinkMap($serviceLogs): array
    {
        $serviceLinkMap = [];

        foreach ($serviceLogs as $serviceLog) {
            $match = $this->matchOccupantFromService($serviceLog);
            $serviceLinkMap[(int) $serviceLog->id] = [
                'occupant_record_id' => $match?->id,
                'category_id' => $match?->cemetery_category_id,
            ];
        }

        return $serviceLinkMap;
    }

    /**
     * @param array<int, array{occupant_record_id: int|null, category_id: int|null}> $serviceLinkMap
     * @return array<string, int|string|null>|null
     */
    private function prefillData(int $occupantRecordId, int $serviceLogId, array $serviceLinkMap): ?array
    {
        if ($occupantRecordId > 0) {
            $occupant = CemeteryOccupantRecord::query()
                ->with('plot:id,plot_reference')
                ->find($occupantRecordId);

            if ($occupant) {
                return [
                    'occupant_record_id' => $occupant->id,
                    'service_log_id' => null,
                    'cemetery_site_id' => (int) $occupant->cemetery_site_id,
                    'cemetery_category_id' => (int) $occupant->cemetery_category_id,
                    'deceased_name' => (string) $occupant->deceased_name,
                    'plot_reference' => (string) ($occupant->plot?->plot_reference ?? ''),
                ];
            }
        }

        if ($serviceLogId > 0) {
            $service = CemeteryServiceLog::query()->find($serviceLogId);
            if ($service) {
                $linkedMeta = $serviceLinkMap[$service->id] ?? ['occupant_record_id' => null, 'category_id' => null];
                return [
                    'occupant_record_id' => $linkedMeta['occupant_record_id'],
                    'service_log_id' => $service->id,
                    'cemetery_site_id' => (int) $service->cemetery_site_id,
                    'cemetery_category_id' => $linkedMeta['category_id'],
                    'deceased_name' => (string) $service->deceased_name,
                    'plot_reference' => (string) $service->plot_reference,
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
        $serviceLogId = (int) ($validated['service_log_id'] ?? 0);

        $occupant = null;
        $service = null;

        if ($occupantRecordId > 0) {
            $occupant = CemeteryOccupantRecord::query()
                ->with('plot:id,plot_reference')
                ->find($occupantRecordId);

            if (! $occupant) {
                throw ValidationException::withMessages([
                    'occupant_record_id' => 'Selected occupant record is not valid.',
                ]);
            }

            $validated['cemetery_site_id'] = (int) $occupant->cemetery_site_id;
            $validated['cemetery_category_id'] = (int) $occupant->cemetery_category_id;
            $validated['deceased_name'] = (string) $occupant->deceased_name;
            $validated['plot_reference'] = (string) ($occupant->plot?->plot_reference ?? $validated['plot_reference']);
            $validated['occupant_record_id'] = (int) $occupant->id;
        } else {
            $validated['occupant_record_id'] = null;
        }

        if ($serviceLogId > 0) {
            $service = CemeteryServiceLog::query()->find($serviceLogId);

            if (! $service) {
                throw ValidationException::withMessages([
                    'service_log_id' => 'Selected service log is not valid.',
                ]);
            }

            $validated['cemetery_site_id'] = (int) $service->cemetery_site_id;
            $validated['deceased_name'] = (string) $service->deceased_name;
            $validated['plot_reference'] = (string) $service->plot_reference;
            $validated['service_log_id'] = (int) $service->id;

            if (! $occupant) {
                $serviceOccupant = $this->matchOccupantFromService($service);
                if ($serviceOccupant) {
                    $validated['occupant_record_id'] = (int) $serviceOccupant->id;
                    $validated['cemetery_category_id'] = (int) $serviceOccupant->cemetery_category_id;
                }
            }
        } else {
            $validated['service_log_id'] = null;
        }

        if ($occupant && $service) {
            $occupantPlotRef = strtoupper(trim((string) ($occupant->plot?->plot_reference ?? '')));
            $servicePlotRef = strtoupper(trim((string) $service->plot_reference));
            $occupantDeceased = strtoupper(trim((string) $occupant->deceased_name));
            $serviceDeceased = strtoupper(trim((string) $service->deceased_name));

            if ((int) $occupant->cemetery_site_id !== (int) $service->cemetery_site_id
                || $occupantPlotRef !== $servicePlotRef
                || $occupantDeceased !== $serviceDeceased) {
                throw ValidationException::withMessages([
                    'service_log_id' => 'Linked occupant record and service log do not refer to the same cemetery person/location.',
                ]);
            }
        }

        return $validated;
    }

    private function matchOccupantFromService(CemeteryServiceLog $service): ?CemeteryOccupantRecord
    {
        $plotReference = strtoupper(trim((string) $service->plot_reference));
        $deceasedName = strtoupper(trim((string) $service->deceased_name));

        if ($plotReference === '' || $deceasedName === '') {
            return null;
        }

        return CemeteryOccupantRecord::query()
            ->with('plot:id,plot_reference')
            ->where('cemetery_site_id', $service->cemetery_site_id)
            ->whereRaw('UPPER(deceased_name) = ?', [$deceasedName])
            ->whereHas('plot', function ($query) use ($plotReference): void {
                $query->whereRaw('UPPER(plot_reference) = ?', [$plotReference]);
            })
            ->latest('id')
            ->first();
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
}
