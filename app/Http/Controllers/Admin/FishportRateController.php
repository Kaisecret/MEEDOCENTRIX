<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FishportCommodity;
use App\Models\FishportPaymentType;
use App\Models\FishportUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FishportRateController extends Controller
{
    public function index(): View
    {
        return view('admin.rates', [
            'paymentTypes' => FishportPaymentType::query()
                ->orderBy('id')
                ->get(['id', 'code', 'name', 'default_fee', 'is_active']),
            'commodities' => FishportCommodity::query()
                ->with(['classification:id,name', 'defaultUnit:id,name'])
                ->orderBy('name')
                ->get(['id', 'name', 'classification_id', 'default_unit_id', 'default_conversion', 'is_active']),
            'units' => FishportUnit::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_types' => ['required', 'array', 'min:1'],
            'payment_types.*.id' => ['required', 'integer', Rule::exists('fishport_payment_types', 'id')],
            'payment_types.*.default_fee' => ['required', 'numeric', 'min:0'],
            'payment_types.*.is_active' => ['nullable', 'boolean'],
            'commodities' => ['required', 'array', 'min:1'],
            'commodities.*.id' => ['required', 'integer', Rule::exists('fishport_commodities', 'id')],
            'commodities.*.default_unit_id' => ['required', 'integer', Rule::exists('fishport_units', 'id')],
            'commodities.*.default_conversion' => ['required', 'numeric', 'min:0.0001'],
            'commodities.*.is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['payment_types'] as $paymentTypeInput) {
                FishportPaymentType::query()
                    ->whereKey((int) $paymentTypeInput['id'])
                    ->update([
                        'default_fee' => round((float) $paymentTypeInput['default_fee'], 2),
                        'is_active' => (bool) ($paymentTypeInput['is_active'] ?? false),
                    ]);
            }

            foreach ($validated['commodities'] as $commodityInput) {
                FishportCommodity::query()
                    ->whereKey((int) $commodityInput['id'])
                    ->update([
                        'default_unit_id' => (int) $commodityInput['default_unit_id'],
                        'default_conversion' => round((float) $commodityInput['default_conversion'], 4),
                        'is_active' => (bool) ($commodityInput['is_active'] ?? false),
                    ]);
            }
        });

        return redirect()
            ->route('admin.rates')
            ->with('status', 'Fishport rate settings updated successfully.');
    }
}
