<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumEventPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AtriumPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', 'all'));
        $range = trim((string) $request->query('range', 'all'));
        $fromInput = trim((string) $request->query('from', ''));
        $toInput = trim((string) $request->query('to', ''));

        [$rangeStart, $rangeEnd, $rangeLabel] = $this->resolveRange($range, $fromInput, $toInput);

        $query = AtriumEventPayment::query()
            ->with(['event.functionHall:id,name,code', 'recordedBy:id,name']);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like): void {
                $q->where('or_number', 'like', $like)
                    ->orWhereHas('event', function ($sub) use ($like): void {
                        $sub->where('event_code', 'like', $like)
                            ->orWhere('name_contact_person', 'like', $like)
                            ->orWhere('event_details', 'like', $like);
                    });
            });
        }

        if (in_array($status, ['paid', 'partial', 'unpaid'], true)) {
            $query->where('payment_status', $status);
        }

        if ($rangeStart && $rangeEnd) {
            $query->whereBetween('date_of_payment', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
        }

        $payments = $query->orderByDesc('date_of_payment')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total_collected' => (float) AtriumEventPayment::query()->sum('payment_amount'),
            'paid_count' => AtriumEventPayment::query()->where('payment_status', 'paid')->count(),
            'partial_count' => AtriumEventPayment::query()->where('payment_status', 'partial')->count(),
            'unpaid_count' => AtriumEvent::query()
                ->whereDoesntHave('payments')
                ->count(),
        ];

        $eventsForSelect = $this->payableEvents();

        return view('atrium.payments', [
            'payments' => $payments,
            'search' => $search,
            'status' => $status,
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'from' => $fromInput,
            'to' => $toInput,
            'summary' => $summary,
            'eventsForSelect' => $eventsForSelect,
            'nextOr' => $this->generateOrNumber(),
        ]);
    }

    public function create(Request $request): View
    {
        $eventId = (int) $request->query('event', 0);
        $event = $eventId > 0 ? AtriumEvent::with(['functionHall', 'payments'])->find($eventId) : null;

        $eventsForSelect = $this->payableEvents($event?->id);

        return view('atrium.payment_form', [
            'event' => $event,
            'payment' => new AtriumEventPayment([
                'date_of_payment' => Carbon::today()->toDateString(),
                'or_number' => $this->generateOrNumber(),
                'payment_status' => 'partial',
            ]),
            'eventsForSelect' => $eventsForSelect,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayment($request);
        $this->assertPaymentWithinBalance((int) $validated['atrium_event_id'], (float) $validated['payment_amount']);

        $payment = DB::transaction(function () use ($validated, $request): AtriumEventPayment {
            $event = AtriumEvent::with('payments')->findOrFail($validated['atrium_event_id']);
            $amount = (float) $validated['payment_amount'];
            $status = $this->resolveStatus($event, $amount);

            $payment = AtriumEventPayment::create([
                'atrium_event_id' => $event->id,
                'or_number' => $validated['or_number'] ?: $this->generateOrNumber(),
                'date_of_payment' => $validated['date_of_payment'],
                'payment_amount' => $amount,
                'payment_status' => $status,
                'remarks' => $validated['remarks'] ?? null,
                'recorded_by_user_id' => $request->user()?->id,
            ]);

            $totalPaid = (float) $event->payments()->sum('payment_amount');
            if ($totalPaid + 0.009 >= (float) $event->actual_due && $event->booking_status === 'reserved') {
                $event->update(['booking_status' => 'confirmed']);
            }

            return $payment;
        });

        return redirect()
            ->route('atrium.payments')
            ->with('status', 'Payment recorded. OR #' . $payment->or_number);
    }

    public function show(AtriumEventPayment $payment): View
    {
        $payment->load(['event.functionHall', 'event.addOns', 'event.payments', 'recordedBy:id,name']);

        return view('atrium.payment_show', [
            'payment' => $payment,
        ]);
    }

    public function edit(AtriumEventPayment $payment): View
    {
        $payment->load('event.functionHall');

        $eventsForSelect = $this->payableEvents($payment->atrium_event_id, $payment->id);

        return view('atrium.payment_form', [
            'event' => $payment->event,
            'payment' => $payment,
            'eventsForSelect' => $eventsForSelect,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, AtriumEventPayment $payment): RedirectResponse
    {
        $validated = $this->validatePayment($request, $payment->id);
        $this->assertPaymentWithinBalance((int) $validated['atrium_event_id'], (float) $validated['payment_amount'], $payment->id);

        DB::transaction(function () use ($validated, $payment): void {
            $event = AtriumEvent::with('payments')->findOrFail($validated['atrium_event_id']);
            $amount = (float) $validated['payment_amount'];

            $payment->update([
                'atrium_event_id' => $event->id,
                'or_number' => $validated['or_number'],
                'date_of_payment' => $validated['date_of_payment'],
                'payment_amount' => $amount,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $payment->update(['payment_status' => $this->resolveStatus($event->fresh('payments'), 0.0, $payment->id, $amount)]);
        });

        return redirect()
            ->route('atrium.payments.show', $payment)
            ->with('status', 'Payment updated.');
    }

    public function destroy(AtriumEventPayment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()
            ->route('atrium.payments')
            ->with('status', 'Payment deleted.');
    }

    private function validatePayment(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'atrium_event_id' => ['required', 'integer', 'exists:atrium_events,id'],
            'or_number' => [
                'nullable', 'string', 'max:40',
                'unique:atrium_event_payments,or_number' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'date_of_payment' => ['required', 'date'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'remarks' => ['nullable', 'string', 'max:300'],
        ]);
    }

    private function resolveStatus(AtriumEvent $event, float $addingAmount, ?int $ignorePaymentId = null, float $replacementAmount = 0.0): string
    {
        $due = (float) $event->actual_due;
        $existing = (float) $event->payments()
            ->when($ignorePaymentId, fn($q) => $q->where('id', '!=', $ignorePaymentId))
            ->sum('payment_amount');

        $total = $existing + $addingAmount + $replacementAmount;

        if ($total <= 0.0) {
            return 'unpaid';
        }
        if ($total + 0.009 >= $due) {
            return 'paid';
        }
        return 'partial';
    }

    private function assertPaymentWithinBalance(int $eventId, float $amount, ?int $ignorePaymentId = null): void
    {
        $event = AtriumEvent::query()->findOrFail($eventId);

        $existingPaid = (float) AtriumEventPayment::query()
            ->where('atrium_event_id', $event->id)
            ->when($ignorePaymentId, fn ($q) => $q->where('id', '!=', $ignorePaymentId))
            ->sum('payment_amount');

        $due = (float) $event->actual_due;
        $remaining = max(0.0, $due - $existingPaid);

        if ($remaining <= 0.0) {
            throw ValidationException::withMessages([
                'payment_amount' => 'This booking has no remaining balance.',
            ]);
        }

        if ($amount > $remaining + 0.009) {
            throw ValidationException::withMessages([
                'payment_amount' => 'Payment amount cannot be greater than remaining balance (PHP ' . number_format($remaining, 2) . ').',
            ]);
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, AtriumEvent>
     */
    private function payableEvents(?int $includeEventId = null, ?int $ignorePaymentId = null)
    {
        return AtriumEvent::query()
            ->with('functionHall:id,name,code')
            ->withSum('payments as total_paid', 'payment_amount')
            ->whereIn('booking_status', ['reserved', 'confirmed', 'completed'])
            ->orderByDesc('date_of_event')
            ->limit(300)
            ->get()
            ->filter(function (AtriumEvent $event) use ($includeEventId, $ignorePaymentId): bool {
                $totalPaid = (float) ($event->total_paid ?? 0);

                if ($ignorePaymentId) {
                    $ignored = (float) AtriumEventPayment::query()
                        ->where('id', $ignorePaymentId)
                        ->where('atrium_event_id', $event->id)
                        ->value('payment_amount');
                    $totalPaid -= $ignored;
                }

                $balance = (float) $event->actual_due - $totalPaid;

                if ($includeEventId && (int) $event->id === (int) $includeEventId) {
                    return true;
                }

                return $balance > 0.009;
            })
            ->values();
    }

    private function generateOrNumber(): string
    {
        $prefix = 'OR-' . Carbon::now()->format('Ymd') . '-';
        do {
            $suffix = Str::upper(Str::random(4));
            $code = $prefix . $suffix;
        } while (AtriumEventPayment::query()->where('or_number', $code)->exists());

        return $code;
    }

    /**
     * @return array{0: ?\Illuminate\Support\Carbon, 1: ?\Illuminate\Support\Carbon, 2: string}
     */
    private function resolveRange(string $range, string $fromInput, string $toInput): array
    {
        $now = Carbon::now();
        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today (' . $now->format('M d, Y') . ')'];
            case 'week':
                $s = $now->copy()->startOfWeek(Carbon::MONDAY);
                $e = $now->copy()->endOfWeek(Carbon::SUNDAY);
                return [$s, $e, 'This Week (' . $s->format('M d') . ' – ' . $e->format('M d, Y') . ')'];
            case 'month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month (' . $now->format('F Y') . ')'];
            case 'custom':
                $s = $fromInput !== '' ? Carbon::parse($fromInput)->startOfDay() : null;
                $e = $toInput !== '' ? Carbon::parse($toInput)->endOfDay() : null;
                $label = 'Custom';
                if ($s && $e) {
                    $label = $s->format('M d, Y') . ' – ' . $e->format('M d, Y');
                }
                return [$s, $e, $label];
            default:
                return [null, null, 'All Dates'];
        }
    }
}
