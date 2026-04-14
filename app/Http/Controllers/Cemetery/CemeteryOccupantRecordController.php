<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryCategory;
use App\Models\CemeteryContact;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeteryPlot;
use App\Models\CemeterySite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CemeteryOccupantRecordController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const STATUS_OPTIONS = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'exhumed' => 'Exhumed',
        'transferred' => 'Transferred',
    ];

    /**
     * @var array<string, string>
     */
    private const MAINTENANCE_STATUS_OPTIONS = [
        'paid' => 'Paid',
        'unpaid' => 'Unpaid',
        'partial' => 'Partial',
        'overdue' => 'Overdue',
    ];

    /**
     * @var array<string, string>
     */
    private const PLOT_TYPE_OPTIONS = [
        'niche' => 'Niche',
        'lot' => 'Lot',
    ];

    public function index(Request $request): View
    {
        $this->syncOverdueMaintenanceStatuses();

        $search = trim((string) $request->query('q', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);
        $categoryId = (int) $request->query('cemetery_category_id', 0);
        $status = trim((string) $request->query('status', ''));
        $maintenanceStatus = trim((string) $request->query('maintenance_fee_status', ''));
        $hasActiveFilters = $search !== ''
            || $siteId > 0
            || $categoryId > 0
            || $status !== ''
            || $maintenanceStatus !== '';

        $recordQuery = CemeteryOccupantRecord::query()
            ->with(['site', 'category', 'plot', 'contact']);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $recordQuery->where(function ($query) use ($like): void {
                $query->where('record_no', 'like', $like)
                    ->orWhere('deceased_name', 'like', $like)
                    ->orWhereHas('plot', function ($plotQuery) use ($like): void {
                        $plotQuery->where('plot_reference', 'like', $like);
                    })
                    ->orWhereHas('contact', function ($contactQuery) use ($like): void {
                        $contactQuery->where('contact_person', 'like', $like)
                            ->orWhere('contact_number', 'like', $like);
                    });
            });
        }

        if ($siteId > 0) {
            $recordQuery->where('cemetery_site_id', $siteId);
        }

        if ($categoryId > 0) {
            $recordQuery->where('cemetery_category_id', $categoryId);
        }

        if (array_key_exists($status, self::STATUS_OPTIONS)) {
            $recordQuery->where('status', $status);
        }

        if (array_key_exists($maintenanceStatus, self::MAINTENANCE_STATUS_OPTIONS)) {
            $recordQuery->where('maintenance_fee_status', $maintenanceStatus);
        }

        $records = $recordQuery
            ->orderByDesc('date_of_interment')
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

        return view('cemetery.records', [
            'records' => $records,
            'sites' => $sites,
            'categories' => $categories,
            'statusOptions' => self::STATUS_OPTIONS,
            'maintenanceStatusOptions' => self::MAINTENANCE_STATUS_OPTIONS,
            'plotTypeOptions' => self::PLOT_TYPE_OPTIONS,
            'search' => $search,
            'selectedSiteId' => $siteId,
            'selectedCategoryId' => $categoryId,
            'selectedStatus' => $status,
            'selectedMaintenanceStatus' => $maintenanceStatus,
            'hasActiveFilters' => $hasActiveFilters,
            'nextRecordNo' => $this->nextRecordNo(),
            'summary' => [
                'total_records' => CemeteryOccupantRecord::query()->count(),
                'occupied_plots' => CemeteryPlot::query()->where('is_occupied', true)->count(),
                'available_plots' => CemeteryPlot::query()->where('is_active', true)->where('is_occupied', false)->count(),
                'unpaid_maintenance' => CemeteryOccupantRecord::query()->where('maintenance_fee_status', 'unpaid')->count(),
                'overdue_maintenance' => CemeteryOccupantRecord::query()->where('maintenance_fee_status', 'overdue')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));

        DB::transaction(function () use ($validated): void {
            $plot = $this->resolvePlot($validated);
            $this->ensurePlotAvailability($plot->id, null, (string) $validated['status']);

            $contact = $this->resolveContact($validated);

            CemeteryOccupantRecord::query()->create([
                'record_no' => strtoupper(trim((string) $validated['record_no'])),
                'cemetery_site_id' => (int) $validated['cemetery_site_id'],
                'cemetery_category_id' => (int) $validated['cemetery_category_id'],
                'cemetery_plot_id' => $plot->id,
                'cemetery_contact_id' => $contact->id,
                'deceased_name' => trim((string) $validated['deceased_name']),
                'date_of_interment' => (string) $validated['date_of_interment'],
                'remarks' => $validated['remarks'] ? trim((string) $validated['remarks']) : null,
                'status' => trim((string) $validated['status']),
                'maintenance_fee_status' => trim((string) $validated['maintenance_fee_status']),
                'coverage_start_date' => $validated['coverage_start_date'] ?? null,
                'coverage_end_date' => $validated['coverage_end_date'] ?? null,
                'created_by_user_id' => Auth::id(),
            ]);

            $this->syncPlotOccupancy($plot->id);
        });

        return redirect()
            ->route('cemetery.records')
            ->with('status', 'Occupant record added successfully.');
    }

    public function update(Request $request, CemeteryOccupantRecord $occupantRecord): RedirectResponse
    {
        $validated = $request->validate($this->rules($request, $occupantRecord));

        DB::transaction(function () use ($validated, $occupantRecord): void {
            $previousPlotId = $occupantRecord->cemetery_plot_id;
            $plot = $this->resolvePlot($validated);
            $this->ensurePlotAvailability($plot->id, $occupantRecord->id, (string) $validated['status']);

            $contact = $this->resolveContact($validated);

            $occupantRecord->update([
                'record_no' => strtoupper(trim((string) $validated['record_no'])),
                'cemetery_site_id' => (int) $validated['cemetery_site_id'],
                'cemetery_category_id' => (int) $validated['cemetery_category_id'],
                'cemetery_plot_id' => $plot->id,
                'cemetery_contact_id' => $contact->id,
                'deceased_name' => trim((string) $validated['deceased_name']),
                'date_of_interment' => (string) $validated['date_of_interment'],
                'remarks' => $validated['remarks'] ? trim((string) $validated['remarks']) : null,
                'status' => trim((string) $validated['status']),
                'maintenance_fee_status' => trim((string) $validated['maintenance_fee_status']),
                'coverage_start_date' => $validated['coverage_start_date'] ?? null,
                'coverage_end_date' => $validated['coverage_end_date'] ?? null,
            ]);

            if ($previousPlotId !== $plot->id) {
                $this->syncPlotOccupancy($previousPlotId);
            }
            $this->syncPlotOccupancy($plot->id);
        });

        return redirect()
            ->back()
            ->with('status', "Record {$occupantRecord->record_no} updated.");
    }

    public function destroy(CemeteryOccupantRecord $occupantRecord): RedirectResponse
    {
        $plotId = $occupantRecord->cemetery_plot_id;
        $recordNo = $occupantRecord->record_no;
        $occupantRecord->delete();
        $this->syncPlotOccupancy($plotId);

        return redirect()
            ->back()
            ->with('status', "Record {$recordNo} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request, ?CemeteryOccupantRecord $record = null): array
    {
        $recordNoRule = $record
            ? Rule::unique('cemetery_occupant_records', 'record_no')->ignore($record->id)
            : Rule::unique('cemetery_occupant_records', 'record_no');

        return [
            'record_no' => ['required', 'string', 'max:40', $recordNoRule],
            'cemetery_site_id' => ['required', 'integer', Rule::exists('cemetery_sites', 'id')],
            'cemetery_category_id' => ['required', 'integer', Rule::exists('cemetery_categories', 'id')],
            'plot_reference' => ['required', 'string', 'max:80'],
            'plot_type' => ['required', Rule::in(array_keys(self::PLOT_TYPE_OPTIONS))],
            'deceased_name' => ['required', 'string', 'max:190'],
            'date_of_interment' => ['required', 'date'],
            'contact_person' => ['required', 'string', 'max:160'],
            'contact_number' => ['required', 'string', 'max:60'],
            'address' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'maintenance_fee_status' => ['required', Rule::in(array_keys(self::MAINTENANCE_STATUS_OPTIONS))],
            'coverage_start_date' => ['nullable', 'date'],
            'coverage_end_date' => ['nullable', 'date', 'after_or_equal:coverage_start_date'],
            'form_mode' => ['nullable', 'string', Rule::in(['create', 'edit'])],
            'form_record_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveContact(array $validated): CemeteryContact
    {
        $contactPerson = trim((string) $validated['contact_person']);
        $contactNumber = trim((string) $validated['contact_number']);
        $address = trim((string) $validated['address']);

        return CemeteryContact::query()->firstOrCreate(
            [
                'contact_person' => $contactPerson,
                'contact_number' => $contactNumber,
                'address' => $address,
            ],
            []
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolvePlot(array $validated): CemeteryPlot
    {
        $plot = CemeteryPlot::query()->firstOrNew([
            'cemetery_site_id' => (int) $validated['cemetery_site_id'],
            'plot_reference' => strtoupper(trim((string) $validated['plot_reference'])),
        ]);

        $plot->fill([
            'cemetery_category_id' => (int) $validated['cemetery_category_id'],
            'plot_type' => trim((string) $validated['plot_type']),
            'is_active' => true,
        ]);

        $plot->save();

        return $plot;
    }

    private function ensurePlotAvailability(int $plotId, ?int $ignoreRecordId, string $status): void
    {
        if ($status !== 'active') {
            return;
        }

        $activeRecordExists = CemeteryOccupantRecord::query()
            ->where('cemetery_plot_id', $plotId)
            ->where('status', 'active')
            ->when($ignoreRecordId !== null, fn ($query) => $query->where('id', '!=', $ignoreRecordId))
            ->exists();

        if (! $activeRecordExists) {
            return;
        }

        throw ValidationException::withMessages([
            'plot_reference' => 'The selected niche/lot is already occupied by another active record.',
        ]);
    }

    private function syncPlotOccupancy(?int $plotId): void
    {
        if (! $plotId) {
            return;
        }

        $hasActiveOccupant = CemeteryOccupantRecord::query()
            ->where('cemetery_plot_id', $plotId)
            ->where('status', 'active')
            ->exists();

        CemeteryPlot::query()
            ->whereKey($plotId)
            ->update(['is_occupied' => $hasActiveOccupant]);
    }

    private function nextRecordNo(): string
    {
        $latestNo = (string) CemeteryOccupantRecord::query()
            ->orderByDesc('id')
            ->value('record_no');

        if (preg_match('/(\d+)$/', $latestNo, $matches) === 1) {
            $next = (int) $matches[1] + 1;
            return 'OCC-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        return 'OCC-0001';
    }

    private function syncOverdueMaintenanceStatuses(): void
    {
        CemeteryOccupantRecord::query()
            ->whereNotNull('coverage_end_date')
            ->whereDate('coverage_end_date', '<', now()->toDateString())
            ->whereIn('maintenance_fee_status', ['unpaid', 'partial'])
            ->update(['maintenance_fee_status' => 'overdue']);
    }
}
