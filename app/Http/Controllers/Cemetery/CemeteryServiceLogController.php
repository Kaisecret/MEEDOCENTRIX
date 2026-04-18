<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryCategory;
use App\Models\CemeteryServiceLog;
use App\Models\CemeteryServiceType;
use App\Models\CemeterySite;
use App\Models\CemeteryTransaction;
use App\Models\CemeteryTransactionType;
use App\Support\CemeteryServiceSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CemeteryServiceLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);
        $serviceTypeId = (int) $request->query('cemetery_service_type_id', 0);

        $serviceLogQuery = CemeteryServiceLog::query()
            ->with(['site', 'serviceType']);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $serviceLogQuery->where(function ($query) use ($like): void {
                $query->where('log_no', 'like', $like)
                    ->orWhere('deceased_name', 'like', $like)
                    ->orWhere('plot_reference', 'like', $like)
                    ->orWhere('processed_by', 'like', $like);
            });
        }

        if ($siteId > 0) {
            $serviceLogQuery->where('cemetery_site_id', $siteId);
        }

        if ($serviceTypeId > 0) {
            $serviceLogQuery->where('cemetery_service_type_id', $serviceTypeId);
        }

        $serviceLogs = $serviceLogQuery
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $sites = CemeterySite::query()
            ->where('is_active', true)
            ->orderBy('site_name')
            ->get();

        $serviceTypes = CemeteryServiceType::query()
            ->where('is_active', true)
            ->orderBy('type_name')
            ->get();

        return view('cemetery.services', [
            'serviceLogs' => $serviceLogs,
            'sites' => $sites,
            'serviceTypes' => $serviceTypes,
            'search' => $search,
            'selectedSiteId' => $siteId,
            'selectedServiceTypeId' => $serviceTypeId,
            'nextLogNo' => $this->nextLogNo(),
            'defaultProcessedBy' => Auth::user()?->name ?? '',
            'summary' => [
                'total_logs' => CemeteryServiceLog::query()->count(),
                'today_logs' => CemeteryServiceLog::query()->whereDate('service_date', now()->toDateString())->count(),
                'interment_logs' => CemeteryServiceLog::query()
                    ->whereHas('serviceType', fn ($query) => $query->where('type_code', 'INTERMENT'))
                    ->count(),
                'burial_logs' => CemeteryServiceLog::query()
                    ->whereHas('serviceType', fn ($query) => $query->where('type_code', 'BURIAL'))
                    ->count(),
                'exhumation_logs' => CemeteryServiceLog::query()
                    ->whereHas('serviceType', fn ($query) => $query->where('type_code', 'EXHUMATION'))
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));
        $suggestion = $this->resolveSuggestion((int) $validated['cemetery_service_type_id'], (int) $validated['cemetery_site_id']);

        $serviceLog = DB::transaction(function () use ($validated, $suggestion): CemeteryServiceLog {
            $serviceLog = CemeteryServiceLog::query()->create([
                'log_no' => strtoupper(trim((string) $validated['log_no'])),
                'service_date' => (string) $validated['service_date'],
                'cemetery_site_id' => (int) $validated['cemetery_site_id'],
                'cemetery_service_type_id' => (int) $validated['cemetery_service_type_id'],
                'suggested_transaction_type_code' => $suggestion['transaction_type_code'],
                'suggested_amount_due' => $suggestion['amount_due'],
                'occupant_record_id' => null,
                'deceased_name' => trim((string) $validated['deceased_name']),
                'plot_reference' => strtoupper(trim((string) $validated['plot_reference'])),
                'details' => $validated['details'] ? trim((string) $validated['details']) : null,
                'processed_by' => trim((string) $validated['processed_by']),
                'remarks' => $validated['remarks'] ? trim((string) $validated['remarks']) : null,
                'created_by_user_id' => Auth::id(),
            ]);

            $this->syncTransactionForLog($serviceLog, $suggestion);

            return $serviceLog;
        });

        return redirect()
            ->route('cemetery.services')
            ->with('status', "Service log {$serviceLog->log_no} added. Linked transaction auto-created.");
    }

    public function update(Request $request, CemeteryServiceLog $serviceLog): RedirectResponse
    {
        $validated = $request->validate($this->rules($request, $serviceLog));
        $suggestion = $this->resolveSuggestion((int) $validated['cemetery_service_type_id'], (int) $validated['cemetery_site_id']);

        DB::transaction(function () use ($serviceLog, $validated, $suggestion): void {
            $serviceLog->update([
                'log_no' => strtoupper(trim((string) $validated['log_no'])),
                'service_date' => (string) $validated['service_date'],
                'cemetery_site_id' => (int) $validated['cemetery_site_id'],
                'cemetery_service_type_id' => (int) $validated['cemetery_service_type_id'],
                'suggested_transaction_type_code' => $suggestion['transaction_type_code'],
                'suggested_amount_due' => $suggestion['amount_due'],
                'occupant_record_id' => null,
                'deceased_name' => trim((string) $validated['deceased_name']),
                'plot_reference' => strtoupper(trim((string) $validated['plot_reference'])),
                'details' => $validated['details'] ? trim((string) $validated['details']) : null,
                'processed_by' => trim((string) $validated['processed_by']),
                'remarks' => $validated['remarks'] ? trim((string) $validated['remarks']) : null,
            ]);

            $this->syncTransactionForLog($serviceLog->fresh(), $suggestion);
        });

        return redirect()
            ->back()
            ->with('status', "Service log {$serviceLog->log_no} updated. Linked transaction synced.");
    }

    /**
     * @param array{transaction_type_code: ?string, amount_due: float} $suggestion
     */
    private function syncTransactionForLog(CemeteryServiceLog $serviceLog, array $suggestion): void
    {
        $typeCode = $suggestion['transaction_type_code'];
        if ($typeCode === null) {
            return;
        }

        $transactionType = CemeteryTransactionType::query()
            ->where('type_code', $typeCode)
            ->first();
        if (! $transactionType) {
            return;
        }

        $defaultCategory = CemeteryCategory::query()
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN category_code = 'REGULAR' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();
        if (! $defaultCategory) {
            return;
        }

        $existing = CemeteryTransaction::query()
            ->where('service_log_id', $serviceLog->id)
            ->first();

        $payload = [
            'transaction_date' => $serviceLog->service_date ? $serviceLog->service_date->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'cemetery_site_id' => $serviceLog->cemetery_site_id,
            'cemetery_category_id' => $existing?->cemetery_category_id ?? $defaultCategory->id,
            'cemetery_transaction_type_id' => $transactionType->id,
            'occupant_record_id' => $serviceLog->occupant_record_id,
            'service_log_id' => $serviceLog->id,
            'deceased_name' => $serviceLog->deceased_name,
            'plot_reference' => $serviceLog->plot_reference,
            'amount_due' => $suggestion['amount_due'],
            'base_fee' => $suggestion['amount_due'],
            'maintenance_fee' => 0,
            'burial_permit_fee' => 0,
            'other_applicable_fee' => 0,
            'maintenance_type' => 'none',
            'maintenance_years' => null,
            'has_burial_permit' => false,
            'remarks' => $serviceLog->remarks,
            'status' => $existing?->status ?? 'pending',
        ];

        if ($existing) {
            $existing->update($payload);
            return;
        }

        CemeteryTransaction::query()->create(array_merge($payload, [
            'transaction_no' => $this->nextTransactionNo(),
            'created_by_user_id' => Auth::id(),
        ]));
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

    /**
     * @return array{transaction_type_code: ?string, amount_due: float}
     */
    private function resolveSuggestion(int $serviceTypeId, int $siteId): array
    {
        $serviceTypeCode = (string) CemeteryServiceType::query()->whereKey($serviceTypeId)->value('type_code');
        $siteCode = (string) CemeterySite::query()->whereKey($siteId)->value('site_code');

        return CemeteryServiceSuggestion::resolve($serviceTypeCode, $siteCode);
    }

    public function destroy(CemeteryServiceLog $serviceLog): RedirectResponse
    {
        $logNo = $serviceLog->log_no;
        $serviceLog->delete();

        return redirect()
            ->back()
            ->with('status', "Service log {$logNo} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request, ?CemeteryServiceLog $serviceLog = null): array
    {
        $logNoRule = $serviceLog
            ? Rule::unique('cemetery_service_logs', 'log_no')->ignore($serviceLog->id)
            : Rule::unique('cemetery_service_logs', 'log_no');

        return [
            'log_no' => ['required', 'string', 'max:40', $logNoRule],
            'service_date' => ['required', 'date'],
            'cemetery_site_id' => ['required', 'integer', Rule::exists('cemetery_sites', 'id')],
            'cemetery_service_type_id' => ['required', 'integer', Rule::exists('cemetery_service_types', 'id')],
            'deceased_name' => ['required', 'string', 'max:190'],
            'plot_reference' => ['required', 'string', 'max:80'],
            'details' => ['nullable', 'string', 'max:1000'],
            'processed_by' => ['required', 'string', 'max:160'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'form_mode' => ['nullable', 'string', Rule::in(['create', 'edit'])],
            'form_service_log_id' => ['nullable', 'integer'],
        ];
    }

    private function nextLogNo(): string
    {
        $latestNo = (string) CemeteryServiceLog::query()
            ->orderByDesc('id')
            ->value('log_no');

        if (preg_match('/(\d+)$/', $latestNo, $matches) === 1) {
            $next = (int) $matches[1] + 1;
            return 'CSL-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        return 'CSL-0001';
    }
}
