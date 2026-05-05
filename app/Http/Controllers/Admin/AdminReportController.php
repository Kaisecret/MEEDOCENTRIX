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
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
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
     * @var array<string, array<string, array<string, string>>>
     */
    private const RECORD_TABS = [
        'fishport' => [
            'vessel_registry' => ['label' => 'Vessel Registry', 'icon' => 'fas fa-ship'],
            'commodity_registry' => ['label' => 'Commodity Registry', 'icon' => 'fas fa-fish'],
            'fee_codes' => ['label' => 'Fee Codes', 'icon' => 'fas fa-tags'],
        ],
        'market' => [
            'tenant_directory' => ['label' => 'Registered Tenants', 'icon' => 'fas fa-users'],
            'lessees' => ['label' => 'Lessees', 'icon' => 'fas fa-id-card'],
            'registered_stalls' => ['label' => 'Registered Stalls', 'icon' => 'fas fa-store'],
        ],
        'cemetery' => [
            'occupant_records' => ['label' => 'Occupant Records', 'icon' => 'fas fa-book-medical'],
            'service_logs' => ['label' => 'Service Logs', 'icon' => 'fas fa-notes-medical'],
            'plot_registry' => ['label' => 'Plot Registry', 'icon' => 'fas fa-map-location-dot'],
        ],
        'terminal' => [
            'vehicle_registry' => ['label' => 'Vehicle Registry', 'icon' => 'fas fa-car-side'],
            'parking_logs' => ['label' => 'Parking Logs', 'icon' => 'fas fa-clipboard-list'],
            'vehicle_types' => ['label' => 'Vehicle Types', 'icon' => 'fas fa-layer-group'],
        ],
        'atrium' => [
            'event_bookings' => ['label' => 'Event Bookings', 'icon' => 'fas fa-calendar-days'],
            'function_halls' => ['label' => 'Function Halls', 'icon' => 'fas fa-building-columns'],
            'supplies_requests' => ['label' => 'Supplies Requests', 'icon' => 'fas fa-box-open'],
        ],
    ];

    private const STATUS_OPTIONS = [
        'all' => 'All Status',
        'active' => 'Active',
        'pending' => 'Pending / Review',
        'completed' => 'Completed / Done',
        'cancelled' => 'Inactive / Cancelled',
    ];

    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $recordTabs = self::RECORD_TABS[$filters['department']];

        if (! array_key_exists($filters['record_tab'], $recordTabs)) {
            $filters['record_tab'] = array_key_first($recordTabs) ?: 'unknown';
        }

        $rows = $this->rowsForDepartmentTab(
            $filters['department'],
            $filters['record_tab'],
            $filters['from'],
            $filters['to']
        );

        $filteredRows = $this->applyFilters($rows, $filters);
        $summary = $this->buildSummary($filteredRows);
        $records = $this->paginateRows($filteredRows, 25, $request);

        return view('admin.reports', [
            'departments' => self::DEPARTMENTS,
            'recordTabs' => $recordTabs,
            'filters' => $filters,
            'statusOptions' => self::STATUS_OPTIONS,
            'records' => $records,
            'summary' => $summary,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $recordTabs = self::RECORD_TABS[$filters['department']];

        if (! array_key_exists($filters['record_tab'], $recordTabs)) {
            $filters['record_tab'] = array_key_first($recordTabs) ?: 'unknown';
        }

        $rows = $this->rowsForDepartmentTab(
            $filters['department'],
            $filters['record_tab'],
            $filters['from'],
            $filters['to']
        );

        $filteredRows = $this->applyFilters($rows, $filters);
        $activeTabLabel = $recordTabs[$filters['record_tab']]['label'] ?? Str::headline((string) $filters['record_tab']);
        $filename = 'admin-reports-' . now()->format('Ymd-His') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($filteredRows, $filters, $activeTabLabel): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderExcelHtml($filteredRows, $filters, $activeTabLabel);
        }, $filename, $headers);
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @param array<string, mixed> $filters
     */
    private function renderExcelHtml(Collection $rows, array $filters, string $activeTabLabel): string
    {
        $esc = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $departmentName = self::DEPARTMENTS[$filters['department']]['name'] ?? Str::headline((string) $filters['department']);

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
            .text { mso-number-format:"\@"; }
            .active { color:#0f766e; font-weight:bold; }
            .pending { color:#b45309; font-weight:bold; }
            .completed { color:#047857; font-weight:bold; }
            .cancelled { color:#b91c1c; font-weight:bold; }
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Admin Records Export</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Records</x:Name>
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
    <tr><td colspan="7" class="title">Reports &amp; Analytics Records</td></tr>
    <tr><td colspan="7" class="subtitle">Department: <?= $esc($departmentName) ?> | Record: <?= $esc($activeTabLabel) ?></td></tr>
    <tr><td colspan="7" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="7" class="meta">Range: <?= $esc($filters['from']->format('F d, Y')) ?> to <?= $esc($filters['to']->format('F d, Y')) ?></td></tr>
    <tr><td colspan="7">&nbsp;</td></tr>
</table>

<table>
    <tr><td colspan="7" class="section-title">RECORDS</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th style="width:170px;">Date Time</th>
            <th style="width:120px;">Department</th>
            <th style="width:140px;">Record Type</th>
            <th style="width:140px;">Reference</th>
            <th style="width:220px;">Subject</th>
            <th style="width:120px;">Status</th>
            <th style="width:320px;">Details</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($rows as $row): $i++; ?>
            <?php
                $statusClass = match ((string) $row['status_key']) {
                    'active' => 'active',
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                    default => 'pending',
                };
            ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td class="text"><?= $esc($row['occurred_at']->format('m/d/Y h:i A')) ?></td>
                <td><?= $esc($row['department_name']) ?></td>
                <td><?= $esc($row['record_type']) ?></td>
                <td class="text"><?= $esc($row['reference']) ?></td>
                <td><?= $esc($row['subject']) ?></td>
                <td class="<?= $statusClass ?>"><?= $esc($row['status_label']) ?></td>
                <td><?= $esc($row['details']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows->isEmpty()): ?>
            <tr><td colspan="7" style="text-align:center;color:#64748b;font-style:italic;">No records found for the selected filters.</td></tr>
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

        $department = strtolower(trim((string) $request->query('department', 'fishport')));
        if (! array_key_exists($department, self::DEPARTMENTS)) {
            $department = 'fishport';
        }

        $status = strtolower(trim((string) $request->query('status', 'all')));
        if (! array_key_exists($status, self::STATUS_OPTIONS)) {
            $status = 'all';
        }

        return [
            'q' => trim((string) $request->query('q', '')),
            'department' => $department,
            'record_tab' => strtolower(trim((string) $request->query('record_tab', ''))),
            'status' => $status,
            'from' => $start,
            'to' => $end,
            'from_input' => $start->toDateString(),
            'to_input' => $end->toDateString(),
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
    private function rowsForDepartmentTab(string $department, string $recordTab, Carbon $from, Carbon $to): Collection
    {
        return (match ($department) {
            'fishport' => match ($recordTab) {
                'vessel_registry' => $this->fishportVesselRows($from, $to),
                'commodity_registry' => $this->fishportCommodityRows($from, $to),
                'fee_codes' => $this->fishportFeeCodeRows($from, $to),
                default => collect(),
            },
            'market' => match ($recordTab) {
                'tenant_directory' => $this->marketTenantRows($from, $to),
                'lessees' => $this->marketLesseeRows($from, $to),
                'registered_stalls' => $this->marketStallRows($from, $to),
                default => collect(),
            },
            'cemetery' => match ($recordTab) {
                'occupant_records' => $this->cemeteryOccupantRows($from, $to),
                'service_logs' => $this->cemeteryServiceRows($from, $to),
                'plot_registry' => $this->cemeteryPlotRows($from, $to),
                default => collect(),
            },
            'terminal' => match ($recordTab) {
                'vehicle_registry' => $this->terminalVehicleRows($from, $to),
                'parking_logs' => $this->terminalParkingLogRows($from, $to),
                'vehicle_types' => $this->terminalVehicleTypeRows($from, $to),
                default => collect(),
            },
            'atrium' => match ($recordTab) {
                'event_bookings' => $this->atriumEventRows($from, $to),
                'function_halls' => $this->atriumHallRows($from, $to),
                'supplies_requests' => $this->atriumSupplyRows($from, $to),
                default => collect(),
            },
            default => collect(),
        })->sortByDesc(static fn (array $row): int => $row['occurred_at']->getTimestamp())->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fishportVesselRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('fishport_vessels')) {
            return collect();
        }

        return DB::table('fishport_vessels as vessel')
            ->leftJoin('fishport_vessel_owners as owner', 'owner.fishport_vessel_id', '=', 'vessel.id')
            ->leftJoin('fishport_vessel_operators as operator', 'operator.fishport_vessel_id', '=', 'vessel.id')
            ->leftJoin('fishport_vessel_registrations as registration', 'registration.fishport_vessel_id', '=', 'vessel.id')
            ->leftJoin('fishport_vessel_documents as documents', 'documents.fishport_vessel_id', '=', 'vessel.id')
            ->select([
                'vessel.id',
                'vessel.name',
                'vessel.vessel_type',
                'vessel.is_active',
                'vessel.created_at',
                'vessel.updated_at',

                DB::raw('COALESCE(registration.registration_number, vessel.registration_number) as registration_number'),
                DB::raw('COALESCE(registration.official_number, vessel.official_number) as official_number'),
                DB::raw('COALESCE(registration.plate_permit_number, vessel.plate_permit_number) as plate_permit_number'),
                DB::raw('COALESCE(registration.home_port, vessel.home_port) as home_port'),
                DB::raw('COALESCE(registration.gross_tonnage, vessel.gross_tonnage) as gross_tonnage'),
                DB::raw('COALESCE(registration.net_tonnage, vessel.net_tonnage) as net_tonnage'),
                DB::raw('COALESCE(registration.vessel_length, vessel.vessel_length) as vessel_length'),
                DB::raw('COALESCE(registration.beam_width, vessel.beam_width) as beam_width'),
                DB::raw('COALESCE(registration.vessel_depth, vessel.vessel_depth) as vessel_depth'),
                DB::raw('COALESCE(registration.engine_type, vessel.engine_type) as engine_type'),
                DB::raw('COALESCE(registration.engine_horsepower, vessel.engine_horsepower) as engine_horsepower'),
                DB::raw('COALESCE(registration.hull_material, vessel.hull_material) as hull_material'),
                DB::raw('COALESCE(registration.color_markings, vessel.color_markings) as color_markings'),
                DB::raw('COALESCE(registration.year_built, vessel.year_built) as year_built'),
                DB::raw('COALESCE(registration.registration_date, vessel.registration_date) as registration_date'),
                DB::raw('COALESCE(registration.expiration_date, vessel.expiration_date) as expiration_date'),
                DB::raw('COALESCE(registration.registration_status, vessel.registration_status) as registration_status'),
                DB::raw('COALESCE(registration.renewal_date, vessel.renewal_date) as renewal_date'),
                DB::raw('COALESCE(registration.issued_by, vessel.issued_by) as issued_by'),
                DB::raw('COALESCE(registration.remarks, vessel.remarks) as registration_remarks'),
                DB::raw('COALESCE(registration.supporting_documents_uploaded, vessel.supporting_documents_uploaded) as supporting_documents_uploaded'),

                DB::raw('COALESCE(owner.full_name, vessel.owner_name) as owner_name'),
                DB::raw('COALESCE(owner.address, vessel.owner_address) as owner_address'),
                DB::raw('COALESCE(owner.contact_number, vessel.owner_contact_number) as owner_contact_number'),
                DB::raw('COALESCE(owner.email, vessel.owner_email) as owner_email'),
                DB::raw('COALESCE(owner.government_id_number, vessel.owner_government_id_number) as owner_government_id_number'),
                DB::raw('COALESCE(owner.business_name, vessel.business_name) as owner_business_name'),

                DB::raw('COALESCE(operator.name, vessel.captain_operator_name) as captain_operator_name'),
                DB::raw('COALESCE(operator.license_number, vessel.captain_license_number) as captain_license_number'),
                DB::raw('COALESCE(operator.contact_number, vessel.captain_contact_number) as captain_contact_number'),
                DB::raw('COALESCE(operator.address, vessel.captain_address) as captain_address'),

                'documents.certificate_of_ownership_path',
                'documents.previous_registration_path',
                'documents.boat_permit_license_path',
                'documents.engine_receipt_proof_path',
                'documents.valid_id_path',
                'documents.inspection_certificate_path',
            ])
            ->whereRaw('DATE(vessel.created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((bool) ($row->is_active ?? true) ? 'active' : 'inactive');
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'fishport',
                    'record_type' => 'Vessel',
                    'reference' => 'FV-' . str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
                    'subject' => (string) ($row->name ?: 'Unknown vessel'),
                    'details' => 'Owner: ' . (string) ($row->owner_name ?: '-') . ' | Type: ' . (string) ($row->vessel_type ?: '-'),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fishportCommodityRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('fishport_commodities')) {
            return collect();
        }

        return DB::table('fishport_commodities as commodity')
            ->leftJoin('fishport_commodity_classifications as class', 'class.id', '=', 'commodity.classification_id')
            ->leftJoin('fishport_units as unit', 'unit.id', '=', 'commodity.default_unit_id')
            ->select([
                'commodity.id',
                'commodity.name',
                'commodity.default_conversion',
                'commodity.is_active',
                'commodity.created_at',
                'class.name as classification_name',
                'unit.name as unit_name',
            ])
            ->whereRaw('DATE(commodity.created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((bool) ($row->is_active ?? true) ? 'active' : 'inactive');
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'fishport',
                    'record_type' => 'Commodity',
                    'reference' => 'FC-' . str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
                    'subject' => (string) ($row->name ?: 'Unknown commodity'),
                    'details' => 'Class: ' . (string) ($row->classification_name ?: '-') . ' | Unit: ' . (string) ($row->unit_name ?: '-') . ' | Conversion: ' . number_format((float) ($row->default_conversion ?? 0), 4),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fishportFeeCodeRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('fishport_payment_types')) {
            return collect();
        }

        return DB::table('fishport_payment_types')
            ->select(['id', 'code', 'name', 'default_fee', 'is_active', 'created_at'])
            ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((bool) ($row->is_active ?? true) ? 'active' : 'inactive');
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'fishport',
                    'record_type' => 'Fee Code',
                    'reference' => (string) ($row->code ?: ('FEE-' . $row->id)),
                    'subject' => (string) ($row->name ?: 'Unnamed fee'),
                    'details' => 'Default Fee: PHP ' . number_format((float) ($row->default_fee ?? 0), 2),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function marketTenantRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('market_tenants')) {
            return collect();
        }

        return DB::table('market_tenants')
            ->select(['id', 'first_name', 'middle_name', 'last_name', 'business_name', 'contact_number', 'created_at'])
            ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $fullName = trim(((string) ($row->first_name ?? '')) . ' ' . ((string) ($row->middle_name ?? '')) . ' ' . ((string) ($row->last_name ?? '')));
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'market',
                    'record_type' => 'Tenant',
                    'reference' => 'MT-' . str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
                    'subject' => $fullName !== '' ? $fullName : 'Unknown tenant',
                    'details' => 'Business: ' . (string) ($row->business_name ?: '-') . ' | Contact: ' . (string) ($row->contact_number ?: '-'),
                    'status_key' => 'active',
                    'status_label' => 'Active',
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function marketLesseeRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('market_stall_leases')) {
            return collect();
        }

        return DB::table('market_stall_leases as lease')
            ->leftJoin('market_tenants as tenant', 'tenant.id', '=', 'lease.market_tenant_id')
            ->leftJoin('market_stalls as stall', 'stall.id', '=', 'lease.market_stall_id')
            ->select([
                'lease.id',
                'lease.contract_number',
                'lease.lease_status',
                'lease.start_date',
                'lease.created_at',
                'tenant.first_name',
                'tenant.last_name',
                'stall.stall_no',
            ])
            ->whereRaw('DATE(COALESCE(lease.created_at, lease.start_date)) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $name = trim(((string) ($row->first_name ?? '')) . ' ' . ((string) ($row->last_name ?? '')));
                $status = $this->statusFromValue((string) ($row->lease_status ?: 'active'));
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'market',
                    'record_type' => 'Lessee',
                    'reference' => (string) ($row->contract_number ?: ('MLS-' . $row->id)),
                    'subject' => $name !== '' ? $name : 'Unknown lessee',
                    'details' => 'Stall: ' . (string) ($row->stall_no ?: '-') . ' | Start: ' . ($row->start_date ? Carbon::parse((string) $row->start_date)->format('M d, Y') : '-'),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at ?? $row->start_date,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function marketStallRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('market_stalls')) {
            return collect();
        }

        return DB::table('market_stalls as stall')
            ->leftJoin('market_stall_locations as location', 'location.id', '=', 'stall.market_stall_location_id')
            ->leftJoin('market_stall_types as type', 'type.id', '=', 'stall.market_stall_type_id')
            ->select([
                'stall.id',
                'stall.stall_no',
                'stall.stall_status',
                'stall.created_at',
                'location.location_code',
                'type.type_name',
            ])
            ->whereRaw('DATE(stall.created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((string) ($row->stall_status ?: 'vacant'));
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'market',
                    'record_type' => 'Stall',
                    'reference' => (string) ($row->stall_no ?: ('STALL-' . $row->id)),
                    'subject' => 'Location ' . (string) ($row->location_code ?: '-'),
                    'details' => 'Type: ' . (string) ($row->type_name ?: '-') . ' | Status: ' . Str::headline((string) ($row->stall_status ?: 'vacant')),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function cemeteryOccupantRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('cemetery_occupant_records')) {
            return collect();
        }

        return DB::table('cemetery_occupant_records as record')
            ->leftJoin('cemetery_plots as plot', 'plot.id', '=', 'record.cemetery_plot_id')
            ->select([
                'record.id',
                'record.record_no',
                'record.deceased_name',
                'record.status',
                'record.maintenance_fee_status',
                'record.created_at',
                'plot.plot_reference',
            ])
            ->whereRaw('DATE(record.created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((string) ($row->status ?: 'active'));
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'cemetery',
                    'record_type' => 'Occupant',
                    'reference' => (string) ($row->record_no ?: ('OCC-' . $row->id)),
                    'subject' => (string) ($row->deceased_name ?: 'Unknown'),
                    'details' => 'Plot: ' . (string) ($row->plot_reference ?: '-') . ' | Maintenance: ' . Str::headline((string) ($row->maintenance_fee_status ?: 'unpaid')),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function cemeteryServiceRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('cemetery_service_logs')) {
            return collect();
        }

        return DB::table('cemetery_service_logs as log')
            ->leftJoin('cemetery_service_types as type', 'type.id', '=', 'log.cemetery_service_type_id')
            ->select([
                'log.id',
                'log.log_no',
                'log.deceased_name',
                'log.plot_reference',
                'log.service_date',
                'log.created_at',
                'type.type_name',
            ])
            ->whereRaw('DATE(COALESCE(log.created_at, log.service_date)) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'cemetery',
                    'record_type' => 'Service Log',
                    'reference' => (string) ($row->log_no ?: ('CSL-' . $row->id)),
                    'subject' => (string) ($row->deceased_name ?: 'Unknown'),
                    'details' => 'Type: ' . (string) ($row->type_name ?: '-') . ' | Plot: ' . (string) ($row->plot_reference ?: '-'),
                    'status_key' => 'completed',
                    'status_label' => 'Logged',
                    'occurred_at' => $row->created_at ?? $row->service_date,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function cemeteryPlotRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('cemetery_plots')) {
            return collect();
        }

        return DB::table('cemetery_plots as plot')
            ->leftJoin('cemetery_sites as site', 'site.id', '=', 'plot.cemetery_site_id')
            ->leftJoin('cemetery_categories as category', 'category.id', '=', 'plot.cemetery_category_id')
            ->select([
                'plot.id',
                'plot.plot_reference',
                'plot.plot_type',
                'plot.is_occupied',
                'plot.is_active',
                'plot.created_at',
                'site.site_name',
                'category.category_name',
            ])
            ->whereRaw('DATE(plot.created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((bool) ($row->is_active ?? true) ? ((bool) ($row->is_occupied ?? false) ? 'occupied' : 'active') : 'inactive');
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'cemetery',
                    'record_type' => 'Plot',
                    'reference' => (string) ($row->plot_reference ?: ('PLOT-' . $row->id)),
                    'subject' => (string) ($row->site_name ?: 'Unknown site'),
                    'details' => 'Category: ' . (string) ($row->category_name ?: '-') . ' | Type: ' . (string) ($row->plot_type ?: '-'),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function terminalVehicleRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('terminal_vehicles')) {
            return collect();
        }

        return DB::table('terminal_vehicles as vehicle')
            ->leftJoin('terminal_vehicle_types as type', 'type.id', '=', 'vehicle.terminal_vehicle_type_id')
            ->select([
                'vehicle.id',
                'vehicle.plate_number',
                'vehicle.operator_name',
                'vehicle.is_active',
                'vehicle.created_at',
                'type.name as vehicle_type_name',
            ])
            ->whereRaw('DATE(vehicle.created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((bool) ($row->is_active ?? true) ? 'active' : 'inactive');
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'terminal',
                    'record_type' => 'Vehicle',
                    'reference' => (string) ($row->plate_number ?: ('TV-' . $row->id)),
                    'subject' => (string) ($row->operator_name ?: 'Unknown operator'),
                    'details' => 'Type: ' . (string) ($row->vehicle_type_name ?: '-'),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function terminalParkingLogRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('terminal_parking_logs')) {
            return collect();
        }

        return DB::table('terminal_parking_logs as log')
            ->leftJoin('terminal_vehicles as vehicle', 'vehicle.id', '=', 'log.terminal_vehicle_id')
            ->select(['log.id', 'log.log_number', 'log.entry_at', 'log.exit_at', 'vehicle.plate_number'])
            ->whereRaw('DATE(log.entry_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue($row->exit_at ? 'completed' : 'active');
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'terminal',
                    'record_type' => 'Parking Log',
                    'reference' => (string) ($row->log_number ?: ('TPL-' . $row->id)),
                    'subject' => (string) ($row->plate_number ?: 'Unknown plate'),
                    'details' => 'Entry: ' . Carbon::parse((string) $row->entry_at)->format('M d, Y h:i A') . ' | Exit: ' . ($row->exit_at ? Carbon::parse((string) $row->exit_at)->format('M d, Y h:i A') : 'Not yet logged'),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->entry_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function terminalVehicleTypeRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('terminal_vehicle_types')) {
            return collect();
        }

        return DB::table('terminal_vehicle_types')
            ->select(['id', 'code', 'name', 'parking_fee_per_hour', 'is_active', 'created_at'])
            ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((bool) ($row->is_active ?? true) ? 'active' : 'inactive');
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'terminal',
                    'record_type' => 'Vehicle Type',
                    'reference' => (string) ($row->code ?: ('TVT-' . $row->id)),
                    'subject' => (string) ($row->name ?: 'Unnamed type'),
                    'details' => 'Parking Fee/Hour: PHP ' . number_format((float) ($row->parking_fee_per_hour ?? 0), 2),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function atriumEventRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('atrium_events')) {
            return collect();
        }

        return DB::table('atrium_events as event')
            ->leftJoin('atrium_function_halls as hall', 'hall.id', '=', 'event.atrium_function_hall_id')
            ->select([
                'event.id',
                'event.event_code',
                'event.event_details',
                'event.name_contact_person',
                'event.booking_status',
                'event.date_of_event',
                'event.created_at',
                'hall.name as hall_name',
            ])
            ->whereRaw('DATE(COALESCE(event.created_at, event.date_of_event)) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((string) ($row->booking_status ?: 'reserved'));
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'atrium',
                    'record_type' => 'Booking',
                    'reference' => (string) ($row->event_code ?: ('ABK-' . $row->id)),
                    'subject' => (string) ($row->event_details ?: 'Event'),
                    'details' => 'Contact: ' . (string) ($row->name_contact_person ?: '-') . ' | Hall: ' . (string) ($row->hall_name ?: '-'),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at ?? $row->date_of_event,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function atriumHallRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('atrium_function_halls')) {
            return collect();
        }

        return DB::table('atrium_function_halls')
            ->select(['id', 'code', 'name', 'capacity', 'hourly_rate', 'is_active', 'created_at'])
            ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((bool) ($row->is_active ?? true) ? 'active' : 'inactive');
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'atrium',
                    'record_type' => 'Function Hall',
                    'reference' => (string) ($row->code ?: ('HALL-' . $row->id)),
                    'subject' => (string) ($row->name ?: 'Unnamed hall'),
                    'details' => 'Capacity: ' . number_format((int) ($row->capacity ?? 0)) . ' | Hourly Rate: PHP ' . number_format((float) ($row->hourly_rate ?? 0), 2),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function atriumSupplyRows(Carbon $from, Carbon $to): Collection
    {
        if (! Schema::hasTable('atrium_supplies_orders')) {
            return collect();
        }

        return DB::table('atrium_supplies_orders as order')
            ->leftJoin('atrium_events as event', 'event.id', '=', 'order.atrium_event_id')
            ->select([
                'order.id',
                'order.request_status',
                'order.requested_supplies',
                'order.created_at',
                'event.event_code',
                'event.name_contact_person',
            ])
            ->whereRaw('DATE(order.created_at) BETWEEN ? AND ?', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(function (object $row): array {
                $status = $this->statusFromValue((string) ($row->request_status ?: 'pending'));
                return $this->withFull(
                    $this->baseRow([
                    'department_code' => 'atrium',
                    'record_type' => 'Supplies Request',
                    'reference' => 'ASO-' . str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
                    'subject' => (string) ($row->event_code ?: '-') . ' - ' . (string) ($row->name_contact_person ?: '-'),
                    'details' => Str::limit((string) ($row->requested_supplies ?: '-'), 140),
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'occurred_at' => $row->created_at,
                    ]),
                    $row
                );
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
            'record_type' => (string) $payload['record_type'],
            'reference' => (string) $payload['reference'],
            'subject' => (string) $payload['subject'],
            'details' => (string) $payload['details'],
            'status_key' => (string) $payload['status_key'],
            'status_label' => (string) $payload['status_label'],
            'occurred_at' => $occurredAt,
            'full' => $payload['full'] ?? [],
            'searchable' => strtolower(
                implode(' ', [
                    $department['name'],
                    (string) $payload['record_type'],
                    (string) $payload['reference'],
                    (string) $payload['subject'],
                    (string) $payload['details'],
                    (string) $payload['status_label'],
                ])
            ),
        ];
    }

    /**
     * @param array<string, mixed> $base
     * @param object $row
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function withFull(array $base, object $row, array $extra = []): array
    {
        $fields = [];
        foreach (get_object_vars($row) as $key => $value) {
            $fields[(string) Str::of((string) $key)->replace('_', ' ')->title()] = $this->formatFullValue($value);
        }

        foreach ($extra as $key => $value) {
            $fields[$key] = $this->formatFullValue($value);
        }

        $base['full'] = $fields;
        return $base;
    }

    private function formatFullValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return trim((string) $value) !== '' ? (string) $value : '-';
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
     * @return array<string, int>
     */
    private function buildSummary(Collection $rows): array
    {
        return [
            'count' => $rows->count(),
            'active' => $rows->where('status_key', 'active')->count(),
            'pending' => $rows->where('status_key', 'pending')->count(),
            'completed' => $rows->where('status_key', 'completed')->count(),
            'cancelled' => $rows->where('status_key', 'cancelled')->count(),
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
                'path' => route('admin.reports'),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * @return array{key: string, label: string}
     */
    private function statusFromValue(string $status): array
    {
        $value = strtolower(trim($status));
        $normalized = str_replace(['-', '_'], ' ', $value);

        if (in_array($normalized, ['active', 'open', 'reserved', 'confirmed', 'occupied'], true)) {
            return ['key' => 'active', 'label' => Str::headline($status)];
        }

        if (in_array($normalized, ['pending', 'partial', 'unpaid', 'overdue', 'for review', 'review', 'approved'], true)) {
            return ['key' => 'pending', 'label' => Str::headline($status)];
        }

        if (in_array($normalized, ['paid', 'completed', 'fulfilled', 'closed', 'done', 'logged'], true)) {
            return ['key' => 'completed', 'label' => Str::headline($status)];
        }

        if (in_array($normalized, ['cancelled', 'canceled', 'rejected', 'void', 'inactive', 'ended', 'vacant'], true)) {
            return ['key' => 'cancelled', 'label' => Str::headline($status)];
        }

        return ['key' => 'pending', 'label' => Str::headline($status !== '' ? $status : 'pending')];
    }
}
