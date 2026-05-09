<?php

namespace Tests\Feature;

use App\Models\AtriumFunctionHall;
use App\Models\CemeteryFeeRule;
use App\Models\FishportCommodity;
use App\Models\FishportCommodityClassification;
use App\Models\FishportPaymentType;
use App\Models\MarketStallLocation;
use App\Models\MarketStallType;
use App\Models\TerminalRouteFare;
use App\Models\TerminalVehicleType;
use App\Models\User;
use App\Support\CemeteryFeeCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRateManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite is required for the in-memory database configured by phpunit.xml.');
        }

        parent::setUp();
    }

    public function test_admin_can_view_all_department_rate_controls(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'department' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.rates'));

        $response->assertOk();
        $response->assertSee('Fishport Rates');
        $response->assertSee('Market Rates');
        $response->assertSee('Cemetery Fees');
        $response->assertSee('Terminal Parking Rates');
        $response->assertSee('Atrium Hall Rates');
    }

    public function test_admin_rate_updates_affect_department_fee_generation_sources(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'department' => 'admin',
            'is_active' => true,
        ]);

        $fishportType = FishportPaymentType::query()->where('code', 'ENTRANCE')->firstOrFail();
        $marketType = MarketStallType::query()->firstOrFail();
        $marketLocation = MarketStallLocation::query()->with('activeRate')->firstOrFail();
        $terminalType = TerminalVehicleType::query()->where('code', 'BUS')->firstOrFail();
        $atriumHall = AtriumFunctionHall::query()->where('code', 'ATR-MAIN')->firstOrFail();
        $cemeteryRule = CemeteryFeeRule::query()->where('fee_key', 'base.single_niche.sjm.infant')->firstOrFail();

        $payload = $this->fullRatePayload();
        $payload['fishport_payment_types'][$fishportType->id]['default_fee'] = 123.45;
        $payload['market_stall_types'][$marketType->id]['default_rate'] = 234.56;
        $payload['market_location_rates'][$marketLocation->id]['rate_amount'] = 345.67;
        $payload['terminal_vehicle_types'][$terminalType->id]['parking_fee_per_hour'] = 456.78;
        $payload['atrium_function_halls'][$atriumHall->id]['hourly_rate'] = 567.89;
        $payload['cemetery_fee_rules'][$cemeteryRule->id]['amount'] = 6789.00;

        $response = $this->actingAs($admin)->put(route('admin.rates.update'), $payload);

        $response->assertRedirect(route('admin.rates'));
        $response->assertSessionHasNoErrors();

        $this->assertSame(123.45, (float) $fishportType->fresh()->default_fee);
        $this->assertSame(234.56, (float) $marketType->fresh()->default_rate);
        $this->assertSame(456.78, (float) $terminalType->fresh()->parking_fee_per_hour);
        $this->assertSame(567.89, (float) $atriumHall->fresh()->hourly_rate);

        $marketLocation->refresh()->load('activeRate');
        $this->assertSame(345.67, (float) $marketLocation->activeRate->rate_amount);

        $fees = CemeteryFeeCalculator::compute(
            'SJM',
            'INFANT',
            'SINGLE_NICHE_PURCHASE',
            'none',
            null,
            false,
            0
        );

        $this->assertSame(6789.00, $fees['base_fee']);
        $this->assertSame(6789.00, $fees['amount_due']);
    }

    public function test_admin_can_delete_newly_added_fishport_payment_type(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'department' => 'admin',
            'is_active' => true,
        ]);

        $newPaymentType = FishportPaymentType::query()->create([
            'code' => 'TEMP_DELETE',
            'name' => 'Temporary Delete',
            'default_fee' => 10.00,
            'is_active' => true,
        ]);

        $payload = $this->fullRatePayload();
        $payload['delete_fishport_payment_type_ids'] = [$newPaymentType->id];

        $response = $this->actingAs($admin)->put(route('admin.rates.update'), $payload);

        $response->assertRedirect(route('admin.rates'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', static fn (string $status): bool => str_contains($status, 'Deleted: 1 Fishport payment type(s).'));
        $this->assertDatabaseMissing('fishport_payment_types', ['id' => $newPaymentType->id]);
    }

    public function test_admin_can_delete_newly_added_fishport_commodity(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'department' => 'admin',
            'is_active' => true,
        ]);

        $classification = FishportCommodityClassification::query()->firstOrFail();
        $unitId = FishportCommodity::query()->firstOrFail()->default_unit_id;

        $newCommodity = FishportCommodity::query()->create([
            'name' => 'Temporary Commodity Delete',
            'classification_id' => $classification->id,
            'default_unit_id' => $unitId,
            'default_conversion' => 1.0000,
            'is_active' => true,
        ]);

        $payload = $this->fullRatePayload();
        $payload['delete_fishport_commodity_ids'] = [$newCommodity->id];

        $response = $this->actingAs($admin)->put(route('admin.rates.update'), $payload);

        $response->assertRedirect(route('admin.rates'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', static fn (string $status): bool => str_contains($status, 'Fishport commodit(ies)'));
        $this->assertDatabaseMissing('fishport_commodities', ['id' => $newCommodity->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fullRatePayload(): array
    {
        return [
            'fishport_payment_types' => FishportPaymentType::query()
                ->get()
                ->mapWithKeys(fn (FishportPaymentType $type): array => [
                    $type->id => [
                        'id' => $type->id,
                        'default_fee' => (float) $type->default_fee,
                        'is_active' => $type->is_active ? 1 : 0,
                    ],
                ])
                ->all(),
            'fishport_commodities' => FishportCommodity::query()
                ->get()
                ->mapWithKeys(fn (FishportCommodity $commodity): array => [
                    $commodity->id => [
                        'id' => $commodity->id,
                        'default_unit_id' => $commodity->default_unit_id,
                        'default_conversion' => (float) $commodity->default_conversion,
                        'is_active' => $commodity->is_active ? 1 : 0,
                    ],
                ])
                ->all(),
            'market_stall_types' => MarketStallType::query()
                ->get()
                ->mapWithKeys(fn (MarketStallType $type): array => [
                    $type->id => [
                        'id' => $type->id,
                        'default_rate' => (float) $type->default_rate,
                        'rate_notes' => $type->rate_notes,
                        'is_active' => $type->is_active ? 1 : 0,
                    ],
                ])
                ->all(),
            'market_location_rates' => MarketStallLocation::query()
                ->with('activeRate')
                ->get()
                ->mapWithKeys(fn (MarketStallLocation $location): array => [
                    $location->id => [
                        'id' => $location->id,
                        'rate_amount' => (float) ($location->activeRate?->rate_amount ?? 0),
                        'effective_start_date' => now()->toDateString(),
                        'is_active' => $location->is_active ? 1 : 0,
                    ],
                ])
                ->all(),
            'terminal_vehicle_types' => TerminalVehicleType::query()
                ->get()
                ->mapWithKeys(fn (TerminalVehicleType $type): array => [
                    $type->id => [
                        'id' => $type->id,
                        'parking_fee_per_hour' => (float) $type->parking_fee_per_hour,
                        'description' => $type->description,
                        'is_active' => $type->is_active ? 1 : 0,
                    ],
                ])
                ->all(),
            'terminal_route_fares' => TerminalRouteFare::query()
                ->get()
                ->mapWithKeys(fn (TerminalRouteFare $routeFare): array => [
                    $routeFare->id => [
                        'id' => $routeFare->id,
                        'vehicle_kind' => $routeFare->vehicle_kind,
                        'route_name' => $routeFare->route_name,
                        'fare_amount' => (float) $routeFare->fare_amount,
                        'is_active' => $routeFare->is_active ? 1 : 0,
                    ],
                ])
                ->all(),
            'atrium_function_halls' => AtriumFunctionHall::query()
                ->get()
                ->mapWithKeys(fn (AtriumFunctionHall $hall): array => [
                    $hall->id => [
                        'id' => $hall->id,
                        'capacity' => $hall->capacity,
                        'hourly_rate' => (float) $hall->hourly_rate,
                        'description' => $hall->description,
                        'is_active' => $hall->is_active ? 1 : 0,
                    ],
                ])
                ->all(),
            'cemetery_fee_rules' => CemeteryFeeRule::query()
                ->get()
                ->mapWithKeys(fn (CemeteryFeeRule $rule): array => [
                    $rule->id => [
                        'id' => $rule->id,
                        'amount' => (float) $rule->amount,
                        'is_active' => $rule->is_active ? 1 : 0,
                    ],
                ])
                ->all(),
        ];
    }
}
