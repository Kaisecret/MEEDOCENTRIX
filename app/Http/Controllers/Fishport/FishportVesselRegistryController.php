<?php

namespace App\Http\Controllers\Fishport;

use App\Http\Controllers\Controller;
use App\Models\FishportVessel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class FishportVesselRegistryController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const VESSEL_TYPES = [
        'Small Municipal',
        'Medium Commercial',
        'Large Commercial',
        'Utility/Service',
        'Cargo/Support',
    ];

    /**
     * @var array<int, string>
     */
    private const REGISTRATION_STATUSES = [
        'Active',
        'Expired',
        'Pending Renewal',
        'Suspended',
    ];

    /**
     * @var array<string, string>
     */
    private const DOCUMENT_INPUT_TO_COLUMN = [
        'certificate_of_ownership_file' => 'certificate_of_ownership_path',
        'previous_registration_file' => 'previous_registration_path',
        'boat_permit_license_file' => 'boat_permit_license_path',
        'engine_receipt_proof_file' => 'engine_receipt_proof_path',
        'valid_id_file' => 'valid_id_path',
        'inspection_certificate_file' => 'inspection_certificate_path',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $vesselQuery = FishportVessel::query()
            ->with([
                'ownerProfile',
                'operatorProfile',
                'documentProfile',
                'registrationProfile.creator:id,name',
                'registrationProfile.updater:id,name',
            ]);

        if ($search !== '') {
            $likeSearch = '%' . $search . '%';
            $vesselQuery->where(function ($query) use ($likeSearch): void {
                $query->where('name', 'like', $likeSearch)
                    ->orWhere('vessel_type', 'like', $likeSearch)
                    ->orWhereHas('ownerProfile', function ($ownerQuery) use ($likeSearch): void {
                        $ownerQuery->where('full_name', 'like', $likeSearch)
                            ->orWhere('business_name', 'like', $likeSearch);
                    })
                    ->orWhereHas('registrationProfile', function ($registrationQuery) use ($likeSearch): void {
                        $registrationQuery->where('registration_number', 'like', $likeSearch)
                            ->orWhere('official_number', 'like', $likeSearch)
                            ->orWhere('plate_permit_number', 'like', $likeSearch)
                            ->orWhere('home_port', 'like', $likeSearch);
                    });
            });
        }

        $vessels = $vesselQuery
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('fishport.vessel_registry', [
            'vessels' => $vessels,
            'search' => $search,
            'vesselTypes' => self::VESSEL_TYPES,
            'registrationStatuses' => self::REGISTRATION_STATUSES,
            'activeCount' => FishportVessel::query()->where('is_active', true)->count(),
            'inactiveCount' => FishportVessel::query()->where('is_active', false)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rulesForRequest($request));
        $actorId = Auth::id();

        $vessel = FishportVessel::query()->create($this->buildVesselPayload($validated, $request));
        $documentPaths = $this->storeUploadedDocuments($request);

        $this->saveNormalizedProfiles($vessel, $validated, $documentPaths, $actorId, true);

        return redirect()
            ->route('fishport.vessel_registry')
            ->with('status', 'Vessel registered successfully.');
    }

    public function update(Request $request, FishportVessel $fishportVessel): RedirectResponse
    {
        $fishportVessel->loadMissing(['documentProfile', 'registrationProfile']);
        $validated = $request->validate($this->rulesForRequest($request, $fishportVessel));
        $actorId = Auth::id();

        $fishportVessel->update($this->buildVesselPayload($validated, $request));
        $documentPaths = $this->storeUploadedDocuments($request);

        $this->saveNormalizedProfiles($fishportVessel, $validated, $documentPaths, $actorId, false);

        return redirect()
            ->back()
            ->with('status', "Vessel {$fishportVessel->name} updated.");
    }

    public function toggleActive(FishportVessel $fishportVessel): RedirectResponse
    {
        $nextStatus = ! $fishportVessel->is_active;
        $fishportVessel->update(['is_active' => $nextStatus]);

        return redirect()
            ->back()
            ->with('status', "Vessel {$fishportVessel->name} is now " . ($nextStatus ? 'active' : 'inactive') . '.');
    }

    public function destroy(FishportVessel $fishportVessel): RedirectResponse
    {
        $vesselName = $fishportVessel->name;
        DB::transaction(function () use ($fishportVessel): void {
            // Permanent delete request: remove dependent logs first, then vessel.
            $fishportVessel->logs()->delete();
            $fishportVessel->delete();
        });

        return redirect()
            ->back()
            ->with('status', "Vessel {$vesselName} permanently deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForRequest(Request $request, ?FishportVessel $fishportVessel = null): array
    {
        $nameRule = $fishportVessel
            ? Rule::unique('fishport_vessels', 'name')->ignore($fishportVessel->id)
            : Rule::unique('fishport_vessels', 'name');

        $rules = [
            'name' => ['required', 'string', 'max:150', $nameRule],
            'vessel_type' => ['required', 'string', 'max:100'],
            'registration_number' => ['required', 'string', 'max:120'],
            'official_number' => ['required', 'string', 'max:120'],
            'plate_permit_number' => ['required', 'string', 'max:120'],
            'home_port' => ['required', 'string', 'max:150'],
            'gross_tonnage' => ['required', 'numeric', 'min:0'],
            'net_tonnage' => ['required', 'numeric', 'min:0'],
            'vessel_length' => ['required', 'numeric', 'min:0'],
            'beam_width' => ['required', 'numeric', 'min:0'],
            'vessel_depth' => ['required', 'numeric', 'min:0'],
            'engine_type' => ['required', 'string', 'max:120'],
            'engine_horsepower' => ['required', 'numeric', 'min:0'],
            'hull_material' => ['required', 'string', 'max:120'],
            'color_markings' => ['required', 'string', 'max:150'],
            'year_built' => ['required', 'integer', 'digits:4', 'min:1900', 'max:' . date('Y')],

            'owner_name' => ['required', 'string', 'max:150'],
            'owner_address' => ['required', 'string', 'max:255'],
            'owner_contact_number' => ['required', 'string', 'max:60'],
            'owner_email' => ['required', 'email', 'max:150'],
            'owner_government_id_number' => ['required', 'string', 'max:150'],
            'business_name' => ['required', 'string', 'max:150'],

            'captain_operator_name' => ['required', 'string', 'max:150'],
            'captain_license_number' => ['required', 'string', 'max:120'],
            'captain_contact_number' => ['required', 'string', 'max:60'],
            'captain_address' => ['required', 'string', 'max:255'],

            'registration_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date', 'after_or_equal:registration_date'],
            'registration_status' => ['required', 'string', Rule::in(self::REGISTRATION_STATUSES)],
            'renewal_date' => ['required', 'date'],
            'issued_by' => ['required', 'string', 'max:150'],

            'remarks' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];

        foreach (self::DOCUMENT_INPUT_TO_COLUMN as $inputName => $columnName) {
            $isMissingCurrentDocument = ! $fishportVessel || empty($fishportVessel->documentProfile?->{$columnName});
            $rules[$inputName] = [
                Rule::requiredIf($isMissingCurrentDocument),
                'nullable',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(5 * 1024),
            ];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildVesselPayload(array $validated, Request $request): array
    {
        return [
            'name' => trim((string) $validated['name']),
            'vessel_type' => trim((string) $validated['vessel_type']),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, string>  $newDocumentPaths
     */
    private function saveNormalizedProfiles(
        FishportVessel $fishportVessel,
        array $validated,
        array $newDocumentPaths,
        ?int $actorId,
        bool $isCreate
    ): void {
        $fishportVessel->loadMissing(['documentProfile', 'registrationProfile']);

        $fishportVessel->ownerProfile()->updateOrCreate([], [
            'full_name' => trim((string) $validated['owner_name']),
            'address' => trim((string) $validated['owner_address']),
            'contact_number' => trim((string) $validated['owner_contact_number']),
            'email' => trim((string) $validated['owner_email']),
            'government_id_number' => trim((string) $validated['owner_government_id_number']),
            'business_name' => trim((string) $validated['business_name']),
        ]);

        $fishportVessel->operatorProfile()->updateOrCreate([], [
            'name' => trim((string) $validated['captain_operator_name']),
            'license_number' => trim((string) $validated['captain_license_number']),
            'contact_number' => trim((string) $validated['captain_contact_number']),
            'address' => trim((string) $validated['captain_address']),
        ]);

        $existingDocuments = $fishportVessel->documentProfile;
        $documentPayload = [];

        foreach (self::DOCUMENT_INPUT_TO_COLUMN as $columnName) {
            if (array_key_exists($columnName, $newDocumentPaths)) {
                $documentPayload[$columnName] = $newDocumentPaths[$columnName];
                continue;
            }

            $documentPayload[$columnName] = $existingDocuments?->{$columnName};
        }

        $allDocumentsPresent = collect(self::DOCUMENT_INPUT_TO_COLUMN)
            ->every(static fn ($columnName) => ! empty($documentPayload[$columnName]));

        $fishportVessel->documentProfile()->updateOrCreate([], $documentPayload);

        $existingRegistration = $fishportVessel->registrationProfile;
        $fishportVessel->registrationProfile()->updateOrCreate([], [
            'registration_number' => trim((string) $validated['registration_number']),
            'official_number' => trim((string) $validated['official_number']),
            'plate_permit_number' => trim((string) $validated['plate_permit_number']),
            'home_port' => trim((string) $validated['home_port']),
            'gross_tonnage' => (float) $validated['gross_tonnage'],
            'net_tonnage' => (float) $validated['net_tonnage'],
            'vessel_length' => (float) $validated['vessel_length'],
            'beam_width' => (float) $validated['beam_width'],
            'vessel_depth' => (float) $validated['vessel_depth'],
            'engine_type' => trim((string) $validated['engine_type']),
            'engine_horsepower' => (float) $validated['engine_horsepower'],
            'hull_material' => trim((string) $validated['hull_material']),
            'color_markings' => trim((string) $validated['color_markings']),
            'year_built' => (int) $validated['year_built'],
            'registration_date' => $validated['registration_date'],
            'expiration_date' => $validated['expiration_date'],
            'registration_status' => trim((string) $validated['registration_status']),
            'renewal_date' => $validated['renewal_date'],
            'issued_by' => trim((string) $validated['issued_by']),
            'supporting_documents_uploaded' => $allDocumentsPresent,
            'created_by' => $isCreate ? $actorId : ($existingRegistration?->created_by ?? $actorId),
            'updated_by' => $actorId,
            'remarks' => trim((string) $validated['remarks']),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function storeUploadedDocuments(Request $request): array
    {
        $stored = [];

        foreach (self::DOCUMENT_INPUT_TO_COLUMN as $inputName => $columnName) {
            if (! $request->hasFile($inputName)) {
                continue;
            }

            $stored[$columnName] = $request
                ->file($inputName)
                ->store('fishport/vessels/documents', 'public');
        }

        return $stored;
    }
}
