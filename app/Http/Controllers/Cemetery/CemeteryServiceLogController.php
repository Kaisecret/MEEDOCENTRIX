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
use Symfony\Component\HttpFoundation\StreamedResponse;

class CemeteryServiceLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);
        $serviceTypeId = (int) $request->query('cemetery_service_type_id', 0);

        $serviceLogQuery = $this->buildFilteredServiceLogQuery($search, $siteId, $serviceTypeId);

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

    public function csv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('q', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);
        $serviceTypeId = (int) $request->query('cemetery_service_type_id', 0);

        $serviceLogs = $this->buildFilteredServiceLogQuery($search, $siteId, $serviceTypeId)
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'cemetery-service-logs-' . now()->format('Ymd-His') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($serviceLogs, $search): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderServiceLogsExcelHtml($serviceLogs, $search);
        }, $filename, $headers);
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

    private function buildFilteredServiceLogQuery(string $search, int $siteId, int $serviceTypeId)
    {
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

        return $serviceLogQuery;
    }

    private function renderServiceLogsExcelHtml($serviceLogs, string $search): string
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
    <title>Cemetery Service Logs</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Service Logs</x:Name>
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
    <tr><td colspan="7" class="title">Cemetery Service Logs</td></tr>
    <tr><td colspan="7" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="7" class="meta">Filter: <?= $esc($searchLabel) ?></td></tr>
    <tr><td colspan="7">&nbsp;</td></tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th>Log No.</th>
            <th>Service Date</th>
            <th>Deceased Name</th>
            <th>Niche / Lot</th>
            <th>Cemetery</th>
            <th>Service Type</th>
            <th>Processed By</th>
        </tr>
    </thead>
    <tbody>
        <?php $rowIndex = 0; foreach ($serviceLogs as $serviceLog): $rowIndex++; ?>
            <tr<?= $rowIndex % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($serviceLog->log_no) ?></strong></td>
                <td><?= $esc(optional($serviceLog->service_date)->format('Y-m-d') ?: '-') ?></td>
                <td><?= $esc($serviceLog->deceased_name ?: '-') ?></td>
                <td><?= $esc($serviceLog->plot_reference ?: '-') ?></td>
                <td><?= $esc($serviceLog->site?->site_name ?: '-') ?></td>
                <td><?= $esc($serviceLog->serviceType?->type_name ?: '-') ?></td>
                <td><?= $esc($serviceLog->processed_by ?: '-') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($serviceLogs->isEmpty()): ?>
            <tr><td colspan="7">No service logs found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>
        <?php

        return (string) ob_get_clean();
    }
}
