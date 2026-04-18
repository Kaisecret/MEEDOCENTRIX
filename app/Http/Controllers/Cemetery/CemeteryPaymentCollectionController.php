<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryContact;
use App\Models\CemeteryPaymentCollection;
use App\Models\CemeterySite;
use App\Models\CemeteryTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CemeteryPaymentCollectionController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const STATUS_OPTIONS = [
        'paid' => 'Paid',
        'unpaid' => 'Unpaid',
        'partial' => 'Partial',
        'overdue' => 'Overdue',
    ];

    public function index(Request $request): View
    {
        $this->syncOverduePaymentStatuses();

        $search = trim((string) $request->query('q', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);
        $status = trim((string) $request->query('payment_status', ''));

        $paymentQuery = CemeteryPaymentCollection::query()
            ->with([
                'transaction.site',
                'transaction.category',
                'contact',
            ]);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $paymentQuery->where(function ($query) use ($like): void {
                $query->where('payment_no', 'like', $like)
                    ->orWhere('official_receipt_no', 'like', $like)
                    ->orWhere('remarks', 'like', $like)
                    ->orWhereHas('contact', function ($contactQuery) use ($like): void {
                        $contactQuery->where('contact_person', 'like', $like);
                    })
                    ->orWhereHas('transaction', function ($transactionQuery) use ($like): void {
                        $transactionQuery->where('transaction_no', 'like', $like)
                            ->orWhere('deceased_name', 'like', $like)
                            ->orWhere('plot_reference', 'like', $like);
                    });
            });
        }

        if ($siteId > 0) {
            $paymentQuery->whereHas('transaction', function ($query) use ($siteId): void {
                $query->where('cemetery_site_id', $siteId);
            });
        }

        if (array_key_exists($status, self::STATUS_OPTIONS)) {
            $paymentQuery->where('payment_status', $status);
        }

        $paymentCollections = $paymentQuery
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $sites = CemeterySite::query()
            ->where('is_active', true)
            ->orderBy('site_name')
            ->get();

        $contacts = CemeteryContact::query()
            ->orderBy('contact_person')
            ->get();

        $transactions = CemeteryTransaction::query()
            ->with([
                'site:id,site_name',
                'category:id,category_name',
                'occupantRecord:id,cemetery_contact_id',
                'occupantRecord.contact:id,contact_person,contact_number',
            ])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $usedTransactionIds = CemeteryPaymentCollection::query()
            ->pluck('cemetery_transaction_id')
            ->all();

        $unrecordedTransactions = $transactions
            ->filter(fn (CemeteryTransaction $transaction): bool => ! in_array($transaction->id, $usedTransactionIds, true))
            ->values();

        $availableTransactions = $transactions
            ->filter(function (CemeteryTransaction $transaction) use ($usedTransactionIds): bool {
                if (in_array($transaction->id, $usedTransactionIds, true)) {
                    return false;
                }

                if ((int) ($transaction->occupant_record_id ?? 0) <= 0) {
                    return false;
                }

                return (int) ($transaction->occupantRecord?->cemetery_contact_id ?? 0) > 0;
            })
            ->values();
        $hasTransactions = $transactions->isNotEmpty();
        $hasAvailableTransactions = $availableTransactions->isNotEmpty();
        $allTransactionsAlreadyRecorded = $hasTransactions && $unrecordedTransactions->isEmpty();
        $hasUnrecordedWithoutContact = $unrecordedTransactions->isNotEmpty() && ! $hasAvailableTransactions;

        $contactByTransactionId = [];
        foreach ($transactions as $transaction) {
            $contactId = (int) ($transaction->occupantRecord?->cemetery_contact_id ?? 0);
            if ($contactId > 0) {
                $contactByTransactionId[$transaction->id] = $contactId;
            }
        }

        $outstandingTotal = (float) (CemeteryPaymentCollection::query()
            ->join('cemetery_transactions as tx', 'tx.id', '=', 'cemetery_payment_collections.cemetery_transaction_id')
            ->selectRaw('COALESCE(SUM(GREATEST(tx.amount_due - cemetery_payment_collections.amount_paid, 0)), 0) as outstanding_total')
            ->value('outstanding_total') ?? 0);

        return view('cemetery.payments', [
            'paymentCollections' => $paymentCollections,
            'sites' => $sites,
            'contacts' => $contacts,
            'transactions' => $transactions,
            'availableTransactions' => $availableTransactions,
            'hasTransactions' => $hasTransactions,
            'hasAvailableTransactions' => $hasAvailableTransactions,
            'allTransactionsAlreadyRecorded' => $allTransactionsAlreadyRecorded,
            'hasUnrecordedWithoutContact' => $hasUnrecordedWithoutContact,
            'contactByTransactionId' => $contactByTransactionId,
            'statusOptions' => self::STATUS_OPTIONS,
            'search' => $search,
            'selectedSiteId' => $siteId,
            'selectedStatus' => $status,
            'nextPaymentNo' => $this->nextPaymentNo(),
            'summary' => [
                'total_records' => CemeteryPaymentCollection::query()->count(),
                'collected_today' => (float) CemeteryPaymentCollection::query()
                    ->whereDate('payment_date', now()->toDateString())
                    ->sum('amount_paid'),
                'total_collected' => (float) CemeteryPaymentCollection::query()->sum('amount_paid'),
                'paid_records' => CemeteryPaymentCollection::query()->where('payment_status', 'paid')->count(),
                'unpaid_records' => CemeteryPaymentCollection::query()->where('payment_status', 'unpaid')->count(),
                'partial_records' => CemeteryPaymentCollection::query()->where('payment_status', 'partial')->count(),
                'overdue_records' => CemeteryPaymentCollection::query()->where('payment_status', 'overdue')->count(),
                'outstanding_total' => $outstandingTotal,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));
        $transactionId = (int) $validated['cemetery_transaction_id'];
        $remainingBalance = $this->remainingBalance($transactionId);
        $resolvedStatus = $this->resolvePaymentStatus(
            (string) ($validated['payment_status'] ?? ''),
            (float) ($validated['amount_paid'] ?? 0),
            $remainingBalance
        );
        $this->validatePaymentConsistency($validated, $remainingBalance, $resolvedStatus);

        $paymentCollection = CemeteryPaymentCollection::query()->create($this->payload($validated, $resolvedStatus) + [
            'created_by_user_id' => Auth::id(),
        ]);
        $this->syncTransactionStatus($paymentCollection);

        return redirect()
            ->route('cemetery.payments')
            ->with('status', 'Payment collection record added successfully.')
            ->with('last_payment_id', $paymentCollection->id);
    }

    public function update(Request $request, CemeteryPaymentCollection $paymentCollection): RedirectResponse
    {
        $validated = $request->validate($this->rules($request, $paymentCollection));
        $transactionId = (int) $validated['cemetery_transaction_id'];
        $remainingBalance = $this->remainingBalance($transactionId, $paymentCollection->id);
        $resolvedStatus = $this->resolvePaymentStatus(
            (string) ($validated['payment_status'] ?? ''),
            (float) ($validated['amount_paid'] ?? 0),
            $remainingBalance
        );
        $this->validatePaymentConsistency($validated, $remainingBalance, $resolvedStatus);

        $paymentCollection->update($this->payload($validated, $resolvedStatus));
        $paymentCollection->refresh();
        $this->syncTransactionStatus($paymentCollection);

        return redirect()
            ->back()
            ->with('status', "Payment record {$paymentCollection->payment_no} updated.");
    }

    public function quickPay(Request $request, CemeteryTransaction $transaction): RedirectResponse
    {
        $rawPaymentDate = trim((string) $request->input('payment_date', ''));
        if ($rawPaymentDate !== '') {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $rawPaymentDate) === 1) {
                $request->merge([
                    'payment_date' => Carbon::createFromFormat('d/m/Y', $rawPaymentDate)->format('Y-m-d'),
                ]);
            } elseif (preg_match('/^\d{2}\-\d{2}\-\d{4}$/', $rawPaymentDate) === 1) {
                $request->merge([
                    'payment_date' => Carbon::createFromFormat('d-m-Y', $rawPaymentDate)->format('Y-m-d'),
                ]);
            }
        }

        $existingPayment = CemeteryPaymentCollection::query()
            ->where('cemetery_transaction_id', (int) $transaction->id)
            ->first();

        $validated = $request->validate([
            'form_mode' => ['nullable', 'string', Rule::in(['quick_pay'])],
            'quick_transaction_id' => ['nullable', 'integer'],
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'official_receipt_no' => ['nullable', 'string', 'max:60'],
            'payment_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $quickTransactionId = (int) ($validated['quick_transaction_id'] ?? 0);
        if ($quickTransactionId > 0 && $quickTransactionId !== (int) $transaction->id) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Invalid transaction selection for quick payment.',
            ]);
        }

        $amountDue = $this->transactionAmountDue($transaction->id);
        $totalPaid = $this->totalPaid($transaction->id);
        $remainingBalance = round(max($amountDue - $totalPaid, 0), 2);
        $amountPaid = round((float) $validated['amount_paid'], 2);

        if ($remainingBalance <= 0) {
            throw ValidationException::withMessages([
                'amount_paid' => 'This transaction is already fully paid.',
            ]);
        }

        if ($amountPaid > $remainingBalance) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Payment amount exceeds remaining balance of PHP ' . number_format($remainingBalance, 2) . '.',
            ]);
        }

        $receiptNo = $this->nullableTrimmedUpper($validated['official_receipt_no'] ?? null);
        if ($receiptNo !== null) {
            $existsQuery = CemeteryPaymentCollection::query()
                ->where('official_receipt_no', $receiptNo);

            if ($existingPayment) {
                $existsQuery->where('id', '!=', $existingPayment->id);
            }

            $exists = $existsQuery->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    'official_receipt_no' => 'Official receipt number already used.',
                ]);
            }
        }

        $contactId = $this->resolveOptionalContactIdForTransaction($transaction->id);
        $paymentDate = (string) $validated['payment_date'];
        $existingRecordedAmount = $existingPayment ? round((float) $existingPayment->amount_paid, 2) : 0.0;
        $otherPaid = $existingPayment
            ? round(max($totalPaid - $existingRecordedAmount, 0), 2)
            : $totalPaid;
        $newTotalPaid = round(min($totalPaid + $amountPaid, $amountDue), 2);
        $resolvedStatus = $newTotalPaid >= $amountDue ? 'paid' : 'partial';

        $payment = DB::transaction(function () use ($existingPayment, $transaction, $validated, $amountPaid, $receiptNo, $contactId, $paymentDate, $resolvedStatus, $newTotalPaid, $otherPaid, $amountDue): CemeteryPaymentCollection {
            if ($existingPayment) {
                $updatedAmountPaid = round(min(max($newTotalPaid - $otherPaid, 0), $amountDue), 2);
                $existingPayment->fill([
                    'cemetery_contact_id' => $contactId ?? $existingPayment->cemetery_contact_id,
                    'amount_paid' => $updatedAmountPaid,
                    'official_receipt_no' => $receiptNo,
                    'payment_date' => $paymentDate,
                    'payment_status' => $resolvedStatus,
                    'remarks' => $this->nullableTrimmed($validated['remarks'] ?? null),
                ]);
                $existingPayment->save();

                return $existingPayment->fresh();
            }

            return CemeteryPaymentCollection::query()->create([
                'payment_no' => $this->nextPaymentNo(),
                'cemetery_transaction_id' => $transaction->id,
                'cemetery_contact_id' => $contactId,
                'amount_paid' => $amountPaid,
                'official_receipt_no' => $receiptNo,
                'payment_date' => $paymentDate,
                'coverage_start_date' => null,
                'coverage_end_date' => null,
                'payment_status' => $resolvedStatus,
                'remarks' => $this->nullableTrimmed($validated['remarks'] ?? null),
                'created_by_user_id' => Auth::id(),
            ]);
        });

        $this->syncTransactionStatus($payment);

        return redirect()
            ->route('cemetery.payments')
            ->with('status', "Payment {$payment->payment_no} recorded for {$transaction->transaction_no}. Balance left: PHP " . number_format(max($amountDue - $newTotalPaid, 0), 2) . '.')
            ->with('last_payment_id', $payment->id);
    }

    public function receipt(CemeteryPaymentCollection $paymentCollection): View
    {
        $paymentCollection->load([
            'transaction.site',
            'transaction.category',
            'transaction.transactionType',
            'contact',
            'creator',
        ]);

        $transaction = $paymentCollection->transaction;
        $amountDue = $transaction ? round((float) $transaction->amount_due, 2) : 0.0;
        $totalPaid = $transaction ? $this->totalPaid($transaction->id) : 0.0;
        $balance = round(max($amountDue - $totalPaid, 0), 2);
        $amountPaidThis = round((float) $paymentCollection->amount_paid, 2);
        $paidBeforeThis = round(max($totalPaid - $amountPaidThis, 0), 2);
        $balanceBeforeThis = round(max($amountDue - $paidBeforeThis, 0), 2);

        $charges = [];
        if ($transaction) {
            if ((float) $transaction->base_fee > 0) {
                $charges[] = ['item' => 'Base Fee', 'qty' => 1, 'total' => (float) $transaction->base_fee];
            }
            if ((float) $transaction->maintenance_fee > 0) {
                $charges[] = ['item' => 'Maintenance Fee', 'qty' => 1, 'total' => (float) $transaction->maintenance_fee];
            }
            if ((float) $transaction->burial_permit_fee > 0) {
                $charges[] = ['item' => 'Burial Permit Fee', 'qty' => 1, 'total' => (float) $transaction->burial_permit_fee];
            }
            if ((float) $transaction->other_applicable_fee > 0) {
                $charges[] = ['item' => 'Other Applicable Fee', 'qty' => 1, 'total' => (float) $transaction->other_applicable_fee];
            }
            if (empty($charges)) {
                $charges[] = ['item' => $transaction->transactionType?->type_name ?: 'Cemetery Service', 'qty' => 1, 'total' => $amountDue];
            }
        }

        $receipt = [
            'business_name' => 'Meedocentrix Cemetery Services',
            'address' => 'San Jose, Antique',
            'tin' => 'N/A',
            'payment_number' => $paymentCollection->payment_no,
            'transaction_number' => $transaction?->transaction_no ?? '-',
            'date' => optional($paymentCollection->payment_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'cashier' => $paymentCollection->creator?->name ?? '-',
            'payer_name' => $paymentCollection->contact?->contact_person ?? '-',
            'deceased' => $transaction?->deceased_name ?? '-',
            'plot_reference' => $transaction?->plot_reference ?? '-',
            'cemetery' => $transaction?->site?->site_name ?? '-',
            'category' => $transaction?->category?->category_name ?? '-',
            'service_type' => $transaction?->transactionType?->type_name ?? '-',
            'charges' => $charges,
            'amount_due' => $amountDue,
            'amount_paid_this' => $amountPaidThis,
            'paid_before_this' => $paidBeforeThis,
            'balance_before_payment' => $balanceBeforeThis,
            'balance_after_payment' => $balance,
            'total_paid' => $totalPaid,
            'balance' => $balance,
            'status' => $paymentCollection->payment_status,
        ];

        return view('cemetery.receipt', [
            'receipt' => $receipt,
        ]);
    }

    private function totalPaid(int $transactionId, ?int $excludePaymentId = null): float
    {
        $query = CemeteryPaymentCollection::query()
            ->where('cemetery_transaction_id', $transactionId);

        if ($excludePaymentId !== null) {
            $query->where('id', '!=', $excludePaymentId);
        }

        return round((float) $query->sum('amount_paid'), 2);
    }

    private function remainingBalance(int $transactionId, ?int $excludePaymentId = null): float
    {
        $amountDue = $this->transactionAmountDue($transactionId);
        $totalPaid = $this->totalPaid($transactionId, $excludePaymentId);
        return round(max($amountDue - $totalPaid, 0), 2);
    }

    public function destroy(CemeteryPaymentCollection $paymentCollection): RedirectResponse
    {
        $paymentNo = $paymentCollection->payment_no;
        $transaction = $paymentCollection->transaction;
        $paymentCollection->delete();
        if ($transaction) {
            $this->syncTransactionByModel($transaction);
        }

        return redirect()
            ->back()
            ->with('status', "Payment record {$paymentNo} deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request, ?CemeteryPaymentCollection $paymentCollection = null): array
    {
        $paymentNoRule = $paymentCollection
            ? Rule::unique('cemetery_payment_collections', 'payment_no')->ignore($paymentCollection->id)
            : Rule::unique('cemetery_payment_collections', 'payment_no');

        $receiptRule = $paymentCollection
            ? Rule::unique('cemetery_payment_collections', 'official_receipt_no')->ignore($paymentCollection->id)
            : Rule::unique('cemetery_payment_collections', 'official_receipt_no');

        return [
            'payment_no' => ['required', 'string', 'max:40', $paymentNoRule],
            'cemetery_transaction_id' => ['required', 'integer', Rule::exists('cemetery_transactions', 'id')],
            'cemetery_contact_id' => ['nullable', 'integer', Rule::exists('cemetery_contacts', 'id')],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'official_receipt_no' => ['nullable', 'string', 'max:60', $receiptRule],
            'payment_date' => ['nullable', 'date'],
            'coverage_start_date' => ['nullable', 'date'],
            'coverage_end_date' => ['nullable', 'date', 'after_or_equal:coverage_start_date'],
            'payment_status' => ['nullable', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'form_mode' => ['nullable', 'string', Rule::in(['create', 'edit'])],
            'form_payment_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function payload(array $validated, string $resolvedStatus): array
    {
        $transactionId = (int) $validated['cemetery_transaction_id'];
        $contactId = $this->resolveContactIdForTransaction(
            $transactionId,
            isset($validated['cemetery_contact_id']) ? (int) $validated['cemetery_contact_id'] : null
        );

        return [
            'payment_no' => strtoupper(trim((string) $validated['payment_no'])),
            'cemetery_transaction_id' => $transactionId,
            'cemetery_contact_id' => $contactId,
            'amount_paid' => round((float) $validated['amount_paid'], 2),
            'official_receipt_no' => $this->nullableTrimmedUpper($validated['official_receipt_no'] ?? null),
            'payment_date' => $validated['payment_date'] ?? null,
            'coverage_start_date' => $validated['coverage_start_date'] ?? null,
            'coverage_end_date' => $validated['coverage_end_date'] ?? null,
            'payment_status' => $resolvedStatus,
            'remarks' => $this->nullableTrimmed($validated['remarks'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function validatePaymentConsistency(array $validated, float $remainingBalance, string $resolvedStatus): void
    {
        $amountPaid = round((float) ($validated['amount_paid'] ?? 0), 2);
        $paymentDate = trim((string) ($validated['payment_date'] ?? ''));
        $errors = [];

        if (in_array($resolvedStatus, ['paid', 'partial'], true)) {
            if ($paymentDate === '') {
                $errors['payment_date'] = 'Payment date is required for paid or partial payment.';
            }
        }

        if ($resolvedStatus === 'paid' && $amountPaid < $remainingBalance) {
            $errors['amount_paid'] = 'Paid status requires amount paid to fully cover the remaining balance.';
        }

        if ($resolvedStatus === 'partial' && ($amountPaid <= 0 || $amountPaid >= $remainingBalance)) {
            $errors['amount_paid'] = 'Partial status requires amount paid greater than 0 and less than remaining balance.';
        }

        if ($resolvedStatus === 'overdue' && $amountPaid >= $remainingBalance && $remainingBalance > 0) {
            $errors['payment_status'] = 'Overdue status applies only when balance is still unpaid.';
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function nextPaymentNo(): string
    {
        $latestNo = (string) CemeteryPaymentCollection::query()
            ->orderByDesc('id')
            ->value('payment_no');

        if (preg_match('/(\d+)$/', $latestNo, $matches) === 1) {
            $next = (int) $matches[1] + 1;
            return 'CPY-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        return 'CPY-0001';
    }

    private function nullableTrimmed(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));
        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableTrimmedUpper(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));
        return $trimmed === '' ? null : strtoupper($trimmed);
    }

    private function syncOverduePaymentStatuses(): void
    {
        CemeteryPaymentCollection::query()
            ->whereNotNull('coverage_end_date')
            ->whereDate('coverage_end_date', '<', now()->toDateString())
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->update(['payment_status' => 'overdue']);
    }

    private function syncTransactionStatus(CemeteryPaymentCollection $paymentCollection): void
    {
        $transaction = $paymentCollection->transaction;
        if (! $transaction) {
            return;
        }

        $this->syncTransactionByModel($transaction);
    }

    private function syncTransactionByModel(CemeteryTransaction $transaction): void
    {
        $amountDue = round((float) $transaction->amount_due, 2);
        $totalPaid = $this->totalPaid($transaction->id);
        $remaining = round(max($amountDue - $totalPaid, 0), 2);

        $transaction->total_paid = $totalPaid;
        $transaction->remaining_balance = $remaining;

        if ($transaction->status !== 'cancelled') {
            if ($amountDue <= 0 || $totalPaid >= $amountDue) {
                $transaction->status = 'paid';
            } elseif ($totalPaid > 0) {
                $transaction->status = 'partial';
            } else {
                $transaction->status = 'pending';
            }
        }

        $transaction->save();
    }

    private function transactionAmountDue(int $transactionId): float
    {
        $amountDue = (float) (CemeteryTransaction::query()
            ->whereKey($transactionId)
            ->value('amount_due') ?? 0);

        return round($amountDue, 2);
    }

    private function resolveContactIdForTransaction(int $transactionId, ?int $fallbackContactId = null): int
    {
        $transaction = CemeteryTransaction::query()
            ->with('occupantRecord:id,cemetery_contact_id')
            ->find($transactionId);

        $contactId = (int) ($transaction?->occupantRecord?->cemetery_contact_id ?? 0);
        if ($contactId > 0) {
            return $contactId;
        }

        if ($fallbackContactId !== null && $fallbackContactId > 0) {
            return $fallbackContactId;
        }

        throw ValidationException::withMessages([
            'cemetery_transaction_id' => 'Selected transaction has no linked occupant contact. Update occupant record contact first.',
        ]);
    }

    private function resolveOptionalContactIdForTransaction(int $transactionId): ?int
    {
        $contactId = (int) (CemeteryTransaction::query()
            ->with('occupantRecord:id,cemetery_contact_id')
            ->find($transactionId)?->occupantRecord?->cemetery_contact_id ?? 0);

        return $contactId > 0 ? $contactId : null;
    }

    private function resolvePaymentStatus(string $requestedStatus, float $amountPaid, float $amountDue): string
    {
        $requestedStatus = strtolower(trim($requestedStatus));
        $amountPaid = round(max($amountPaid, 0), 2);
        $amountDue = round(max($amountDue, 0), 2);

        if ($amountDue <= 0) {
            return 'paid';
        }

        if ($amountPaid <= 0) {
            return $requestedStatus === 'overdue' ? 'overdue' : 'unpaid';
        }

        if ($amountPaid >= $amountDue) {
            return 'paid';
        }

        return $requestedStatus === 'overdue' ? 'overdue' : 'partial';
    }
}
