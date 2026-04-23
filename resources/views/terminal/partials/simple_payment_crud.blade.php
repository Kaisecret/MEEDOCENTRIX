@include('terminal.partials.terminal_shared_styles')
@php
    $historyMode = (bool) ($isHistoryMode ?? false);
@endphp

<div class="tm" data-server-rendered-page="{{ $serverRenderedPage }}" data-page-title="{{ $pageTitle }}">
    <section class="tm-hero">
        <div>
            <h2>{{ $pageTitle }}</h2>
            <p>
                {{ $historyMode
                    ? 'Read-only payment history with filters for Today, Week, Month, All, and Custom range.'
                    : 'Manage pending transactions. Mark as paid to move records to Payment History.' }}
            </p>
        </div>
        @if (! $historyMode)
            <div class="tm-action-row">
                <button type="button" class="tm-btn-primary" id="openAddPaymentModal">
                    <i class="fas fa-plus"></i> Add Payment
                </button>
            </div>
        @endif
    </section>

    @if (session('status'))
        <div class="tm-flash">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="tm-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="tm-error">{{ $errors->first() }}</div>
    @endif

    <section class="tm-card">
        <div class="tm-card-head">
            <h3>
                <i class="fas fa-list"></i>
                {{ $historyMode ? 'Payment History' : 'Pending Transactions' }}
            </h3>
            <span>{{ number_format($payments->total()) }} total records</span>
        </div>
        <form method="GET" action="{{ request()->url() }}" class="tm-filter-bar">
            <select name="period" class="tm-input">
                <option value="today" {{ ($period ?? 'all') === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ ($period ?? 'all') === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ ($period ?? 'all') === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="all" {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                <option value="custom" {{ ($period ?? 'all') === 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
            <input type="date" name="date_from" class="tm-input" value="{{ $dateFrom ?? '' }}">
            <input type="date" name="date_to" class="tm-input" value="{{ $dateTo ?? '' }}">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search payer or remarks..." class="tm-input tm-input--grow">
            <button type="submit" class="tm-btn-outline"><i class="fas fa-search"></i> Search</button>
            <a href="{{ request()->url() }}" class="tm-btn-outline"><i class="fas fa-rotate"></i> Reset</a>
        </form>
        <div class="tm-table-wrap">
            <table class="tm-table">
                <thead>
                    <tr>
                        <th>Payer Name</th>
                        <th>Total Payment</th>
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
                            <td>{{ $payment->payer_name }}</td>
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
                                            data-name="{{ $payment->payer_name }}"
                                            data-total="{{ number_format((float) $payment->total_payment, 2, '.', '') }}"
                                            data-date="{{ optional($payment->payment_date)->format('Y-m-d\TH:i') }}"
                                            data-remarks="{{ $payment->remarks }}"
                                        >
                                            <i class="fas fa-pen"></i> Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="tm-btn-success js-mark-paid"
                                            data-id="{{ $payment->id }}"
                                            data-name="{{ $payment->payer_name }}"
                                            data-total="{{ number_format((float) $payment->total_payment, 2) }}"
                                        >
                                            <i class="fas fa-check-circle"></i> Mark as Paid
                                        </button>
                                        <button
                                            type="button"
                                            class="tm-btn-danger js-delete-payment"
                                            data-id="{{ $payment->id }}"
                                            data-name="{{ $payment->payer_name }}"
                                        >
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $historyMode ? 6 : 6 }}" class="tm-empty">
                                {{ $historyMode
                                    ? 'No paid records found for this filter.'
                                    : 'No pending transactions found. Add a payment first.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="tm-card-body">
            {{ $payments->links() }}
        </div>
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
                        <label for="add_payer_name">Name</label>
                        <input id="add_payer_name" name="payer_name" class="tm-input" value="{{ old('form_context') === 'add' ? old('payer_name') : '' }}" required>
                    </div>
                    <div class="tm-field">
                        <label for="add_total_payment">Total Payment</label>
                        <input id="add_total_payment" type="number" min="0.01" step="0.01" name="total_payment" class="tm-input" value="{{ old('form_context') === 'add' ? old('total_payment') : '' }}" required>
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
                        <label for="edit_payer_name">Name</label>
                        <input id="edit_payer_name" name="payer_name" class="tm-input" value="{{ old('payer_name', '') }}" required>
                    </div>
                    <div class="tm-field">
                        <label for="edit_total_payment">Total Payment</label>
                        <input id="edit_total_payment" type="number" min="0.01" step="0.01" name="total_payment" class="tm-input" value="{{ old('total_payment', '') }}" required>
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

    <div id="markPaidSheet" class="tm-sheet-wrap" style="display:none;">
        <div class="tm-sheet-card">
            <h4><i class="fas fa-check-circle"></i> Confirm Mark as Paid</h4>
            <p id="markPaidSheetText">Are you sure you want to mark this transaction as paid?</p>
            <div class="tm-sheet-meta">
                <span id="markPaidSheetName"></span>
                <span id="markPaidSheetAmount"></span>
            </div>
            <div class="tm-form-actions" style="border-top:0;padding-top:0;position:static;">
                <button type="button" class="tm-btn-outline" id="markPaidCancelBtn"><i class="fas fa-times"></i> Cancel</button>
                <button type="button" class="tm-btn-primary tm-btn-primary-strong" id="markPaidConfirmBtn">
                    <i class="fas fa-check"></i> Yes, Mark Paid
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
    .tm-sheet-meta { display: flex; gap: 8px; flex-wrap: wrap; }
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
        let markPaidTargetId = '';

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

                document.getElementById('edit_payer_name').value = button.getAttribute('data-name') || '';
                document.getElementById('edit_total_payment').value = button.getAttribute('data-total') || '';
                document.getElementById('edit_payment_date').value = button.getAttribute('data-date') || '';
                document.getElementById('edit_remarks').value = button.getAttribute('data-remarks') || '';
                openModal(editModal);
            });
        });

        document.querySelectorAll('.js-delete-payment').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!deleteForm) return;
                const name = button.getAttribute('data-name') || 'this record';
                if (!window.confirm('Delete transaction for ' + name + '?')) return;

                const id = String(button.getAttribute('data-id') || '');
                const routeTemplate = String(deleteForm.dataset.routeTemplate || '');
                deleteForm.action = routeTemplate.replace('__ID__', id);
                deleteForm.submit();
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

