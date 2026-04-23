@extends('layouts.app')

@section('content')
@include('terminal.partials.terminal_shared_styles')

<div class="tm" data-server-rendered-page="vehicles" data-page-title="Terminal Vehicles">
    <section class="tm-hero">
        <div>
            <h2>Terminal Vehicle Registry</h2>
            <p>Manage active vehicle units and their parking rates from TFCO vehicle type settings.</p>
        </div>
        <div class="tm-action-row">
            <span class="tm-help">{{ $vehicles->total() }} total vehicle records</span>
            <button type="button" id="openAddVehicleModal" class="tm-btn-primary">
                <i class="fas fa-plus"></i> Add Vehicle
            </button>
        </div>
    </section>

    @if (session('status'))
        <div class="tm-flash">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="tm-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="tm-error">{{ $errors->first() }}</div>
    @endif

    <section class="tm-card">
        <div class="tm-card-head">
            <h3><i class="fas fa-bus"></i> Registered Vehicles</h3>
            <span>Update details or activate/deactivate</span>
        </div>
        <form method="GET" action="{{ route('terminal.vehicles') }}" class="tm-filter-bar">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search plate/operator..." class="tm-input tm-input--grow">
            <select name="status" class="tm-input">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="tm-btn-outline"><i class="fas fa-filter"></i> Apply</button>
            <a href="{{ route('terminal.vehicles') }}" class="tm-btn-outline"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="tm-table-wrap">
            <table class="tm-table">
                <thead>
                    <tr>
                        <th>Plate</th>
                        <th>Operator</th>
                        <th>Type</th>
                        <th>Rate/hr</th>
                        <th>Logs</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td><strong>{{ $vehicle->plate_number }}</strong></td>
                            <td>{{ $vehicle->operator_name ?: '-' }}</td>
                            <td>{{ $vehicle->type?->name ?: '-' }}</td>
                            <td>PHP {{ number_format((float) ($vehicle->type?->parking_fee_per_hour ?? 0), 2) }}</td>
                            <td>{{ number_format((int) $vehicle->total_logs_count) }} total / {{ number_format((int) $vehicle->open_logs_count) }} open</td>
                            <td>
                                @if ($vehicle->is_active)
                                    <span class="tm-tag tm-tag-active">Active</span>
                                @else
                                    <span class="tm-tag tm-tag-inactive">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="tm-action-row">
                                    <button
                                        type="button"
                                        class="tm-btn-outline js-edit-vehicle"
                                        data-modal-id="editVehicleModal{{ $vehicle->id }}"
                                    >
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('terminal.vehicles.toggle_active', $vehicle) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="{{ $vehicle->is_active ? 'tm-btn-danger' : 'tm-btn-primary' }}">
                                            <i class="fas {{ $vehicle->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            {{ $vehicle->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="tm-empty">No vehicles found for this filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="tm-card-body">
            {{ $vehicles->links() }}
        </div>
    </section>
</div>

<div id="addVehicleModal" class="tm-modal-wrap" style="display:none;">
    <div class="tm-modal-card">
        <div class="tm-card-head">
            <h3><i class="fas fa-plus"></i> Register Vehicle</h3>
            <button type="button" class="tm-btn-outline js-close-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="tm-card-body">
            <form method="POST" action="{{ route('terminal.vehicles.store') }}" class="tm-form-grid">
                @csrf
                <input type="hidden" name="form_context" value="add">
                <div class="tm-field">
                    <label for="add_plate_number">Plate Number</label>
                    <input id="add_plate_number" name="plate_number" class="tm-input" value="{{ old('plate_number') }}" required>
                </div>
                <div class="tm-field">
                    <label for="add_operator_name">Operator Name</label>
                    <input id="add_operator_name" name="operator_name" class="tm-input" value="{{ old('operator_name') }}">
                </div>
                <div class="tm-field">
                    <label for="add_terminal_vehicle_type_id">Vehicle Type</label>
                    <select id="add_terminal_vehicle_type_id" name="terminal_vehicle_type_id" class="tm-input" required>
                        <option value="">Select type...</option>
                        @foreach ($vehicleTypes as $type)
                            <option value="{{ $type->id }}" {{ (string) old('terminal_vehicle_type_id') === (string) $type->id ? 'selected' : '' }}>
                                {{ $type->name }} - PHP {{ number_format((float) $type->parking_fee_per_hour, 2) }}/hr
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="tm-field">
                    <label for="add_notes">Notes</label>
                    <input id="add_notes" name="notes" class="tm-input" value="{{ old('notes') }}">
                </div>
                <div class="tm-field full tm-form-actions">
                    <button type="button" class="tm-btn-outline js-close-modal"><i class="fas fa-times"></i> Cancel</button>
                    <button type="submit" class="tm-btn-primary tm-btn-primary-strong"><i class="fas fa-save"></i> Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($vehicles as $vehicle)
    <div id="editVehicleModal{{ $vehicle->id }}" class="tm-modal-wrap" style="display:none;">
        <div class="tm-modal-card">
            <div class="tm-card-head">
                <h3><i class="fas fa-pen"></i> Edit Vehicle</h3>
                <button type="button" class="tm-btn-outline js-close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="tm-card-body">
                <form method="POST" action="{{ route('terminal.vehicles.update', $vehicle) }}" class="tm-form-grid">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="edit-{{ $vehicle->id }}">
                    <div class="tm-field">
                        <label>Plate Number</label>
                        <input name="plate_number" class="tm-input" value="{{ old('form_context') === 'edit-' . $vehicle->id ? old('plate_number') : $vehicle->plate_number }}" required>
                    </div>
                    <div class="tm-field">
                        <label>Operator Name</label>
                        <input name="operator_name" class="tm-input" value="{{ old('form_context') === 'edit-' . $vehicle->id ? old('operator_name') : $vehicle->operator_name }}">
                    </div>
                    <div class="tm-field">
                        <label>Vehicle Type</label>
                        <select name="terminal_vehicle_type_id" class="tm-input" required>
                            @foreach ($vehicleTypes as $type)
                                @php
                                    $selectedType = old('form_context') === 'edit-' . $vehicle->id
                                        ? (int) old('terminal_vehicle_type_id')
                                        : (int) $vehicle->terminal_vehicle_type_id;
                                @endphp
                                <option value="{{ $type->id }}" {{ $selectedType === (int) $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} - PHP {{ number_format((float) $type->parking_fee_per_hour, 2) }}/hr
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tm-field">
                        <label>Notes</label>
                        <input name="notes" class="tm-input" value="{{ old('form_context') === 'edit-' . $vehicle->id ? old('notes') : $vehicle->notes }}">
                    </div>
                    <div class="tm-field full tm-form-actions">
                        <button type="button" class="tm-btn-outline js-close-modal"><i class="fas fa-times"></i> Cancel</button>
                        <button type="submit" class="tm-btn-primary tm-btn-primary-strong"><i class="fas fa-save"></i> Update Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<style>
    .tm-modal-wrap {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        z-index: 1400;
        padding: 1rem;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .tm-modal-wrap.is-open { display: flex !important; }
    .tm-modal-card {
        width: min(860px, 100%);
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .tm-modal-card .tm-card-head {
        background: #fff;
        flex-shrink: 0;
    }
    .tm-modal-card .tm-card-body {
        overflow-y: auto;
        padding: 1rem 1.2rem 1.2rem;
    }
    .tm-form-actions {
        display: flex;
        gap: 10px;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
        position: sticky;
        bottom: -1px;
        background: #fff;
    }
    .tm-btn-primary-strong {
        background: #0f5fa8 !important;
        border-color: #0f5fa8 !important;
        color: #fff !important;
        min-width: 190px;
        justify-content: center;
        box-shadow: 0 6px 16px rgba(15,95,168,.22);
    }
    .tm-btn-primary-strong:hover {
        background: #0a4880 !important;
        border-color: #0a4880 !important;
    }
    @media (max-width: 640px) {
        .tm-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }
        .tm-form-actions .tm-btn-primary,
        .tm-form-actions .tm-btn-outline {
            justify-content: center;
        }
    }
</style>

<script>
    (function () {
        const addModal = document.getElementById('addVehicleModal');
        const addButton = document.getElementById('openAddVehicleModal');

        function openModal(modal) {
            if (!modal) return;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modal) {
            if (!modal) return;
            modal.classList.remove('is-open');
            if (!document.querySelector('.tm-modal-wrap.is-open')) {
                document.body.style.overflow = '';
            }
        }

        if (addButton) {
            addButton.addEventListener('click', function () {
                openModal(addModal);
            });
        }

        document.querySelectorAll('.js-edit-vehicle').forEach(function (button) {
            button.addEventListener('click', function () {
                const modalId = button.getAttribute('data-modal-id');
                const modal = document.getElementById(modalId);
                openModal(modal);
            });
        });

        document.querySelectorAll('.tm-modal-wrap').forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.querySelectorAll('.js-close-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(button.closest('.tm-modal-wrap'));
            });
        });

        const oldFormContext = @json(old('form_context', ''));
        if (oldFormContext === 'add') {
            openModal(addModal);
        } else if (oldFormContext.startsWith('edit-')) {
            const id = oldFormContext.replace('edit-', '');
            openModal(document.getElementById('editVehicleModal' + id));
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal(addModal);
                document.querySelectorAll('.tm-modal-wrap').forEach(function (modal) {
                    closeModal(modal);
                });
            }
        });
    })();
</script>
@endsection
