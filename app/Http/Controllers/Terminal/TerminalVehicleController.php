<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Models\TerminalVehicle;
use App\Models\TerminalVehicleType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TerminalVehicleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = strtolower(trim((string) $request->query('status', 'active')));
        if (! in_array($status, ['all', 'active', 'inactive'], true)) {
            $status = 'active';
        }

        $vehicles = TerminalVehicle::query()
            ->with('type:id,name,parking_fee_per_hour')
            ->withCount([
                'parkingLogs as total_logs_count',
                'parkingLogs as open_logs_count' => static fn ($query) => $query->whereNull('exit_at'),
            ])
            ->when($search !== '', static function ($query) use ($search): void {
                $like = '%' . $search . '%';
                $query->where(function ($nested) use ($like): void {
                    $nested->where('plate_number', 'like', $like)
                        ->orWhere('operator_name', 'like', $like);
                });
            })
            ->when($status !== 'all', static function ($query) use ($status): void {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('plate_number')
            ->paginate(15)
            ->withQueryString();

        $vehicleTypes = TerminalVehicleType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'parking_fee_per_hour']);

        return view('terminal.vehicles', [
            'vehicles' => $vehicles,
            'vehicleTypes' => $vehicleTypes,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:40', 'unique:terminal_vehicles,plate_number'],
            'operator_name' => ['nullable', 'string', 'max:160'],
            'terminal_vehicle_type_id' => [
                'required',
                Rule::exists('terminal_vehicle_types', 'id')->where(static fn ($query) => $query->where('is_active', true)),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        TerminalVehicle::query()->create([
            'plate_number' => strtoupper(trim((string) $validated['plate_number'])),
            'operator_name' => $validated['operator_name'] ?: null,
            'terminal_vehicle_type_id' => (int) $validated['terminal_vehicle_type_id'],
            'notes' => $validated['notes'] ?: null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('terminal.vehicles')
            ->with('status', 'Vehicle registered successfully.');
    }

    public function update(Request $request, TerminalVehicle $terminalVehicle): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('terminal_vehicles', 'plate_number')->ignore($terminalVehicle->id),
            ],
            'operator_name' => ['nullable', 'string', 'max:160'],
            'terminal_vehicle_type_id' => [
                'required',
                Rule::exists('terminal_vehicle_types', 'id')->where(static fn ($query) => $query->where('is_active', true)),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $terminalVehicle->update([
            'plate_number' => strtoupper(trim((string) $validated['plate_number'])),
            'operator_name' => $validated['operator_name'] ?: null,
            'terminal_vehicle_type_id' => (int) $validated['terminal_vehicle_type_id'],
            'notes' => $validated['notes'] ?: null,
        ]);

        return redirect()
            ->route('terminal.vehicles')
            ->with('status', 'Vehicle updated successfully.');
    }

    public function toggleActive(TerminalVehicle $terminalVehicle): RedirectResponse
    {
        if ($terminalVehicle->is_active && $terminalVehicle->parkingLogs()->whereNull('exit_at')->exists()) {
            return redirect()
                ->route('terminal.vehicles')
                ->with('error', 'Vehicle cannot be deactivated while it has an active parking log.');
        }

        $terminalVehicle->update([
            'is_active' => ! $terminalVehicle->is_active,
        ]);

        return redirect()
            ->route('terminal.vehicles')
            ->with('status', $terminalVehicle->is_active ? 'Vehicle activated.' : 'Vehicle deactivated.');
    }
}

