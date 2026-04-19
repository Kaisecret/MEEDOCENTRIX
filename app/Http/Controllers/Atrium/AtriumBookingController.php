<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumEventAddOn;
use App\Models\AtriumFunctionHall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AtriumBookingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', 'all'));
        $hallId = (int) $request->query('hall', 0);
        $range = trim((string) $request->query('range', 'all'));
        $fromInput = trim((string) $request->query('from', ''));
        $toInput = trim((string) $request->query('to', ''));

        [$rangeStart, $rangeEnd, $rangeLabel] = $this->resolveRange($range, $fromInput, $toInput);

        $query = AtriumEvent::query()
            ->with(['functionHall:id,name,code', 'addOns', 'payments']);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like): void {
                $q->where('event_code', 'like', $like)
                    ->orWhere('name_contact_person', 'like', $like)
                    ->orWhere('event_details', 'like', $like)
                    ->orWhere('contact_number', 'like', $like);
            });
        }

        if (in_array($status, ['reserved', 'confirmed', 'completed', 'cancelled'], true)) {
            $query->where('booking_status', $status);
        }

        if ($hallId > 0) {
            $query->where('atrium_function_hall_id', $hallId);
        }

        if ($rangeStart && $rangeEnd) {
            $query->whereBetween('date_of_event', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
        }

        $events = $query
            ->orderByDesc('date_of_event')
            ->paginate(15)
            ->withQueryString();

        $halls = AtriumFunctionHall::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $summary = [
            'total' => AtriumEvent::query()->count(),
            'reserved' => AtriumEvent::query()->where('booking_status', 'reserved')->count(),
            'confirmed' => AtriumEvent::query()->where('booking_status', 'confirmed')->count(),
            'completed' => AtriumEvent::query()->where('booking_status', 'completed')->count(),
            'cancelled' => AtriumEvent::query()->where('booking_status', 'cancelled')->count(),
        ];

        return view('atrium.bookings', [
            'events' => $events,
            'halls' => $halls,
            'search' => $search,
            'status' => $status,
            'hallId' => $hallId,
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'from' => $fromInput,
            'to' => $toInput,
            'summary' => $summary,
            'nextCode' => $this->generateEventCode(),
        ]);
    }

    public function show(AtriumEvent $event): View
    {
        $event->load(['functionHall', 'addOns', 'payments.recordedBy:id,name', 'suppliesOrders']);

        return view('atrium.booking_show', [
            'event' => $event,
        ]);
    }

    public function create(): View
    {
        $halls = AtriumFunctionHall::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('atrium.booking_form', [
            'event' => new AtriumEvent(['booking_status' => 'reserved', 'no_of_hours' => 1]),
            'halls' => $halls,
            'mode' => 'create',
            'nextCode' => $this->generateEventCode(),
        ]);
    }

    public function edit(AtriumEvent $event): View
    {
        $event->load('addOns');
        $halls = AtriumFunctionHall::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('atrium.booking_form', [
            'event' => $event,
            'halls' => $halls,
            'mode' => 'edit',
            'nextCode' => $event->event_code,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBooking($request);

        $event = DB::transaction(function () use ($validated, $request): AtriumEvent {
            $event = AtriumEvent::create([
                'event_code' => $validated['event_code'] ?: $this->generateEventCode(),
                'date_of_event' => $validated['date_of_event'],
                'start_time' => $validated['start_time'] ?? null,
                'event_details' => $validated['event_details'],
                'name_contact_person' => $validated['name_contact_person'],
                'contact_number' => $validated['contact_number'] ?? null,
                'atrium_function_hall_id' => (int) $validated['atrium_function_hall_id'],
                'no_of_hours' => (float) $validated['no_of_hours'],
                'hall_payment' => (float) $validated['hall_payment'],
                'miscellaneous_payment' => (float) ($validated['miscellaneous_payment'] ?? 0),
                'accommodation_payment' => (float) ($validated['accommodation_payment'] ?? 0),
                'actual_due' => 0,
                'booking_status' => $validated['booking_status'] ?? 'reserved',
                'created_by_user_id' => $request->user()?->id,
            ]);

            $this->syncAddOns($event, $request);
            $this->recomputeActualDue($event);

            return $event;
        });

        if ($request->input('redirect_to') === 'bookings') {
            return redirect()
                ->route('atrium.bookings')
                ->with('status', 'Booking saved. Event code: ' . $event->event_code);
        }

        return redirect()
            ->route('atrium.bookings.show', $event)
            ->with('status', 'Booking saved. Event code: ' . $event->event_code);
    }

    public function update(Request $request, AtriumEvent $event): RedirectResponse
    {
        $validated = $this->validateBooking($request, $event->id);

        DB::transaction(function () use ($validated, $request, $event): void {
            $event->update([
                'event_code' => $validated['event_code'],
                'date_of_event' => $validated['date_of_event'],
                'start_time' => $validated['start_time'] ?? null,
                'event_details' => $validated['event_details'],
                'name_contact_person' => $validated['name_contact_person'],
                'contact_number' => $validated['contact_number'] ?? null,
                'atrium_function_hall_id' => (int) $validated['atrium_function_hall_id'],
                'no_of_hours' => (float) $validated['no_of_hours'],
                'hall_payment' => (float) $validated['hall_payment'],
                'miscellaneous_payment' => (float) ($validated['miscellaneous_payment'] ?? 0),
                'accommodation_payment' => (float) ($validated['accommodation_payment'] ?? 0),
                'booking_status' => $validated['booking_status'] ?? $event->booking_status,
            ]);

            $this->syncAddOns($event, $request);
            $this->recomputeActualDue($event);
        });

        if ($request->input('redirect_to') === 'bookings') {
            return redirect()
                ->route('atrium.bookings')
                ->with('status', 'Booking updated.');
        }

        return redirect()
            ->route('atrium.bookings.show', $event)
            ->with('status', 'Booking updated.');
    }

    public function destroy(AtriumEvent $event): RedirectResponse
    {
        $event->delete();

        return redirect()
            ->route('atrium.bookings')
            ->with('status', 'Booking deleted.');
    }

    public function cancel(AtriumEvent $event): RedirectResponse
    {
        $event->update(['booking_status' => 'cancelled']);

        return redirect()
            ->route('atrium.bookings.show', $event)
            ->with('status', 'Booking marked as cancelled.');
    }

    public function complete(AtriumEvent $event): RedirectResponse
    {
        $event->update(['booking_status' => 'completed']);

        return redirect()
            ->route('atrium.bookings.show', $event)
            ->with('status', 'Booking marked as completed.');
    }

    private function validateBooking(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'event_code' => [
                'nullable', 'string', 'max:30',
                'unique:atrium_events,event_code' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'date_of_event' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'event_details' => ['required', 'string', 'max:500'],
            'name_contact_person' => ['required', 'string', 'max:160'],
            'contact_number' => ['nullable', 'string', 'max:60'],
            'atrium_function_hall_id' => ['required', 'integer', 'exists:atrium_function_halls,id'],
            'no_of_hours' => ['required', 'numeric', 'min:0.5', 'max:48'],
            'hall_payment' => ['required', 'numeric', 'min:0'],
            'miscellaneous_payment' => ['nullable', 'numeric', 'min:0'],
            'accommodation_payment' => ['nullable', 'numeric', 'min:0'],
            'booking_status' => ['nullable', 'in:reserved,confirmed,completed,cancelled'],
            'add_ons' => ['nullable', 'array'],
            'add_ons.*.description' => ['nullable', 'string', 'max:200'],
            'add_ons.*.amount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function syncAddOns(AtriumEvent $event, Request $request): void
    {
        $event->addOns()->delete();

        $rows = $request->input('add_ons', []);
        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $desc = trim((string) ($row['description'] ?? ''));
            $amount = (float) ($row['amount'] ?? 0);
            if ($desc === '' && $amount <= 0) {
                continue;
            }
            AtriumEventAddOn::create([
                'atrium_event_id' => $event->id,
                'description' => $desc !== '' ? $desc : 'Add-on',
                'amount' => $amount,
            ]);
        }
    }

    private function recomputeActualDue(AtriumEvent $event): void
    {
        $event->loadMissing('addOns');

        $actualDue = (float) $event->hall_payment
            + (float) $event->miscellaneous_payment
            + (float) $event->accommodation_payment
            + (float) $event->addOns->sum('amount');

        $event->update(['actual_due' => $actualDue]);
    }

    private function generateEventCode(): string
    {
        $prefix = 'EVT-' . Carbon::now()->format('Ymd') . '-';
        do {
            $suffix = Str::upper(Str::random(4));
            $code = $prefix . $suffix;
        } while (AtriumEvent::query()->where('event_code', $code)->exists());

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
