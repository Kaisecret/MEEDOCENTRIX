<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryContact;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeteryPaymentCollection;
use App\Models\CemeterySite;
use App\Models\CemeteryTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->with(['site:id,site_name', 'category:id,category_name'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $usedTransactionIds = CemeteryPaymentCollection::query()
            ->pluck('cemetery_transaction_id')
            ->all();

        $availableTransactions = $transactions
            ->filter(fn (CemeteryTransaction $transaction): bool => ! in_array($transaction->id, $usedTransactionIds, true))
            ->values();
        $hasTransactions = $transactions->isNotEmpty();
        $hasAvailableTransactions = $availableTransactions->isNotEmpty();

        $contactByTransactionId = [];
        $occupantByKey = [];
        $occupantRecords = CemeteryOccupantRecord::query()
            ->with(['plot:id,plot_reference', 'contact:id'])
            ->get();

        foreach ($occupantRecords as $occupantRecord) {
            $plotReference = strtoupper(trim((string) ($occupantRecord->plot?->plot_reference ?? '')));
            $deceasedName = strtoupper(trim((string) $occupantRecord->deceased_name));
            if ($plotReference === '' || $deceasedName === '' || $occupantRecord->cemetery_contact_id === null) {
                continue;
            }

            $occupantByKey[$this->occupantKey((int) $occupantRecord->cemetery_site_id, $deceasedName, $plotReference)] = (int) $occupantRecord->cemetery_contact_id;
        }

        foreach ($transactions as $transaction) {
            $key = $this->occupantKey(
                (int) $transaction->cemetery_site_id,
                strtoupper(trim((string) $transaction->deceased_name)),
                strtoupper(trim((string) $transaction->plot_reference))
            );

            if (isset($occupantByKey[$key])) {
                $contactByTransactionId[$transaction->id] = $occupantByKey[$key];
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
            'allTransactionsAlreadyRecorded' => $hasTransactions && ! $hasAvailableTransactions,
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
        $amountDue = $this->transactionAmountDue((int) $validated['cemetery_transaction_id']);
        $resolvedStatus = $this->resolvePaymentStatus(
            (string) ($validated['payment_status'] ?? ''),
            (float) ($validated['amount_paid'] ?? 0),
            $amountDue
        );
        $this->validatePaymentConsistency($validated, $amountDue, $resolvedStatus);

        $paymentCollection = CemeteryPaymentCollection::query()->create($this->payload($validated, $resolvedStatus) + [
            'created_by_user_id' => Auth::id(),
        ]);
        $this->syncTransactionStatus($paymentCollection);

        return redirect()
            ->route('cemetery.payments')
            ->with('status', 'Payment collection record added successfully.');
    }

    public function update(Request $request, CemeteryPaymentCollection $paymentCollection): RedirectResponse
    {
        $validated = $request->validate($this->rules($request, $paymentCollection));
        $amountDue = $this->transactionAmountDue((int) $validated['cemetery_transaction_id']);
        $resolvedStatus = $this->resolvePaymentStatus(
            (string) ($validated['payment_status'] ?? ''),
            (float) ($validated['amount_paid'] ?? 0),
            $amountDue
        );
        $this->validatePaymentConsistency($validated, $amountDue, $resolvedStatus);

        $paymentCollection->update($this->payload($validated, $resolvedStatus));
        $paymentCollection->refresh();
        $this->syncTransactionStatus($paymentCollection);

        return redirect()
            ->back()
            ->with('status', "Payment record {$paymentCollection->payment_no} updated.");
    }

    public function destroy(CemeteryPaymentCollection $paymentCollection): RedirectResponse
    {
        $paymentNo = $paymentCollection->payment_no;
        $transaction = $paymentCollection->transaction;
        $paymentCollection->delete();
        if ($transaction && $transaction->status !== 'cancelled') {
            $transaction->status = 'pending';
            $transaction->save();
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

        $transactionRule = $paymentCollection
            ? Rule::unique('cemetery_payment_collections', 'cemetery_transaction_id')->ignore($paymentCollection->id)
            : Rule::unique('cemetery_payment_collections', 'cemetery_transaction_id');

        $receiptRule = $paymentCollection
            ? Rule::unique('cemetery_payment_collections', 'official_receipt_no')->ignore($paymentCollection->id)
            : Rule::unique('cemetery_payment_collections', 'official_receipt_no');

        return [
            'payment_no' => ['required', 'string', 'max:40', $paymentNoRule],
            'cemetery_transaction_id' => ['required', 'integer', Rule::exists('cemetery_transactions', 'id'), $transactionRule],
            'cemetery_contact_id' => ['required', 'integer', Rule::exists('cemetery_contacts', 'id')],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'official_receipt_no' => ['nullable', 'string', 'max:60', $receiptRule],
            'payment_date' => ['nullable', 'date'],
            'coverage_start_date' => ['nullable', 'date'],
            'coverage_end_date' => ['nullable', 'date', 'after_or_equal:coverage_start_date'],
            'payment_status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
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
        return [
            'payment_no' => strtoupper(trim((string) $validated['payment_no'])),
            'cemetery_transaction_id' => (int) $validated['cemetery_transaction_id'],
            'cemetery_contact_id' => (int) $validated['cemetery_contact_id'],
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
    private function validatePaymentConsistency(array $validated, float $amountDue, string $resolvedStatus): void
    {
        $amountPaid = round((float) ($validated['amount_paid'] ?? 0), 2);
        $receiptNo = trim((string) ($validated['official_receipt_no'] ?? ''));
        $paymentDate = trim((string) ($validated['payment_date'] ?? ''));
        $errors = [];

        if (in_array($resolvedStatus, ['paid', 'partial'], true)) {
            if ($receiptNo === '') {
                $errors['official_receipt_no'] = 'Official receipt number is required for paid or partial payment.';
            }

            if ($paymentDate === '') {
                $errors['payment_date'] = 'Payment date is required for paid or partial payment.';
            }
        }

        if ($resolvedStatus === 'paid' && $amountPaid < $amountDue) {
            $errors['amount_paid'] = 'Paid status requires amount paid to fully cover amount due.';
        }

        if ($resolvedStatus === 'partial' && ($amountPaid <= 0 || $amountPaid >= $amountDue)) {
            $errors['amount_paid'] = 'Partial status requires amount paid greater than 0 and less than amount due.';
        }

        if ($resolvedStatus === 'overdue' && $amountPaid >= $amountDue && $amountDue > 0) {
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

    private function occupantKey(int $siteId, string $deceasedName, string $plotReference): string
    {
        return $siteId . '|' . $deceasedName . '|' . $plotReference;
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
        if (! $transaction || $transaction->status === 'cancelled') {
            return;
        }

        $amountDue = round((float) $transaction->amount_due, 2);
        $amountPaid = round((float) $paymentCollection->amount_paid, 2);

        if ($amountDue <= 0 || $amountPaid >= $amountDue) {
            $transaction->status = 'paid';
        } elseif ($amountPaid > 0) {
            $transaction->status = 'partial';
        } else {
            $transaction->status = 'pending';
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
