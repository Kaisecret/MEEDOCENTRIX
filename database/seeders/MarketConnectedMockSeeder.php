<?php

namespace Database\Seeders;

use App\Models\CollectionDispatch;
use App\Models\CollectionDispatchItem;
use App\Models\CollectorDepartmentAssignment;
use App\Models\Department;
use App\Models\MarketPaymentCollection;
use App\Models\MarketStall;
use App\Models\MarketStallLease;
use App\Models\MarketStallLocation;
use App\Models\MarketStallRate;
use App\Models\MarketStallType;
use App\Models\MarketTenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MarketConnectedMockSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = Carbon::now();

            $department = Department::query()->updateOrCreate(
                ['code' => 'market'],
                [
                    'name' => 'Public Market',
                    'allows_collectors' => true,
                    'direct_payment_only' => false,
                    'is_active' => true,
                ]
            );

            $sender = User::query()->firstOrCreate(
                ['email' => 'lena.ramos@meedocentrix.local'],
                [
                    'name' => 'Lena Ramos',
                    'username' => 'lena.ramos',
                    'password' => 'password123',
                    'role' => 'personnel',
                    'department' => 'market',
                    'is_active' => true,
                ]
            );

            $collector = User::query()->firstOrCreate(
                ['email' => 'marco.reyes@meedocentrix.local'],
                [
                    'name' => 'Marco Reyes',
                    'username' => 'marco.reyes',
                    'password' => 'password123',
                    'role' => 'collector',
                    'department' => 'collector',
                    'is_active' => true,
                ]
            );

            CollectorDepartmentAssignment::query()->updateOrCreate(
                ['collector_user_id' => $collector->id],
                [
                    'department_id' => $department->id,
                    'assigned_by_user_id' => $sender->id,
                ]
            );

            $locationSeeds = [
                ['code' => 'A1-104', 'name' => 'Arcade Row 1', 'zone' => 'East Wing', 'floor' => 'Ground', 'rate' => 92.50],
                ['code' => 'B2-018', 'name' => 'Block B Row 2', 'zone' => 'Central', 'floor' => 'Ground', 'rate' => 88.00],
                ['code' => 'C1-031', 'name' => 'Cold Goods Strip', 'zone' => 'West Wing', 'floor' => 'Ground', 'rate' => 110.00],
                ['code' => 'D3-011', 'name' => 'Daily Vendors Extension', 'zone' => 'South Wing', 'floor' => 'Ground', 'rate' => 79.25],
                ['code' => 'T2-202', 'name' => 'Terminal Row 2', 'zone' => 'North Wing', 'floor' => 'Ground', 'rate' => 104.92],
            ];

            $locationMap = [];
            foreach ($locationSeeds as $seed) {
                $location = MarketStallLocation::query()->updateOrCreate(
                    ['location_code' => $seed['code']],
                    [
                        'location_name' => $seed['name'],
                        'zone' => $seed['zone'],
                        'floor_level' => $seed['floor'],
                        'remarks' => 'Main market location',
                        'is_active' => true,
                    ]
                );

                $activeRate = MarketStallRate::query()
                    ->where('market_stall_location_id', $location->id)
                    ->where('is_active', true)
                    ->orderByDesc('effective_start_date')
                    ->first();

                if (! $activeRate) {
                    $activeRate = MarketStallRate::query()->create([
                        'market_stall_location_id' => $location->id,
                        'rate_amount' => $seed['rate'],
                        'effective_start_date' => $now->toDateString(),
                        'effective_end_date' => null,
                        'is_active' => true,
                        'created_by_user_id' => $sender->id,
                    ]);
                }

                $locationMap[$seed['code']] = [
                    'location' => $location,
                    'rate' => $activeRate,
                ];
            }

            $types = MarketStallType::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->limit(6)
                ->get();

            if ($types->isEmpty()) {
                $typeRows = [
                    ['type_name' => 'Dry Goods', 'description' => 'Dry products and staple items', 'default_rate' => 95.00, 'rate_notes' => null, 'is_active' => true],
                    ['type_name' => 'Wet Market', 'description' => 'Fresh meat and seafood', 'default_rate' => 110.00, 'rate_notes' => null, 'is_active' => true],
                    ['type_name' => 'Vegetables', 'description' => 'Vegetable and produce section', 'default_rate' => 82.00, 'rate_notes' => null, 'is_active' => true],
                    ['type_name' => 'Canteen', 'description' => 'Prepared food and eatery', 'default_rate' => 125.00, 'rate_notes' => null, 'is_active' => true],
                    ['type_name' => 'Frozen Foods', 'description' => 'Frozen and chilled products', 'default_rate' => 118.00, 'rate_notes' => null, 'is_active' => true],
                    ['type_name' => 'Mixed Retail', 'description' => 'General retail merchandise', 'default_rate' => 90.00, 'rate_notes' => null, 'is_active' => true],
                ];

                foreach ($typeRows as $row) {
                    MarketStallType::query()->updateOrCreate(
                        ['type_name' => $row['type_name']],
                        $row
                    );
                }

                $types = MarketStallType::query()
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->limit(6)
                    ->get();
            }

            $tenantRows = [
                ['first' => 'Andrea', 'last' => 'Santos', 'business' => 'Santos Fresh Produce', 'type' => 'Vegetables', 'contact' => '09171234001', 'address' => 'Hamtic, Antique', 'mpo' => 'MPO-TRD-0001', 'stall' => 'A1-201', 'loc' => 'A1-104'],
                ['first' => 'Miguel', 'last' => 'Reyes', 'business' => 'Reyes Dry Goods', 'type' => 'Dry Goods', 'contact' => '09171234002', 'address' => 'San Jose, Antique', 'mpo' => 'MPO-TRD-0002', 'stall' => 'A1-202', 'loc' => 'A1-104'],
                ['first' => 'Carla', 'last' => 'Villanueva', 'business' => 'CV Seafood Supply', 'type' => 'Wet Market', 'contact' => '09171234003', 'address' => 'Tobias Fornier, Antique', 'mpo' => 'MPO-TRD-0003', 'stall' => 'B2-101', 'loc' => 'B2-018'],
                ['first' => 'Paolo', 'last' => 'Navarro', 'business' => 'Navarro Canteen', 'type' => 'Canteen', 'contact' => '09171234004', 'address' => 'Belison, Antique', 'mpo' => 'MPO-TRD-0004', 'stall' => 'B2-102', 'loc' => 'B2-018'],
                ['first' => 'Liza', 'last' => 'Domingo', 'business' => 'Domingo Fruit Mart', 'type' => 'Vegetables', 'contact' => '09171234005', 'address' => 'Patnongon, Antique', 'mpo' => 'MPO-TRD-0005', 'stall' => 'C1-201', 'loc' => 'C1-031'],
                ['first' => 'Ramon', 'last' => 'Lopez', 'business' => 'Lopez Meat & Poultry', 'type' => 'Wet Market', 'contact' => '09171234006', 'address' => 'Sibalom, Antique', 'mpo' => 'MPO-TRD-0006', 'stall' => 'C1-202', 'loc' => 'C1-031'],
                ['first' => 'Jessa', 'last' => 'Marquez', 'business' => 'Marquez Frozen Stop', 'type' => 'Frozen Foods', 'contact' => '09171234007', 'address' => 'Hamtic, Antique', 'mpo' => 'MPO-TRD-0007', 'stall' => 'D3-301', 'loc' => 'D3-011'],
                ['first' => 'Kevin', 'last' => 'Cruz', 'business' => 'Cruz General Store', 'type' => 'Mixed Retail', 'contact' => '09171234008', 'address' => 'Anini-y, Antique', 'mpo' => 'MPO-TRD-0008', 'stall' => 'D3-302', 'loc' => 'D3-011'],
                ['first' => 'Nina', 'last' => 'Torres', 'business' => 'Torres Grain Depot', 'type' => 'Dry Goods', 'contact' => '09171234009', 'address' => 'San Remigio, Antique', 'mpo' => 'MPO-TRD-0009', 'stall' => 'T2-401', 'loc' => 'T2-202'],
                ['first' => 'Victor', 'last' => 'Garcia', 'business' => 'Garcia Daily Needs', 'type' => 'Mixed Retail', 'contact' => '09171234010', 'address' => 'Culasi, Antique', 'mpo' => 'MPO-TRD-0010', 'stall' => 'T2-402', 'loc' => 'T2-202'],
                ['first' => 'Marian', 'last' => 'Flores', 'business' => 'Flores Veggie Hub', 'type' => 'Vegetables', 'contact' => '09171234011', 'address' => 'Bugasong, Antique', 'mpo' => 'MPO-TRD-0011', 'stall' => 'A1-203', 'loc' => 'A1-104'],
                ['first' => 'Dennis', 'last' => 'Mendoza', 'business' => 'Mendoza Retail Corner', 'type' => 'Dry Goods', 'contact' => '09171234012', 'address' => 'Tibiao, Antique', 'mpo' => 'MPO-TRD-0012', 'stall' => 'B2-103', 'loc' => 'B2-018'],
                ['first' => 'Sheila', 'last' => 'Roman', 'business' => 'Roman Food Stall', 'type' => 'Canteen', 'contact' => '09171234013', 'address' => 'Valderrama, Antique', 'mpo' => 'MPO-TRD-0013', 'stall' => 'C1-203', 'loc' => 'C1-031'],
                ['first' => 'Joel', 'last' => 'Aquino', 'business' => 'Aquino Seafood Hub', 'type' => 'Wet Market', 'contact' => '09171234014', 'address' => 'Laua-an, Antique', 'mpo' => 'MPO-TRD-0014', 'stall' => 'D3-303', 'loc' => 'D3-011'],
                ['first' => 'Patricia', 'last' => 'Salazar', 'business' => 'Salazar Essentials', 'type' => 'Mixed Retail', 'contact' => '09171234015', 'address' => 'Sebaste, Antique', 'mpo' => 'MPO-TRD-0015', 'stall' => 'T2-403', 'loc' => 'T2-202'],
                ['first' => 'Bryan', 'last' => 'Estrella', 'business' => 'Estrella Grain Center', 'type' => 'Dry Goods', 'contact' => '09171234016', 'address' => 'Pandan, Antique', 'mpo' => 'MPO-TRD-0016', 'stall' => 'T2-404', 'loc' => 'T2-202'],
                ['first' => 'Hazel', 'last' => 'Peralta', 'business' => 'Peralta Veggie Line', 'type' => 'Vegetables', 'contact' => '09171234017', 'address' => 'San Jose, Antique', 'mpo' => 'MPO-TRD-0017', 'stall' => 'A1-204', 'loc' => 'A1-104'],
                ['first' => 'Orlando', 'last' => 'Javier', 'business' => 'Javier Fish Catch', 'type' => 'Wet Market', 'contact' => '09171234018', 'address' => 'Hamtic, Antique', 'mpo' => 'MPO-TRD-0018', 'stall' => 'B2-104', 'loc' => 'B2-018'],
                ['first' => 'Kristine', 'last' => 'Paredes', 'business' => 'Paredes Daily Foods', 'type' => 'Canteen', 'contact' => '09171234019', 'address' => 'Belison, Antique', 'mpo' => 'MPO-TRD-0019', 'stall' => 'C1-204', 'loc' => 'C1-031'],
                ['first' => 'Alvin', 'last' => 'Mercado', 'business' => 'Mercado Home Basics', 'type' => 'Mixed Retail', 'contact' => '09171234020', 'address' => 'Sibalom, Antique', 'mpo' => 'MPO-TRD-0020', 'stall' => 'D3-304', 'loc' => 'D3-011'],
            ];

            $leases = [];
            $periods = ['monthly', 'weekly', 'daily'];

            foreach ($tenantRows as $index => $row) {
                $tenantUpdatedAt = $now->copy()->subDays($index);
                $tenant = MarketTenant::query()->updateOrCreate(
                    ['mpo_control_no' => $row['mpo']],
                    [
                        'first_name' => $row['first'],
                        'last_name' => $row['last'],
                        'middle_name' => null,
                        'address' => $row['address'],
                        'contact_number' => $row['contact'],
                        'business_name' => $row['business'],
                        'business_type' => $row['type'],
                        'updated_at' => $tenantUpdatedAt,
                    ]
                );

                $type = $types[$index % max(1, $types->count())];
                $locBundle = $locationMap[$row['loc']] ?? reset($locationMap);
                $location = $locBundle['location'];
                $rate = $locBundle['rate'];

                $stall = MarketStall::query()->updateOrCreate(
                    ['stall_no' => $row['stall']],
                    [
                        'market_stall_location_id' => $location->id,
                        'market_stall_type_id' => $type->id,
                        'dimension_sq_m' => 6 + ($index % 4),
                        'description' => 'Connected stall record',
                        'stall_status' => 'occupied',
                        'is_billable' => true,
                    ]
                );

                $billingPeriod = $periods[$index % count($periods)];
                $periodMultiplier = match ($billingPeriod) {
                    'daily' => 1.0,
                    'weekly' => 7.0,
                    default => 30.0,
                };

                $baseRate = (float) ($type->default_rate ?? $rate->rate_amount ?? 100);
                $computedRate = round($baseRate * $periodMultiplier, 2);
                $contractNumber = 'MKT-CN-2026-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

                $lease = MarketStallLease::query()->updateOrCreate(
                    ['contract_number' => $contractNumber],
                    [
                        'market_stall_id' => $stall->id,
                        'market_tenant_id' => $tenant->id,
                        'market_stall_rate_id' => $rate->id,
                        'selected_type_rates' => [[
                            'id' => $type->id,
                            'name' => $type->type_name,
                            'base_rate' => round($baseRate, 2),
                            'notes' => $type->rate_notes,
                        ]],
                        'billing_period' => $billingPeriod,
                        'billing_cycles' => 1,
                        'rate_multiplier' => 1.00,
                        'computed_rate_amount' => $computedRate,
                        'start_date' => $now->copy()->subDays(45 - $index)->toDateString(),
                        'end_date' => null,
                        'lease_status' => 'active',
                        'remarks' => 'Connected lease for tenant directory',
                        'created_by_user_id' => $sender->id,
                    ]
                );

                $leases[] = [
                    'tenant' => $tenant,
                    'stall' => $stall,
                    'lease' => $lease,
                    'amount' => $computedRate,
                ];
            }

            $dispatchCompleted = CollectionDispatch::query()->updateOrCreate(
                ['department_code' => 'market', 'notes' => 'MARKET-COMPLETED-BATCH'],
                [
                    'collector_user_id' => $collector->id,
                    'sent_by_user_id' => $sender->id,
                    'period_type' => 'month',
                    'from_date' => $now->copy()->startOfMonth()->toDateString(),
                    'to_date' => $now->copy()->endOfMonth()->toDateString(),
                    'status' => 'completed',
                    'sent_at' => $now->copy()->subDays(10),
                    'completed_at' => $now->copy()->subDays(2),
                ]
            );

            $dispatchAwaiting = CollectionDispatch::query()->updateOrCreate(
                ['department_code' => 'market', 'notes' => 'MARKET-AWAITING-BATCH'],
                [
                    'collector_user_id' => $collector->id,
                    'sent_by_user_id' => $sender->id,
                    'period_type' => 'week',
                    'from_date' => $now->copy()->subDays(7)->toDateString(),
                    'to_date' => $now->toDateString(),
                    'status' => 'awaiting_confirmation',
                    'sent_at' => $now->copy()->subDays(3),
                    'completed_at' => null,
                ]
            );

            $dispatchSent = CollectionDispatch::query()->updateOrCreate(
                ['department_code' => 'market', 'notes' => 'MARKET-SENT-BATCH'],
                [
                    'collector_user_id' => $collector->id,
                    'sent_by_user_id' => $sender->id,
                    'period_type' => 'today',
                    'from_date' => $now->toDateString(),
                    'to_date' => $now->toDateString(),
                    'status' => 'sent',
                    'sent_at' => $now->copy()->subHours(6),
                    'completed_at' => null,
                ]
            );

            $statusPlan = [
                'accepted',
                'accepted',
                'rejected',
                'cancelled',
                'accepted',
                'accepted',
                'collected_pending_confirmation',
                'collected_pending_confirmation',
                'sent',
                'sent',
                'accepted',
                'rejected',
                'accepted',
                'cancelled',
                'accepted',
                'accepted',
                'collected_pending_confirmation',
                'sent',
                'rejected',
                'accepted',
            ];

            foreach ($leases as $index => $bundle) {
                $status = $statusPlan[$index] ?? 'accepted';
                $lease = $bundle['lease'];
                $tenant = $bundle['tenant'];
                $amount = (float) $bundle['amount'];

                $dispatch = match ($status) {
                    'sent' => $dispatchSent,
                    'collected_pending_confirmation' => $dispatchAwaiting,
                    default => $dispatchCompleted,
                };

                $itemUpdatedAt = $now->copy()->subDays($index % 14);
                $item = CollectionDispatchItem::query()->updateOrCreate(
                    [
                        'collection_dispatch_id' => $dispatch->id,
                        'market_stall_lease_id' => $lease->id,
                    ],
                    [
                        'fishport_log_id' => null,
                        'payment_record_id' => null,
                        'market_payment_collection_id' => null,
                        'amount_snapshot' => $amount,
                        'status' => $status,
                        'payer_name' => $tenant->fullName(),
                        'collected_at' => in_array($status, ['accepted', 'rejected', 'collected_pending_confirmation'], true)
                            ? $itemUpdatedAt->copy()->subHours(2)
                            : null,
                        'collected_by_user_id' => in_array($status, ['accepted', 'rejected', 'collected_pending_confirmation'], true)
                            ? $collector->id
                            : null,
                        'collector_note' => 'Collection note from assigned market collector',
                        'reviewed_at' => in_array($status, ['accepted', 'rejected', 'cancelled'], true)
                            ? $itemUpdatedAt
                            : null,
                        'reviewed_by_user_id' => in_array($status, ['accepted', 'rejected', 'cancelled'], true)
                            ? $sender->id
                            : null,
                        'review_note' => match ($status) {
                            'accepted' => 'Accepted after cashier validation.',
                            'rejected' => 'Rejected after payment detail verification.',
                            'cancelled' => 'Cancelled before collection cutoff.',
                            default => null,
                        },
                        'updated_at' => $itemUpdatedAt,
                    ]
                );

                if ($status === 'accepted') {
                    $paymentNumber = 'MKT-PAY-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
                    $payment = MarketPaymentCollection::query()->updateOrCreate(
                        ['payment_number' => $paymentNumber],
                        [
                            'market_stall_lease_id' => $lease->id,
                            'collection_dispatch_item_id' => $item->id,
                            'amount_paid' => $amount,
                            'payer_name' => $tenant->fullName(),
                            'payment_date' => $itemUpdatedAt->copy()->addHour(),
                            'collector_note' => 'Payment collected and endorsed by collector',
                            'remarks' => 'Connected market payment record',
                            'generated_by_user_id' => $sender->id,
                        ]
                    );

                    $item->update([
                        'market_payment_collection_id' => $payment->id,
                    ]);
                }
            }

            $dispatchCompleted->update(['status' => 'completed', 'completed_at' => $now->copy()->subDay()]);
            $dispatchAwaiting->update(['status' => 'awaiting_confirmation', 'completed_at' => null]);
            $dispatchSent->update(['status' => 'sent', 'completed_at' => null]);
        });
    }
}
