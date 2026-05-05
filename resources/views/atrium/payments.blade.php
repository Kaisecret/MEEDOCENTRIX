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

<div class="atr" data-server-rendered-page="atrium_payments" data-page-title="Atrium Payments">
    @if (session('status'))
        <div class="atr-flash">{{ session('status') }}</div>
    @endif

    <section class="atr-kpi-grid">
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Total Collected</span><span class="atr-kpi-icon green"><i class="fa-solid fa-peso-sign"></i></span></div>
            <div class="atr-kpi-value" style="font-size:1.2rem;">PHP {{ number_format($summary['total_collected'], 2) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Paid in Full</span><span class="atr-kpi-icon green"><i class="fa-solid fa-circle-check"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['paid_count']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Partial</span><span class="atr-kpi-icon amber"><i class="fa-solid fa-hourglass-half"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['partial_count']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Unpaid Events</span><span class="atr-kpi-icon red"><i class="fa-solid fa-file-invoice"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['unpaid_count']) }}</div>
        </article>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-filter" style="color:var(--atr-primary);"></i>Filters</h3>
            <a class="atr-btn-primary" href="{{ route('atrium.payments.create') }}"><i class="fa-solid fa-plus"></i>Record Payment</a>
        </div>
        <form method="GET" action="{{ route('atrium.payments') }}" class="atr-filter-bar" id="atrPaymentFiltersForm">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search OR #, event code, contact..." class="atr-input atr-input--grow" id="atrPaymentSearchInput" autocomplete="off">
            <select name="status" class="atr-input" onchange="this.form.submit()">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            </select>
            <select name="range" id="atrPaymentRangeSelect" class="atr-input atr-range-select" onchange="this.form.submit()">
                <option value="all" {{ !in_array($range, ['today', 'week', 'month'], true) ? 'selected' : '' }}>All</option>
                <option value="today" {{ $range === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ $range === 'week' ? 'selected' : '' }}>Week</option>
                <option value="month" {{ $range === 'month' ? 'selected' : '' }}>Month</option>
            </select>
        </form>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-list" style="color:var(--atr-primary);"></i>Payment Records</h3>
            <span>{{ $payments->total() }} record(s)</span>
        </div>
        @if ($payments->isEmpty())
            <div class="atr-empty">No payment records.</div>
        @else
            <div class="atr-table-wrap">
                <table class="atr-table">
                    <thead>
                        <tr>
                            <th>OR #</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Total Collected</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $p)
                            @php
                                $tag = 'atr-tag-' . $p->payment_status;
                                $event = $p->event;
                                $totalPaid = $event ? (float) $event->payments->sum('payment_amount') : (float) $p->payment_amount;
                                $balance = $event ? max(0.0, (float) $event->actual_due - $totalPaid) : 0.0;
                                $isBookingCancelled = $event && $event->booking_status === 'cancelled';
                            @endphp
                            <tr>
                                <td><strong>{{ $p->or_number }}</strong></td>
                                <td>
                                    <strong>{{ $event?->event_code ?? '—' }}</strong><br>
                                    <span style="font-size:.78rem;color:var(--atr-muted);">{{ $event?->name_contact_person }}</span>
                                    @if ($isBookingCancelled)
                                        <br><span class="atr-tag atr-tag-cancelled" style="margin-top:4px;">Cancelled Booking</span>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;">{{ optional($p->date_of_payment)->format('M d, Y') }}</td>
                                <td><b>PHP {{ number_format((float) $p->payment_amount, 2) }}</b></td>
                                <td>PHP {{ number_format($totalPaid, 2) }}</td>
                                <td>PHP {{ number_format($balance, 2) }}</td>
                                <td>
                                    @if ($isBookingCancelled)
                                        <span class="atr-tag atr-tag-cancelled">Cancelled</span>
                                    @else
                                        <span class="atr-tag {{ $tag }}">{{ ucfirst($p->payment_status) }}</span>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;color:var(--atr-muted);">{{ \Illuminate\Support\Str::limit($p->remarks, 40) }}</td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a class="atr-link" href="{{ route('atrium.payments.show', $p) }}">View</a>
                                    <span style="color:#cbd5e1;">|</span>
                                    <a class="atr-link" href="{{ route('atrium.payments.edit', $p) }}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="atr-pagination-wrap">
                    <div class="atr-pagination-summary">
                        Showing {{ $payments->firstItem() }}-{{ $payments->lastItem() }} of {{ $payments->total() }} records
                    </div>
                    <nav class="atr-pagination" aria-label="Payment records pagination">
                        @if ($payments->onFirstPage())
                            <span class="atr-page-link is-disabled">Prev</span>
                        @else
                            <a class="atr-page-link" href="{{ $payments->previousPageUrl() }}" rel="prev">Prev</a>
                        @endif

                        @php
                            $startPage = max(1, $payments->currentPage() - 2);
                            $endPage = min($payments->lastPage(), $payments->currentPage() + 2);
                        @endphp

                        @if ($startPage > 1)
                            <a class="atr-page-link" href="{{ $payments->url(1) }}">1</a>
                            @if ($startPage > 2)
                                <span class="atr-page-link is-disabled">...</span>
                            @endif
                        @endif

                        @for ($page = $startPage; $page <= $endPage; $page++)
                            @if ($page === $payments->currentPage())
                                <span class="atr-page-link is-active">{{ $page }}</span>
                            @else
                                <a class="atr-page-link" href="{{ $payments->url($page) }}">{{ $page }}</a>
                            @endif
                        @endfor

                        @if ($endPage < $payments->lastPage())
                            @if ($endPage < $payments->lastPage() - 1)
                                <span class="atr-page-link is-disabled">...</span>
                            @endif
                            <a class="atr-page-link" href="{{ $payments->url($payments->lastPage()) }}">{{ $payments->lastPage() }}</a>
                        @endif

                        @if ($payments->hasMorePages())
                            <a class="atr-page-link" href="{{ $payments->nextPageUrl() }}" rel="next">Next</a>
                        @else
                            <span class="atr-page-link is-disabled">Next</span>
                        @endif
                    </nav>
                </div>
            @endif
        @endif
    </section>
</div>

<script>
(function () {
    const filtersForm = document.getElementById('atrPaymentFiltersForm');
    const searchInput = document.getElementById('atrPaymentSearchInput');

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
})();
</script>
@endsection
