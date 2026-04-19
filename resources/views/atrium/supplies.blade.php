@extends('layouts.app')

@section('content')
@include('atrium.partials.atrium_shared_styles')

<div class="atr" data-server-rendered-page="atrium_supplies" data-page-title="Atrium Supplies">
    <section class="atr-hero">
        <div>
            <h2><i class="fa-solid fa-boxes-stacked" style="margin-right:8px;opacity:.88;"></i>Supplies Management</h2>
            <p>Submit new supplies requests and update existing orders tied to atrium events.</p>
        </div>
        <div class="atr-hero-meta">
            <span style="font-size:.83rem;color:rgba(255,255,255,.84);font-weight:600;">Total requests: <b>{{ number_format($summary['total']) }}</b></span>
        </div>
    </section>

    @if (session('status'))
        <div class="atr-flash">{{ session('status') }}</div>
    @endif

    <section class="atr-kpi-grid">
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Pending</span><span class="atr-kpi-icon amber"><i class="fa-solid fa-hourglass-half"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['pending']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Approved</span><span class="atr-kpi-icon blue"><i class="fa-solid fa-circle-check"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['approved']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Fulfilled</span><span class="atr-kpi-icon green"><i class="fa-solid fa-box-open"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['fulfilled']) }}</div>
        </article>
        <article class="atr-kpi">
            <div class="atr-kpi-head"><span class="atr-kpi-title">Rejected</span><span class="atr-kpi-icon red"><i class="fa-solid fa-ban"></i></span></div>
            <div class="atr-kpi-value">{{ number_format($summary['rejected']) }}</div>
        </article>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-filter" style="color:var(--atr-primary);"></i>Filters</h3>
            <a class="atr-btn-primary" href="{{ route('atrium.supplies.create') }}"><i class="fa-solid fa-plus"></i>New Request</a>
        </div>
        <form method="GET" action="{{ route('atrium.supplies') }}" class="atr-filter-bar">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search supplies, event, contact..." class="atr-input atr-input--grow">
            <select name="status" class="atr-input" onchange="this.form.submit()">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="fulfilled" {{ $status === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button class="atr-btn-outline" type="submit"><i class="fa-solid fa-magnifying-glass"></i>Search</button>
        </form>
    </section>

    <section class="atr-card">
        <div class="atr-card-head">
            <h3><i class="fa-solid fa-list" style="color:var(--atr-primary);"></i>Supplies Requests</h3>
            <span>{{ $orders->total() }} record(s)</span>
        </div>
        @if ($orders->isEmpty())
            <div class="atr-empty">No supplies requests found.</div>
        @else
            <div class="atr-table-wrap">
                <table class="atr-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Time Needed</th>
                            <th>Requested Supplies</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $o)
                            @php $tag = 'atr-tag-' . $o->request_status; @endphp
                            <tr>
                                <td>
                                    <strong>{{ $o->event?->event_code }}</strong><br>
                                    <span style="font-size:.78rem;color:var(--atr-muted);">{{ $o->event?->name_contact_person }} — {{ optional($o->event?->date_of_event)->format('M d, Y') }}</span>
                                </td>
                                <td style="white-space:nowrap;">{{ $o->time_needed ?? '—' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($o->requested_supplies, 100) }}</td>
                                <td>{{ $o->requestedBy?->name ?? '—' }}</td>
                                <td><span class="atr-tag {{ $tag }}">{{ ucfirst($o->request_status) }}</span></td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a class="atr-link" href="{{ route('atrium.supplies.edit', $o) }}">Edit</a>
                                    @if ($o->request_status === 'pending')
                                        <span style="color:#cbd5e1;">|</span>
                                        <form method="POST" action="{{ route('atrium.supplies.approve', $o) }}" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="atr-link" style="background:none;border:none;cursor:pointer;">Approve</button>
                                        </form>
                                        <span style="color:#cbd5e1;">|</span>
                                        <form method="POST" action="{{ route('atrium.supplies.reject', $o) }}" style="display:inline;" onsubmit="return confirm('Reject this request?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="atr-link" style="background:none;border:none;cursor:pointer;color:var(--atr-red);">Reject</button>
                                        </form>
                                    @elseif ($o->request_status === 'approved')
                                        <span style="color:#cbd5e1;">|</span>
                                        <form method="POST" action="{{ route('atrium.supplies.fulfill', $o) }}" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="atr-link" style="background:none;border:none;cursor:pointer;color:var(--atr-green);">Fulfill</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: .8rem 1rem; border-top:1px solid var(--atr-border);">{{ $orders->links() }}</div>
        @endif
    </section>
</div>
@endsection
