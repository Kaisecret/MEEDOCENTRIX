<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumSuppliesOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AtriumSuppliesController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', 'all'));

        $query = AtriumSuppliesOrder::query()
            ->with(['event.functionHall:id,name,code', 'requestedBy:id,name']);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like): void {
                $q->where('requested_supplies', 'like', $like)
                    ->orWhere('remarks', 'like', $like)
                    ->orWhereHas('event', function ($sub) use ($like): void {
                        $sub->where('event_code', 'like', $like)
                            ->orWhere('name_contact_person', 'like', $like)
                            ->orWhere('event_details', 'like', $like);
                    });
            });
        }

        if (in_array($status, ['pending', 'approved', 'fulfilled', 'rejected'], true)) {
            $query->where('request_status', $status);
        }

        $orders = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => AtriumSuppliesOrder::query()->count(),
            'pending' => AtriumSuppliesOrder::query()->where('request_status', 'pending')->count(),
            'approved' => AtriumSuppliesOrder::query()->where('request_status', 'approved')->count(),
            'fulfilled' => AtriumSuppliesOrder::query()->where('request_status', 'fulfilled')->count(),
            'rejected' => AtriumSuppliesOrder::query()->where('request_status', 'rejected')->count(),
        ];

        $eventsForSelect = AtriumEvent::query()
            ->with('functionHall:id,name,code')
            ->whereIn('booking_status', ['reserved', 'confirmed'])
            ->orderBy('date_of_event')
            ->limit(200)
            ->get();

        return view('atrium.supplies', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'summary' => $summary,
            'eventsForSelect' => $eventsForSelect,
        ]);
    }

    public function create(Request $request): View
    {
        $eventId = (int) $request->query('event', 0);
        $event = $eventId > 0 ? AtriumEvent::with('functionHall')->find($eventId) : null;

        $eventsForSelect = AtriumEvent::query()
            ->with('functionHall:id,name,code')
            ->whereIn('booking_status', ['reserved', 'confirmed'])
            ->orderBy('date_of_event')
            ->limit(200)
            ->get();

        return view('atrium.supplies_form', [
            'order' => new AtriumSuppliesOrder(['request_status' => 'pending']),
            'event' => $event,
            'eventsForSelect' => $eventsForSelect,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateOrder($request);

        AtriumSuppliesOrder::create([
            'atrium_event_id' => (int) $validated['atrium_event_id'],
            'time_needed' => $validated['time_needed'] ?? null,
            'requested_supplies' => $validated['requested_supplies'],
            'request_status' => $validated['request_status'] ?? 'pending',
            'remarks' => $validated['remarks'] ?? null,
            'requested_by_user_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('atrium.supplies')
            ->with('status', 'Supplies request submitted.');
    }

    public function edit(AtriumSuppliesOrder $order): View
    {
        $order->load('event.functionHall');

        $eventsForSelect = AtriumEvent::query()
            ->with('functionHall:id,name,code')
            ->whereIn('booking_status', ['reserved', 'confirmed'])
            ->orderBy('date_of_event')
            ->limit(200)
            ->get();

        return view('atrium.supplies_form', [
            'order' => $order,
            'event' => $order->event,
            'eventsForSelect' => $eventsForSelect,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, AtriumSuppliesOrder $order): RedirectResponse
    {
        $validated = $this->validateOrder($request, $order->id);

        $order->update([
            'atrium_event_id' => (int) $validated['atrium_event_id'],
            'time_needed' => $validated['time_needed'] ?? null,
            'requested_supplies' => $validated['requested_supplies'],
            'request_status' => $validated['request_status'] ?? $order->request_status,
            'remarks' => $validated['remarks'] ?? null,
            'fulfilled_at' => ($validated['request_status'] ?? null) === 'fulfilled' && ! $order->fulfilled_at
                ? Carbon::now()
                : $order->fulfilled_at,
        ]);

        return redirect()
            ->route('atrium.supplies')
            ->with('status', 'Supplies request updated.');
    }

    public function destroy(AtriumSuppliesOrder $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('atrium.supplies')
            ->with('status', 'Supplies request deleted.');
    }

    public function approve(AtriumSuppliesOrder $order): RedirectResponse
    {
        $order->update(['request_status' => 'approved']);

        return redirect()
            ->route('atrium.supplies')
            ->with('status', 'Request approved.');
    }

    public function fulfill(AtriumSuppliesOrder $order): RedirectResponse
    {
        $order->update([
            'request_status' => 'fulfilled',
            'fulfilled_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('atrium.supplies')
            ->with('status', 'Request marked as fulfilled.');
    }

    public function reject(AtriumSuppliesOrder $order): RedirectResponse
    {
        $order->update(['request_status' => 'rejected']);

        return redirect()
            ->route('atrium.supplies')
            ->with('status', 'Request rejected.');
    }

    private function validateOrder(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'atrium_event_id' => ['required', 'integer', 'exists:atrium_events,id'],
            'time_needed' => ['nullable', 'string', 'max:60'],
            'requested_supplies' => ['required', 'string', 'max:2000'],
            'request_status' => ['nullable', 'in:pending,approved,fulfilled,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
