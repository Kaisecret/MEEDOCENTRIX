@extends('layouts.app')

@section('content')
@php
    /** @var array<string, array<string, string>> $departments */
    /** @var array<string, mixed> $filters */
    /** @var array<string, string> $statusOptions */
    /** @var \Illuminate\Pagination\LengthAwarePaginator $transactions */
    /** @var array<string, mixed> $summary */

    $startEntry = $transactions->total() > 0 ? (($transactions->currentPage() - 1) * $transactions->perPage()) + 1 : 0;
    $endEntry = $transactions->total() > 0 ? min($transactions->currentPage() * $transactions->perPage(), $transactions->total()) : 0;
    $money = static fn (float $amount): string => 'PHP ' . number_format($amount, 2);
    $exportQuery = [
        'q' => $filters['q'],
        'department' => $filters['department'],
        'status' => $filters['status'],
        'from' => $filters['from_input'],
        'to' => $filters['to_input'],
    ];
@endphp

<div class="admin-ledger" data-server-rendered-page="transactions" data-page-title="All Transactions">
    @if (session('status'))
        <div class="ledger-alert ledger-alert-success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="ledger-alert ledger-alert-error">
            <i class="fas fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <section class="ledger-header">
        <div>
            <h2>Master Transaction Ledger</h2>
            <p>Unified view of payment and collection records from Fishport, Market, Cemetery, Terminal, and Atrium.</p>
        </div>
        <div class="ledger-header-meta">
            <span>{{ $summary['count'] }} entries</span>
            <strong>{{ $money((float) $summary['amount']) }}</strong>
        </div>
    </section>

    <section class="ledger-panel">
        <form method="GET" action="{{ route('admin.transactions') }}" class="ledger-filters">
            <label class="ledger-search">
                <span>Search</span>
                <i class="fas fa-search"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search">
            </label>

            <label>
                <span>Department</span>
                <select name="department">
                    <option value="all" {{ $filters['department'] === 'all' ? 'selected' : '' }}>All Departments</option>
                    @foreach ($departments as $code => $config)
                        <option value="{{ $code }}" {{ $filters['department'] === $code ? 'selected' : '' }}>{{ $config['name'] }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Status</span>
                <select name="status">
                    @foreach ($statusOptions as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" {{ $filters['status'] === $statusKey ? 'selected' : '' }}>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>From</span>
                <input type="date" name="from" value="{{ $filters['from_input'] }}">
            </label>

            <label>
                <span>To</span>
                <input type="date" name="to" value="{{ $filters['to_input'] }}">
            </label>

            <div class="ledger-filter-actions">
                <button type="submit" class="ledger-btn ledger-btn-primary">
                    <i class="fas fa-filter"></i>
                    Apply
                </button>
                <a href="{{ route('admin.transactions') }}" class="ledger-btn ledger-btn-light">Reset</a>
                <a href="{{ route('admin.transactions.csv', $exportQuery) }}" class="ledger-btn ledger-btn-export">
                    <i class="fa-solid fa-file-excel"></i>
                    Export CSV
                </a>
            </div>
        </form>

        <div class="ledger-department-strip">
            @foreach ($summary['by_department'] as $item)
                <article class="ledger-department-card">
                    <div class="ledger-department-title">
                        <i class="{{ $item['icon'] }}" style="color: {{ $item['color'] }}"></i>
                        <span>{{ $item['name'] }}</span>
                    </div>
                    <strong>{{ number_format((int) $item['count']) }}</strong>
                    <small>{{ $money((float) $item['amount']) }}</small>
                </article>
            @endforeach
        </div>

        <div class="ledger-table-wrap">
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Department</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Person / Payer</th>
                        <th class="is-right">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $row)
                        @php
                            $statusClass = match ($row['status_key']) {
                                'paid' => 'is-paid',
                                'partial' => 'is-partial',
                                'cancelled' => 'is-cancelled',
                                default => 'is-pending',
                            };
                        @endphp
                        <tr>
                            <td class="is-nowrap">{{ $row['occurred_at']->format('M d, Y h:i A') }}</td>
                            <td>
                                <span class="ledger-dept-chip" style="--dept-color: {{ $row['department_color'] }}">
                                    <i class="{{ $row['department_icon'] }}"></i>
                                    {{ $row['department_name'] }}
                                </span>
                            </td>
                            <td><span class="ledger-code">{{ $row['reference'] }}</span></td>
                            <td>{{ $row['source'] }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td>{{ $row['person'] }}</td>
                            <td class="is-right">{{ number_format((float) $row['amount'], 2) }}</td>
                            <td>
                                <span class="ledger-status {{ $statusClass }}">{{ $row['status_label'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="ledger-empty">
                                <i class="fas fa-inbox"></i>
                                <strong>No transactions found</strong>
                                <span>Try widening the date range or adjusting the filters.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ledger-footer">
            <span>Showing {{ $startEntry }} to {{ $endEntry }} of {{ $transactions->total() }} entries</span>
            <div class="ledger-pagination">
                @if ($transactions->onFirstPage())
                    <span class="is-disabled">Previous</span>
                @else
                    <a href="{{ $transactions->previousPageUrl() }}">Previous</a>
                @endif

                <span class="ledger-page-indicator">Page {{ $transactions->currentPage() }} of {{ max($transactions->lastPage(), 1) }}</span>

                @if ($transactions->hasMorePages())
                    <a href="{{ $transactions->nextPageUrl() }}">Next</a>
                @else
                    <span class="is-disabled">Next</span>
                @endif
            </div>
        </div>
    </section>
</div>

<style>
    #contentArea {
        padding-top: 5px;
    }

    .admin-ledger {
        --ink: #0b1a2c;
        --muted: #6b7d93;
        --line: #e3eaf3;
        --line-strong: #cfdae6;
        --soft: #f6f9fd;
        --panel: #ffffff;
        --primary: #155e8f;
        --primary-dark: #124f78;
        --good: #0f8a5f;
        --warn: #c46a17;
        --danger: #b1342f;
        max-width: 1480px;
        margin: 0 auto;
        padding: 5px 0 16px;
        color: var(--ink);
        display: grid;
        gap: 10px;
    }

    .ledger-alert {
        display: flex;
        gap: 10px;
        align-items: center;
        border-radius: 10px;
        padding: 10px 12px;
        border: 1px solid var(--line);
        box-shadow: 0 1px 2px rgba(15, 35, 60, 0.04);
        font-weight: 700;
    }

    .ledger-alert-success {
        color: var(--good);
        border-color: #b5e4cc;
        background: linear-gradient(180deg, #f0fbf5, #e6f7ee);
    }

    .ledger-alert-error {
        color: var(--danger);
        border-color: #f3c0bf;
        background: linear-gradient(180deg, #fff5f4, #ffeceb);
    }

    .ledger-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 12px;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff, #f3f8fd);
        padding: 10px;
    }

    .ledger-header h2 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 850;
    }

    .ledger-header p {
        margin: 4px 0 0;
        color: var(--muted);
    }

    .ledger-header-meta {
        text-align: right;
        color: var(--muted);
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
    }

    .ledger-header-meta strong {
        display: block;
        color: var(--ink);
        font-size: 1.05rem;
        text-transform: none;
        letter-spacing: 0;
    }

    .ledger-panel {
        border: 1px solid var(--line);
        border-radius: 14px;
        background: var(--panel);
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 35, 60, 0.04);
    }

    .ledger-filters {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) repeat(4, minmax(0, 1fr)) auto;
        gap: 10px;
        padding: 10px;
        border-bottom: 1px solid var(--line);
        background: #ffffff;
    }

    .ledger-filters > * {
        min-width: 0;
    }

    .ledger-filters label {
        display: grid;
        gap: 5px;
    }

    .ledger-filters label span {
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .ledger-search {
        position: relative;
        display: grid;
        gap: 5px;
        width: 100%;
        max-width: none;
    }

    .ledger-search i {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: #8aa0b6;
        pointer-events: none;
    }

    .ledger-search input {
        min-height: 38px;
        width: 100%;
        padding-left: 36px !important;
    }

    .ledger-filters input,
    .ledger-filters select {
        min-height: 38px;
        border: 1px solid var(--line);
        border-radius: 9px;
        padding: 8px 10px;
        background: var(--soft);
        font-size: 0.88rem;
    }

    .ledger-filters input:focus,
    .ledger-filters select:focus {
        outline: none;
        border-color: var(--primary);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(21, 94, 143, 0.14);
    }

    .ledger-filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .ledger-btn {
        min-height: 38px;
        padding: 0 14px;
        border-radius: 9px;
        font-weight: 800;
        font-size: 0.85rem;
        text-decoration: none;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
    }

    .ledger-btn-primary {
        color: #ffffff;
        background: var(--primary);
        border-color: var(--primary);
    }

    .ledger-btn-primary:hover {
        background: var(--primary-dark);
    }

    .ledger-btn-light {
        color: var(--ink);
        background: #ffffff;
        border-color: var(--line-strong);
    }

    .ledger-btn-light:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .ledger-btn-export {
        color: var(--primary);
        background: #ffffff;
        border-color: #cbd9e8;
        box-shadow: 0 1px 2px rgba(15, 35, 60, 0.05);
    }

    .ledger-btn-export:hover {
        background: #f0f7fd;
        border-color: var(--primary);
        color: var(--primary);
    }

    .ledger-department-strip {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        padding: 10px;
        border-bottom: 1px solid var(--line);
        background: #fbfdff;
    }

    .ledger-department-card {
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #ffffff;
        padding: 9px 10px;
        display: grid;
        gap: 3px;
    }

    .ledger-department-title {
        display: flex;
        gap: 6px;
        align-items: center;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .ledger-department-card strong {
        font-size: 1rem;
    }

    .ledger-department-card small {
        color: var(--muted);
        font-size: 0.76rem;
    }

    .ledger-table-wrap {
        overflow-x: auto;
        padding: 0 10px 10px;
    }

    .ledger-table {
        width: 100%;
        min-width: 1140px;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid var(--line);
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }

    .ledger-table th {
        text-align: left;
        background: linear-gradient(180deg, #f7fafd 0%, #eef3f9 100%);
        color: #4a5e76;
        padding: 8px 10px;
        border-bottom: 1px solid var(--line);
        font-size: 0.7rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }

    .ledger-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #eef2f7;
        color: #2a3e57;
        font-size: 0.86rem;
        vertical-align: middle;
    }

    .ledger-table tbody tr:hover td {
        background: #f9fcff;
    }

    .ledger-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .ledger-code {
        display: inline-flex;
        border-radius: 6px;
        padding: 3px 7px;
        background: rgba(21, 94, 143, 0.1);
        color: #124f78;
        font-size: 0.74rem;
        font-weight: 800;
        font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    }

    .ledger-dept-chip {
        --dept-color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 3px 9px;
        font-size: 0.74rem;
        font-weight: 800;
        background: color-mix(in srgb, var(--dept-color) 14%, white);
        color: var(--dept-color);
    }

    .ledger-status {
        display: inline-flex;
        border-radius: 999px;
        padding: 3px 9px;
        font-size: 0.72rem;
        font-weight: 800;
        border: 1px solid var(--line);
        color: #42526b;
        background: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .ledger-status.is-paid {
        border-color: #b5e4cc;
        color: var(--good);
        background: #e6f7ee;
    }

    .ledger-status.is-pending {
        border-color: #f1d2ad;
        color: var(--warn);
        background: #fff4e8;
    }

    .ledger-status.is-partial {
        border-color: #d8d3f8;
        color: #6d52d8;
        background: #f3f0ff;
    }

    .ledger-status.is-cancelled {
        border-color: #f3c0bf;
        color: var(--danger);
        background: #ffeceb;
    }

    .ledger-empty {
        text-align: center;
        padding: 24px 12px !important;
    }

    .ledger-empty i {
        font-size: 1.4rem;
        color: #8aa0b6;
    }

    .ledger-empty strong,
    .ledger-empty span {
        display: block;
        margin-top: 5px;
    }

    .ledger-empty span {
        color: var(--muted);
    }

    .is-nowrap {
        white-space: nowrap;
    }

    .is-right {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .ledger-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-top: 1px solid var(--line);
        background: #ffffff;
        color: var(--muted);
        font-size: 0.82rem;
    }

    .ledger-pagination {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .ledger-pagination a,
    .ledger-pagination span {
        border: 1px solid var(--line-strong);
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #2a3e57;
        text-decoration: none;
        background: #ffffff;
    }

    .ledger-pagination a:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .ledger-pagination .is-disabled {
        opacity: 0.55;
    }

    .ledger-pagination .ledger-page-indicator {
        border-color: transparent;
        background: transparent;
        padding: 0;
    }

    @media (max-width: 1280px) {
        .ledger-filters {
            grid-template-columns: 1fr 1fr 1fr 1fr;
        }

        .ledger-search {
            max-width: 100%;
        }

        .ledger-filter-actions {
            grid-column: span 4;
            justify-content: flex-end;
        }

        .ledger-department-strip {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 860px) {
        .ledger-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .ledger-header-meta {
            text-align: left;
        }

        .ledger-filters {
            grid-template-columns: 1fr;
        }

        .ledger-filter-actions {
            grid-column: auto;
            justify-content: flex-start;
        }

        .ledger-department-strip {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ledger-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endsection
