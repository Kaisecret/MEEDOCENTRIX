@extends('layouts.app')

@section('content')
@php
    /** @var array<string, array<string, string>> $departments */
    /** @var array<string, array<string, int|float>> $departmentStats */
    /** @var \Illuminate\Support\Collection<int, \App\Models\FishportPaymentType> $fishportPaymentTypes */
    /** @var \Illuminate\Support\Collection<int, \App\Models\FishportCommodity> $fishportCommodities */
    /** @var \Illuminate\Support\Collection<int, \App\Models\FishportUnit> $fishportUnits */
    /** @var \Illuminate\Support\Collection<int, \App\Models\MarketStallType> $marketStallTypes */
    /** @var \Illuminate\Support\Collection<int, \App\Models\MarketStallLocation> $marketLocations */
    /** @var \Illuminate\Support\Collection<int, \App\Models\TerminalVehicleType> $terminalVehicleTypes */
    /** @var \Illuminate\Support\Collection<int, \App\Models\AtriumFunctionHall> $atriumFunctionHalls */
    /** @var \Illuminate\Support\Collection<int, \App\Models\CemeteryFeeRule> $cemeteryFeeRules */

    $money = static fn (float $value): string => 'PHP ' . number_format($value, 2);
    $lastUpdatedLabel = $lastUpdatedAt
        ? \Illuminate\Support\Carbon::parse($lastUpdatedAt)->format('M d, Y h:i A')
        : 'No updates yet';
    $totalItems = collect($departmentStats)->sum('items');
    $totalActive = collect($departmentStats)->sum('active');
    $highestReference = collect($departmentStats)->max('highest') ?? 0;
    $averageReference = collect($departmentStats)->avg('average') ?? 0;
@endphp

<div data-server-rendered-page="rates" data-page-title="Rates & Fees Control" class="rate-page">
    @if (session('status'))
        <div class="rate-alert rate-alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="rate-alert rate-alert-error">
            <i class="fas fa-triangle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <section class="rate-summary" aria-label="Fee summary">
        <div class="rate-kpi">
            <span>Total Fee Items</span>
            <strong>{{ number_format((int) $totalItems) }}</strong>
            <small>Across all departments</small>
        </div>
        <div class="rate-kpi">
            <span>Active Items</span>
            <strong>{{ number_format((int) $totalActive) }}</strong>
            <small>Currently usable in workflows</small>
        </div>
        <div class="rate-kpi">
            <span>Highest Reference</span>
            <strong>{{ $money((float) $highestReference) }}</strong>
            <small>Largest configured amount</small>
        </div>
        <div class="rate-kpi">
            <span>Average Reference</span>
            <strong>{{ $money((float) $averageReference) }}</strong>
            <small>Mean of department averages</small>
        </div>
    </section>

    <form action="{{ route('admin.rates.update') }}" method="POST" class="rate-workspace" id="rateSettingsForm">
        @csrf
        @method('PUT')

        <aside class="rate-nav" aria-label="Department fee tabs">
            @foreach ($departments as $code => $department)
                @php $stats = $departmentStats[$code]; @endphp
                <button
                    type="button"
                    class="rate-tab {{ $loop->first ? 'is-active' : '' }}"
                    data-rate-tab="{{ $code }}"
                    style="--dept-color: {{ $department['accent'] }};"
                >
                    <span class="rate-tab-icon"><i class="{{ $department['icon'] }}"></i></span>
                    <span>
                        <strong>{{ $department['name'] }}</strong>
                        <small>{{ $stats['active'] }} active of {{ $stats['items'] }}</small>
                    </span>
                </button>
            @endforeach
        </aside>

        <section class="rate-main">
            <div class="rate-toolbar">
                <div class="rate-search">
                    <i class="fas fa-search"></i>
                    <input id="rateSearch" type="search" placeholder="Search rates, codes, departments..." autocomplete="off">
                </div>
                <button type="submit" class="rate-save-btn">
                    <i class="fas fa-floppy-disk"></i>
                    Save Fee Settings
                </button>
            </div>

            <section class="rate-panel is-active" data-rate-panel="fishport">
                <div class="rate-panel-head">
                    <div>
                        <h2>Fishport Rates</h2>
                        <p>Controls payment type fees and commodity unit defaults used when Fishport generates transaction charges.</p>
                    </div>
                    @include('admin.partials.rate-stat-strip', ['stats' => $departmentStats['fishport'], 'money' => $money])
                </div>

                <div class="rate-section-title">
                    <h3>Payment Type Fees</h3>
                    <span>Used as default fee per quantity</span>
                </div>
                <div class="rate-table-wrap">
                    <table class="rate-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Item</th>
                                <th class="is-money">Default Fee</th>
                                <th class="is-center">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fishportPaymentTypes as $paymentType)
                                @php
                                    $key = (string) $paymentType->id;
                                    $active = old("fishport_payment_types.{$key}.is_active", $paymentType->is_active);
                                @endphp
                                <tr data-rate-row data-search="{{ strtolower($paymentType->code . ' ' . $paymentType->name . ' fishport') }}">
                                    <td><span class="rate-code">{{ $paymentType->code }}</span></td>
                                    <td>
                                        <input type="hidden" name="fishport_payment_types[{{ $paymentType->id }}][id]" value="{{ $paymentType->id }}">
                                        <strong>{{ $paymentType->name }}</strong>
                                    </td>
                                    <td class="is-money">
                                        <input class="rate-input money-input" type="number" name="fishport_payment_types[{{ $paymentType->id }}][default_fee]" value="{{ old("fishport_payment_types.{$key}.default_fee", number_format((float) $paymentType->default_fee, 2, '.', '')) }}" min="0" step="0.01" required>
                                    </td>
                                    <td class="is-center">
                                        <input type="hidden" name="fishport_payment_types[{{ $paymentType->id }}][is_active]" value="0">
                                        <label class="rate-switch">
                                            <input type="checkbox" name="fishport_payment_types[{{ $paymentType->id }}][is_active]" value="1" {{ $active ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="rate-section-title">
                    <h3>Commodity Unit Defaults</h3>
                    <span>Used when adding commodity rows</span>
                </div>
                <div class="rate-table-wrap">
                    <table class="rate-table">
                        <thead>
                            <tr>
                                <th>Commodity</th>
                                <th>Classification</th>
                                <th>Default Unit</th>
                                <th class="is-money">Conversion</th>
                                <th class="is-center">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fishportCommodities as $commodity)
                                @php
                                    $key = (string) $commodity->id;
                                    $active = old("fishport_commodities.{$key}.is_active", $commodity->is_active);
                                @endphp
                                <tr data-rate-row data-search="{{ strtolower($commodity->name . ' ' . ($commodity->classification?->name ?? '') . ' fishport commodity') }}">
                                    <td>
                                        <input type="hidden" name="fishport_commodities[{{ $commodity->id }}][id]" value="{{ $commodity->id }}">
                                        <strong>{{ $commodity->name }}</strong>
                                    </td>
                                    <td>{{ $commodity->classification?->name ?? '-' }}</td>
                                    <td>
                                        <select class="rate-input" name="fishport_commodities[{{ $commodity->id }}][default_unit_id]" required>
                                            @foreach ($fishportUnits as $unit)
                                                <option value="{{ $unit->id }}" {{ (string) old("fishport_commodities.{$key}.default_unit_id", $commodity->default_unit_id) === (string) $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="is-money">
                                        <input class="rate-input money-input" type="number" name="fishport_commodities[{{ $commodity->id }}][default_conversion]" value="{{ old("fishport_commodities.{$key}.default_conversion", number_format((float) $commodity->default_conversion, 4, '.', '')) }}" min="0.0001" step="0.0001" required>
                                    </td>
                                    <td class="is-center">
                                        <input type="hidden" name="fishport_commodities[{{ $commodity->id }}][is_active]" value="0">
                                        <label class="rate-switch">
                                            <input type="checkbox" name="fishport_commodities[{{ $commodity->id }}][is_active]" value="1" {{ $active ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rate-panel" data-rate-panel="market">
                <div class="rate-panel-head">
                    <div>
                        <h2>Market Rates</h2>
                        <p>Controls stall type rates, notes, and active location reference rates used by market leases.</p>
                    </div>
                    @include('admin.partials.rate-stat-strip', ['stats' => $departmentStats['market'], 'money' => $money])
                </div>

                <div class="rate-section-title"><h3>Stall Type Rates</h3><span>Used for type-based billing</span></div>
                <div class="rate-table-wrap">
                    <table class="rate-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th class="is-money">Default Rate</th>
                                <th>Notes</th>
                                <th class="is-center">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($marketStallTypes as $type)
                                @php $key = (string) $type->id; @endphp
                                <tr data-rate-row data-search="{{ strtolower($type->type_name . ' ' . $type->description . ' ' . $type->rate_notes . ' market') }}">
                                    <td>
                                        <input type="hidden" name="market_stall_types[{{ $type->id }}][id]" value="{{ $type->id }}">
                                        <strong>{{ $type->type_name }}</strong>
                                        <small>{{ $type->description ?: 'No description' }}</small>
                                    </td>
                                    <td class="is-money">
                                        <input class="rate-input money-input" type="number" name="market_stall_types[{{ $type->id }}][default_rate]" value="{{ old("market_stall_types.{$key}.default_rate", number_format((float) $type->default_rate, 2, '.', '')) }}" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <input class="rate-input" type="text" name="market_stall_types[{{ $type->id }}][rate_notes]" value="{{ old("market_stall_types.{$key}.rate_notes", $type->rate_notes) }}" placeholder="Optional rate note">
                                    </td>
                                    <td class="is-center">
                                        <input type="hidden" name="market_stall_types[{{ $type->id }}][is_active]" value="0">
                                        <label class="rate-switch">
                                            <input type="checkbox" name="market_stall_types[{{ $type->id }}][is_active]" value="1" {{ old("market_stall_types.{$key}.is_active", $type->is_active) ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="rate-section-title"><h3>Location Reference Rates</h3><span>Creates a new active rate when amount changes</span></div>
                <div class="rate-table-wrap">
                    <table class="rate-table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Zone / Floor</th>
                                <th class="is-money">Active Rate</th>
                                <th>Effective Start</th>
                                <th class="is-center">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($marketLocations as $location)
                                @php $key = (string) $location->id; @endphp
                                <tr data-rate-row data-search="{{ strtolower($location->location_code . ' ' . $location->location_name . ' ' . $location->zone . ' market location') }}">
                                    <td>
                                        <input type="hidden" name="market_location_rates[{{ $location->id }}][id]" value="{{ $location->id }}">
                                        <strong>{{ $location->location_code }}</strong>
                                        <small>{{ $location->location_name }}</small>
                                    </td>
                                    <td>{{ trim(($location->zone ?: '-') . ' / ' . ($location->floor_level ?: '-')) }}</td>
                                    <td class="is-money">
                                        <input class="rate-input money-input" type="number" name="market_location_rates[{{ $location->id }}][rate_amount]" value="{{ old("market_location_rates.{$key}.rate_amount", number_format((float) ($location->activeRate?->rate_amount ?? 0), 2, '.', '')) }}" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <input class="rate-input" type="date" name="market_location_rates[{{ $location->id }}][effective_start_date]" value="{{ old("market_location_rates.{$key}.effective_start_date", optional($location->activeRate?->effective_start_date)->toDateString() ?: now()->toDateString()) }}">
                                    </td>
                                    <td class="is-center">
                                        <input type="hidden" name="market_location_rates[{{ $location->id }}][is_active]" value="0">
                                        <label class="rate-switch">
                                            <input type="checkbox" name="market_location_rates[{{ $location->id }}][is_active]" value="1" {{ old("market_location_rates.{$key}.is_active", $location->is_active) ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rate-panel" data-rate-panel="cemetery">
                <div class="rate-panel-head">
                    <div>
                        <h2>Cemetery Fees</h2>
                        <p>Controls fee rules used by the Cemetery transaction calculator for future generated dues.</p>
                    </div>
                    @include('admin.partials.rate-stat-strip', ['stats' => $departmentStats['cemetery'], 'money' => $money])
                </div>
                <div class="rate-table-wrap">
                    <table class="rate-table">
                        <thead>
                            <tr>
                                <th>Rule</th>
                                <th>Key</th>
                                <th class="is-money">Amount</th>
                                <th class="is-center">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cemeteryFeeRules as $rule)
                                @php $key = (string) $rule->id; @endphp
                                <tr data-rate-row data-search="{{ strtolower($rule->label . ' ' . $rule->fee_key . ' ' . $rule->description . ' cemetery') }}">
                                    <td>
                                        <input type="hidden" name="cemetery_fee_rules[{{ $rule->id }}][id]" value="{{ $rule->id }}">
                                        <strong>{{ $rule->label }}</strong>
                                        <small>{{ $rule->description }}</small>
                                    </td>
                                    <td><span class="rate-code">{{ $rule->fee_key }}</span></td>
                                    <td class="is-money">
                                        <input class="rate-input money-input" type="number" name="cemetery_fee_rules[{{ $rule->id }}][amount]" value="{{ old("cemetery_fee_rules.{$key}.amount", number_format((float) $rule->amount, 2, '.', '')) }}" min="0" step="0.01" required>
                                    </td>
                                    <td class="is-center">
                                        <input type="hidden" name="cemetery_fee_rules[{{ $rule->id }}][is_active]" value="0">
                                        <label class="rate-switch">
                                            <input type="checkbox" name="cemetery_fee_rules[{{ $rule->id }}][is_active]" value="1" {{ old("cemetery_fee_rules.{$key}.is_active", $rule->is_active) ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rate-panel" data-rate-panel="terminal">
                <div class="rate-panel-head">
                    <div>
                        <h2>Terminal Parking Rates</h2>
                        <p>Controls the hourly parking fee used when terminal parking logs compute billed amounts.</p>
                    </div>
                    @include('admin.partials.rate-stat-strip', ['stats' => $departmentStats['terminal'], 'money' => $money])
                </div>
                <div class="rate-table-wrap">
                    <table class="rate-table">
                        <thead>
                            <tr>
                                <th>Vehicle Type</th>
                                <th>Code</th>
                                <th class="is-money">Hourly Fee</th>
                                <th>Description</th>
                                <th class="is-center">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($terminalVehicleTypes as $type)
                                @php $key = (string) $type->id; @endphp
                                <tr data-rate-row data-search="{{ strtolower($type->code . ' ' . $type->name . ' ' . $type->description . ' terminal') }}">
                                    <td>
                                        <input type="hidden" name="terminal_vehicle_types[{{ $type->id }}][id]" value="{{ $type->id }}">
                                        <strong>{{ $type->name }}</strong>
                                    </td>
                                    <td><span class="rate-code">{{ $type->code }}</span></td>
                                    <td class="is-money">
                                        <input class="rate-input money-input" type="number" name="terminal_vehicle_types[{{ $type->id }}][parking_fee_per_hour]" value="{{ old("terminal_vehicle_types.{$key}.parking_fee_per_hour", number_format((float) $type->parking_fee_per_hour, 2, '.', '')) }}" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <input class="rate-input" type="text" name="terminal_vehicle_types[{{ $type->id }}][description]" value="{{ old("terminal_vehicle_types.{$key}.description", $type->description) }}" placeholder="Optional description">
                                    </td>
                                    <td class="is-center">
                                        <input type="hidden" name="terminal_vehicle_types[{{ $type->id }}][is_active]" value="0">
                                        <label class="rate-switch">
                                            <input type="checkbox" name="terminal_vehicle_types[{{ $type->id }}][is_active]" value="1" {{ old("terminal_vehicle_types.{$key}.is_active", $type->is_active) ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rate-panel" data-rate-panel="atrium">
                <div class="rate-panel-head">
                    <div>
                        <h2>Atrium Hall Rates</h2>
                        <p>Controls hourly function hall rates shown in booking forms and used for hall payment calculations.</p>
                    </div>
                    @include('admin.partials.rate-stat-strip', ['stats' => $departmentStats['atrium'], 'money' => $money])
                </div>
                <div class="rate-table-wrap">
                    <table class="rate-table">
                        <thead>
                            <tr>
                                <th>Hall</th>
                                <th>Code</th>
                                <th>Capacity</th>
                                <th class="is-money">Hourly Rate</th>
                                <th>Description</th>
                                <th class="is-center">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($atriumFunctionHalls as $hall)
                                @php $key = (string) $hall->id; @endphp
                                <tr data-rate-row data-search="{{ strtolower($hall->code . ' ' . $hall->name . ' ' . $hall->description . ' atrium') }}">
                                    <td>
                                        <input type="hidden" name="atrium_function_halls[{{ $hall->id }}][id]" value="{{ $hall->id }}">
                                        <strong>{{ $hall->name }}</strong>
                                    </td>
                                    <td><span class="rate-code">{{ $hall->code }}</span></td>
                                    <td>
                                        <input class="rate-input compact-input" type="number" name="atrium_function_halls[{{ $hall->id }}][capacity]" value="{{ old("atrium_function_halls.{$key}.capacity", $hall->capacity) }}" min="1" step="1">
                                    </td>
                                    <td class="is-money">
                                        <input class="rate-input money-input" type="number" name="atrium_function_halls[{{ $hall->id }}][hourly_rate]" value="{{ old("atrium_function_halls.{$key}.hourly_rate", number_format((float) $hall->hourly_rate, 2, '.', '')) }}" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <input class="rate-input" type="text" name="atrium_function_halls[{{ $hall->id }}][description]" value="{{ old("atrium_function_halls.{$key}.description", $hall->description) }}" placeholder="Optional description">
                                    </td>
                                    <td class="is-center">
                                        <input type="hidden" name="atrium_function_halls[{{ $hall->id }}][is_active]" value="0">
                                        <label class="rate-switch">
                                            <input type="checkbox" name="atrium_function_halls[{{ $hall->id }}][is_active]" value="1" {{ old("atrium_function_halls.{$key}.is_active", $hall->is_active) ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="rate-empty" id="rateSearchEmpty" hidden>
                <i class="fas fa-magnifying-glass"></i>
                <strong>No matching fee items</strong>
                <span>Try another code, department, or rate name.</span>
            </div>
        </section>
    </form>
</div>

<style>
    .rate-page {
        --rate-ink: #0b1a2c;
        --rate-ink-soft: #2a3e57;
        --rate-muted: #6b7d93;
        --rate-line: #e3eaf3;
        --rate-line-strong: #cfdae6;
        --rate-panel: #ffffff;
        --rate-soft: #f6f9fd;
        --rate-softer: #fafcfe;
        --rate-action: #1e6fb8;
        --rate-action-dark: #14528b;
        --rate-success: #0f8a5f;
        --rate-success-soft: #e6f7ee;
        --rate-danger: #b1342f;
        --rate-shadow-sm: 0 1px 2px rgba(15, 35, 60, 0.04);
        --rate-shadow-md: 0 4px 14px rgba(15, 35, 60, 0.06);
        --rate-shadow-lg: 0 10px 30px rgba(15, 35, 60, 0.08);
        --rate-radius: 14px;
        --rate-radius-sm: 10px;
        max-width: 1480px;
        margin: 0 auto;
        padding: 0.5rem 0 2.5rem;
        color: var(--rate-ink);
        font-feature-settings: "ss01", "cv11";
    }

    .rate-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1.2rem;
        padding: 1.4rem 1.6rem;
        margin-bottom: 1rem;
        border-radius: var(--rate-radius);
        background:
            radial-gradient(1100px 220px at -10% -40%, rgba(30, 111, 184, 0.12), transparent 60%),
            radial-gradient(900px 200px at 110% 140%, rgba(15, 138, 95, 0.08), transparent 60%),
            linear-gradient(180deg, #ffffff 0%, #f3f8fd 100%);
        border: 1px solid var(--rate-line);
        box-shadow: var(--rate-shadow-md);
    }

    .rate-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--rate-action);
        background: rgba(30, 111, 184, 0.1);
        padding: 0.32rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .rate-header h1 {
        margin: 0.55rem 0 0;
        font-size: 1.78rem;
        line-height: 1.15;
        font-weight: 850;
        letter-spacing: -0.01em;
    }

    .rate-header p {
        margin: 0.5rem 0 0;
        color: var(--rate-muted);
        max-width: 720px;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .rate-header-meta {
        display: grid;
        gap: 0.2rem;
        text-align: right;
        color: var(--rate-muted);
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
    }

    .rate-header-meta strong {
        color: var(--rate-ink);
        font-size: 0.95rem;
        text-transform: none;
        letter-spacing: 0;
        font-weight: 800;
    }

    .rate-alert {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        margin-top: 1rem;
        border-radius: var(--rate-radius-sm);
        padding: 0.9rem 1.1rem;
        font-weight: 700;
        box-shadow: var(--rate-shadow-sm);
    }

    .rate-alert i {
        font-size: 1.05rem;
    }

    .rate-alert-success {
        border: 1px solid #b5e4cc;
        background: linear-gradient(180deg, #f0fbf5 0%, #e6f7ee 100%);
        color: var(--rate-success);
    }

    .rate-alert-error {
        border: 1px solid #f3c0bf;
        background: linear-gradient(180deg, #fff5f4 0%, #ffeceb 100%);
        color: var(--rate-danger);
    }

    .rate-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.85rem;
        padding: 0 0 1.2rem;
    }

    .rate-kpi {
        position: relative;
        border: 1px solid var(--rate-line);
        border-radius: var(--rate-radius);
        background: var(--rate-panel);
        padding: 1.05rem 1.15rem;
        box-shadow: var(--rate-shadow-sm);
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .rate-kpi::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--rate-action), #4ea3e0);
        opacity: 0.85;
    }

    .rate-kpi:hover {
        transform: translateY(-2px);
        box-shadow: var(--rate-shadow-md);
        border-color: var(--rate-line-strong);
    }

    .rate-kpi span,
    .rate-stat span {
        display: block;
        color: var(--rate-muted);
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.06em;
    }

    .rate-kpi strong {
        display: block;
        margin-top: 0.4rem;
        font-size: 1.5rem;
        font-weight: 850;
        letter-spacing: -0.01em;
    }

    .rate-kpi small {
        display: block;
        margin-top: 0.25rem;
        color: var(--rate-muted);
        font-size: 0.78rem;
    }

    .rate-workspace {
        display: grid;
        grid-template-columns: 290px minmax(0, 1fr);
        align-items: start;
        gap: 1.1rem;
    }

    .rate-nav {
        position: sticky;
        top: 1rem;
        display: grid;
        gap: 0.4rem;
        border: 1px solid var(--rate-line);
        background: var(--rate-panel);
        border-radius: var(--rate-radius);
        padding: 0.65rem;
        box-shadow: var(--rate-shadow-sm);
    }

    .rate-tab {
        position: relative;
        width: 100%;
        border: 1px solid transparent;
        border-radius: var(--rate-radius-sm);
        background: transparent;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.7rem 0.8rem;
        color: var(--rate-ink-soft);
        text-align: left;
        cursor: pointer;
        transition: background 0.18s ease, border-color 0.18s ease, transform 0.12s ease, box-shadow 0.18s ease;
    }

    .rate-tab::before {
        content: '';
        position: absolute;
        left: -1px;
        top: 50%;
        transform: translateY(-50%) scaleY(0);
        width: 3px;
        height: 60%;
        border-radius: 0 3px 3px 0;
        background: var(--dept-color);
        transition: transform 0.18s ease;
    }

    .rate-tab:hover {
        background: var(--rate-softer);
    }

    .rate-tab.is-active {
        background: color-mix(in srgb, var(--dept-color) 7%, white);
        border-color: color-mix(in srgb, var(--dept-color) 28%, var(--rate-line));
        box-shadow: var(--rate-shadow-sm);
    }

    .rate-tab.is-active::before {
        transform: translateY(-50%) scaleY(1);
    }

    .rate-tab-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--dept-color) 14%, white);
        color: var(--dept-color);
        flex-shrink: 0;
        font-size: 0.95rem;
        transition: transform 0.18s ease;
    }

    .rate-tab:hover .rate-tab-icon,
    .rate-tab.is-active .rate-tab-icon {
        transform: scale(1.05);
    }

    .rate-tab strong,
    .rate-tab small {
        display: block;
    }

    .rate-tab strong {
        color: var(--rate-ink);
        font-size: 0.93rem;
        font-weight: 800;
    }

    .rate-tab small {
        margin-top: 0.15rem;
        color: var(--rate-muted);
        font-size: 0.76rem;
    }

    .rate-main {
        min-width: 0;
    }

    .rate-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 0.8rem;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.75rem;
        border: 1px solid var(--rate-line);
        background: var(--rate-panel);
        border-radius: var(--rate-radius);
        box-shadow: var(--rate-shadow-sm);
    }

    .rate-search {
        position: relative;
        flex: 1;
        max-width: 560px;
    }

    .rate-search i {
        position: absolute;
        top: 50%;
        left: 0.95rem;
        transform: translateY(-50%);
        color: #8aa0b6;
        pointer-events: none;
        transition: color 0.18s ease;
    }

    .rate-search input {
        width: 100%;
        min-height: 44px;
        border: 1px solid var(--rate-line);
        border-radius: var(--rate-radius-sm);
        background: var(--rate-soft);
        padding: 0.6rem 0.9rem 0.6rem 2.4rem;
        color: var(--rate-ink);
        font-size: 0.92rem;
        transition: background 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .rate-search input::placeholder {
        color: #8aa0b6;
    }

    .rate-search input:focus {
        background: #ffffff;
    }

    .rate-search:focus-within i {
        color: var(--rate-action);
    }

    .rate-save-btn {
        min-height: 44px;
        border: 0;
        border-radius: var(--rate-radius-sm);
        background: #2563eb;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.6rem 1.25rem;
        font-weight: 800;
        font-size: 0.92rem;
        letter-spacing: 0.01em;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
        transition: transform 0.12s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .rate-save-btn:hover {
        transform: translateY(-1px);
        background: #1d4ed8;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.32);
    }

    .rate-save-btn:active {
        transform: translateY(0);
    }

    .rate-panel {
        display: none;
        border: 1px solid var(--rate-line);
        border-radius: var(--rate-radius);
        background: var(--rate-panel);
        overflow: hidden;
        box-shadow: var(--rate-shadow-md);
    }

    .rate-panel.is-active {
        display: block;
        animation: rateFadeIn 0.25s ease;
    }

    .rate-panel-head {
        display: flex;
        justify-content: space-between;
        gap: 1.2rem;
        align-items: flex-start;
        padding: 1.2rem 1.3rem;
        background:
            radial-gradient(600px 120px at 0% 0%, rgba(30, 111, 184, 0.07), transparent 60%),
            linear-gradient(180deg, #ffffff 0%, var(--rate-soft) 100%);
        border-bottom: 1px solid var(--rate-line);
    }

    .rate-panel-head h2 {
        margin: 0;
        font-size: 1.18rem;
        font-weight: 850;
        letter-spacing: -0.005em;
    }

    .rate-panel-head p {
        margin: 0.4rem 0 0;
        color: var(--rate-muted);
        font-size: 0.9rem;
        max-width: 760px;
        line-height: 1.5;
    }

    .rate-stat-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(96px, 1fr));
        gap: 0.55rem;
        min-width: 320px;
    }

    .rate-stat {
        border: 1px solid var(--rate-line);
        border-radius: var(--rate-radius-sm);
        padding: 0.6rem 0.75rem;
        background: #ffffff;
        transition: border-color 0.18s ease, transform 0.12s ease;
    }

    .rate-stat:hover {
        border-color: var(--rate-line-strong);
        transform: translateY(-1px);
    }

    .rate-stat strong {
        display: block;
        margin-top: 0.25rem;
        font-size: 1rem;
        font-weight: 800;
        color: var(--rate-ink);
    }

    .rate-section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 1.1rem 1.3rem 0.6rem;
    }

    .rate-section-title h3 {
        position: relative;
        margin: 0;
        font-size: 1rem;
        font-weight: 850;
        padding-left: 0.7rem;
        letter-spacing: -0.005em;
    }

    .rate-section-title h3::before {
        content: '';
        position: absolute;
        top: 0.18rem;
        bottom: 0.18rem;
        left: 0;
        width: 3px;
        border-radius: 3px;
        background: linear-gradient(180deg, var(--rate-action), #4ea3e0);
    }

    .rate-section-title span {
        color: var(--rate-muted);
        font-size: 0.82rem;
        font-weight: 600;
    }

    .rate-table-wrap {
        overflow-x: auto;
        padding: 0 1.3rem 1.3rem;
    }

    .rate-table {
        width: 100%;
        min-width: 780px;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid var(--rate-line);
        border-radius: var(--rate-radius-sm);
        overflow: hidden;
        background: #ffffff;
    }

    .rate-table th {
        background: linear-gradient(180deg, #f7fafd 0%, #eef3f9 100%);
        color: #4a5e76;
        padding: 0.78rem 0.85rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 850;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-bottom: 1px solid var(--rate-line);
        white-space: nowrap;
        position: sticky;
        top: 0;
    }

    .rate-table td {
        padding: 0.78rem 0.85rem;
        border-bottom: 1px solid #eef2f7;
        color: var(--rate-ink-soft);
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .rate-table tr:last-child td {
        border-bottom: 0;
    }

    .rate-table tbody tr {
        transition: background 0.14s ease;
    }

    .rate-table tbody tr:hover td {
        background: var(--rate-softer);
    }

    .rate-table tbody tr:focus-within td {
        background: color-mix(in srgb, var(--rate-action) 5%, white);
    }

    .rate-table strong,
    .rate-table small {
        display: block;
    }

    .rate-table strong {
        color: var(--rate-ink);
        font-weight: 800;
    }

    .rate-table small {
        margin-top: 0.2rem;
        color: var(--rate-muted);
        font-size: 0.78rem;
        font-weight: 500;
    }

    .rate-code {
        display: inline-flex;
        max-width: 260px;
        overflow-wrap: anywhere;
        border-radius: 6px;
        background: rgba(30, 111, 184, 0.1);
        color: var(--rate-action-dark);
        padding: 0.22rem 0.5rem;
        font-size: 0.74rem;
        font-weight: 800;
        font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
        letter-spacing: 0.02em;
    }

    .rate-input {
        width: 100%;
        min-height: 40px;
        border: 1px solid var(--rate-line);
        border-radius: 9px;
        background: var(--rate-soft);
        padding: 0.5rem 0.65rem;
        color: var(--rate-ink);
        font-size: 0.9rem;
        transition: background 0.14s ease, border-color 0.14s ease, box-shadow 0.14s ease;
    }

    .rate-input:hover {
        border-color: var(--rate-line-strong);
    }

    .rate-input:focus,
    .rate-search input:focus {
        outline: none;
        background: #ffffff;
        border-color: var(--rate-action);
        box-shadow: 0 0 0 4px rgba(30, 111, 184, 0.14);
    }

    .money-input,
    .compact-input {
        max-width: 150px;
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-weight: 700;
    }

    .is-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .is-center {
        text-align: center;
    }

    .rate-switch {
        display: inline-flex;
        cursor: pointer;
        position: relative;
    }

    .rate-switch input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .rate-switch span {
        width: 46px;
        height: 26px;
        border-radius: 999px;
        background: #cbd7e4;
        position: relative;
        transition: background 0.2s ease, box-shadow 0.2s ease;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.06);
    }

    .rate-switch span::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 5px rgba(12, 38, 63, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .rate-switch:hover span {
        background: #b9c8d8;
    }

    .rate-switch input:checked + span {
        background: linear-gradient(180deg, #14a574 0%, #0f8a5f 100%);
    }

    .rate-switch input:checked + span::after {
        transform: translateX(20px);
    }

    .rate-switch input:focus-visible + span {
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.06), 0 0 0 3px rgba(30, 111, 184, 0.25);
    }

    .rate-empty {
        display: grid;
        place-items: center;
        gap: 0.45rem;
        margin-top: 1rem;
        border: 1px dashed var(--rate-line-strong);
        border-radius: var(--rate-radius);
        padding: 2.2rem 1.6rem;
        color: var(--rate-muted);
        text-align: center;
        background: var(--rate-softer);
    }

    .rate-empty i {
        font-size: 1.5rem;
        color: #8aa0b6;
        margin-bottom: 0.25rem;
    }

    .rate-empty strong {
        color: var(--rate-ink);
        font-size: 1rem;
    }

    @keyframes rateFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1120px) {
        .rate-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rate-workspace {
            grid-template-columns: 1fr;
        }

        .rate-nav {
            position: static;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .rate-header,
        .rate-panel-head,
        .rate-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .rate-header {
            padding: 1.1rem 1.1rem;
        }

        .rate-header-meta {
            text-align: left;
        }

        .rate-summary,
        .rate-nav,
        .rate-stat-strip {
            grid-template-columns: 1fr;
        }

        .rate-stat-strip {
            min-width: 0;
        }

        .rate-search {
            max-width: none;
        }

        .rate-section-title {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(document.querySelectorAll('[data-rate-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-rate-panel]'));
    const searchInput = document.getElementById('rateSearch');
    const emptyState = document.getElementById('rateSearchEmpty');

    const activateTab = function (department) {
        tabs.forEach((tab) => tab.classList.toggle('is-active', tab.dataset.rateTab === department));
        panels.forEach((panel) => panel.classList.toggle('is-active', panel.dataset.ratePanel === department));
        if (searchInput) {
            searchInput.value = '';
            applySearch('');
        }
        window.localStorage.setItem('adminRateDepartment', department);
    };

    const applySearch = function (query) {
        const normalized = query.trim().toLowerCase();
        const activePanel = document.querySelector('[data-rate-panel].is-active');
        if (!activePanel) {
            return;
        }

        const rows = Array.from(activePanel.querySelectorAll('[data-rate-row]'));
        let visibleCount = 0;

        rows.forEach((row) => {
            const isMatch = normalized === '' || (row.dataset.search || '').includes(normalized);
            row.style.display = isMatch ? '' : 'none';
            if (isMatch) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            emptyState.hidden = normalized === '' || visibleCount > 0;
        }
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.rateTab));
    });

    if (searchInput) {
        searchInput.addEventListener('input', (event) => applySearch(event.target.value));
    }

    const savedDepartment = window.localStorage.getItem('adminRateDepartment');
    if (savedDepartment && tabs.some((tab) => tab.dataset.rateTab === savedDepartment)) {
        activateTab(savedDepartment);
    }
});
</script>
@endsection
