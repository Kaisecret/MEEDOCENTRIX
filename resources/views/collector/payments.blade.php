@extends('layouts.app')

@section('content')
    @php
        /** @var \App\Models\CollectorDepartmentAssignment|null $assignment */
        /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\CollectionDispatchItem> $items */
    @endphp

    <style>
        :root {
            --cpm-primary: #0f5fa8;
            --cpm-primary-dk: #0a4880;
            --cpm-teal: #0891b2;
            --cpm-green: #059669;
            --cpm-red: #dc2626;
            --cpm-amber: #d97706;
            --cpm-border: #e2e8f0;
            --cpm-soft: #f8fafc;
            --cpm-text: #334155;
            --cpm-muted: #64748b;
            --cpm-head: #0f172a;
        }

        .cpm-page {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            gap: 18px;
            padding-bottom: 2rem;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--cpm-text);
        }

        /* ── Alerts ── */
        .cpm-alert {
            border-radius: 10px;
            padding: .8rem 1rem;
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: .9rem;
            font-weight: 600;
        }

        .cpm-alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .cpm-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* ── Hero Banner ── */
        .cpm-hero {
            border-radius: 14px;
            padding: 1.35rem 1.6rem;
            color: #fff;
            background: linear-gradient(135deg, #0a3d6b 0%, var(--cpm-primary) 55%, #1a7fd4 100%);
            box-shadow: 0 4px 14px rgba(10, 63, 168, .22);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }

        .cpm-hero-left h1 {
            margin: 0 0 5px;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -.02em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cpm-hero-left p {
            margin: 0;
            font-size: .9rem;
            opacity: .88;
        }

        .cpm-dept-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 999px;
            padding: .3rem .85rem;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .02em;
        }

        /* ── Tabs ── */
        .cpm-tabs {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 5px;
            background: #f1f5f9;
            border: 1px solid var(--cpm-border);
            border-radius: 12px;
            padding: 5px;
        }

        .cpm-tab {
            text-decoration: none;
            color: var(--cpm-muted);
            font-weight: 700;
            border-radius: 9px;
            padding: .5rem .95rem;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: .88rem;
            transition: all .18s;
        }

        .cpm-tab span {
            min-width: 22px;
            height: 20px;
            border-radius: 999px;
            background: #e2e8f0;
            color: var(--cpm-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .71rem;
            font-weight: 800;
        }

        .cpm-tab.active {
            background: #fff;
            color: var(--cpm-primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .cpm-tab.active span {
            background: #dbeafe;
            color: var(--cpm-primary);
        }

        .cpm-tab:hover:not(.active) {
            background: #fff;
            color: var(--cpm-head);
        }

        /* ── Table Card ── */
        .cpm-card {
            border: 1px solid var(--cpm-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
            overflow: hidden;
        }

        .cpm-table-wrap {
            overflow-x: auto;
        }

        .cpm-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cpm-table thead th {
            background: #eef5fb;
            color: #103250;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: .73rem;
            font-weight: 700;
            text-align: left;
            padding: .9rem 1rem;
            border-bottom: 1px solid var(--cpm-border);
            white-space: nowrap;
        }

        .cpm-table tbody td {
            padding: .9rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: .875rem;
            color: var(--cpm-text);
            vertical-align: middle;
        }

        .cpm-table tbody tr:last-child td {
            border-bottom: none;
        }

        .cpm-table tbody tr {
            transition: background .12s;
        }

        .cpm-table tbody tr:hover td {
            background: #f8fafd;
        }

        /* ── Log / Payment identifiers ── */
        .cpm-log-no {
            font-family: 'Courier New', monospace;
            font-size: .82rem;
            font-weight: 700;
            color: var(--cpm-head);
        }

        .cpm-pay-no {
            font-family: 'Courier New', monospace;
            font-size: .78rem;
            color: var(--cpm-muted);
        }

        /* ── Status Pills ── */
        .cpm-pill {
            border-radius: 999px;
            padding: .26rem .7rem;
            font-size: .71rem;
            font-weight: 700;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .cpm-pill-success {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }

        .cpm-pill-danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: var(--cpm-red);
        }

        .cpm-pill-warning {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        .cpm-sub {
            color: var(--cpm-muted);
            font-size: .82rem;
        }

        /* Review Note */
        .cpm-review-note {
            max-width: 200px;
            font-size: .82rem;
            color: var(--cpm-red);
            line-height: 1.4;
        }

        /* Proof View Button */
        .cpm-view-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1.5px solid var(--cpm-border);
            background: #fff;
            color: var(--cpm-text);
            border-radius: 7px;
            padding: .3rem .65rem;
            font-size: .78rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .15s;
        }

        .cpm-view-btn:hover {
            border-color: var(--cpm-primary);
            color: var(--cpm-primary);
            background: #f0f7ff;
        }

        /* Edit & Resend Button */
        .cpm-resend-trigger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--cpm-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .4rem .85rem;
            font-size: .8rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s, transform .1s;
            white-space: nowrap;
        }

        .cpm-resend-trigger:hover {
            background: var(--cpm-primary-dk);
            transform: translateY(-1px);
        }

        .cpm-cancel-trigger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff1f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: .4rem .85rem;
            font-size: .8rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s, transform .1s, border-color .15s;
            white-space: nowrap;
        }
        .cpm-cancel-trigger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            transform: translateY(-1px);
        }

        /* Cancel Confirm Modal */
        .cpm-confirm-overlay {
            position: fixed; inset: 0; background: rgba(15, 23, 42, .55);
            display: none; align-items: center; justify-content: center;
            z-index: 9999; padding: 1rem;
            opacity: 0; transition: opacity .18s ease;
        }
        .cpm-confirm-overlay.is-open { display: flex; opacity: 1; }
        .cpm-confirm-card {
            background: #fff; border-radius: 16px; width: 100%; max-width: 440px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .25);
            overflow: hidden; transform: translateY(14px) scale(.98);
            transition: transform .22s ease;
        }
        .cpm-confirm-overlay.is-open .cpm-confirm-card { transform: translateY(0) scale(1); }
        .cpm-confirm-head {
            padding: 20px 24px 12px;
            display: flex; align-items: center; gap: 14px;
            border-bottom: 1px solid #fee2e2;
            background: linear-gradient(180deg, #fff5f5 0%, #fff 100%);
        }
        .cpm-confirm-icon {
            width: 46px; height: 46px; border-radius: 50%;
            background: #fee2e2; color: #b91c1c;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .cpm-confirm-title { margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; }
        .cpm-confirm-sub { margin: 2px 0 0; font-size: .82rem; color: #64748b; }
        .cpm-confirm-body { padding: 18px 24px 6px; color: #334155; font-size: .9rem; }
        .cpm-confirm-meta {
            margin-top: 12px;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 10px 14px; display: grid; gap: 4px;
        }
        .cpm-confirm-meta-row { display: flex; justify-content: space-between; gap: 10px; font-size: .82rem; }
        .cpm-confirm-meta-row span:first-child { color: #64748b; font-weight: 600; }
        .cpm-confirm-meta-row span:last-child { color: #0f172a; font-weight: 700; text-align: right; }
        .cpm-confirm-foot {
            padding: 16px 24px 20px;
            display: flex; gap: 10px; justify-content: flex-end;
        }
        .cpm-confirm-btn {
            display: inline-flex; align-items: center; gap: 6px;
            border-radius: 10px; padding: .55rem 1rem;
            font-weight: 700; font-size: .85rem; cursor: pointer;
            font-family: inherit; border: 1px solid transparent;
            transition: background .15s, transform .1s, border-color .15s;
        }
        .cpm-confirm-btn-ghost {
            background: #fff; color: #334155; border-color: #cbd5e1;
        }
        .cpm-confirm-btn-ghost:hover { background: #f1f5f9; }
        .cpm-confirm-btn-danger {
            background: #dc2626; color: #fff; border-color: #dc2626;
        }
        .cpm-confirm-btn-danger:hover { background: #b91c1c; border-color: #b91c1c; transform: translateY(-1px); }

        /* Empty State */
        .cpm-empty {
            text-align: center;
            padding: 3rem 1rem !important;
            font-size: .9rem;
        }

        .cpm-empty-icon {
            font-size: 2.2rem;
            color: #cbd5e1;
            display: block;
            margin-bottom: 10px;
        }

        .cpm-empty-txt {
            color: var(--cpm-muted);
        }

        /* ── Modal ── */
        .cpm-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(10, 29, 49, .55);
            backdrop-filter: blur(3px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            z-index: 2200;
        }

        .cpm-modal-backdrop.is-open {
            display: flex;
        }

        .cpm-modal {
            width: min(560px, 100%);
            border-radius: 16px;
            border: 1px solid var(--cpm-border);
            background: #fff;
            box-shadow: 0 24px 60px rgba(9, 36, 62, .28);
            overflow: hidden;
            animation: cpm-pop .22s ease;
        }

        .cpm-modal-head {
            border-bottom: 1px solid var(--cpm-border);
            padding: 1rem 1.2rem;
            background: #eef5fb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cpm-modal-head h3 {
            margin: 0;
            font-size: 1rem;
            color: var(--cpm-head);
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cpm-modal-head h3 i {
            color: var(--cpm-primary);
        }

        .cpm-modal-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--cpm-border);
            background: #fff;
            color: var(--cpm-text);
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
        }

        .cpm-modal-close:hover {
            background: #f1f5f9;
        }

        .cpm-modal-body {
            padding: 1.1rem 1.2rem;
            display: grid;
            gap: 14px;
        }

        .cpm-modal-text {
            margin: 0;
            color: var(--cpm-muted);
            font-size: .88rem;
            line-height: 1.5;
        }

        .cpm-modal-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .cpm-modal-tags span {
            border: 1px solid var(--cpm-border);
            background: var(--cpm-soft);
            color: var(--cpm-primary);
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            padding: .22rem .65rem;
        }

        /* Form elements inside modal */
        .cpm-resend-form {
            display: contents;
        }

        .cpm-form-field {
            display: grid;
            gap: 5px;
        }

        .cpm-form-label {
            font-size: .73rem;
            font-weight: 700;
            color: var(--cpm-muted);
            text-transform: uppercase;
            letter-spacing: .04em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cpm-form-label .req {
            color: var(--cpm-red);
        }

        .cpm-text-input {
            border: 1.5px solid var(--cpm-border);
            border-radius: 9px;
            min-height: 40px;
            background: var(--cpm-soft);
            color: var(--cpm-head);
            padding: .5rem .8rem;
            width: 100%;
            font-family: inherit;
            font-size: .9rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .cpm-text-input:focus {
            border-color: var(--cpm-primary);
            box-shadow: 0 0 0 3px rgba(15, 95, 168, .1);
            background: #fff;
        }

        .cpm-textarea {
            min-height: 76px;
            resize: vertical;
        }

        /* Custom file upload areas */
        .cpm-proof-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .cpm-upload-zone {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 1rem .75rem;
            border: 2px dashed var(--cpm-border);
            border-radius: 12px;
            background: var(--cpm-soft);
            cursor: pointer;
            min-height: 88px;
            transition: all .2s;
            text-align: center;
            overflow: hidden;
        }

        .cpm-upload-zone:hover {
            border-color: var(--cpm-primary);
            background: #f0f7ff;
        }

        .cpm-upload-zone.has-file {
            border-color: var(--cpm-green);
            background: #f0fdf4;
            border-style: solid;
        }

        .cpm-upload-zone input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .cpm-upload-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #e2e8f0;
            color: var(--cpm-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            transition: all .2s;
            flex-shrink: 0;
        }

        .cpm-upload-zone:hover .cpm-upload-icon {
            background: #dbeafe;
            color: var(--cpm-primary);
        }

        .cpm-upload-zone.has-file .cpm-upload-icon {
            background: #d1fae5;
            color: var(--cpm-green);
        }

        .cpm-upload-name {
            font-size: .78rem;
            font-weight: 700;
            color: var(--cpm-muted);
            line-height: 1.3;
        }

        .cpm-upload-zone:hover .cpm-upload-name {
            color: var(--cpm-primary);
        }

        .cpm-upload-zone.has-file .cpm-upload-name {
            color: var(--cpm-green);
        }

        .cpm-upload-sub {
            font-size: .68rem;
            color: #94a3b8;
        }

        .cpm-proof-note {
            font-size: .75rem;
            color: var(--cpm-muted);
            display: flex;
            align-items: flex-start;
            gap: 5px;
            margin-top: 2px;
        }

        .cpm-proof-note i {
            color: var(--cpm-primary);
            margin-top: 1px;
            flex-shrink: 0;
        }

        .cpm-modal-foot {
            border-top: 1px solid var(--cpm-border);
            padding: .9rem 1.2rem;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            background: var(--cpm-soft);
        }

        .cpm-cancel-btn {
            border: 1.5px solid var(--cpm-border);
            background: #fff;
            color: var(--cpm-text);
            border-radius: 9px;
            padding: .55rem 1.1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
        }

        .cpm-cancel-btn:hover {
            background: #f1f5f9;
        }

        .cpm-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--cpm-primary);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: .55rem 1.2rem;
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
        }

        .cpm-submit-btn:hover {
            background: var(--cpm-primary-dk);
        }

        /* ── Payment Items box in modal ── */
        .cpm-items-box {
            border: 1px solid var(--cpm-border);
            border-radius: 10px;
            overflow: hidden;
        }

        .cpm-items-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .6rem .9rem;
            background: #eef5fb;
            border-bottom: 1px solid var(--cpm-border);
        }

        .cpm-items-head span:first-child {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #103250;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cpm-items-head span:last-child {
            font-size: .72rem;
            font-weight: 700;
            color: var(--cpm-muted);
        }

        .cpm-items-list {
            display: grid;
        }

        .cpm-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .62rem .9rem;
            font-size: .875rem;
            border-bottom: 1px solid #f1f5f9;
            gap: 6px;
        }

        .cpm-item-row:last-child {
            border-bottom: none;
        }

        .cpm-item-left {
            display: flex;
            align-items: center;
            gap: 7px;
            flex: 1;
            min-width: 0;
        }

        .cpm-item-name {
            color: var(--cpm-text);
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .cpm-item-qty {
            display: inline-flex;
            align-items: center;
            padding: .15rem .45rem;
            background: #e2e8f0;
            border-radius: 5px;
            font-size: .72rem;
            font-weight: 700;
            color: var(--cpm-text);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .cpm-item-total {
            font-weight: 800;
            color: var(--cpm-primary);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .cpm-items-empty {
            padding: .7rem .9rem;
            font-size: .82rem;
            color: var(--cpm-muted);
            font-style: italic;
        }

        /* Mobile List Fallback */
        .cpm-mobile-list { display: none; }

        @keyframes cpm-pop {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 700px) {
            .cpm-proof-grid {
                grid-template-columns: 1fr;
            }

            .cpm-hero {
                flex-direction: column;
                align-items: flex-start;
            }

            .cpm-table-wrap { display: none; }
            .cpm-mobile-list { display: grid; gap: 12px; padding: 12px; }
            .cpm-mobile-card { border: 1px solid var(--cpm-border); border-radius: 12px; background: #fff; padding: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.04); display: grid; gap: 8px; }
            .cpm-mobile-head { display: flex; justify-content: space-between; gap: 8px; align-items: flex-start; margin-bottom: 4px; }
            .cpm-mobile-title { font-family: 'Courier New', monospace; font-weight: 800; color: var(--cpm-head); font-size: 1rem; }
            .cpm-mobile-meta { font-family: 'Courier New', monospace; font-size: .8rem; color: var(--cpm-muted); }
            .cpm-mobile-row { display: flex; justify-content: space-between; gap: 8px; font-size: .86rem; align-items: flex-start; }
            .cpm-mobile-row > span:first-child { color: var(--cpm-muted); font-size: .8rem; flex-shrink: 0; }
            .cpm-mobile-row > strong { text-align: right; color: var(--cpm-text); overflow-wrap: anywhere; }
            .cpm-mobile-actions { display: flex; gap: 8px; flex-wrap: wrap; border-top: 1px solid #f1f5f9; padding-top: 12px; margin-top: 4px; }
            .cpm-mobile-actions .cpm-resend-trigger, .cpm-mobile-actions .cpm-view-btn { flex: 1; justify-content: center; text-align: center; }
        }
    </style>

    <div data-server-rendered-page="collector_payments" data-page-title="Collector Payments" class="cpm-page">

        {{-- ── Alerts ── --}}
        @if(session('status'))
            <div class="cpm-alert cpm-alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="cpm-alert cpm-alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="cpm-alert cpm-alert-error"><i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        {{-- ── Hero ── --}}

        {{-- ── Status Tabs ── --}}
        <section class="cpm-tabs">
            <a href="{{ route('collector.payments', ['status' => 'all']) }}"
                class="cpm-tab {{ $statusFilter === 'all' ? 'active' : '' }}">
                <i class="fa-solid fa-table-list"></i> All <span>{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('collector.payments', ['status' => 'awaiting']) }}"
                class="cpm-tab {{ $statusFilter === 'awaiting' ? 'active' : '' }}">
                <i class="fa-solid fa-hourglass-half"></i> Awaiting <span>{{ $counts['awaiting'] }}</span>
            </a>
            <a href="{{ route('collector.payments', ['status' => 'accepted']) }}"
                class="cpm-tab {{ $statusFilter === 'accepted' ? 'active' : '' }}">
                <i class="fa-solid fa-circle-check"></i> Accepted <span>{{ $counts['accepted'] }}</span>
            </a>
            <a href="{{ route('collector.payments', ['status' => 'rejected']) }}"
                class="cpm-tab {{ $statusFilter === 'rejected' ? 'active' : '' }}">
                <i class="fa-solid fa-circle-xmark"></i> Rejected <span>{{ $counts['rejected'] }}</span>
            </a>
        </section>

        {{-- ── Payments Table ── --}}
        <section class="cpm-card">
            <div class="cpm-table-wrap">
                <table class="cpm-table">
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-hashtag" style="margin-right:4px;"></i>Log No.</th>
                            <th>Payment No.</th>
                            <th><i class="fa-solid fa-sailboat" style="margin-right:4px;"></i>Vessel</th>
                            <th><i class="fa-solid fa-user" style="margin-right:4px;"></i>Payer</th>
                            <th><i class="fa-solid fa-peso-sign" style="margin-right:4px;"></i>Total</th>
                            <th>Status</th>
                            <th>Review Note</th>
                            <th>Proof</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php $status = (string) $item->status; @endphp
                            <tr>
                                <td><span class="cpm-log-no">{{ $item->fishportLog?->log_number ?? '-' }}</span></td>
                                <td><span class="cpm-pay-no">{{ $item->paymentRecord?->payment_number ?? '-' }}</span></td>
                                <td>{{ $item->fishportLog?->vessel?->name ?? '-' }}</td>
                                <td>{{ $item->payer_name ?: '-' }}</td>
                                <td><strong style="color:var(--cpm-primary);">PHP
                                        {{ number_format((float) $item->amount_snapshot, 2) }}</strong></td>
                                <td>
                                    @if($status === 'accepted')
                                        <span class="cpm-pill cpm-pill-success"><i class="fa-solid fa-check"></i> Accepted</span>
                                    @elseif($status === 'rejected')
                                        <span class="cpm-pill cpm-pill-danger"><i class="fa-solid fa-xmark"></i> Rejected</span>
                                    @else
                                        <span class="cpm-pill cpm-pill-warning"><i class="fa-solid fa-clock"></i> Awaiting</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->review_note)
                                        <span class="cpm-review-note">{{ $item->review_note }}</span>
                                    @else
                                        <span class="cpm-sub">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->proof_image_path)
                                        <button
                                            type="button"
                                            class="cpm-view-btn js-cpm-proof-view-btn"
                                            data-proof-url="{{ route('collection.proof', $item) }}"
                                            data-proof-label="{{ $item->fishportLog?->log_number ?? '-' }} | {{ $item->paymentRecord?->payment_number ?? '-' }}"
                                        >
                                            <i class="fa-solid fa-image"></i> View
                                        </button>
                                    @else
                                        <span class="cpm-sub">No image</span>
                                    @endif
                                </td>
                                <td class="cpm-sub" style="white-space:nowrap;">
                                    {{ optional($item->updated_at)->format('m/d/Y h:i A') ?: '-' }}</td>
                                <td>
                                    @if($status === 'rejected')
                                        <button type="button" class="cpm-resend-trigger js-cpm-resend-btn"
                                            data-action="{{ route('collector.pending_collections.collect', $item) }}"
                                            data-log-no="{{ $item->fishportLog?->log_number ?? '-' }}"
                                            data-payment-no="{{ $item->paymentRecord?->payment_number ?? '-' }}"
                                            data-vessel="{{ $item->fishportLog?->vessel?->name ?? '-' }}"
                                            data-payer-name="{{ $item->payer_name ?? '' }}"
                                            data-collector-note="{{ $item->collector_note ?? '' }}"
                                            data-has-proof="{{ $item->proof_image_path ? '1' : '0' }}"
                                            data-items="{{ json_encode(($item->fishportLog?->payments ?? collect())->map(fn($p) => ['name' => $p->paymentType?->name ?? 'Charge', 'qty' => number_format((float) $p->quantity, 2), 'total' => number_format((float) $p->total, 2)])->values()->toArray()) }}">
                                            <i class="fa-solid fa-rotate-right"></i> Edit & Resend
                                        </button>
                                    @elseif($status === 'collected_pending_confirmation')
                                        <button type="button" class="cpm-cancel-trigger js-cpm-cancel-btn"
                                            data-action="{{ route('collector.payments.cancel', $item) }}"
                                            data-log-no="{{ $item->fishportLog?->log_number ?? '-' }}"
                                            data-payment-no="{{ $item->paymentRecord?->payment_number ?? '-' }}"
                                            data-vessel="{{ $item->fishportLog?->vessel?->name ?? '-' }}"
                                            data-amount="{{ number_format((float) $item->amount_snapshot, 2) }}">
                                            <i class="fa-solid fa-ban"></i> Cancel
                                        </button>
                                    @else
                                        <span class="cpm-sub">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="cpm-empty">
                                    <span class="cpm-empty-icon"><i class="fa-solid fa-inbox"></i></span>
                                    <span class="cpm-empty-txt">No payment updates found for this filter.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ── Mobile Card Fallback ── --}}
            <div class="cpm-mobile-list">
                @forelse ($items as $item)
                    @php $status = (string) $item->status; @endphp
                    <div class="cpm-mobile-card">
                        <div class="cpm-mobile-head">
                            <div style="min-width:0;">
                                <div class="cpm-mobile-title">{{ $item->fishportLog?->log_number ?? '-' }}</div>
                                <div class="cpm-mobile-meta">{{ $item->paymentRecord?->payment_number ?? '-' }}</div>
                            </div>
                            <div>
                                @if($status === 'accepted')
                                    <span class="cpm-pill cpm-pill-success"><i class="fa-solid fa-check"></i> Accepted</span>
                                @elseif($status === 'rejected')
                                    <span class="cpm-pill cpm-pill-danger"><i class="fa-solid fa-xmark"></i> Rejected</span>
                                @else
                                    <span class="cpm-pill cpm-pill-warning"><i class="fa-solid fa-clock"></i> Awaiting</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="cpm-mobile-row"><span>Vessel</span><strong>{{ $item->fishportLog?->vessel?->name ?? '-' }}</strong></div>
                        <div class="cpm-mobile-row"><span>Payer</span><strong>{{ $item->payer_name ?: '-' }}</strong></div>
                        <div class="cpm-mobile-row"><span>Updated</span><strong>{{ optional($item->updated_at)->format('m/d/Y h:i A') ?: '-' }}</strong></div>
                        <div class="cpm-mobile-row"><span>Total</span><strong style="color:var(--cpm-primary);font-size:1.05rem;">PHP {{ number_format((float) $item->amount_snapshot, 2) }}</strong></div>

                        @if($status === 'rejected' && $item->review_note)
                            <div class="cpm-mobile-row" style="flex-direction:column;gap:3px;">
                                <span>Review Note</span>
                                <strong style="color:var(--cpm-red);text-align:left;font-size:.85rem;">{{ $item->review_note }}</strong>
                            </div>
                        @endif

                        <div class="cpm-mobile-actions">
                            @if($item->proof_image_path)
                                <button
                                    type="button"
                                    class="cpm-view-btn js-cpm-proof-view-btn"
                                    data-proof-url="{{ route('collection.proof', $item) }}"
                                    data-proof-label="{{ $item->fishportLog?->log_number ?? '-' }} | {{ $item->paymentRecord?->payment_number ?? '-' }}"
                                >
                                    <i class="fa-solid fa-image"></i> View Proof
                                </button>
                            @endif
                            @if($status === 'rejected')
                                <button type="button" class="cpm-resend-trigger js-cpm-resend-btn"
                                    data-action="{{ route('collector.pending_collections.collect', $item) }}"
                                    data-log-no="{{ $item->fishportLog?->log_number ?? '-' }}"
                                    data-payment-no="{{ $item->paymentRecord?->payment_number ?? '-' }}"
                                    data-vessel="{{ $item->fishportLog?->vessel?->name ?? '-' }}"
                                    data-payer-name="{{ $item->payer_name ?? '' }}"
                                    data-collector-note="{{ $item->collector_note ?? '' }}"
                                    data-has-proof="{{ $item->proof_image_path ? '1' : '0' }}"
                                    data-items="{{ json_encode(($item->fishportLog?->payments ?? collect())->map(fn($p) => ['name' => $p->paymentType?->name ?? 'Charge', 'qty' => number_format((float) $p->quantity, 2), 'total' => number_format((float) $p->total, 2)])->values()->toArray()) }}">
                                    <i class="fa-solid fa-rotate-right"></i> Edit & Resend
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="cpm-empty" style="padding: 2rem 1rem;">
                        <span class="cpm-empty-icon"><i class="fa-solid fa-inbox"></i></span>
                        <span class="cpm-empty-txt">No payment updates found.</span>
                    </div>
                @endforelse
            </div>
        </section>

        <div>{{ $items->links() }}</div>
    </div>

    <div class="cpm-modal-backdrop" id="cpmProofPreviewModal" aria-hidden="true">
        <div class="cpm-modal" role="dialog" aria-modal="true" aria-labelledby="cpmProofPreviewTitle">
            <div class="cpm-modal-head">
                <h3 id="cpmProofPreviewTitle"><i class="fa-solid fa-image"></i> Proof Preview</h3>
                <button type="button" class="cpm-modal-close" id="cpmProofPreviewCloseBtn" aria-label="Close">&times;</button>
            </div>
            <div class="cpm-modal-body">
                <div style="display:grid;gap:8px;width:100%;">
                    <div id="cpmProofPreviewMeta" class="cpm-sub">-</div>
                    <div style="border:1px solid var(--cpm-border);border-radius:10px;background:#f8fafc;overflow:hidden;">
                        <img id="cpmProofPreviewImage" src="" alt="Proof preview" style="display:block;width:100%;max-height:70vh;object-fit:contain;">
                    </div>
                </div>
            </div>
            <div class="cpm-modal-foot">
                <button type="button" class="cpm-cancel-btn" id="cpmProofPreviewCloseFootBtn">Close</button>
            </div>
        </div>
    </div>

    {{-- ── Edit & Resend Modal ── --}}
    <div class="cpm-modal-backdrop" id="cpmResendModal" aria-hidden="true">
        <div class="cpm-modal" role="dialog" aria-modal="true" aria-labelledby="cpmResendTitle">
            <div class="cpm-modal-head">
                <h3 id="cpmResendTitle">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Rejected Collection &amp; Resend
                </h3>
                <button type="button" class="cpm-modal-close" id="cpmResendCloseBtn" aria-label="Close">&times;</button>
            </div>
            <form method="POST" id="cpmResendForm" enctype="multipart/form-data" class="cpm-resend-form">
                @csrf
                <div class="cpm-modal-body">
                    <p class="cpm-modal-text">Update the details below, then resend to fishport personnel for review.</p>

                    {{-- Reference tags --}}
                    <div class="cpm-modal-tags">
                        <span id="cpmResendLogNo"><i class="fa-solid fa-hashtag"></i> Log: -</span>
                        <span id="cpmResendPaymentNo"><i class="fa-solid fa-receipt"></i> Payment: -</span>
                        <span id="cpmResendVessel"><i class="fa-solid fa-sailboat"></i> Vessel: -</span>
                    </div>

                    {{-- Payment Items breakdown --}}
                    <div class="cpm-items-box" id="cpmItemsBox">
                        <div class="cpm-items-head">
                            <span><i class="fa-solid fa-receipt"></i> Payment Items</span>
                            <span id="cpmItemsCount">0 item(s)</span>
                        </div>
                        <div class="cpm-items-list" id="cpmItemsList">
                            <div class="cpm-items-empty">No payment items.</div>
                        </div>
                    </div>

                    {{-- Payer Name --}}
                    <div class="cpm-form-field">
                        <div class="cpm-form-label">
                            <i class="fa-solid fa-user"></i> Payer Name <span class="req">*</span>
                        </div>
                        <input type="text" name="payer_name" id="cpmResendPayerName" maxlength="150" required
                            class="cpm-text-input" placeholder="Full name of the person who paid">
                    </div>

                    {{-- Collector Note --}}
                    <div class="cpm-form-field">
                        <div class="cpm-form-label">
                            <i class="fa-solid fa-note-sticky"></i> Collector Note
                        </div>
                        <textarea name="collector_note" id="cpmResendCollectorNote" rows="2" placeholder="Optional note…"
                            class="cpm-text-input cpm-textarea"></textarea>
                    </div>

                    {{-- Proof Photo — custom styled ── --}}
                    <div class="cpm-form-field">
                        <div class="cpm-form-label">
                            <i class="fa-solid fa-camera"></i> Proof Photo
                        </div>
                        <div class="cpm-proof-grid">
                            {{-- Upload from gallery --}}
                            <label class="cpm-upload-zone" id="cpmUploadLabel">
                                <input type="file" name="proof_image" accept="image/*" id="cpmResendProofUpload"
                                    onchange="cpmSetFile(this,'cpmUploadLabel','cpmUploadSub')">
                                <div class="cpm-upload-icon"><i class="fa-solid fa-image"></i></div>
                                <div class="cpm-upload-name">Upload Photo</div>
                                <div class="cpm-upload-sub" id="cpmUploadSub">Browse gallery</div>
                            </label>
                            {{-- Use camera --}}
                            <label class="cpm-upload-zone" id="cpmCameraLabel">
                                <input type="file" name="proof_camera" accept="image/*" capture="environment"
                                    id="cpmResendProofCamera" onchange="cpmSetFile(this,'cpmCameraLabel','cpmCameraSub')">
                                <div class="cpm-upload-icon"><i class="fa-solid fa-camera-retro"></i></div>
                                <div class="cpm-upload-name">Use Camera</div>
                                <div class="cpm-upload-sub" id="cpmCameraSub">Capture photo</div>
                            </label>
                        </div>
                        <p class="cpm-proof-note" id="cpmResendProofHint">
                            <i class="fa-solid fa-circle-info"></i>
                            Upload or capture a new proof photo.
                        </p>
                    </div>
                </div>

                <div class="cpm-modal-foot">
                    <button type="button" class="cpm-cancel-btn" id="cpmResendCancelBtn">Cancel</button>
                    <button type="submit" class="cpm-submit-btn">
                        <i class="fa-solid fa-paper-plane"></i> Resend to Personnel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function cpmSetFile(input, labelId, subId) {
            const label = document.getElementById(labelId);
            const sub = document.getElementById(subId);
            const file = input.files?.[0];
            if (file && label) {
                label.classList.add('has-file');
                if (sub) sub.textContent = file.name.length > 20 ? file.name.substring(0, 18) + '…' : file.name;
            } else if (label) {
                label.classList.remove('has-file');
                if (sub) sub.textContent = labelId.includes('Camera') ? 'Capture photo' : 'Browse gallery';
            }
        }

        (() => {
            const modal = document.getElementById('cpmResendModal');
            const proofPreviewModal = document.getElementById('cpmProofPreviewModal');
            const resendForm = document.getElementById('cpmResendForm');
            const closeBtn = document.getElementById('cpmResendCloseBtn');
            const cancelBtn = document.getElementById('cpmResendCancelBtn');
            const proofPreviewCloseBtn = document.getElementById('cpmProofPreviewCloseBtn');
            const proofPreviewCloseFootBtn = document.getElementById('cpmProofPreviewCloseFootBtn');
            const proofPreviewImage = document.getElementById('cpmProofPreviewImage');
            const proofPreviewMeta = document.getElementById('cpmProofPreviewMeta');
            const logNo = document.getElementById('cpmResendLogNo');
            const paymentNo = document.getElementById('cpmResendPaymentNo');
            const vessel = document.getElementById('cpmResendVessel');
            const payerName = document.getElementById('cpmResendPayerName');
            const collectorNote = document.getElementById('cpmResendCollectorNote');
            const proofUpload = document.getElementById('cpmResendProofUpload');
            const proofCamera = document.getElementById('cpmResendProofCamera');
            const cameraLabel = document.getElementById('cpmCameraLabel');
            const proofHint = document.getElementById('cpmResendProofHint');

            if (!modal || !resendForm) return;

            const openModal = () => {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };
            const closeModal = () => {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                resendForm.removeAttribute('data-has-existing-proof');
                resendForm.removeAttribute('action');
                resendForm.reset();
                // Reset upload zones
                document.getElementById('cpmUploadLabel')?.classList.remove('has-file');
                document.getElementById('cpmCameraLabel')?.classList.remove('has-file');
                document.getElementById('cpmUploadSub').textContent = 'Browse gallery';
                document.getElementById('cpmCameraSub').textContent = 'Capture photo';
            };

            const openProofPreview = (url, label) => {
                if (!proofPreviewModal || !proofPreviewImage) return;
                proofPreviewImage.src = url || '';
                if (proofPreviewMeta) {
                    proofPreviewMeta.textContent = label || '-';
                }
                proofPreviewModal.classList.add('is-open');
                proofPreviewModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };

            const closeProofPreview = () => {
                if (!proofPreviewModal) return;
                proofPreviewModal.classList.remove('is-open');
                proofPreviewModal.setAttribute('aria-hidden', 'true');
                if (proofPreviewImage) proofPreviewImage.src = '';
                if (!modal.classList.contains('is-open')) {
                    document.body.style.overflow = '';
                }
            };

            document.querySelectorAll('.js-cpm-resend-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    resendForm.action = button.dataset.action || '';
                    resendForm.setAttribute('data-has-existing-proof', button.dataset.hasProof === '1' ? '1' : '0');
                    logNo.textContent = button.dataset.logNo || '-';
                    paymentNo.textContent = button.dataset.paymentNo || '-';
                    vessel.textContent = button.dataset.vessel || '-';
                    payerName.value = button.dataset.payerName || '';
                    collectorNote.value = button.dataset.collectorNote || '';

                    proofHint.innerHTML = '<i class="fa-solid fa-circle-info"></i> Upload or capture a new proof photo (required).';

                    openModal();
                });
            });

            document.querySelectorAll('.js-cpm-proof-view-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    openProofPreview(button.dataset.proofUrl || '', button.dataset.proofLabel || 'Proof image');
                });
            });

            resendForm.addEventListener('submit', (event) => {
                const hasUpload = (proofUpload?.files?.length || 0) > 0;
                const hasCamera = (proofCamera?.files?.length || 0) > 0;
                if (!hasUpload && !hasCamera) {
                    event.preventDefault();
                    if (proofUpload) {
                        proofUpload.setCustomValidity('Upload a proof photo or capture one using camera.');
                        proofUpload.reportValidity();
                        proofUpload.setCustomValidity('');
                    }
                }
            });

            const openCameraPicker = () => {
                if (!proofCamera) return;
                proofCamera.setAttribute('accept', 'image/*');
                proofCamera.setAttribute('capture', 'environment');
                proofCamera.click();
            };

            cameraLabel?.addEventListener('click', (event) => {
                if (!proofCamera) return;
                if (event.target === proofCamera) return;
                event.preventDefault();
                openCameraPicker();
            });

            closeBtn?.addEventListener('click', closeModal);
            cancelBtn?.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
            proofPreviewCloseBtn?.addEventListener('click', closeProofPreview);
            proofPreviewCloseFootBtn?.addEventListener('click', closeProofPreview);
            proofPreviewModal?.addEventListener('click', (e) => { if (e.target === proofPreviewModal) closeProofPreview(); });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
                if (e.key === 'Escape' && proofPreviewModal?.classList.contains('is-open')) closeProofPreview();
            });
        })();
    </script>

    {{-- Cancel Awaiting Confirmation Modal --}}
    <div id="cpmCancelOverlay" class="cpm-confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="cpmCancelTitle">
        <div class="cpm-confirm-card">
            <div class="cpm-confirm-head">
                <div class="cpm-confirm-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <h3 id="cpmCancelTitle" class="cpm-confirm-title">Cancel Awaiting Submission?</h3>
                    <p class="cpm-confirm-sub">This will send the record back to Pending Collections.</p>
                </div>
            </div>
            <div class="cpm-confirm-body">
                The department will no longer see this proof in their approval inbox. Your proof photo will be removed and you can re-collect and resubmit the payment.
                <div class="cpm-confirm-meta" id="cpmCancelMeta"></div>
            </div>
            <form id="cpmCancelForm" method="POST" action="">
                @csrf
                <div class="cpm-confirm-foot">
                    <button type="button" class="cpm-confirm-btn cpm-confirm-btn-ghost" id="cpmCancelKeep">
                        <i class="fa-solid fa-xmark"></i> Keep Awaiting
                    </button>
                    <button type="submit" class="cpm-confirm-btn cpm-confirm-btn-danger" id="cpmCancelConfirm">
                        <i class="fa-solid fa-ban"></i> Yes, Cancel It
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const overlay = document.getElementById('cpmCancelOverlay');
            const form = document.getElementById('cpmCancelForm');
            const meta = document.getElementById('cpmCancelMeta');
            const keepBtn = document.getElementById('cpmCancelKeep');
            if (!overlay || !form) return;

            function openModal() {
                overlay.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function closeModal() {
                overlay.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            function buildMeta(trigger) {
                const rows = [
                    ['Log No.', trigger.dataset.logNo],
                    ['Payment No.', trigger.dataset.paymentNo],
                    ['Vessel', trigger.dataset.vessel],
                    ['Amount', trigger.dataset.amount ? ('PHP ' + trigger.dataset.amount) : null],
                ].filter(r => r[1] && r[1] !== '-');

                meta.innerHTML = rows.map(r =>
                    `<div class="cpm-confirm-meta-row"><span>${r[0]}</span><span>${r[1]}</span></div>`
                ).join('');
            }

            document.querySelectorAll('.js-cpm-cancel-btn').forEach((btn) => {
                btn.addEventListener('click', () => {
                    form.setAttribute('action', btn.dataset.action || '');
                    buildMeta(btn);
                    openModal();
                });
            });

            keepBtn?.addEventListener('click', closeModal);
            overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
            });
        })();
    </script>
@endsection

