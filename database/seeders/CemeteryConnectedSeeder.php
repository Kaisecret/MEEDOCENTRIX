<?php

namespace Database\Seeders;

use App\Models\CemeteryCategory;
use App\Models\CemeteryContact;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeteryPaymentCollection;
use App\Models\CemeteryPlot;
use App\Models\CemeteryServiceLog;
use App\Models\CemeteryServiceType;
use App\Models\CemeterySite;
use App\Models\CemeteryTransaction;
use App\Models\CemeteryTransactionType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CemeteryConnectedSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $today = Carbon::today();

            $cemeteryPersonnel = User::query()->firstOrCreate(
                ['email' => 'celia.mendoza@meedocentrix.local'],
                [
                    'name' => 'Celia Mendoza',
                    'username' => 'celia.mendoza',
                    'password' => 'password123',
                    'role' => 'personnel',
                    'department' => 'cemetery',
                    'is_active' => true,
                ]
            );

            $sites = CemeterySite::query()->where('is_active', true)->get()->keyBy(
                fn (CemeterySite $site): string => strtoupper((string) $site->site_code)
            );
            $categories = CemeteryCategory::query()->where('is_active', true)->get()->keyBy(
                fn (CemeteryCategory $category): string => strtoupper((string) $category->category_code)
            );
            $serviceTypes = CemeteryServiceType::query()->where('is_active', true)->get()->keyBy(
                fn (CemeteryServiceType $type): string => strtoupper((string) $type->type_code)
            );
            $transactionTypes = CemeteryTransactionType::query()->where('is_active', true)->get()->keyBy(
                fn (CemeteryTransactionType $type): string => strtoupper((string) $type->type_code)
            );

            if (
                $sites->isEmpty()
                || $categories->isEmpty()
                || $serviceTypes->isEmpty()
                || $transactionTypes->isEmpty()
            ) {
                return;
            }

            $entries = [
                ['deceased' => 'Ernesto Dela Cruz', 'contact' => 'Marina Dela Cruz', 'phone' => '09176850001', 'address' => 'Brgy. Funda, Hamtic, Antique'],
                ['deceased' => 'Rosario Villanueva', 'contact' => 'Elmer Villanueva', 'phone' => '09176850002', 'address' => 'Brgy. Igbonglo, Bugasong, Antique'],
                ['deceased' => 'Domingo Alvarez', 'contact' => 'Nora Alvarez', 'phone' => '09176850003', 'address' => 'Brgy. Tigbas, San Jose, Antique'],
                ['deceased' => 'Lydia Ramos', 'contact' => 'Fidel Ramos', 'phone' => '09176850004', 'address' => 'Brgy. San Pedro, San Jose, Antique'],
                ['deceased' => 'Benjamin Torres', 'contact' => 'Amelia Torres', 'phone' => '09176850005', 'address' => 'Brgy. Igbarabatuan, Laua-an, Antique'],
                ['deceased' => 'Milagros Santos', 'contact' => 'Joel Santos', 'phone' => '09176850006', 'address' => 'Brgy. Cawayan, Sibalom, Antique'],
                ['deceased' => 'Teodoro Mendoza', 'contact' => 'Rica Mendoza', 'phone' => '09176850007', 'address' => 'Brgy. San Fernando, Belison, Antique'],
                ['deceased' => 'Felisa Navarro', 'contact' => 'Arturo Navarro', 'phone' => '09176850008', 'address' => 'Brgy. Bacong, Tobias Fornier, Antique'],
                ['deceased' => 'Alfredo Garcia', 'contact' => 'Gemma Garcia', 'phone' => '09176850009', 'address' => 'Brgy. Cubay, Sebaste, Antique'],
                ['deceased' => 'Corazon Bautista', 'contact' => 'Pio Bautista', 'phone' => '09176850010', 'address' => 'Brgy. Poblacion, Culasi, Antique'],
                ['deceased' => 'Romulo Reyes', 'contact' => 'Mila Reyes', 'phone' => '09176850011', 'address' => 'Brgy. Talisayan, Anini-y, Antique'],
                ['deceased' => 'Elena Castillo', 'contact' => 'Randy Castillo', 'phone' => '09176850012', 'address' => 'Brgy. San Ramon, Valderrama, Antique'],
                ['deceased' => 'Pascual Flores', 'contact' => 'Helen Flores', 'phone' => '09176850013', 'address' => 'Brgy. Badiangan, Sibalom, Antique'],
                ['deceased' => 'Lourdes Aguilar', 'contact' => 'Vic Aguilar', 'phone' => '09176850014', 'address' => 'Brgy. Nasuli, Hamtic, Antique'],
                ['deceased' => 'Mateo Fernandez', 'contact' => 'Cora Fernandez', 'phone' => '09176850015', 'address' => 'Brgy. Poblacion, Patnongon, Antique'],
                ['deceased' => 'Anita Gonzales', 'contact' => 'Mario Gonzales', 'phone' => '09176850016', 'address' => 'Brgy. Aningalan, San Remigio, Antique'],
                ['deceased' => 'Ricardo Herrera', 'contact' => 'Jean Herrera', 'phone' => '09176850017', 'address' => 'Brgy. Poblacion, Libertad, Antique'],
                ['deceased' => 'Cecilia Lopez', 'contact' => 'Dante Lopez', 'phone' => '09176850018', 'address' => 'Brgy. Cabiawan, Tibiao, Antique'],
                ['deceased' => 'Vicente Domingo', 'contact' => 'Pearl Domingo', 'phone' => '09176850019', 'address' => 'Brgy. Igtandog, Laua-an, Antique'],
                ['deceased' => 'Juliana Rivera', 'contact' => 'Nico Rivera', 'phone' => '09176850020', 'address' => 'Brgy. Tiguis, San Jose, Antique'],
                ['deceased' => 'Carlos Miranda', 'contact' => 'Adora Miranda', 'phone' => '09176850021', 'address' => 'Brgy. Carit-an, Hamtic, Antique'],
                ['deceased' => 'Emilia Aquino', 'contact' => 'Paolo Aquino', 'phone' => '09176850022', 'address' => 'Brgy. Aras-asan, Laua-an, Antique'],
                ['deceased' => 'Renato Salazar', 'contact' => 'Lea Salazar', 'phone' => '09176850023', 'address' => 'Brgy. Funda-Dalipe, Hamtic, Antique'],
                ['deceased' => 'Teresa Cordero', 'contact' => 'Alden Cordero', 'phone' => '09176850024', 'address' => 'Brgy. Magyapo, Antique'],
            ];

            $siteCodes = ['SJM', 'OMC', 'NMC', 'SPMC'];
            $categoryCodes = ['REGULAR', 'REGULAR_LARGE', 'INFANT', 'COLUMBARIUM', 'MAUSOLEUM_PLOT', 'FAMILY_PLOT'];
            $serviceTypeCodes = ['INTERMENT', 'BURIAL', 'RENEWAL', 'MAINTENANCE_UPDATE', 'TRANSFER', 'EXHUMATION'];
            $transactionTypeCodes = [
                'SINGLE_NICHE_PURCHASE',
                'MAINTENANCE_FEE',
                'BURIAL_PERMIT',
                'ADDITIONAL_BURIAL',
                'RENEWAL',
                'TRANSFER',
                'EXHUMATION',
                'LOT_PURCHASE',
            ];
            $daysAgoList = [0, 1, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 23, 26, 29, 32, 36, 40, 44, 48, 55, 62, 70, 80];

            foreach ($entries as $index => $entry) {
                $siteCode = $siteCodes[$index % count($siteCodes)];
                $categoryCode = $categoryCodes[$index % count($categoryCodes)];
                $serviceTypeCode = $serviceTypeCodes[$index % count($serviceTypeCodes)];
                $transactionTypeCode = $transactionTypeCodes[$index % count($transactionTypeCodes)];

                $site = $sites[$siteCode] ?? $sites->first();
                $category = $categories[$categoryCode] ?? $categories->first();
                $serviceType = $serviceTypes[$serviceTypeCode] ?? $serviceTypes->first();
                $transactionType = $transactionTypes[$transactionTypeCode] ?? $transactionTypes->first();

                if (! $site || ! $category || ! $serviceType || ! $transactionType) {
                    continue;
                }

                $daysAgo = $daysAgoList[$index] ?? (5 + ($index * 3));
                $serviceDate = $today->copy()->subDays($daysAgo);
                $intermentDate = $serviceDate->copy()->subDay();
                $transactionDate = $serviceDate->copy()->setTime(9, 15);

                $plotReference = strtoupper(sprintf(
                    '%s-%s-%03d',
                    $siteCode,
                    $categoryCode === 'FAMILY_PLOT' || $categoryCode === 'MAUSOLEUM_PLOT' ? 'L' : 'N',
                    201 + $index
                ));
                $plotType = $categoryCode === 'FAMILY_PLOT' || $categoryCode === 'MAUSOLEUM_PLOT'
                    ? 'lot'
                    : 'niche';

                $fees = $this->feeProfile($transactionTypeCode);
                $payment = $this->paymentProfile($index, $daysAgo, $fees['amount_due']);

                $coverageStart = $serviceDate->copy()->startOfMonth();
                $coverageEnd = $coverageStart->copy()->addYear()->subDay();
                if ($daysAgo > 45 && in_array($payment['payment_status'], ['unpaid', 'partial'], true)) {
                    $coverageEnd = $today->copy()->subDays(15 + ($index % 20));
                }

                $recordNo = sprintf('OCC-HIS-%04d', $index + 1);
                $logNo = sprintf('CSL-HIS-%04d', $index + 1);
                $transactionNo = sprintf('CTX-HIS-%04d', $index + 1);
                $paymentNo = sprintf('CPY-HIS-%04d', $index + 1);

                $contact = CemeteryContact::query()->firstOrCreate(
                    [
                        'contact_person' => $entry['contact'],
                        'contact_number' => $entry['phone'],
                        'address' => $entry['address'],
                    ]
                );

                $plot = CemeteryPlot::query()->updateOrCreate(
                    [
                        'cemetery_site_id' => $site->id,
                        'plot_reference' => $plotReference,
                    ],
                    [
                        'cemetery_category_id' => $category->id,
                        'plot_type' => $plotType,
                        'is_active' => true,
                        'is_occupied' => true,
                        'remarks' => 'Historical cemetery record plot.',
                    ]
                );

                $maintenanceStatus = match ($payment['payment_status']) {
                    'paid' => 'paid',
                    'partial' => $daysAgo > 45 ? 'overdue' : 'partial',
                    'overdue' => 'overdue',
                    default => $daysAgo > 45 ? 'overdue' : 'unpaid',
                };

                $occupantStatus = $index % 14 === 0 && $index !== 0 ? 'transferred' : 'active';

                $occupant = CemeteryOccupantRecord::query()->updateOrCreate(
                    ['record_no' => $recordNo],
                    [
                        'cemetery_site_id' => $site->id,
                        'cemetery_category_id' => $category->id,
                        'cemetery_plot_id' => $plot->id,
                        'cemetery_contact_id' => $contact->id,
                        'deceased_name' => $entry['deceased'],
                        'date_of_interment' => $intermentDate->toDateString(),
                        'remarks' => 'Legacy entry migrated to digital registry.',
                        'status' => $occupantStatus,
                        'maintenance_fee_status' => $maintenanceStatus,
                        'coverage_start_date' => $coverageStart->toDateString(),
                        'coverage_end_date' => $coverageEnd->toDateString(),
                        'created_by_user_id' => $cemeteryPersonnel->id,
                    ]
                );

                $serviceLog = CemeteryServiceLog::query()->updateOrCreate(
                    ['log_no' => $logNo],
                    [
                        'service_date' => $serviceDate->toDateString(),
                        'cemetery_site_id' => $site->id,
                        'cemetery_service_type_id' => $serviceType->id,
                        'suggested_transaction_type_code' => $transactionTypeCode,
                        'suggested_amount_due' => $fees['amount_due'],
                        'occupant_record_id' => $occupant->id,
                        'deceased_name' => $entry['deceased'],
                        'plot_reference' => $plotReference,
                        'details' => 'Service log imported from historical paper record.',
                        'processed_by' => $cemeteryPersonnel->name,
                        'remarks' => 'Validated by records office.',
                        'created_by_user_id' => $cemeteryPersonnel->id,
                    ]
                );

                $transactionStatus = $payment['transaction_status'];
                $totalPaid = round((float) $payment['amount_paid'], 2);
                $remaining = round(max($fees['amount_due'] - $totalPaid, 0), 2);

                $transaction = CemeteryTransaction::query()->updateOrCreate(
                    ['transaction_no' => $transactionNo],
                    [
                        'transaction_date' => $transactionDate->format('Y-m-d H:i:s'),
                        'cemetery_site_id' => $site->id,
                        'cemetery_category_id' => $category->id,
                        'cemetery_transaction_type_id' => $transactionType->id,
                        'occupant_record_id' => $occupant->id,
                        'service_log_id' => $serviceLog->id,
                        'deceased_name' => $entry['deceased'],
                        'plot_reference' => $plotReference,
                        'quantity' => 1,
                        'amount_due' => $fees['amount_due'],
                        'total_paid' => $totalPaid,
                        'remaining_balance' => $remaining,
                        'maintenance_type' => $fees['maintenance_type'],
                        'maintenance_years' => $fees['maintenance_years'],
                        'has_burial_permit' => $fees['has_burial_permit'],
                        'base_fee' => $fees['base_fee'],
                        'maintenance_fee' => $fees['maintenance_fee'],
                        'burial_permit_fee' => $fees['burial_permit_fee'],
                        'other_applicable_fee' => $fees['other_applicable_fee'],
                        'remarks' => 'Connected with service and occupant record.',
                        'status' => $transactionStatus,
                        'created_by_user_id' => $cemeteryPersonnel->id,
                    ]
                );

                $receiptNo = $payment['amount_paid'] > 0
                    ? sprintf('OR-HIS-%04d', $index + 1)
                    : null;

                CemeteryPaymentCollection::query()->updateOrCreate(
                    ['payment_no' => $paymentNo],
                    [
                        'cemetery_transaction_id' => $transaction->id,
                        'cemetery_contact_id' => $contact->id,
                        'amount_paid' => $payment['amount_paid'],
                        'official_receipt_no' => $receiptNo,
                        'payment_date' => $payment['payment_date'],
                        'coverage_start_date' => $coverageStart->toDateString(),
                        'coverage_end_date' => $coverageEnd->toDateString(),
                        'payment_status' => $payment['payment_status'],
                        'remarks' => 'Linked payment entry for cemetery reports.',
                        'created_by_user_id' => $cemeteryPersonnel->id,
                    ]
                );

                $plot->update(['is_occupied' => $occupantStatus === 'active']);
            }
        });
    }

    /**
     * @return array{
     *   amount_due: float,
     *   base_fee: float,
     *   maintenance_fee: float,
     *   burial_permit_fee: float,
     *   other_applicable_fee: float,
     *   maintenance_type: string,
     *   maintenance_years: ?int,
     *   has_burial_permit: bool
     * }
     */
    private function feeProfile(string $transactionTypeCode): array
    {
        return match ($transactionTypeCode) {
            'MAINTENANCE_FEE' => [
                'amount_due' => 1500.00,
                'base_fee' => 0.00,
                'maintenance_fee' => 1500.00,
                'burial_permit_fee' => 0.00,
                'other_applicable_fee' => 0.00,
                'maintenance_type' => 'five_year_fixed',
                'maintenance_years' => 5,
                'has_burial_permit' => false,
            ],
            'BURIAL_PERMIT' => [
                'amount_due' => 300.00,
                'base_fee' => 300.00,
                'maintenance_fee' => 0.00,
                'burial_permit_fee' => 0.00,
                'other_applicable_fee' => 0.00,
                'maintenance_type' => 'none',
                'maintenance_years' => null,
                'has_burial_permit' => false,
            ],
            'ADDITIONAL_BURIAL' => [
                'amount_due' => 5300.00,
                'base_fee' => 5000.00,
                'maintenance_fee' => 0.00,
                'burial_permit_fee' => 300.00,
                'other_applicable_fee' => 0.00,
                'maintenance_type' => 'none',
                'maintenance_years' => null,
                'has_burial_permit' => true,
            ],
            'RENEWAL' => [
                'amount_due' => 1200.00,
                'base_fee' => 0.00,
                'maintenance_fee' => 1200.00,
                'burial_permit_fee' => 0.00,
                'other_applicable_fee' => 0.00,
                'maintenance_type' => 'yearly',
                'maintenance_years' => 4,
                'has_burial_permit' => false,
            ],
            'TRANSFER' => [
                'amount_due' => 450.00,
                'base_fee' => 300.00,
                'maintenance_fee' => 0.00,
                'burial_permit_fee' => 0.00,
                'other_applicable_fee' => 150.00,
                'maintenance_type' => 'none',
                'maintenance_years' => null,
                'has_burial_permit' => false,
            ],
            'EXHUMATION' => [
                'amount_due' => 200.00,
                'base_fee' => 200.00,
                'maintenance_fee' => 0.00,
                'burial_permit_fee' => 0.00,
                'other_applicable_fee' => 0.00,
                'maintenance_type' => 'none',
                'maintenance_years' => null,
                'has_burial_permit' => false,
            ],
            'LOT_PURCHASE' => [
                'amount_due' => 10000.00,
                'base_fee' => 10000.00,
                'maintenance_fee' => 0.00,
                'burial_permit_fee' => 0.00,
                'other_applicable_fee' => 0.00,
                'maintenance_type' => 'none',
                'maintenance_years' => null,
                'has_burial_permit' => false,
            ],
            default => [
                'amount_due' => 10000.00,
                'base_fee' => 10000.00,
                'maintenance_fee' => 0.00,
                'burial_permit_fee' => 0.00,
                'other_applicable_fee' => 0.00,
                'maintenance_type' => 'none',
                'maintenance_years' => null,
                'has_burial_permit' => false,
            ],
        };
    }

    /**
     * @return array{
     *   amount_paid: float,
     *   payment_status: string,
     *   payment_date: ?string,
     *   transaction_status: string
     * }
     */
    private function paymentProfile(int $index, int $daysAgo, float $amountDue): array
    {
        $bucket = $index % 6;
        $paymentDate = Carbon::today()->subDays(max($daysAgo - 1, 0))->toDateString();

        if ($bucket === 0 || $bucket === 2) {
            return [
                'amount_paid' => round($amountDue, 2),
                'payment_status' => 'paid',
                'payment_date' => $paymentDate,
                'transaction_status' => 'paid',
            ];
        }

        if ($bucket === 1 || $bucket === 4) {
            return [
                'amount_paid' => round($amountDue * 0.55, 2),
                'payment_status' => 'partial',
                'payment_date' => $paymentDate,
                'transaction_status' => 'partial',
            ];
        }

        if ($bucket === 5) {
            return [
                'amount_paid' => 0.00,
                'payment_status' => 'unpaid',
                'payment_date' => null,
                'transaction_status' => 'cancelled',
            ];
        }

        return [
            'amount_paid' => 0.00,
            'payment_status' => 'unpaid',
            'payment_date' => null,
            'transaction_status' => 'pending',
        ];
    }
}
