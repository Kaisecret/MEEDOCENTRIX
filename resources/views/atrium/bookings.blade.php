@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')
<style>
    #contentArea {
        padding-top: 10px;
    }

    .atr {
        gap: 10px;
    }

    .atr-kpi-grid {
        gap: 10px;
    }

    .atr-kpi-head {
        gap: 10px;
    }

    .atr-card-head {
        padding: 10px;
        gap: 10px;
    }

    .atr-card-head h3 {
        gap: 10px;
    }

    .atr-filter-bar {
        gap: 10px;
        padding: 10px;
    }

    .atr-range-bar,
    .atr-range-fields {
        gap: 10px;
    }

    .atr-range-select {
        min-width: 140px;
    }

    .atr-flash {
        position: fixed;
        top: 74px;
        right: 14px;
        z-index: 1700;
        min-width: 220px;
        max-width: min(360px, calc(100vw - 28px));
        border-radius: 10px;
        border: 1px solid #86efac;
        background: #ecfdf5;
        color: #065f46;
        padding: 8px 11px;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .14);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.3;
        transition: opacity .22s ease, transform .22s ease;
    }

    .atr-flash i {
        font-size: .9rem;
    }

    .atr-flash.is-hiding {
        opacity: 0;
        transform: translateY(-10px);
    }

    @media (max-width: 640px) {
        .atr-flash {
            top: 70px;
            left: 10px;
            right: 10px;
            max-width: none;
        }
    }

    .atr-pagination-wrap {
        border-top: 1px solid var(--atr-border);
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .atr-pagination-summary {
        font-size: .82rem;
        color: var(--atr-muted);
        font-weight: 600;
    }

    .atr-pagination {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .atr-page-link {
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border-radius: 9px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: var(--atr-primary);
        font-size: .82rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .16s ease;
    }

    .atr-page-link:hover {
        background: #f0f7ff;
        border-color: var(--atr-primary);
    }

    .atr-page-link.is-active {
        background: var(--atr-primary);
        border-color: var(--atr-primary);
        color: #fff;
    }

    .atr-page-link.is-disabled {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        pointer-events: none;
    }
</style>

@php
    $modalAddOns = old('add_ons', [['description' => '', 'amount' => '']]);
    if (!is_array($modalAddOns) || count($modalAddOns) === 0) {
        $modalAddOns = [['description' => '', 'amount' => '']];
    }

    $eventsMap = $events->getCollection()->mapWithKeys(function ($event) {
        $paid = (float) $event->payments->sum('payment_amount');
        $due = (float) $event->actual_due;

        return [
            (string) $event->id => [
                'id' => (int) $event->id,
                'event_code' => (string) $event->event_code,
                'date_of_event' => optional($event->date_of_event)->format('Y-m-d') ?: '',
                'date_label' => optional($event->date_of_event)->format('M d, Y') ?: '-',
                'start_time' => (string) ($event->start_time ?? ''),
                'event_details' => (string) ($event->event_details ?? ''),
                'name_contact_person' => (string) ($event->name_contact_person ?? ''),
                'contact_number' => (string) ($event->contact_number ?? ''),
                'hall_id' => (int) ($event->atrium_function_hall_id ?? 0),
                'hall_name' => (string) ($event->functionHall->name ?? '-'),
                'no_of_hours' => (float) ($event->no_of_hours ?? 0),
                'hall_payment' => (float) ($event->hall_payment ?? 0),
                'miscellaneous_payment' => (float) ($event->miscellaneous_payment ?? 0),
                'accommodation_payment' => (float) ($event->accommodation_payment ?? 0),
                'booking_status' => (string) ($event->booking_status ?? 'reserved'),
                'actual_due' => $due,
                'paid' => $paid,
                'balance' => max(0, $due - $paid),
                'add_ons' => $event->addOns
                    ->map(fn ($addon) => [
                        'description' => (string) ($addon->description ?? ''),
                        'amount' => (float) ($addon->amount ?? 0),
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    })->all();

    $oldEditPayload = null;
    if (old('edit_booking_id')) {
        $oldEditPayload = [
            'id' => (int) old('edit_booking_id'),
            'event_code' => (string) old('event_code', ''),
            'date_of_event' => (string) old('date_of_event', ''),
            'start_time' => (string) old('start_time', ''),
            'event_details' => (string) old('event_details', ''),
            'name_contact_person' => (string) old('name_contact_person', ''),
            'contact_number' => (string) old('contact_number', ''),
            'hall_id' => (int) old('atrium_function_hall_id', 0),
            'no_of_hours' => (float) old('no_of_hours', 1),
            'hall_payment' => (float) old('hall_payment', 0),
            'miscellaneous_payment' => (float) old('miscellaneous_payment', 0),
            'accommodation_payment' => (float) old('accommodation_payment', 0),
            'booking_status' => (string) old('booking_status', 'reserved'),
            'add_ons' => $modalAddOns,
        ];
    }
@endphp

<div class="atr" data-server-rendered-page="atrium_bookings" data-page-title="Atrium Bookings">
    @if (session('status'))
        <div id="atrStatusToast" class="atr-flash" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <section class="atr-kpi-grid">
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Reserved</span><span class="atr-kpi-icon blue"><i class="fa-solid fa-bookmark"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['reserved']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Confirmed</span><span class="atr-kpi-icon green"><i class="fa-solid fa-circle-check"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['confirmed']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Completed</span><span class="atr-kpi-icon purple"><i class="fa-solid fa-flag-checkered"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['completed']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Cancelled</span><span class="atr-kpi-icon red"><i class="fa-solid fa-ban"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['cancelled']) }}</div>
        </article>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-filter" style="color:var(--atr-primary);"></i>Filters</h3>
            <button type="button" class="atr-btn-primary" id="atrOpenBookingModal"><i class="fa-solid fa-plus"></i>New Booking</button>
        </div>
        <form method="GET" action="{{ route('atrium.bookings') }}" class="atr-filter-bar" id="atrBookingFiltersForm">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search code, name, contact..." class="atr-input atr-input--grow" id="atrBookingSearchInput" autocomplete="off">
            <select name="status" class="atr-input" onchange="this.form.submit()">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                <option value="reserved" {{ $status === 'reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <select name="hall" class="atr-input" onchange="this.form.submit()">
                <option value="0">All halls</option>
                @foreach ($halls as $hall)
                    <option value="{{ $hall->id }}" {{ (int) $hallId === $hall->id ? 'selected' : '' }}>{{ $hall->name }}</option>
                @endforeach
            </select>
            <select name="range" id="atrRangeSelect" class="atr-input atr-range-select" onchange="this.form.submit()">
                <option value="all" {{ !in_array($range, ['today', 'week', 'month'], true) ? 'selected' : '' }}>All</option>
                <option value="today" {{ $range === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ $range === 'week' ? 'selected' : '' }}>Week</option>
                <option value="month" {{ $range === 'month' ? 'selected' : '' }}>Month</option>
            </select>
        </form>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-calendar" style="color:var(--atr-primary);"></i>Event Bookings</h3>
            <span>{{ $events->total() }} record(s) - next code: <b>{{ $nextCode }}</b></span>
        </div>
        @if ($events->isEmpty())
            <div class="atr-empty"><i class="fa-solid fa-folder-open" style="font-size:1.45rem;color:#cbd5e1;display:block;margin-bottom:8px;"></i>No bookings match the current filters.</div>
        @else
            <div class="atr-table-wrap">
                <table class="atr-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Contact / Event</th>
                            <th>Hall</th>
                            <th>Hours</th>
                            <th>Due</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            @php
                                $paid = (float) $event->payments->sum('payment_amount');
                                $tagClass = match ($event->booking_status) {
                                    'confirmed' => 'atr-tag-confirmed',
                                    'completed' => 'atr-tag-completed',
                                    'cancelled' => 'atr-tag-cancelled',
                                    default => 'atr-tag-reserved',
                                };
                            @endphp
                            <tr>
                                <td><strong>{{ $event->event_code }}</strong></td>
                                <td style="white-space:nowrap;">
                                    {{ $event->date_of_event?->format('M d, Y') }}
                                    @if ($event->start_time)
                                        <br><span style="font-size:.78rem;color:var(--atr-muted);">{{ $event->start_time }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $event->name_contact_person }}</strong><br>
                                    <span style="font-size:.78rem;color:var(--atr-muted);">{{ \Illuminate\Support\Str::limit($event->event_details, 45) }}</span>
                                </td>
                                <td>{{ $event->functionHall?->name ?? '-' }}</td>
                                <td>{{ number_format((float) $event->no_of_hours, 2) }}</td>
                                <td><strong>PHP {{ number_format((float) $event->actual_due, 2) }}</strong></td>
                                <td>PHP {{ number_format($paid, 2) }}</td>
                                <td><span class="atr-tag {{ $tagClass }}">{{ ucfirst($event->booking_status) }}</span></td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <div class="atr-actions">
                                        <button type="button" class="atr-icon-btn view js-atr-view" data-event-id="{{ $event->id }}" title="View booking" aria-label="View booking">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button type="button" class="atr-icon-btn edit js-atr-edit" data-event-id="{{ $event->id }}" title="Edit booking" aria-label="Edit booking">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="atr-icon-btn delete js-atr-delete" data-event-id="{{ $event->id }}" title="Delete booking" aria-label="Delete booking">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($events->hasPages())
                <div class="atr-pagination-wrap">
                    <div class="atr-pagination-summary">
                        Showing {{ $events->firstItem() }}-{{ $events->lastItem() }} of {{ $events->total() }} records
                    </div>
                    <nav class="atr-pagination" aria-label="Event bookings pagination">
                        @if ($events->onFirstPage())
                            <span class="atr-page-link is-disabled">Prev</span>
                        @else
                            <a class="atr-page-link" href="{{ $events->previousPageUrl() }}" rel="prev">Prev</a>
                        @endif

                        @php
                            $startPage = max(1, $events->currentPage() - 2);
                            $endPage = min($events->lastPage(), $events->currentPage() + 2);
                        @endphp

                        @if ($startPage > 1)
                            <a class="atr-page-link" href="{{ $events->url(1) }}">1</a>
                            @if ($startPage > 2)
                                <span class="atr-page-link is-disabled">...</span>
                            @endif
                        @endif

                        @for ($page = $startPage; $page <= $endPage; $page++)
                            @if ($page === $events->currentPage())
                                <span class="atr-page-link is-active">{{ $page }}</span>
                            @else
                                <a class="atr-page-link" href="{{ $events->url($page) }}">{{ $page }}</a>
                            @endif
                        @endfor

                        @if ($endPage < $events->lastPage())
                            @if ($endPage < $events->lastPage() - 1)
                                <span class="atr-page-link is-disabled">...</span>
                            @endif
                            <a class="atr-page-link" href="{{ $events->url($events->lastPage()) }}">{{ $events->lastPage() }}</a>
                        @endif

                        @if ($events->hasMorePages())
                            <a class="atr-page-link" href="{{ $events->nextPageUrl() }}" rel="next">Next</a>
                        @else
                            <span class="atr-page-link is-disabled">Next</span>
                        @endif
                    </nav>
                </div>
            @endif
        @endif
    </section>

    <div class="atr-modal-backdrop" id="atrBookingModal" aria-hidden="true">
        <div class="atr-modal atr-modal-wide" role="dialog" aria-modal="true" aria-labelledby="atrBookingModalTitle">
            <div class="atr-modal-head">
                <div>
                    <h3 id="atrBookingModalTitle"><i class="fa-solid fa-calendar-plus" style="color:var(--atr-primary);"></i>New Booking</h3>
                    <p>Create a booking without leaving this page.</p>
                </div>
                <button type="button" class="atr-modal-close" data-atr-close-modal="atrBookingModal" aria-label="Close">&times;</button>
            </div>
            <div class="atr-modal-body">
                @if ($errors->any() && !old('edit_booking_id'))
                    <div class="atr-modal-error">
                        <strong>Please correct the following:</strong>
                        <ul>
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('atrium.bookings.store') }}" class="atr-card atr-modal-form" id="atrBookingModalForm">
                    @csrf
                    <input type="hidden" name="redirect_to" value="bookings">

                    <div class="atr-card-head">
                        <h3><i class="fa-solid fa-calendar-plus" style="color:var(--atr-primary);"></i>Event Details</h3>
                        <span>Fields marked * are required</span>
                    </div>

                    <div class="atr-form-grid">
                        <div class="atr-field">
                            <label>Event Code</label>
                            <input type="text" name="event_code" class="atr-input" value="{{ old('event_code', $nextCode) }}" placeholder="Auto-generated if blank">
                            <span class="atr-help">Leave blank to auto-generate.</span>
                        </div>
                        <div class="atr-field">
                            <label>Date of Event *</label>
                            <input type="date" name="date_of_event" class="atr-input" required value="{{ old('date_of_event') }}">
                        </div>
                        <div class="atr-field">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="atr-input" value="{{ old('start_time') }}">
                        </div>
                        <div class="atr-field">
                            <label>Number of Hours *</label>
                            <input type="number" step="0.25" min="0.5" max="48" name="no_of_hours" class="atr-input" required value="{{ old('no_of_hours', 1) }}">
                        </div>

                        <div class="atr-field full">
                            <label>Event Details *</label>
                            <textarea name="event_details" class="atr-input" required maxlength="500" placeholder="Describe the event (e.g., Wedding Reception, Birthday, Seminar...)">{{ old('event_details') }}</textarea>
                        </div>

                        <div class="atr-field">
                            <label>Name of Contact Person *</label>
                            <input type="text" name="name_contact_person" class="atr-input" required maxlength="160" value="{{ old('name_contact_person') }}">
                        </div>
                        <div class="atr-field">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" class="atr-input" maxlength="60" value="{{ old('contact_number') }}">
                        </div>

                        <div class="atr-field">
                            <label>Function Hall *</label>
                            <select name="atrium_function_hall_id" class="atr-input" required>
                                <option value="">- Choose a hall -</option>
                                @foreach ($halls as $hall)
                                    <option value="{{ $hall->id }}" data-rate="{{ $hall->hourly_rate }}" {{ (int) old('atrium_function_hall_id') === $hall->id ? 'selected' : '' }}>
                                        {{ $hall->name }} ({{ $hall->code }}) - PHP {{ number_format((float) $hall->hourly_rate, 2) }}/hr
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="atr-field">
                            <label>Booking Status</label>
                            <select name="booking_status" class="atr-input">
                                @foreach (['reserved', 'confirmed', 'cancelled'] as $st)
                                    <option value="{{ $st }}" {{ old('booking_status', 'reserved') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="atr-card-head" style="border-top:1px solid var(--atr-border);">
                        <h3><i class="fa-solid fa-peso-sign" style="color:var(--atr-primary);"></i>Rates & Charges</h3>
                        <span>All amounts in PHP</span>
                    </div>

                    <div class="atr-form-grid atr-form-grid--wide">
                        <div class="atr-field">
                            <label>Hall Payment *</label>
                            <input type="number" step="0.01" min="0" name="hall_payment" id="atrCreateHallPayment" class="atr-input" required value="{{ old('hall_payment', 0) }}">
                            <span class="atr-help">Auto-computed from hourly rate x hours; override freely.</span>
                        </div>
                        <div class="atr-field">
                            <label>Miscellaneous Payment</label>
                            <input type="number" step="0.01" min="0" name="miscellaneous_payment" class="atr-input" value="{{ old('miscellaneous_payment', 0) }}">
                        </div>
                        <div class="atr-field">
                            <label>Accommodation Payment</label>
                            <input type="number" step="0.01" min="0" name="accommodation_payment" class="atr-input" value="{{ old('accommodation_payment', 0) }}">
                        </div>
                    </div>

                    <div class="atr-card-head" style="border-top:1px solid var(--atr-border);">
                        <h3><i class="fa-solid fa-boxes-stacked" style="color:var(--atr-primary);"></i>Add-Ons</h3>
                        <button type="button" class="atr-btn-outline" id="atrCreateAddOnAdd"><i class="fa-solid fa-plus"></i>Add Row</button>
                    </div>
                    <div id="atrCreateAddOnList">
                        @foreach ($modalAddOns as $idx => $row)
                            @php
                                $rowDescription = is_array($row) ? (string) ($row['description'] ?? '') : '';
                                $rowAmount = is_array($row) ? (string) ($row['amount'] ?? '') : '';
                            @endphp
                            <div class="atr-addon-row">
                                <input type="text" name="add_ons[{{ $idx }}][description]" class="atr-input" placeholder="Description (optional)" value="{{ old('add_ons.' . $idx . '.description', $rowDescription) }}">
                                <input type="number" step="0.01" min="0" name="add_ons[{{ $idx }}][amount]" class="atr-input" placeholder="Amount" value="{{ old('add_ons.' . $idx . '.amount', $rowAmount) }}">
                                <button type="button" class="atr-btn-danger atr-addon-remove"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        @endforeach
                    </div>

                    <div class="atr-addon-head">
                        <button type="button" class="atr-btn-outline" data-atr-close-modal="atrBookingModal"><i class="fa-solid fa-xmark"></i>Cancel</button>
                        <button type="submit" class="atr-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Create Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="atr-modal-backdrop" id="atrViewBookingModal" aria-hidden="true">
        <div class="atr-modal atr-modal-medium" role="dialog" aria-modal="true" aria-labelledby="atrViewBookingModalTitle">
            <div class="atr-modal-head">
                <div>
                    <h3 id="atrViewBookingModalTitle"><i class="fa-solid fa-eye" style="color:var(--atr-primary);"></i>Booking Details</h3>
                    <p id="atrViewSubtitle">Review full booking information.</p>
                </div>
                <button type="button" class="atr-modal-close" data-atr-close-modal="atrViewBookingModal" aria-label="Close">&times;</button>
            </div>
            <div class="atr-modal-body" style="padding:1rem;">
                <div class="atr-view-grid">
                    <div class="atr-view-item"><span class="atr-view-label">Event Code</span><span class="atr-view-value" id="atrViewCode">-</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Status</span><span class="atr-view-value"><span class="atr-tag atr-tag-reserved" id="atrViewStatusTag">Reserved</span></span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Date</span><span class="atr-view-value" id="atrViewDate">-</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Start Time</span><span class="atr-view-value" id="atrViewTime">-</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Contact Person</span><span class="atr-view-value" id="atrViewContactPerson">-</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Contact Number</span><span class="atr-view-value" id="atrViewContactNumber">-</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Hall</span><span class="atr-view-value" id="atrViewHall">-</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Hours</span><span class="atr-view-value" id="atrViewHours">-</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Total Due</span><span class="atr-view-value" id="atrViewDue">PHP 0.00</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Paid</span><span class="atr-view-value" id="atrViewPaid">PHP 0.00</span></div>
                    <div class="atr-view-item"><span class="atr-view-label">Balance</span><span class="atr-view-value" id="atrViewBalance">PHP 0.00</span></div>
                    <div class="atr-view-item full"><span class="atr-view-label">Event Details</span><span class="atr-view-value" id="atrViewDetails">-</span></div>
                    <div class="atr-view-item full">
                        <span class="atr-view-label">Add-Ons</span>
                        <ul class="atr-view-addon-list" id="atrViewAddonList"></ul>
                        <div class="atr-view-empty" id="atrViewAddonEmpty" hidden>No add-ons recorded.</div>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:1rem;">
                    <button type="button" class="atr-btn-outline" data-atr-close-modal="atrViewBookingModal">Close</button>
                    <button type="button" class="atr-btn-primary" id="atrViewEditBtn"><i class="fa-solid fa-pen"></i>Edit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="atr-modal-backdrop" id="atrEditBookingModal" aria-hidden="true">
        <div class="atr-modal atr-modal-wide" role="dialog" aria-modal="true" aria-labelledby="atrEditBookingModalTitle">
            <div class="atr-modal-head">
                <div>
                    <h3 id="atrEditBookingModalTitle"><i class="fa-solid fa-pen" style="color:var(--atr-primary);"></i>Edit Booking</h3>
                    <p>Update booking information and save instantly.</p>
                </div>
                <button type="button" class="atr-modal-close" data-atr-close-modal="atrEditBookingModal" aria-label="Close">&times;</button>
            </div>
            <div class="atr-modal-body">
                @if ($errors->any() && old('edit_booking_id'))
                    <div class="atr-modal-error">
                        <strong>Please correct the following:</strong>
                        <ul>
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" class="atr-card atr-modal-form" id="atrEditBookingForm" data-action-template="{{ route('atrium.bookings.update', '__ID__') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_to" value="bookings">
                    <input type="hidden" name="edit_booking_id" id="atrEditBookingId" value="">

                    <div class="atr-card-head">
                        <h3><i class="fa-solid fa-calendar-plus" style="color:var(--atr-primary);"></i>Event Details</h3>
                        <span>Fields marked * are required</span>
                    </div>

                    <div class="atr-form-grid">
                        <div class="atr-field">
                            <label>Event Code</label>
                            <input type="text" name="event_code" id="atrEditEventCode" class="atr-input" placeholder="Auto-generated if blank">
                        </div>
                        <div class="atr-field">
                            <label>Date of Event *</label>
                            <input type="date" name="date_of_event" id="atrEditDate" class="atr-input" required>
                        </div>
                        <div class="atr-field">
                            <label>Start Time</label>
                            <input type="time" name="start_time" id="atrEditStartTime" class="atr-input">
                        </div>
                        <div class="atr-field">
                            <label>Number of Hours *</label>
                            <input type="number" step="0.25" min="0.5" max="48" name="no_of_hours" id="atrEditHours" class="atr-input" required>
                        </div>

                        <div class="atr-field full">
                            <label>Event Details *</label>
                            <textarea name="event_details" id="atrEditDetails" class="atr-input" required maxlength="500"></textarea>
                        </div>

                        <div class="atr-field">
                            <label>Name of Contact Person *</label>
                            <input type="text" name="name_contact_person" id="atrEditContactPerson" class="atr-input" required maxlength="160">
                        </div>
                        <div class="atr-field">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" id="atrEditContactNumber" class="atr-input" maxlength="60">
                        </div>

                        <div class="atr-field">
                            <label>Function Hall *</label>
                            <select name="atrium_function_hall_id" id="atrEditHallId" class="atr-input" required>
                                <option value="">- Choose a hall -</option>
                                @foreach ($halls as $hall)
                                    <option value="{{ $hall->id }}" data-rate="{{ $hall->hourly_rate }}">
                                        {{ $hall->name }} ({{ $hall->code }}) - PHP {{ number_format((float) $hall->hourly_rate, 2) }}/hr
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="atr-field">
                            <label>Booking Status</label>
                            <select name="booking_status" id="atrEditStatus" class="atr-input">
                                @foreach (['reserved', 'confirmed', 'cancelled'] as $st)
                                    <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="atr-card-head" style="border-top:1px solid var(--atr-border);">
                        <h3><i class="fa-solid fa-peso-sign" style="color:var(--atr-primary);"></i>Rates & Charges</h3>
                        <span>All amounts in PHP</span>
                    </div>

                    <div class="atr-form-grid atr-form-grid--wide">
                        <div class="atr-field">
                            <label>Hall Payment *</label>
                            <input type="number" step="0.01" min="0" name="hall_payment" id="atrEditHallPayment" class="atr-input" required>
                        </div>
                        <div class="atr-field">
                            <label>Miscellaneous Payment</label>
                            <input type="number" step="0.01" min="0" name="miscellaneous_payment" id="atrEditMisc" class="atr-input">
                        </div>
                        <div class="atr-field">
                            <label>Accommodation Payment</label>
                            <input type="number" step="0.01" min="0" name="accommodation_payment" id="atrEditAccommodation" class="atr-input">
                        </div>
                    </div>

                    <div class="atr-card-head" style="border-top:1px solid var(--atr-border);">
                        <h3><i class="fa-solid fa-boxes-stacked" style="color:var(--atr-primary);"></i>Add-Ons</h3>
                        <button type="button" class="atr-btn-outline" id="atrEditAddOnAdd"><i class="fa-solid fa-plus"></i>Add Row</button>
                    </div>
                    <div id="atrEditAddOnList"></div>

                    <div class="atr-addon-head">
                        <button type="button" class="atr-btn-danger" id="atrEditDeleteBtn"><i class="fa-solid fa-trash"></i>Delete</button>
                        <div style="display:flex;gap:8px;">
                            <button type="button" class="atr-btn-outline" data-atr-close-modal="atrEditBookingModal"><i class="fa-solid fa-xmark"></i>Cancel</button>
                            <button type="submit" class="atr-btn-primary"><i class="fa-solid fa-floppy-disk"></i>Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="atr-modal-backdrop" id="atrDeleteBookingModal" aria-hidden="true">
        <div class="atr-modal atr-modal-small" role="dialog" aria-modal="true" aria-labelledby="atrDeleteBookingModalTitle">
            <div class="atr-modal-head">
                <div>
                    <h3 id="atrDeleteBookingModalTitle"><i class="fa-solid fa-triangle-exclamation" style="color:#b91c1c;"></i>Delete Booking</h3>
                    <p>This action cannot be undone.</p>
                </div>
                <button type="button" class="atr-modal-close" data-atr-close-modal="atrDeleteBookingModal" aria-label="Close">&times;</button>
            </div>
            <div class="atr-modal-body" style="padding:1rem;">
                <p style="margin:0;color:var(--atr-text);font-size:.9rem;">You are about to delete booking <strong id="atrDeleteCode">-</strong>.</p>
                <div class="atr-confirm-box" id="atrDeleteSummary">Please confirm to continue.</div>

                <form method="POST" id="atrDeleteBookingForm" data-action-template="{{ route('atrium.bookings.destroy', '__ID__') }}" style="display:flex;justify-content:flex-end;gap:8px;margin-top:1rem;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="atr-btn-outline" data-atr-close-modal="atrDeleteBookingModal">Cancel</button>
                    <button type="submit" class="atr-btn-danger"><i class="fa-solid fa-trash"></i>Delete Booking</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const bookingMap = @json($eventsMap);
    const oldEditPayload = @json($oldEditPayload);

    const filtersForm = document.getElementById('atrBookingFiltersForm');
    const searchInput = document.getElementById('atrBookingSearchInput');
    const statusToast = document.getElementById('atrStatusToast');

    const createModal = document.getElementById('atrBookingModal');
    const viewModal = document.getElementById('atrViewBookingModal');
    const editModal = document.getElementById('atrEditBookingModal');
    const deleteModal = document.getElementById('atrDeleteBookingModal');

    const createForm = document.getElementById('atrBookingModalForm');
    const createHallSelect = createForm ? createForm.querySelector('select[name=\"atrium_function_hall_id\"]') : null;
    const createHoursInput = createForm ? createForm.querySelector('input[name=\"no_of_hours\"]') : null;
    const createHallPayment = document.getElementById('atrCreateHallPayment');
    const createAddOnList = document.getElementById('atrCreateAddOnList');
    const createAddOnAddBtn = document.getElementById('atrCreateAddOnAdd');

    const editForm = document.getElementById('atrEditBookingForm');
    const editIdInput = document.getElementById('atrEditBookingId');
    const editHallSelect = document.getElementById('atrEditHallId');
    const editHoursInput = document.getElementById('atrEditHours');
    const editHallPayment = document.getElementById('atrEditHallPayment');
    const editAddOnList = document.getElementById('atrEditAddOnList');
    const editAddOnAddBtn = document.getElementById('atrEditAddOnAdd');
    const editDeleteBtn = document.getElementById('atrEditDeleteBtn');

    const deleteForm = document.getElementById('atrDeleteBookingForm');
    const viewEditBtn = document.getElementById('atrViewEditBtn');

    let currentEditId = null;
    let currentViewId = null;

    const formatMoney = (value) => `PHP ${Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const statusClass = (status) => {
        switch ((status || '').toLowerCase()) {
            case 'confirmed': return 'atr-tag-confirmed';
            case 'completed': return 'atr-tag-completed';
            case 'cancelled': return 'atr-tag-cancelled';
            default: return 'atr-tag-reserved';
        }
    };

    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.atr-modal-backdrop.is-open')) {
            document.body.style.overflow = '';
        }
    };

    const recomputeCreateHallPayment = () => {
        if (!createHallSelect || !createHoursInput || !createHallPayment) return;
        const selected = createHallSelect.options[createHallSelect.selectedIndex];
        const rate = Number.parseFloat(selected ? selected.dataset.rate || 0 : 0) || 0;
        const hours = Number.parseFloat(createHoursInput.value || 0) || 0;
        if (rate > 0 && hours > 0) {
            createHallPayment.value = (rate * hours).toFixed(2);
        }
    };

    const recomputeEditHallPayment = () => {
        if (!editHallSelect || !editHoursInput || !editHallPayment) return;
        const selected = editHallSelect.options[editHallSelect.selectedIndex];
        const rate = Number.parseFloat(selected ? selected.dataset.rate || 0 : 0) || 0;
        const hours = Number.parseFloat(editHoursInput.value || 0) || 0;
        if (rate > 0 && hours > 0) {
            editHallPayment.value = (rate * hours).toFixed(2);
        }
    };

    const renderEditAddOns = (rows) => {
        if (!editAddOnList) return;
        const safeRows = Array.isArray(rows) && rows.length > 0 ? rows : [{ description: '', amount: '' }];
        editAddOnList.innerHTML = '';

        safeRows.forEach((row, idx) => {
            const wrap = document.createElement('div');
            wrap.className = 'atr-addon-row';
            wrap.innerHTML = `
                <input type=\"text\" name=\"add_ons[${idx}][description]\" class=\"atr-input\" placeholder=\"Description (optional)\" value=\"${String(row.description ?? '').replace(/\"/g, '&quot;')}\">
                <input type=\"number\" step=\"0.01\" min=\"0\" name=\"add_ons[${idx}][amount]\" class=\"atr-input\" placeholder=\"Amount\" value=\"${String(row.amount ?? '').replace(/\"/g, '&quot;')}\">
                <button type=\"button\" class=\"atr-btn-danger atr-addon-remove\"><i class=\"fa-solid fa-trash\"></i></button>
            `;
            editAddOnList.appendChild(wrap);
        });
    };

    const openViewModal = (eventId) => {
        const data = bookingMap[String(eventId)];
        if (!data) return;
        currentViewId = String(eventId);

        document.getElementById('atrViewCode').textContent = data.event_code || '-';
        document.getElementById('atrViewDate').textContent = data.date_label || '-';
        document.getElementById('atrViewTime').textContent = data.start_time || '-';
        document.getElementById('atrViewContactPerson').textContent = data.name_contact_person || '-';
        document.getElementById('atrViewContactNumber').textContent = data.contact_number || '-';
        document.getElementById('atrViewHall').textContent = data.hall_name || '-';
        document.getElementById('atrViewHours').textContent = Number(data.no_of_hours || 0).toFixed(2);
        document.getElementById('atrViewDue').textContent = formatMoney(data.actual_due);
        document.getElementById('atrViewPaid').textContent = formatMoney(data.paid);
        document.getElementById('atrViewBalance').textContent = formatMoney(data.balance);
        document.getElementById('atrViewDetails').textContent = data.event_details || '-';

        const tag = document.getElementById('atrViewStatusTag');
        if (tag) {
            tag.className = `atr-tag ${statusClass(data.booking_status)}`;
            tag.textContent = (data.booking_status || 'reserved').toString().replace(/^./, (char) => char.toUpperCase());
        }

        const addonList = document.getElementById('atrViewAddonList');
        const addonEmpty = document.getElementById('atrViewAddonEmpty');
        if (addonList) {
            addonList.innerHTML = '';
            const addons = Array.isArray(data.add_ons) ? data.add_ons : [];
            if (addons.length === 0) {
                if (addonEmpty) addonEmpty.hidden = false;
            } else {
                if (addonEmpty) addonEmpty.hidden = true;
                addons.forEach((addon) => {
                    const li = document.createElement('li');
                    const desc = addon.description || 'Add-on';
                    li.textContent = `${desc} - ${formatMoney(addon.amount)}`;
                    addonList.appendChild(li);
                });
            }
        }

        openModal(viewModal);
    };

    const setEditFormValue = (selector, value) => {
        const input = document.querySelector(selector);
        if (input) input.value = value ?? '';
    };

    const openEditModal = (eventId, payloadOverride = null) => {
        const data = payloadOverride || bookingMap[String(eventId)];
        if (!data || !editForm) return;

        currentEditId = String(eventId);
        const actionTemplate = editForm.dataset.actionTemplate || '';
        editForm.action = actionTemplate.replace('__ID__', currentEditId);

        if (editIdInput) editIdInput.value = currentEditId;

        setEditFormValue('#atrEditEventCode', data.event_code || '');
        setEditFormValue('#atrEditDate', data.date_of_event || '');
        setEditFormValue('#atrEditStartTime', data.start_time || '');
        setEditFormValue('#atrEditHours', data.no_of_hours ?? 1);
        setEditFormValue('#atrEditDetails', data.event_details || '');
        setEditFormValue('#atrEditContactPerson', data.name_contact_person || '');
        setEditFormValue('#atrEditContactNumber', data.contact_number || '');
        setEditFormValue('#atrEditHallId', data.hall_id || '');
        setEditFormValue('#atrEditStatus', data.booking_status || 'reserved');
        setEditFormValue('#atrEditHallPayment', data.hall_payment ?? 0);
        setEditFormValue('#atrEditMisc', data.miscellaneous_payment ?? 0);
        setEditFormValue('#atrEditAccommodation', data.accommodation_payment ?? 0);

        renderEditAddOns(data.add_ons);

        const title = document.getElementById('atrEditBookingModalTitle');
        if (title) {
            title.innerHTML = `<i class=\"fa-solid fa-pen\" style=\"color:var(--atr-primary);\"></i>Edit Booking ${data.event_code || ''}`;
        }

        openModal(editModal);
    };

    const openDeleteModal = (eventId) => {
        const data = bookingMap[String(eventId)];
        if (!data || !deleteForm) return;

        const actionTemplate = deleteForm.dataset.actionTemplate || '';
        deleteForm.action = actionTemplate.replace('__ID__', String(eventId));

        const codeEl = document.getElementById('atrDeleteCode');
        const summaryEl = document.getElementById('atrDeleteSummary');

        if (codeEl) codeEl.textContent = data.event_code || '-';
        if (summaryEl) {
            summaryEl.textContent = `Contact: ${data.name_contact_person || '-'} | Event: ${data.date_label || '-'}`;
        }

        openModal(deleteModal);
    };

    if (filtersForm && searchInput) {
        let searchTimer = null;
        const submitFilters = () => filtersForm.submit();

        searchInput.addEventListener('input', () => {
            if (searchTimer) {
                window.clearTimeout(searchTimer);
            }
            searchTimer = window.setTimeout(submitFilters, 380);
        });

        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                if (searchTimer) {
                    window.clearTimeout(searchTimer);
                }
                submitFilters();
            }
        });
    }

    if (statusToast) {
        window.setTimeout(() => {
            statusToast.classList.add('is-hiding');
            window.setTimeout(() => statusToast.remove(), 240);
        }, 3200);
    }

    const openCreateBtn = document.getElementById('atrOpenBookingModal');
    if (openCreateBtn) {
        openCreateBtn.addEventListener('click', () => openModal(createModal));
    }

    document.querySelectorAll('[data-atr-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-atr-close-modal');
            const targetModal = targetId ? document.getElementById(targetId) : null;
            closeModal(targetModal);
        });
    });

    document.querySelectorAll('.atr-modal-backdrop').forEach((backdrop) => {
        backdrop.addEventListener('click', (event) => {
            if (event.target === backdrop) {
                closeModal(backdrop);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        const opened = Array.from(document.querySelectorAll('.atr-modal-backdrop.is-open'));
        if (opened.length === 0) return;
        closeModal(opened[opened.length - 1]);
    });

    if (createHallSelect) createHallSelect.addEventListener('change', recomputeCreateHallPayment);
    if (createHoursInput) createHoursInput.addEventListener('input', recomputeCreateHallPayment);

    if (createAddOnAddBtn && createAddOnList) {
        createAddOnAddBtn.addEventListener('click', () => {
            const idx = createAddOnList.querySelectorAll('.atr-addon-row').length;
            const row = document.createElement('div');
            row.className = 'atr-addon-row';
            row.innerHTML = `
                <input type=\"text\" name=\"add_ons[${idx}][description]\" class=\"atr-input\" placeholder=\"Description (optional)\">
                <input type=\"number\" step=\"0.01\" min=\"0\" name=\"add_ons[${idx}][amount]\" class=\"atr-input\" placeholder=\"Amount\">
                <button type=\"button\" class=\"atr-btn-danger atr-addon-remove\"><i class=\"fa-solid fa-trash\"></i></button>
            `;
            createAddOnList.appendChild(row);
        });

        createAddOnList.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('.atr-addon-remove');
            if (!removeBtn) return;
            const row = removeBtn.closest('.atr-addon-row');
            const totalRows = createAddOnList.querySelectorAll('.atr-addon-row').length;
            if (totalRows > 1 && row) {
                row.remove();
                return;
            }
            if (row) {
                row.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
            }
        });
    }

    if (editHallSelect) editHallSelect.addEventListener('change', recomputeEditHallPayment);
    if (editHoursInput) editHoursInput.addEventListener('input', recomputeEditHallPayment);

    if (editAddOnAddBtn && editAddOnList) {
        editAddOnAddBtn.addEventListener('click', () => {
            const idx = editAddOnList.querySelectorAll('.atr-addon-row').length;
            const row = document.createElement('div');
            row.className = 'atr-addon-row';
            row.innerHTML = `
                <input type=\"text\" name=\"add_ons[${idx}][description]\" class=\"atr-input\" placeholder=\"Description (optional)\">
                <input type=\"number\" step=\"0.01\" min=\"0\" name=\"add_ons[${idx}][amount]\" class=\"atr-input\" placeholder=\"Amount\">
                <button type=\"button\" class=\"atr-btn-danger atr-addon-remove\"><i class=\"fa-solid fa-trash\"></i></button>
            `;
            editAddOnList.appendChild(row);
        });

        editAddOnList.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('.atr-addon-remove');
            if (!removeBtn) return;
            const row = removeBtn.closest('.atr-addon-row');
            const totalRows = editAddOnList.querySelectorAll('.atr-addon-row').length;
            if (totalRows > 1 && row) {
                row.remove();
                return;
            }
            if (row) {
                row.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
            }
        });
    }

    document.querySelectorAll('.js-atr-view').forEach((btn) => {
        btn.addEventListener('click', () => {
            const eventId = btn.getAttribute('data-event-id');
            if (!eventId) return;
            openViewModal(eventId);
        });
    });

    document.querySelectorAll('.js-atr-edit').forEach((btn) => {
        btn.addEventListener('click', () => {
            const eventId = btn.getAttribute('data-event-id');
            if (!eventId) return;
            openEditModal(eventId);
        });
    });

    document.querySelectorAll('.js-atr-delete').forEach((btn) => {
        btn.addEventListener('click', () => {
            const eventId = btn.getAttribute('data-event-id');
            if (!eventId) return;
            openDeleteModal(eventId);
        });
    });

    if (viewEditBtn) {
        viewEditBtn.addEventListener('click', () => {
            if (!currentViewId) return;
            closeModal(viewModal);
            openEditModal(currentViewId);
        });
    }

    if (editDeleteBtn) {
        editDeleteBtn.addEventListener('click', () => {
            if (!currentEditId) return;
            openDeleteModal(currentEditId);
        });
    }

    const url = new URL(window.location.href);
    const shouldAutoOpenFromQuery = url.searchParams.get('new_booking') === '1';
    const hasErrors = @json($errors->any());
    const oldEditId = @json(old('edit_booking_id'));

    if (shouldAutoOpenFromQuery) {
        openModal(createModal);
        url.searchParams.delete('new_booking');
        window.history.replaceState({}, '', url.toString());
    }

    if (hasErrors) {
        if (oldEditId) {
            const payload = oldEditPayload || bookingMap[String(oldEditId)] || null;
            openEditModal(oldEditId, payload);
        } else {
            openModal(createModal);
        }
    }
})();
</script>
@endsection
