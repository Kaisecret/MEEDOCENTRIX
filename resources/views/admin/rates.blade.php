@extends('layouts.app')

@section('content')
@php
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\FishportPaymentType> $paymentTypes */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\FishportCommodity> $commodities */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\FishportUnit> $units */
@endphp

<div data-server-rendered-page="rates" data-page-title="Rates & Fees Control" style="max-width: 1360px; margin: 0 auto; padding-bottom: 2rem;">
    @if (session('status'))
        <div class="alert alert-success mb-4" style="border-radius: 12px; border: 1px solid #a7f3d0; background: #ecfdf5; color: #065f46; padding: 1rem 1.1rem;">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4" style="border-radius: 12px; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; padding: 1rem 1.1rem;">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="card" style="border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
            <h3 style="margin: 0; color: #0f172a;">Fishport Rates & Unit Defaults</h3>
            <p style="margin: 0.35rem 0 0; color: #475569;">Admin controls prices here. Fishport transactions automatically use these values.</p>
        </div>

        <form action="{{ route('admin.rates.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f8fafc; color: #334155; font-size: 0.93rem;">
                Update fee prices and unit conversion defaults, then click <strong>Save Rate Settings</strong>.
            </div>

            <div style="padding: 1.1rem 1.5rem 0.75rem;">
                <h4 style="margin: 0; color: #1e293b;">1. Payment Type Fees</h4>
                <p style="margin: 0.25rem 0 0; color: #64748b; font-size: 0.92rem;">These are the per-unit prices used when Fishport computes transaction totals.</p>
            </div>

            <div style="padding: 0 1.5rem 1.25rem; overflow-x: auto;">
                <table style="width: 100%; min-width: 740px; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th style="text-align: left; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Code</th>
                            <th style="text-align: left; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Payment Item</th>
                            <th style="text-align: right; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Default Fee (PHP)</th>
                            <th style="text-align: center; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paymentTypes as $paymentType)
                            @php
                                $paymentKey = (string) $paymentType->id;
                                $oldActive = old("payment_types.{$paymentKey}.is_active");
                                $isActive = is_null($oldActive) ? (bool) $paymentType->is_active : (bool) $oldActive;
                            @endphp
                            <tr style="border-bottom: 1px solid #edf2f7;">
                                <td style="padding: 0.75rem 0.85rem; color: #0f172a; font-weight: 600;">{{ $paymentType->code }}</td>
                                <td style="padding: 0.75rem 0.85rem; color: #334155;">
                                    <input type="hidden" name="payment_types[{{ $paymentType->id }}][id]" value="{{ $paymentType->id }}">
                                    {{ $paymentType->name }}
                                </td>
                                <td style="padding: 0.55rem 0.85rem; text-align: right;">
                                    <input
                                        type="number"
                                        name="payment_types[{{ $paymentType->id }}][default_fee]"
                                        value="{{ old("payment_types.{$paymentKey}.default_fee", number_format((float) $paymentType->default_fee, 2, '.', '')) }}"
                                        min="0"
                                        step="0.01"
                                        required
                                        style="width: 160px; text-align: right; border: 1px solid #cbd5e1; border-radius: 8px; min-height: 38px; padding: 0.4rem 0.6rem;"
                                    >
                                </td>
                                <td style="padding: 0.55rem 0.85rem; text-align: center;">
                                    <input type="hidden" name="payment_types[{{ $paymentType->id }}][is_active]" value="0">
                                    <input type="checkbox" name="payment_types[{{ $paymentType->id }}][is_active]" value="1" {{ $isActive ? 'checked' : '' }} style="width: 1rem; height: 1rem;">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 0.25rem 1.5rem 0.75rem;">
                <h4 style="margin: 0; color: #1e293b;">2. Commodity Unit Defaults</h4>
                <p style="margin: 0.25rem 0 0; color: #64748b; font-size: 0.92rem;">Set the default unit and conversion value used when adding commodity rows.</p>
            </div>

            <div style="padding: 0 1.5rem 1.25rem; overflow-x: auto;">
                <table style="width: 100%; min-width: 920px; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th style="text-align: left; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Commodity</th>
                            <th style="text-align: left; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Classification</th>
                            <th style="text-align: left; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Default Unit</th>
                            <th style="text-align: right; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Unit Conversion</th>
                            <th style="text-align: center; padding: 0.75rem 0.85rem; border-bottom: 1px solid #dbe3ed; font-size: 0.78rem; letter-spacing: .04em; text-transform: uppercase; color: #475569;">Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($commodities as $commodity)
                            @php
                                $commodityKey = (string) $commodity->id;
                                $oldCommodityActive = old("commodities.{$commodityKey}.is_active");
                                $commodityIsActive = is_null($oldCommodityActive) ? (bool) $commodity->is_active : (bool) $oldCommodityActive;
                            @endphp
                            <tr style="border-bottom: 1px solid #edf2f7;">
                                <td style="padding: 0.75rem 0.85rem; color: #0f172a; font-weight: 600;">
                                    <input type="hidden" name="commodities[{{ $commodity->id }}][id]" value="{{ $commodity->id }}">
                                    {{ $commodity->name }}
                                </td>
                                <td style="padding: 0.75rem 0.85rem; color: #334155;">{{ $commodity->classification?->name ?? '-' }}</td>
                                <td style="padding: 0.55rem 0.85rem;">
                                    <select name="commodities[{{ $commodity->id }}][default_unit_id]" required style="width: 100%; min-width: 150px; border: 1px solid #cbd5e1; border-radius: 8px; min-height: 38px; padding: 0.35rem 0.55rem;">
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}" {{ (string) old("commodities.{$commodityKey}.default_unit_id", $commodity->default_unit_id) === (string) $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding: 0.55rem 0.85rem; text-align: right;">
                                    <input
                                        type="number"
                                        name="commodities[{{ $commodity->id }}][default_conversion]"
                                        value="{{ old("commodities.{$commodityKey}.default_conversion", number_format((float) $commodity->default_conversion, 4, '.', '')) }}"
                                        min="0.0001"
                                        step="0.0001"
                                        required
                                        style="width: 160px; text-align: right; border: 1px solid #cbd5e1; border-radius: 8px; min-height: 38px; padding: 0.4rem 0.6rem;"
                                    >
                                </td>
                                <td style="padding: 0.55rem 0.85rem; text-align: center;">
                                    <input type="hidden" name="commodities[{{ $commodity->id }}][is_active]" value="0">
                                    <input type="checkbox" name="commodities[{{ $commodity->id }}][is_active]" value="1" {{ $commodityIsActive ? 'checked' : '' }} style="width: 1rem; height: 1rem;">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 1rem 1.5rem 1.25rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 0.75rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary" style="padding: 0.7rem 1.35rem; border-radius: 8px; font-weight: 600;">
                    Save Rate Settings
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
