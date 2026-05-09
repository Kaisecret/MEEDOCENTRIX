@include('terminal.partials.terminal_shared_styles')
@php
    $historyMode = (bool) ($isHistoryMode ?? false);
    $routeFareConfig = $routeFareConfig ?? [];
    $routeGroups = $routeGroups ?? [];
@endphp

<div class="tm tm-transactions-compact" data-server-rendered-page="{{ $serverRenderedPage }}" data-page-title="{{ $pageTitle }}">
    @include('terminal.partials.toast_stack')

    <section class="tm-card">
        <div class="tm-card-head">
            <div class="tm-card-head-main">
                @if (! $historyMode)
                    <h3>
                        <i class="fas fa-list"></i>
                        Pending Transactions
                    </h3>
                @endif
            </div>
            <div class="tm-card-head-side">
                <span>{{ number_format($payments->total()) }} total records</span>
                @if (! $historyMode)
                    <button type="button" class="tm-btn-primary" id="openAddPaymentModal">
                        <i class="fas fa-plus"></i> Add Payment
                    </button>
                @endif
            </div>
        </div>
        <form method="GET" action="{{ request()->url() }}" class="tm-filter-bar js-tm-auto-filter-form">
            <select name="period" class="tm-input js-tm-auto-filter">
                <option value="today" {{ ($period ?? 'all') === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ ($period ?? 'all') === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ ($period ?? 'all') === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="all" {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                <option value="custom" {{ ($period ?? 'all') === 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
            <input type="date" name="date_from" class="tm-input js-tm-auto-filter" value="{{ $dateFrom ?? '' }}">
            <input type="date" name="date_to" class="tm-input js-tm-auto-filter" value="{{ $dateTo ?? '' }}">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search ticket, route, remarks..." class="tm-input tm-filter-search js-tm-auto-filter-search">
        </form>
        <div class="tm-table-wrap">
            <table class="tm-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Vehicle</th>
                        <th>Route / Operator</th>
                        <th>Terminal Fee</th>
                        <th>{{ $historyMode ? 'Paid At' : 'Recorded Date' }}</th>
                        <th>Remarks</th>
                        <th>Saved By</th>
                        @if ($historyMode)
                            <th>Paid By</th>
                        @else
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->ticket_number ?: '-' }}</td>
                            <td>{{ $payment->vehicle_kind ?: '-' }}</td>
                            <td>{{ $payment->route_name ?: '-' }}</td>
                            <td>PHP {{ number_format((float) $payment->total_payment, 2) }}</td>
                            <td>
                                {{ $historyMode
                                    ? (optional($payment->paid_at)->format('m/d/Y h:i A') ?: '-')
                                    : (optional($payment->payment_date)->format('m/d/Y h:i A') ?: '-') }}
                            </td>
                            <td>{{ $payment->remarks ?: '-' }}</td>
                            <td>{{ $payment->recordedBy?->name ?: '-' }}</td>
                            @if ($historyMode)
                                <td>{{ $payment->paidBy?->name ?: '-' }}</td>
                            @else
                                <td>
                                    <div class="tm-action-row">
                                        <button
                                            type="button"
                                            class="tm-btn-outline js-edit-payment"
                                            data-id="{{ $payment->id }}"
                                            data-ticket-number="{{ $payment->ticket_number }}"
                                            data-route-code="{{ $payment->route_code }}"
                                            data-date="{{ optional($payment->payment_date)->format('Y-m-d\TH:i') }}"
                                            data-remarks="{{ $payment->remarks }}"
                                        >
                                            <i class="fas fa-pen"></i> Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="tm-btn-success js-mark-paid"
                                            data-id="{{ $payment->id }}"
                                            data-name="{{ $payment->ticket_number }}"
                                            data-total="{{ number_format((float) $payment->total_payment, 2) }}"
                                        >
                                            <i class="fas fa-check-circle"></i> Mark as Paid
                                        </button>
                                        <button
                                            type="button"
                                            class="tm-btn-danger js-delete-payment"
                                            data-id="{{ $payment->id }}"
                                            data-name="{{ $payment->ticket_number }}"
                                        >
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="tm-empty">
                                {{ $historyMode
                                    ? 'No paid records found for this filter.'
                                    : 'No pending transactions found. Add a payment first.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="tm-card-body tm-pagination-wrap">
                <span class="tm-pagination-summary">
                    Showing {{ number_format((int) $payments->firstItem()) }}-{{ number_format((int) $payments->lastItem()) }}
                    of {{ number_format((int) $payments->total()) }}
                </span>
                <nav class="tm-pagination" aria-label="Transactions pagination">
                    @if ($payments->onFirstPage())
                        <span class="tm-page-btn is-disabled">Prev</span>
                    @else
                        <a href="{{ $payments->previousPageUrl() }}" class="tm-page-btn">Prev</a>
                    @endif

                    @php
                        $startPage = max(1, $payments->currentPage() - 1);
                        $endPage = min($payments->lastPage(), $payments->currentPage() + 1);
                    @endphp

                    @if ($startPage > 1)
                        <a href="{{ $payments->url(1) }}" class="tm-page-btn">1</a>
                        @if ($startPage > 2)
                            <span class="tm-page-dots">...</span>
                        @endif
                    @endif

                    @for ($page = $startPage; $page <= $endPage; $page++)
                        @if ($page === $payments->currentPage())
                            <span class="tm-page-btn is-active">{{ $page }}</span>
                        @else
                            <a href="{{ $payments->url($page) }}" class="tm-page-btn">{{ $page }}</a>
                        @endif
                    @endfor

                    @if ($endPage < $payments->lastPage())
                        @if ($endPage < $payments->lastPage() - 1)
                            <span class="tm-page-dots">...</span>
                        @endif
                        <a href="{{ $payments->url($payments->lastPage()) }}" class="tm-page-btn">{{ $payments->lastPage() }}</a>
                    @endif

                    @if ($payments->hasMorePages())
                        <a href="{{ $payments->nextPageUrl() }}" class="tm-page-btn">Next</a>
                    @else
                        <span class="tm-page-btn is-disabled">Next</span>
                    @endif
                </nav>
            </div>
        @endif
    </section>
</div>

@if (! $historyMode)
    <div id="addPaymentModal" class="tm-modal-wrap" style="display:none;">
        <div class="tm-modal-card">
            <div class="tm-card-head">
                <h3><i class="fas fa-plus"></i> Add Payment</h3>
                <button type="button" class="tm-btn-outline js-close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="tm-card-body">
                <form method="POST" action="{{ route('terminal.simple_payments.store') }}" class="tm-form-grid">
                    @csrf
                    <input type="hidden" name="form_context" value="add">
                    <div class="tm-field">
                        <label for="add_ticket_number">Ticket Number</label>
                        <input
                            id="add_ticket_number"
                            name="ticket_number"
                            class="tm-input"
                            value="{{ old('form_context') === 'add' ? old('ticket_number') : '' }}"
                            inputmode="numeric"
                            pattern="\d{6}"
                            maxlength="6"
                            minlength="6"
                            placeholder="6-digit ticket number"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)"
                            required
                        >
                    </div>
                    <div class="tm-field">
                        <label for="add_route_code">Route / Operator</label>
                        <select id="add_route_code" name="route_code" class="tm-input js-route-select" data-target-fare="add_total_payment" required>
                            <option value="">Select route or bus operator</option>
                            @if (count($routeGroups) === 0)
                                <option value="" disabled>No active route/operator rates configured in Admin > Rates</option>
                            @else
                                @foreach ($routeGroups as $groupLabel => $groupCodes)
                                    <optgroup label="{{ $groupLabel }}">
                                        @foreach ($groupCodes as $routeCode)
                                            @if (isset($routeFareConfig[$routeCode]))
                                                <option value="{{ $routeCode }}" {{ old('form_context') === 'add' && old('route_code') === $routeCode ? 'selected' : '' }}>
                                                    {{ $routeFareConfig[$routeCode]['label'] }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="tm-field">
                        <label for="add_total_payment">Terminal Fee (Auto)</label>
                        <input id="add_total_payment" type="text" class="tm-input" value="{{ old('form_context') === 'add' && old('route_code') && isset($routeFareConfig[old('route_code')]) ? number_format((float) $routeFareConfig[old('route_code')]['fare'], 2) : '0.00' }}" readonly>
                    </div>
                    <div class="tm-field">
                        <label for="add_payment_date">Recorded Date</label>
                        <input id="add_payment_date" type="datetime-local" name="payment_date" class="tm-input" value="{{ old('form_context') === 'add' ? old('payment_date', now()->format('Y-m-d\TH:i')) : now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="tm-field full">
                        <label for="add_remarks">Remarks</label>
                        <textarea id="add_remarks" name="remarks" class="tm-input">{{ old('form_context') === 'add' ? old('remarks') : '' }}</textarea>
                    </div>
                    <div class="tm-field full tm-form-actions">
                        <button type="button" class="tm-btn-outline js-close-modal"><i class="fas fa-times"></i> Cancel</button>
                        <button type="submit" class="tm-btn-primary tm-btn-primary-strong"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editPaymentModal" class="tm-modal-wrap" style="display:none;">
        <div class="tm-modal-card">
            <div class="tm-card-head">
                <h3><i class="fas fa-pen"></i> Edit Payment</h3>
                <button type="button" class="tm-btn-outline js-close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="tm-card-body">
                <form method="POST" id="editPaymentForm" data-route-template="{{ route('terminal.simple_payments.update', ['quickPayment' => '__ID__']) }}" class="tm-form-grid">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" id="edit_form_context" value="{{ old('form_context', '') }}">
                    <div class="tm-field">
                        <label for="edit_ticket_number">Ticket Number</label>
                        <input
                            id="edit_ticket_number"
                            name="ticket_number"
                            class="tm-input"
                            value="{{ old('ticket_number', '') }}"
                            inputmode="numeric"
                            pattern="\d{6}"
                            maxlength="6"
                            minlength="6"
                            placeholder="6-digit ticket number"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)"
                            required
                        >
                    </div>
                    <div class="tm-field">
                        <label for="edit_route_code">Route / Operator</label>
                        <select id="edit_route_code" name="route_code" class="tm-input js-route-select" data-target-fare="edit_total_payment" required>
                            <option value="">Select route or bus operator</option>
                            @if (count($routeGroups) === 0)
                                <option value="" disabled>No active route/operator rates configured in Admin > Rates</option>
                            @else
                                @foreach ($routeGroups as $groupLabel => $groupCodes)
                                    <optgroup label="{{ $groupLabel }}">
                                        @foreach ($groupCodes as $routeCode)
                                            @if (isset($routeFareConfig[$routeCode]))
                                                <option value="{{ $routeCode }}" {{ old('route_code', '') === $routeCode ? 'selected' : '' }}>
                                                    {{ $routeFareConfig[$routeCode]['label'] }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="tm-field">
                        <label for="edit_total_payment">Terminal Fee (Auto)</label>
                        <input id="edit_total_payment" type="text" class="tm-input" value="{{ old('route_code') && isset($routeFareConfig[old('route_code')]) ? number_format((float) $routeFareConfig[old('route_code')]['fare'], 2) : '0.00' }}" readonly>
                    </div>
                    <div class="tm-field">
                        <label for="edit_payment_date">Recorded Date</label>
                        <input id="edit_payment_date" type="datetime-local" name="payment_date" class="tm-input" value="{{ old('payment_date', '') }}">
                    </div>
                    <div class="tm-field full">
                        <label for="edit_remarks">Remarks</label>
                        <textarea id="edit_remarks" name="remarks" class="tm-input">{{ old('remarks', '') }}</textarea>
                    </div>
                    <div class="tm-field full tm-form-actions">
                        <button type="button" class="tm-btn-outline js-close-modal"><i class="fas fa-times"></i> Cancel</button>
                        <button type="submit" class="tm-btn-primary tm-btn-primary-strong"><i class="fas fa-save"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="markPaidSheet" class="tm-sheet-wrap tm-sheet-wrap-center" style="display:none;">
        <div class="tm-sheet-card">
            <h4><i class="fas fa-check-circle"></i> Confirm Mark as Paid</h4>
            <p id="markPaidSheetText">Are you sure you want to mark this transaction as paid?</p>
            <div class="tm-sheet-meta">
                <span id="markPaidSheetName"></span>
                <span id="markPaidSheetAmount"></span>
            </div>
            <div class="tm-form-actions tm-sheet-actions" style="border-top:0;padding-top:0;position:static;">
                <button type="button" class="tm-btn-outline" id="markPaidCancelBtn"><i class="fas fa-times"></i> Cancel</button>
                <button type="button" class="tm-btn-primary tm-btn-primary-strong" id="markPaidConfirmBtn">
                    <i class="fas fa-check"></i> Yes, Mark Paid
                </button>
            </div>
        </div>
    </div>

    <div id="deletePaymentSheet" class="tm-sheet-wrap tm-sheet-wrap-center" style="display:none;">
        <div class="tm-sheet-card">
            <h4><i class="fas fa-trash"></i> Confirm Delete</h4>
            <p id="deletePaymentSheetText">Delete this transaction record?</p>
            <div class="tm-sheet-meta">
                <span id="deletePaymentSheetName"></span>
            </div>
            <div class="tm-form-actions tm-sheet-actions" style="border-top:0;padding-top:0;position:static;">
                <button type="button" class="tm-btn-outline" id="deletePaymentCancelBtn"><i class="fas fa-times"></i> Cancel</button>
                <button type="button" class="tm-btn-danger" id="deletePaymentConfirmBtn">
                    <i class="fas fa-trash"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>

    <form method="POST" id="deletePaymentForm" data-route-template="{{ route('terminal.simple_payments.destroy', ['quickPayment' => '__ID__']) }}" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <form method="POST" id="markPaidForm" data-route-template="{{ route('terminal.simple_payments.mark_paid', ['quickPayment' => '__ID__']) }}" style="display:none;">
        @csrf
        @method('PATCH')
    </form>
@endif

<style>
    .tm-transactions-compact .tm-hero {
        gap: 10px;
        padding: 10px 0;
    }
    .tm-transactions-compact .tm-hero h2 {
        margin: 0;
    }
    .tm-transactions-compact {
        gap: 10px;
    }
    .tm-transactions-compact .tm-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .tm-transactions-compact .tm-card-head-main {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .tm-transactions-compact .tm-card-head-side {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-left: auto;
        flex-wrap: wrap;
    }
    .tm-transactions-compact .tm-card {
        margin-top: 0;
    }
    .tm-transactions-compact .tm-filter-bar {
        display: grid;
        grid-template-columns: 180px 170px 170px minmax(240px, 1fr);
        gap: 10px;
        align-items: end;
        padding: 10px;
    }
    .tm-transactions-compact .tm-filter-search {
        flex: 1 1 560px;
        min-width: 280px;
        max-width: none;
        width: 100%;
    }
    .tm-transactions-compact .tm-table {
        min-width: 980px;
    }
    .tm-transactions-compact .tm-card-head,
    .tm-transactions-compact .tm-card-body,
    .tm-transactions-compact .tm-kpi,
    .tm-transactions-compact .tm-field {
        gap: 10px;
    }
    .tm-pagination-wrap {
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: .8rem;
        padding-bottom: .8rem;
    }
    .tm-pagination-summary {
        font-size: .8rem;
        color: #64748b;
        font-weight: 600;
    }
    .tm-pagination {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .tm-page-btn {
        min-width: 36px;
        height: 34px;
        padding: 0 .7rem;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .83rem;
        font-weight: 700;
        text-decoration: none;
    }
    .tm-page-btn:hover {
        border-color: #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .tm-page-btn.is-active {
        border-color: #0f5fa8;
        background: #0f5fa8;
        color: #fff;
    }
    .tm-page-btn.is-disabled {
        opacity: .5;
        pointer-events: none;
        background: #f8fafc;
        color: #64748b;
    }
    .tm-page-dots {
        color: #64748b;
        font-weight: 700;
        padding: 0 .1rem;
    }

    .tm-btn-success {
        border-radius: 9px;
        padding: .55rem .95rem;
        font-size: .85rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #86efac;
        background: #f0fdf4;
        color: #166534;
    }
    .tm-btn-success:hover { background: #dcfce7; }

    .tm-modal-wrap {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        z-index: 1400;
        padding: 1rem;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .tm-modal-wrap.is-open { display: flex !important; }
    .tm-modal-card {
        width: min(860px, 100%);
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .tm-modal-card .tm-card-head {
        background: #fff;
        flex-shrink: 0;
    }
    .tm-modal-card .tm-card-body {
        overflow-y: auto;
        padding: 1rem 1.2rem 1.2rem;
    }
    .tm-form-actions {
        display: flex;
        gap: 10px;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
        position: sticky;
        bottom: -1px;
        background: #fff;
    }
    .tm-btn-primary-strong {
        background: #0f5fa8 !important;
        border-color: #0f5fa8 !important;
        color: #fff !important;
        min-width: 170px;
        justify-content: center;
        box-shadow: 0 6px 16px rgba(15,95,168,.22);
    }
    .tm-btn-primary-strong:hover {
        background: #0a4880 !important;
        border-color: #0a4880 !important;
    }

    .tm-sheet-wrap {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .45);
        z-index: 1450;
        display: none;
        align-items: flex-end;
        justify-content: center;
        padding: 0 1rem 1rem;
    }
    .tm-sheet-wrap-center {
        align-items: center;
        padding: 1rem;
    }
    .tm-sheet-wrap.is-open { display: flex !important; }
    .tm-sheet-card {
        width: min(760px, 100%);
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 20px 40px rgba(15, 23, 42, .24);
        padding: 1rem 1.2rem 1.1rem;
        display: grid;
        gap: 10px;
        text-align: center;
        justify-items: center;
        animation: tmSheetIn .18s ease-out;
    }
    .tm-sheet-card h4 {
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1.08rem;
        color: #0f172a;
    }
    .tm-sheet-card p { margin: 0; color: #475569; }
    .tm-sheet-meta { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
    .tm-sheet-actions {
        justify-content: center;
        gap: 12px;
        width: 100%;
    }
    #deletePaymentSheet .tm-sheet-card h4 { color: #991b1b; }
    #deletePaymentSheet .tm-sheet-card {
        width: min(620px, 100%);
        border: 1px solid #fecaca;
        box-shadow: 0 22px 48px rgba(127, 29, 29, .2);
    }
    #deletePaymentSheet .tm-sheet-card p {
        max-width: 520px;
    }
    #deletePaymentSheet .tm-sheet-meta span {
        border-color: #fecaca;
        background: #fef2f2;
        color: #991b1b;
        font-size: .95rem;
        padding: .35rem .9rem;
    }
    .tm-sheet-meta span {
        padding: .25rem .6rem;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
        font-size: .78rem;
    }
    @keyframes tmSheetIn {
        from { transform: translateY(16px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 640px) {
        .tm-transactions-compact .tm-filter-bar {
            grid-template-columns: 1fr;
        }
        .tm-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }
        .tm-form-actions .tm-btn-primary,
        .tm-form-actions .tm-btn-outline,
        .tm-form-actions .tm-btn-success {
            justify-content: center;
        }
    }
</style>

<script>
    (function () {
        const filterForm = document.querySelector('.js-tm-auto-filter-form');
        if (!filterForm) return;

        let submitTimer = null;
        let submitting = false;

        function submitFilters() {
            if (submitting) return;
            submitting = true;
            filterForm.submit();
        }

        filterForm.querySelectorAll('.js-tm-auto-filter').forEach(function (input) {
            input.addEventListener('change', function () {
                submitFilters();
            });
        });

        const searchInput = filterForm.querySelector('.js-tm-auto-filter-search');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                if (submitTimer) {
                    window.clearTimeout(submitTimer);
                }
                submitTimer = window.setTimeout(function () {
                    submitFilters();
                }, 420);
            });
        }
    })();
</script>

@if (! $historyMode)
<script>
    (function () {
        const addModal = document.getElementById('addPaymentModal');
        const editModal = document.getElementById('editPaymentModal');
        const openAddButton = document.getElementById('openAddPaymentModal');
        const editForm = document.getElementById('editPaymentForm');
        const deleteForm = document.getElementById('deletePaymentForm');
        const markPaidForm = document.getElementById('markPaidForm');
        const markPaidSheet = document.getElementById('markPaidSheet');
        const markPaidConfirmBtn = document.getElementById('markPaidConfirmBtn');
        const markPaidCancelBtn = document.getElementById('markPaidCancelBtn');
        const deletePaymentSheet = document.getElementById('deletePaymentSheet');
        const deletePaymentConfirmBtn = document.getElementById('deletePaymentConfirmBtn');
        const deletePaymentCancelBtn = document.getElementById('deletePaymentCancelBtn');
        const routeFareConfig = @json($routeFareConfig);
        let markPaidTargetId = '';
        let deleteTargetId = '';

        function syncFareInput(routeSelect) {
            if (!routeSelect) return;
            const targetId = String(routeSelect.getAttribute('data-target-fare') || '');
            const fareInput = document.getElementById(targetId);
            if (!fareInput) return;

            const code = String(routeSelect.value || '');
            const selectedConfig = routeFareConfig[code] || null;
            const fareValue = selectedConfig ? Number(selectedConfig.fare || 0) : 0;
            fareInput.value = fareValue.toFixed(2);
        }

        function openModal(modal) {
            if (!modal) return;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modal) {
            if (!modal) return;
            modal.classList.remove('is-open');
            if (!document.querySelector('.tm-modal-wrap.is-open') && !document.querySelector('.tm-sheet-wrap.is-open')) {
                document.body.style.overflow = '';
            }
        }

        function openSheet(sheet) {
            if (!sheet) return;
            sheet.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeSheet(sheet) {
            if (!sheet) return;
            sheet.classList.remove('is-open');
            if (!document.querySelector('.tm-modal-wrap.is-open') && !document.querySelector('.tm-sheet-wrap.is-open')) {
                document.body.style.overflow = '';
            }
        }

        if (openAddButton) {
            openAddButton.addEventListener('click', function () {
                openModal(addModal);
            });
        }

        document.querySelectorAll('.js-close-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(button.closest('.tm-modal-wrap'));
            });
        });

        document.querySelectorAll('.tm-modal-wrap').forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        if (markPaidSheet) {
            markPaidSheet.addEventListener('click', function (event) {
                if (event.target === markPaidSheet) {
                    closeSheet(markPaidSheet);
                }
            });
        }

        if (markPaidCancelBtn) {
            markPaidCancelBtn.addEventListener('click', function () {
                closeSheet(markPaidSheet);
            });
        }

        if (deletePaymentSheet) {
            deletePaymentSheet.addEventListener('click', function (event) {
                if (event.target === deletePaymentSheet) {
                    closeSheet(deletePaymentSheet);
                }
            });
        }

        if (deletePaymentCancelBtn) {
            deletePaymentCancelBtn.addEventListener('click', function () {
                closeSheet(deletePaymentSheet);
            });
        }

        if (deletePaymentConfirmBtn) {
            deletePaymentConfirmBtn.addEventListener('click', function () {
                if (!deleteForm || deleteTargetId === '') return;
                const routeTemplate = String(deleteForm.dataset.routeTemplate || '');
                deleteForm.action = routeTemplate.replace('__ID__', deleteTargetId);
                deleteForm.submit();
            });
        }

        if (markPaidConfirmBtn) {
            markPaidConfirmBtn.addEventListener('click', function () {
                if (!markPaidForm || markPaidTargetId === '') return;
                const routeTemplate = String(markPaidForm.dataset.routeTemplate || '');
                markPaidForm.action = routeTemplate.replace('__ID__', markPaidTargetId);
                markPaidForm.submit();
            });
        }

        document.querySelectorAll('.js-edit-payment').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!editForm) return;
                const id = String(button.getAttribute('data-id') || '');
                const routeTemplate = String(editForm.dataset.routeTemplate || '');
                editForm.action = routeTemplate.replace('__ID__', id);

                const formContextInput = document.getElementById('edit_form_context');
                if (formContextInput) {
                    formContextInput.value = 'edit-' + id;
                }

                document.getElementById('edit_ticket_number').value = button.getAttribute('data-ticket-number') || '';
                document.getElementById('edit_route_code').value = button.getAttribute('data-route-code') || '';
                syncFareInput(document.getElementById('edit_route_code'));
                document.getElementById('edit_payment_date').value = button.getAttribute('data-date') || '';
                document.getElementById('edit_remarks').value = button.getAttribute('data-remarks') || '';
                openModal(editModal);
            });
        });

        document.querySelectorAll('.js-route-select').forEach(function (select) {
            select.addEventListener('change', function () {
                syncFareInput(select);
            });
            syncFareInput(select);
        });

        document.querySelectorAll('.js-delete-payment').forEach(function (button) {
            button.addEventListener('click', function () {
                const name = button.getAttribute('data-name') || 'this record';
                deleteTargetId = String(button.getAttribute('data-id') || '');
                const sheetName = document.getElementById('deletePaymentSheetName');
                const sheetText = document.getElementById('deletePaymentSheetText');
                if (sheetName) sheetName.textContent = name;
                if (sheetText) sheetText.textContent = 'Delete transaction for ' + name + '? This action cannot be undone.';
                openSheet(deletePaymentSheet);
            });
        });

        document.querySelectorAll('.js-mark-paid').forEach(function (button) {
            button.addEventListener('click', function () {
                markPaidTargetId = String(button.getAttribute('data-id') || '');
                const name = button.getAttribute('data-name') || '-';
                const total = button.getAttribute('data-total') || '0.00';
                const sheetName = document.getElementById('markPaidSheetName');
                const sheetAmount = document.getElementById('markPaidSheetAmount');
                const sheetText = document.getElementById('markPaidSheetText');

                if (sheetName) sheetName.textContent = name;
                if (sheetAmount) sheetAmount.textContent = 'PHP ' + total;
                if (sheetText) sheetText.textContent = 'Confirm and move this transaction to Payment History?';
                openSheet(markPaidSheet);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal(addModal);
                closeModal(editModal);
                closeSheet(markPaidSheet);
                closeSheet(deletePaymentSheet);
            }
        });

        const oldFormContext = @json(old('form_context', ''));
        if (oldFormContext === 'add') {
            openModal(addModal);
        } else if (oldFormContext.startsWith('edit-')) {
            const editId = oldFormContext.replace('edit-', '');
            if (editForm) {
                const routeTemplate = String(editForm.dataset.routeTemplate || '');
                editForm.action = routeTemplate.replace('__ID__', editId);
                const formContextInput = document.getElementById('edit_form_context');
                if (formContextInput) {
                    formContextInput.value = oldFormContext;
                }
            }
            openModal(editModal);
        }
    })();
</script>
@endif
