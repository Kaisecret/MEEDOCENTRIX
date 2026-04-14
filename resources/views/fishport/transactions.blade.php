@extends('layouts.app')

@section('content')
    @php
        $isEditing = $editingLog !== null;
        $formAction = $isEditing ? route('fishport.records.update', $editingLog) : route('fishport.records.store');
        $unitLookup = $units->map(static fn($unit) => ['id' => $unit->id, 'name' => $unit->name])->values();
        $paymentTypeLookup = $paymentTypes->map(static fn($paymentType) => [
            'id' => $paymentType->id,
            'code' => $paymentType->code,
            'name' => $paymentType->name,
            'fee' => (float) $paymentType->default_fee,
        ])->values();
        $manualPaymentTypes = $paymentTypes
            ->whereIn('code', ['ENTRANCE', 'DOCKING'])
            ->values();
        $hasPendingLogs = count($pendingLogs) > 0;
        $paymentNumberPreview = $isEditing
            ? ($editingLog->paymentRecord?->payment_number ?? 'Auto-generated on save')
            : 'Auto-generated on save';
        $linkedVesselNamePreview = $isEditing
            ? ($editingLog->vessel?->name ?? 'Select logged entry first')
            : 'Select logged entry first';
        $clientState = [
            'commodities' => $commodityLookup,
            'units' => $unitLookup,
            'paymentTypes' => $paymentTypeLookup,
            'baseFees' => $baseFees,
            'oldItems' => old('items_payload'),
            'oldPayments' => old('payments_payload'),
            'editingItems' => $editingItems,
            'editingPayments' => $editingPayments,
            'isEditing' => $isEditing,
            'hasStatus' => session()->has('status'),
            'statusMessage' => session('status'),
            'printReceipt' => session('print_receipt_data'),
            'savedLogLookup' => $savedLogLookup,
            'savedHasFilters' => $savedHasFilters ?? false,
            'pendingLogs' => $pendingLogs,
            'oldSourceLogId' => old('source_log_id'),
            'hasPendingLogs' => $hasPendingLogs,
        ];
    @endphp

    <style>
        .fishport-page {
            --fp-bg: #f3f7fb;
            --fp-panel: #ffffff;
            --fp-line: #d9e3ef;
            --fp-text: #0f2740;
            --fp-muted: #5e7188;
            --fp-primary: #155f8f;
            --fp-primary-dark: #0f4b72;
            --fp-soft: #e9f2fa;
            --fp-danger: #c0392b;
            --fp-danger-soft: #fceceb;
            --fp-success: #1f8f67;
            --fp-success-soft: #e7f7f1;
            --fp-warning: #9a6a00;
            --fp-warning-soft: #fff4dd;
            color: var(--fp-text);
            background: var(--fp-bg);
            border: 1px solid #e1e8f0;
            border-radius: 18px;
            padding: 22px;
            max-width: 1320px;
            margin: 0 auto;
        }

        .fishport-hero {
            background: linear-gradient(140deg, #0f3f66 0%, #1a6d95 100%);
            color: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 16px 26px rgba(12, 47, 74, 0.2);
            margin-bottom: 18px;
        }

        .fishport-hero h2 {
            margin: 0;
            font-size: 1.85rem;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .fishport-hero p {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.98rem;
        }

        .fishport-alert {
            border-radius: 12px;
            border: 1px solid transparent;
            padding: 12px 14px;
            margin-bottom: 12px;
            font-size: 0.96rem;
        }

        .fishport-alert-success {
            background: var(--fp-success-soft);
            border-color: #b8e5d3;
            color: #145c43;
        }

        .fishport-toast {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1500;
            margin: 0;
            min-width: min(420px, calc(100vw - 32px));
            max-width: min(560px, calc(100vw - 32px));
            box-shadow: 0 14px 28px rgba(15, 39, 64, 0.24);
            transition: opacity .22s ease, transform .22s ease;
        }

        .fishport-toast.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
            pointer-events: none;
        }

        .fishport-alert-danger {
            background: #fdf0ef;
            border-color: #f1cbc7;
            color: #8a251a;
        }

        .fishport-alert-warning {
            background: var(--fp-warning-soft);
            border-color: #efd89d;
            color: #684600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .fishport-form-stack {
            display: grid;
            gap: 18px;
            margin-bottom: 18px;
        }

        .fishport-panel {
            background: var(--fp-panel);
            border: 1px solid var(--fp-line);
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(15, 39, 64, 0.06);
            overflow: hidden;
        }

        .fishport-panel-head {
            padding: 14px 18px;
            border-bottom: 1px solid var(--fp-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            background: #fbfdff;
        }

        .fishport-panel-head h3 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fishport-panel-head h3 i {
            color: var(--fp-primary);
        }

        .fishport-panel-sub {
            margin: 4px 0 0;
            font-size: 0.88rem;
            color: var(--fp-muted);
        }

        .fishport-panel-body {
            padding: 18px;
        }

        .fishport-fields {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }

        .fishport-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #173754;
        }

        .fishport-field-hint {
            margin-top: 4px;
            color: var(--fp-muted);
            font-size: .8rem;
            line-height: 1.3;
        }

        .fishport-field.full-width {
            grid-column: 1 / -1;
        }

        .fishport-input,
        .fishport-select {
            width: 100%;
            border: 1px solid #c4d3e2;
            border-radius: 10px;
            min-height: 42px;
            padding: 9px 11px;
            font-size: 0.96rem;
            color: #12314d;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .fishport-input:focus,
        .fishport-select:focus {
            outline: none;
            border-color: #2e7caf;
            box-shadow: 0 0 0 3px rgba(46, 124, 175, 0.16);
        }

        .fishport-icon-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .fishport-icon-wrap>i {
            position: absolute;
            left: 14px;
            color: #8fa0b5;
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .fishport-icon-wrap .fishport-input,
        .fishport-icon-wrap .fishport-select {
            padding-left: 38px;
        }

        .fishport-icon-wrap:focus-within>i {
            color: var(--fp-primary);
        }

        .fishport-input[readonly] {
            background: #f2f6fb;
            color: #38536f;
        }

        .fishport-table-wrap {
            overflow: auto;
            border-top: 1px solid var(--fp-line);
            border-bottom: 1px solid var(--fp-line);
        }

        .fishport-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        .fishport-table th,
        .fishport-table td {
            border-bottom: 1px solid #e5edf6;
            padding: 10px 12px;
            vertical-align: middle;
        }

        .fishport-table td {
            font-size: 0.9rem;
            color: #334155;
        }

        .fishport-table td:first-child,
        .fishport-table th:first-child,
        .fishport-table td:last-child,
        .fishport-table th:last-child {
            white-space: nowrap;
        }

        .fishport-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #eef5fb;
            color: #103250;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-weight: 700;
        }

        .fishport-table tbody tr:nth-child(even) {
            background: #fafcfe;
        }

        .fishport-table tbody tr:hover {
            background: #f0f7ff;
        }

        .fishport-input-sm,
        .fishport-select-sm {
            min-height: 36px;
            padding: 6px 8px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .fishport-metrics {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding: 14px 18px 16px;
            background: #fbfdff;
        }

        .fishport-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 11px;
            border-radius: 999px;
            border: 1px solid #c8d9ea;
            background: #ecf3fb;
            color: #123a5c;
            font-weight: 600;
            font-size: 0.87rem;
        }

        .fishport-chip strong {
            font-size: 0.92rem;
        }

        .fishport-chip.is-volume {
            background: #e8f8f0;
            border-color: #b8e8d3;
            color: #196346;
        }

        .fishport-chip.is-ice {
            background: #fff4dd;
            border-color: #f0dca9;
            color: #7a5600;
        }

        .fishport-breakdown-foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 14px 18px;
            color: var(--fp-muted);
            background: #fbfdff;
        }

        .fishport-breakdown-foot strong {
            color: #0f3454;
            font-size: 1.03rem;
        }

        .fishport-btn {
            border: 0;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease;
        }

        .fishport-btn:hover {
            transform: translateY(-1px);
        }

        .fishport-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .fishport-btn-primary {
            background: var(--fp-primary);
            color: #fff;
            box-shadow: 0 6px 14px rgba(21, 95, 143, 0.3);
        }

        .fishport-btn-primary:hover {
            background: var(--fp-primary-dark);
        }

        .fishport-btn-muted {
            background: #e2eaf3;
            color: #1b3f5f;
        }

        .fishport-btn-muted:hover {
            background: #d4e0ec;
        }

        .fishport-btn-outline {
            background: #fff;
            color: var(--fp-primary);
            border: 1px solid #8ab3d1;
        }

        .fishport-btn-outline:hover {
            background: #edf5fb;
        }

        .fishport-btn-danger {
            background: #fff;
            color: var(--fp-danger);
            border: 1px solid #e5b6b1;
        }

        .fishport-btn-danger:hover {
            background: var(--fp-danger-soft);
        }

        .fishport-btn-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #10b981;
        }

        .fishport-btn-success:hover {
            background: #d1fae5;
        }

        .fishport-btn-warning {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #f59e0b;
        }

        .fishport-btn-warning:hover {
            background: #fef3c7;
        }

        .fishport-btn-sm {
            padding: 7px 11px;
            border-radius: 8px;
            font-size: 0.83rem;
        }

        .fishport-btn-icon {
            padding: 0;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 8px;
        }

        .fishport-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .fishport-actions-cell {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .fishport-inline-form {
            margin: 0;
        }

        .fishport-empty {
            text-align: center;
            color: var(--fp-muted);
            padding: 24px 10px;
        }

        .money-input {
            display: flex;
            align-items: center;
            border: 1px solid #c4d3e2;
            border-radius: 8px;
            overflow: hidden;
            min-height: 36px;
            background: #fff;
        }

        .money-input span {
            background: #edf4fb;
            color: #234d72;
            font-weight: 700;
            font-size: 0.82rem;
            padding: 0 10px;
            align-self: stretch;
            display: flex;
            align-items: center;
        }

        .money-input input {
            border: 0;
            border-left: 1px solid #d2deea;
            border-radius: 0;
        }

        .text-right {
            text-align: right;
        }

        .commodity-picker {
            display: grid;
            grid-template-columns: minmax(170px, 1fr) minmax(130px, 0.85fr);
            gap: 6px;
        }

        .payment-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .payment-add-select {
            min-width: 220px;
        }

        .saved-search-shell {
            display: grid;
            grid-template-columns: minmax(320px, 1fr) 168px 168px auto auto;
            align-items: center;
            gap: 8px;
            width: 100%;
        }

        .saved-search-input {
            min-width: 0;
            width: 100%;
        }

        .saved-date-input {
            min-width: 168px;
            width: 100%;
        }

        .saved-search-meta {
            color: var(--fp-muted);
            font-size: .86rem;
            font-weight: 600;
            justify-self: end;
            white-space: nowrap;
        }

        .saved-status-tabs {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px;
            border: 1px solid var(--fp-line);
            border-radius: 12px;
            background: #f8fafc;
        }

        .saved-status-tab {
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #64748b;
            font-size: .85rem;
            font-weight: 600;
            padding: 8px 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s ease;
        }

        .saved-status-tab:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .saved-status-tab.is-active {
            background: #fff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .saved-status-count {
            min-width: 20px;
            height: 20px;
            border-radius: 999px;
            background: #e2e8f0;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            line-height: 1;
            padding: 0 6px;
        }

        .fishport-pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            padding: 12px 0 2px;
        }

        .fishport-page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 9px;
            border: 1px solid #b8cce2;
            background: #fff;
            color: #154f7a;
            text-decoration: none;
            font-weight: 700;
            font-size: .84rem;
            transition: background-color .2s ease, border-color .2s ease;
        }

        .fishport-page-link:hover {
            background: #edf5fb;
            border-color: #8fb3d4;
        }

        .fishport-page-link.is-disabled {
            background: #eef3f8;
            color: #7b8fa4;
            border-color: #d0dce8;
            pointer-events: none;
        }

        .payment-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .75rem;
            line-height: 1;
            font-weight: 700;
            letter-spacing: .03em;
            border: 1px solid transparent;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .payment-status-badge.is-paid {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
        }

        .payment-status-badge.is-not-paid {
            background: #fffbeb;
            border-color: #fde68a;
            color: #b45309;
        }

        .fishport-tabs {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 6px;
            background: #f8fafc;
            border: 1px solid var(--fp-line);
            border-radius: 14px;
            margin: 6px 0 24px;
        }

        .fishport-tab {
            border: 0;
            background: transparent;
            color: #64748b;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all .2s ease;
        }

        .fishport-tab:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .fishport-tab.is-active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .fishport-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            min-height: 26px;
            padding: 0 8px;
            border-radius: 999px;
            background: #e2e8f0;
            color: #475569;
            font-size: .8rem;
            font-weight: 700;
        }

        .fishport-tab.is-active .fishport-tab-count {
            background: #f1f5f9;
            color: #0f172a;
        }

        .fishport-tab-panel {
            display: none;
        }

        .fishport-tab-panel.is-active {
            display: block;
        }

        .save-action-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 39, 64, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            padding: 16px;
        }

        .save-action-modal.is-open {
            display: flex;
        }

        .save-action-card {
            width: min(460px, 96vw);
            background: #fff;
            border: 1px solid var(--fp-line);
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .save-action-head {
            padding: 16px 20px;
            border-bottom: 1px solid var(--fp-line);
            background: #fff;
        }

        .save-action-head h4 {
            margin: 0;
            font-size: 1.1rem;
            color: #0f172a;
            font-weight: 700;
        }

        .save-action-body {
            padding: 20px;
            display: grid;
            gap: 12px;
        }

        .save-action-desc {
            margin: 0 0 8px;
            color: #64748b;
            font-size: 0.95rem;
        }

        .save-action-foot {
            padding: 16px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            border-top: 1px solid var(--fp-line);
            background: #f8fafc;
        }

        .mark-paid-modal {
            z-index: 1250;
        }

        .mark-paid-card {
            width: min(520px, 96vw);
        }

        .mark-paid-head {
            background: #f8fafc;
        }

        .cancel-payment-head {
            background: #fef2f2;
            border-bottom-color: #fee2e2;
        }

        .cancel-payment-head h4 {
            color: #ef4444;
        }

        .delete-log-head {
            background: #fff4f4;
            border-bottom-color: #fecaca;
        }

        .delete-log-head h4 {
            color: #dc2626;
        }

        .mark-paid-preview {
            border: 1px solid var(--fp-line);
            border-radius: 12px;
            background: #f8fafc;
            padding: 12px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .mark-paid-preview article {
            background: #fff;
            border: 1px solid var(--fp-line);
            border-radius: 10px;
            padding: 10px 12px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .mark-paid-preview span {
            display: block;
            color: #64748b;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 600;
        }

        .mark-paid-preview strong {
            display: block;
            margin-top: 4px;
            color: #0f172a;
            font-size: 1rem;
            line-height: 1.3;
        }

        .mark-paid-payer-field {
            display: grid;
            gap: 6px;
            margin-top: 4px;
        }

        .mark-paid-payer-field label {
            color: #173754;
            font-weight: 700;
            font-size: .9rem;
        }

        .saved-log-view-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 39, 64, 0.52);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1300;
            padding: 14px;
        }

        .saved-log-view-modal.is-open {
            display: flex;
        }

        .saved-log-view-card {
            width: min(980px, 98vw);
            max-height: calc(100vh - 28px);
            overflow: auto;
            background: #fff;
            border-radius: 20px;
            border: 1px solid var(--fp-line);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .saved-log-view-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--fp-line);
            background: #fff;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .saved-log-view-head h4 {
            margin: 0;
            font-size: 1.25rem;
            color: #0f172a;
            font-weight: 700;
        }

        .saved-log-view-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            padding: 16px 24px;
            background: #f8fafc;
            border-bottom: 1px solid var(--fp-line);
        }

        .saved-log-meta {
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 12px;
            padding: 12px 14px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .saved-log-meta span {
            display: block;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 600;
        }

        .saved-log-meta strong {
            display: block;
            margin-top: 4px;
            color: #0f172a;
            font-size: 1rem;
        }

        .saved-log-view-body {
            padding: 24px;
            display: grid;
            gap: 20px;
        }

        .saved-log-card {
            border: 1px solid var(--fp-line);
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .saved-log-card-head {
            padding: 14px 20px;
            border-bottom: 1px solid var(--fp-line);
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .saved-log-card-title {
            margin: 0;
            font-size: 1.05rem;
            color: #0f172a;
            font-weight: 600;
        }

        .saved-log-simple-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .saved-log-simple-table th,
        .saved-log-simple-table td {
            padding: 12px 20px;
            border-bottom: 1px solid var(--fp-line);
            font-size: .9rem;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .saved-log-simple-table th {
            background: #fff;
            color: #475569;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
        }

        .saved-log-note {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px 20px;
            color: #334155;
            font-size: .95rem;
            white-space: pre-wrap;
        }

        .saved-log-empty {
            text-align: center;
            color: #678198;
            font-style: italic;
        }

        @media (max-width: 860px) {
            .fishport-page {
                padding: 14px;
                border-radius: 12px;
            }

            .fishport-hero {
                padding: 16px;
            }

            .fishport-hero h2 {
                font-size: 1.5rem;
            }

            .fishport-panel-head,
            .fishport-panel-body,
            .fishport-metrics,
            .fishport-breakdown-foot {
                padding-left: 12px;
                padding-right: 12px;
            }

            .commodity-picker {
                grid-template-columns: 1fr;
            }

            .payment-toolbar {
                width: 100%;
            }

            .payment-add-select {
                flex: 1 1 100%;
                min-width: 100%;
            }

            .saved-search-shell {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
            }

            .saved-search-input {
                min-width: 100%;
                width: 100%;
            }

            .saved-date-input {
                min-width: 48%;
                width: 100%;
            }

            .saved-status-tabs {
                width: 100%;
                justify-content: flex-start;
            }

            .saved-search-meta {
                justify-self: start;
                white-space: normal;
            }

            .fishport-pagination {
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .mark-paid-preview {
                grid-template-columns: 1fr;
            }

            .fishport-toast {
                left: 12px;
                right: 12px;
                top: 12px;
                min-width: 0;
                max-width: none;
            }

            .saved-log-simple-table thead {
                display: none;
            }

            .saved-log-simple-table,
            .saved-log-simple-table tbody,
            .saved-log-simple-table tr,
            .saved-log-simple-table td {
                display: block;
                width: 100%;
            }

            .saved-log-simple-table tr {
                padding: 9px 10px;
                border-bottom: 1px solid #edf3fa;
            }

            .saved-log-simple-table td {
                border-bottom: 0;
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 12px;
                padding: 5px 0;
                text-align: right;
            }

            .saved-log-simple-table td::before {
                content: attr(data-label);
                text-align: left;
                color: #2c4a64;
                font-weight: 700;
                font-size: .75rem;
                letter-spacing: .03em;
                text-transform: uppercase;
                flex: 0 0 44%;
            }

            .saved-log-empty {
                text-align: left;
                color: #5f7991;
            }

            .saved-log-empty::before {
                content: none;
            }
        }
    </style>

    <div class="fishport-page" data-server-rendered-page="fishport_records" data-page-title="Fishport Data Management">
        <section class="fishport-hero">
            <h2>Fishport Data Management</h2>
            <p>Record vessel movement, commodity details, and payment computation in one readable transaction flow.</p>
        </section>

        @if (session('status'))
            <div id="fishportStatusToast" class="fishport-alert fishport-alert-success fishport-toast" role="status"
                aria-live="polite">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="fishport-alert fishport-alert-danger">{{ $errors->first() }}</div>
        @endif

        @if ($isEditing)
            <div class="fishport-alert fishport-alert-warning">
                <span>Editing <strong>{{ $editingLog->log_number }}</strong></span>
                <a href="{{ route('fishport.records') }}" class="fishport-btn fishport-btn-sm fishport-btn-outline">Cancel
                    Edit</a>
            </div>
        @endif

        <div class="fishport-tabs" role="tablist" aria-label="Fishport Tabs">
            <button type="button" class="fishport-tab is-active" data-fishport-tab-trigger="entry"
                aria-selected="true">Transaction Entry</button>
            <button type="button" class="fishport-tab" data-fishport-tab-trigger="saved" aria-selected="false">Saved
                Transactions <span class="fishport-tab-count">{{ $logs->count() }}</span></button>
        </div>

        <div class="fishport-tab-panel is-active" data-fishport-tab-panel="entry">
            <form id="fishportLogForm" action="{{ $formAction }}" method="POST" class="fishport-form-stack">
                @csrf
                @if ($isEditing)
                    @method('PUT')
                @endif
                <input type="hidden" name="source_log_id" id="sourceLogIdInput" value="{{ old('source_log_id') }}">
                <input type="hidden" name="items_payload" id="itemsPayloadInput" value="{{ old('items_payload', '[]') }}">
                <input type="hidden" name="payments_payload" id="paymentsPayloadInput"
                    value="{{ old('payments_payload', '[]') }}">
                <input type="hidden" name="print_receipt" id="printReceiptInput" value="0">
                <datalist id="commodityOptionsList">
                    @foreach ($commodities as $commodity)
                        <option value="{{ $commodity->name }}"></option>
                    @endforeach
                </datalist>

                <section class="fishport-panel">
                    <div class="fishport-panel-head">
                        <div>
                            <h3><i class="fa-solid fa-file-invoice"></i> 1. Log Header</h3>
                            <p class="fishport-panel-sub">Basic trip information before commodity and payment details.</p>
                        </div>
                    </div>
                    <div class="fishport-panel-body">
                        <div class="fishport-fields">
                            <div class="fishport-field full-width">
                                <label for="sourceLogSelect">Logged Vessel Entry (From Vessel Logs)</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-solid fa-list-check"></i>
                                    <select id="sourceLogSelect" class="fishport-select" @if($isEditing) disabled @endif>
                                        <option value="">Select logged vessel entry first</option>
                                        @foreach ($pendingLogs as $pendingLog)
                                            <option value="{{ $pendingLog['id'] }}"
                                                data-payment-number="{{ $pendingLog['payment_number'] ?? '' }}"
                                                data-log-number="{{ $pendingLog['log_number'] }}"
                                                data-log-date="{{ $pendingLog['log_date'] }}"
                                                data-log-time="{{ $pendingLog['log_time'] }}"
                                                data-arr-dep="{{ $pendingLog['arr_dep'] }}"
                                                data-vessel-id="{{ $pendingLog['vessel_id'] }}"
                                                data-vessel-name="{{ $pendingLog['vessel_name'] }}"
                                                data-origin-id="{{ $pendingLog['origin_id'] }}"
                                                data-remarks="{{ $pendingLog['remarks'] }}">
                                                {{ $pendingLog['log_number'] }} - {{ $pendingLog['vessel_name'] }} ({{ $pendingLog['arr_dep'] }}) {{ $pendingLog['log_date'] }} {{ $pendingLog['log_time'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="fishport-field-hint">
                                    You must pick a vessel from Vessel Logs first. Transaction will be linked to that exact Log ID.
                                </div>
                            </div>
                            @if (!$isEditing && ! $hasPendingLogs)
                                <div class="fishport-field full-width">
                                    <div class="fishport-alert fishport-alert-warning" style="margin:0;">
                                        <span>No pending vessel logs found. Please go to <strong>Vessel Logs</strong> and log a vessel first.</span>
                                        <a href="{{ route('fishport.vessel_logs') }}" class="fishport-btn fishport-btn-sm fishport-btn-outline">Open Vessel Logs</a>
                                    </div>
                                </div>
                            @endif
                            <div class="fishport-field">
                                <label for="paymentNumberPreview">Payment Number</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-solid fa-hashtag"></i>
                                    <input id="paymentNumberPreview" class="fishport-input"
                                        value="{{ $paymentNumberPreview }}" readonly>
                                </div>
                            </div>
                            <div class="fishport-field">
                                <label for="logNumberPreview">Linked Log Number</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-solid fa-link"></i>
                                    <input id="logNumberPreview" class="fishport-input"
                                        value="{{ $isEditing ? $editingLog->log_number : 'Select logged entry first' }}" readonly>
                                </div>
                            </div>
                            <div class="fishport-field">
                                <label for="logDate">Log Date</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-regular fa-calendar"></i>
                                    <input id="logDate" name="log_date" type="date" class="fishport-input"
                                        value="{{ old('log_date', $isEditing ? optional($editingLog->log_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                        required>
                                </div>
                            </div>
                            <div class="fishport-field">
                                <label for="logTime">Log Time</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-regular fa-clock"></i>
                                    <input id="logTime" name="log_time" type="time" class="fishport-input"
                                        value="{{ old('log_time', $isEditing ? substr((string) $editingLog->log_time, 0, 5) : now()->format('H:i')) }}"
                                        required>
                                </div>
                            </div>
                            <div class="fishport-field">
                                <label for="arrDep">ARR/DEP</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-solid fa-arrows-left-right"></i>
                                    <select id="arrDep" name="arr_dep" class="fishport-select" required>
                                        <option value="ARR" {{ old('arr_dep', $isEditing ? $editingLog->arr_dep : 'ARR') === 'ARR' ? 'selected' : '' }}>ARR</option>
                                        <option value="DEP" {{ old('arr_dep', $isEditing ? $editingLog->arr_dep : 'ARR') === 'DEP' ? 'selected' : '' }}>DEP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="fishport-field">
                                <label for="vesselNamePreview">Vessel (from selected log)</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-solid fa-ship"></i>
                                    <input id="vesselNamePreview" class="fishport-input" value="{{ $linkedVesselNamePreview }}" readonly>
                                </div>
                                <input id="vesselId" name="vessel_id" type="hidden" value="{{ old('vessel_id', $isEditing ? $editingLog->fishport_vessel_id : '') }}">
                            </div>
                            <div class="fishport-field">
                                <label for="originId">Origin</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <select id="originId" name="origin_id" class="fishport-select" required>
                                        <option value="">Select origin</option>
                                        @foreach ($origins as $origin)
                                            <option value="{{ $origin->id }}" {{ (string) old('origin_id', $isEditing ? $editingLog->fishport_origin_id : '') === (string) $origin->id ? 'selected' : '' }}>
                                                {{ $origin->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="fishport-field full-width">
                                <label for="remarks">Remarks</label>
                                <div class="fishport-icon-wrap">
                                    <i class="fa-regular fa-comment-dots"></i>
                                    <input id="remarks" name="remarks" class="fishport-input"
                                        value="{{ old('remarks', $isEditing ? $editingLog->remarks : '') }}"
                                        placeholder="Optional notes regarding the payload or weather conditions">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="fishport-panel">
                    <div class="fishport-panel-head">
                        <div>
                            <h3><i class="fa-solid fa-boxes-stacked"></i> 2. Commodity Entries</h3>
                            <p class="fishport-panel-sub">Add all commodity lines for this transaction. Volume updates
                                automatically.</p>
                        </div>
                        <button type="button" id="addCommodityRowBtn"
                            class="fishport-btn fishport-btn-sm fishport-btn-primary">Add Commodity</button>
                    </div>
                    <div class="fishport-table-wrap">
                        <table class="fishport-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Commodity</th>
                                    <th>Classification</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Conv.</th>
                                    <th>Volume</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="commodityRows"></tbody>
                        </table>
                    </div>
                    <div class="fishport-metrics">
                        <span class="fishport-chip">Qty <strong id="totalQuantityBadge">0.00</strong></span>
                        <span class="fishport-chip is-volume">Volume <strong id="totalVolumeBadge">0.00</strong></span>
                        <span class="fishport-chip is-ice">Ice Qty <strong id="iceQuantityBadge">0.00</strong></span>
                    </div>
                </section>

                <section class="fishport-panel">
                    <div class="fishport-panel-head">
                        <div>
                            <h3><i class="fa-solid fa-calculator"></i> 3. Auto Payment Breakdown</h3>
                            <p class="fishport-panel-sub">Fees are generated from ARR/DEP and commodity quantities, using
                                admin-controlled rates.</p>
                        </div>
                        <div class="payment-toolbar">
                            <select id="paymentTypeToAdd" class="fishport-select fishport-select-sm payment-add-select">
                                <option value="">Add payment item...</option>
                                @foreach ($manualPaymentTypes as $paymentType)
                                    <option value="{{ $paymentType->id }}">{{ $paymentType->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="addPaymentTypeBtn"
                                class="fishport-btn fishport-btn-sm fishport-btn-outline">Add Payment</button>
                        </div>
                    </div>
                    <div class="fishport-table-wrap">
                        <table class="fishport-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Fee</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="paymentRows"></tbody>
                        </table>
                    </div>
                    <div class="fishport-breakdown-foot">
                        <span>Auto-based on ARR/DEP and commodity inputs.</span>
                        <strong>Grand Total: PHP <span id="grandTotalValue">0.00</span></strong>
                    </div>
                </section>

                <div class="fishport-actions">
                    <button type="button" id="clearFormBtn" class="fishport-btn fishport-btn-muted">Reset Form</button>
                    <button type="button" id="openSaveActionBtn"
                        class="fishport-btn fishport-btn-primary"
                        @if (!$isEditing && ! $hasPendingLogs) disabled @endif>
                        {{ $isEditing ? 'Update Fishport Log' : 'Save Fishport Log' }}
                    </button>
                </div>
            </form>
        </div>

        <div id="fishportSavedPanel" class="fishport-tab-panel" data-fishport-tab-panel="saved">
            <section class="fishport-panel">
                <div class="fishport-panel-head">
                    <div>
                        <h3>Saved Fishport Logs</h3>
                        <p class="fishport-panel-sub">Fast cursor pagination with server-side filtering for large datasets.
                        </p>
                    </div>
                    <form method="GET" action="{{ route('fishport.records') }}" id="savedFiltersForm"
                        class="saved-search-shell">
                        <input type="hidden" name="saved_tab" value="saved">
                        <input type="hidden" name="saved_status" id="savedStatusInput" value="{{ $savedStatusFilter }}">
                        <input id="savedSearchInput" name="saved_search" type="text" value="{{ $savedSearchQuery }}"
                            class="fishport-input fishport-input-sm saved-search-input"
                            placeholder="Search payment no., log no., vessel, date, origin, ARR/DEP...">
                        <input id="savedDateFromInput" name="saved_from" type="date" value="{{ $savedFromDate }}"
                            class="fishport-input fishport-input-sm saved-date-input" title="From date">
                        <input id="savedDateToInput" name="saved_to" type="date" value="{{ $savedToDate }}"
                            class="fishport-input fishport-input-sm saved-date-input" title="To date">
                        <div class="saved-status-tabs" role="tablist" aria-label="Payment status filter">
                            <button type="button"
                                class="saved-status-tab {{ $savedStatusFilter === 'all' ? 'is-active' : '' }}"
                                data-server-status-filter="all"
                                aria-selected="{{ $savedStatusFilter === 'all' ? 'true' : 'false' }}">All <span
                                    id="savedStatusCountAll"
                                    class="saved-status-count">{{ $savedCounts['all'] ?? 0 }}</span></button>
                            <button type="button"
                                class="saved-status-tab {{ $savedStatusFilter === 'not_paid' ? 'is-active' : '' }}"
                                data-server-status-filter="not_paid"
                                aria-selected="{{ $savedStatusFilter === 'not_paid' ? 'true' : 'false' }}">Not Paid <span
                                    id="savedStatusCountNotPaid"
                                    class="saved-status-count">{{ $savedCounts['not_paid'] ?? 0 }}</span></button>
                            <button type="button"
                                class="saved-status-tab {{ $savedStatusFilter === 'paid' ? 'is-active' : '' }}"
                                data-server-status-filter="paid"
                                aria-selected="{{ $savedStatusFilter === 'paid' ? 'true' : 'false' }}">Paid <span
                                    id="savedStatusCountPaid"
                                    class="saved-status-count">{{ $savedCounts['paid'] ?? 0 }}</span></button>
                        </div>
                        <span id="savedSearchMeta" class="saved-search-meta">Showing {{ $logs->count() }} of
                            {{ $savedFilteredCount }}</span>
                    </form>
                </div>
                <div class="fishport-table-wrap">
                    <table class="fishport-table">
                        <thead>
                            <tr>
                                <th>Log No.</th>
                                <th>Payment No.</th>
                                <th>Date/Time</th>
                                <th>Vessel</th>
                                <th>ARR/DEP</th>
                                <th>Origin</th>
                                <th>Lines</th>
                                <th>Volume</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="savedLogRowsBody">
                            @forelse ($logs as $log)
                                @php
                                    $rowTotal = (float) $log->payments->sum('total');
                                    if ($rowTotal <= 0) {
                                        $rowTotal = (float) ($log->paymentRecord?->total_amount ?? 0);
                                    }
                                @endphp
                                <tr data-saved-log-row data-log-date="{{ optional($log->log_date)->format('Y-m-d') }}"
                                    data-paid="{{ $log->is_paid ? '1' : '0' }}">
                                    <td><strong>{{ $log->log_number }}</strong></td>
                                    <td>{{ $log->paymentRecord?->payment_number ?? '-' }}</td>
                                    <td>{{ optional($log->log_date)->format('m/d/Y') }}
                                        {{ substr((string) $log->log_time, 0, 5) }}
                                    </td>
                                    <td>{{ $log->vessel?->name ?? '-' }}</td>
                                    <td>{{ $log->arr_dep }}</td>
                                    <td>{{ $log->origin?->name ?? '-' }}</td>
                                    <td class="text-right">{{ $log->items->count() }}</td>
                                    <td class="text-right">{{ number_format((float) $log->items->sum('volume'), 2) }}</td>
                                    <td class="text-right">PHP {{ number_format($rowTotal, 2) }}</td>
                                    <td>
                                        <span
                                            class="payment-status-badge {{ $log->is_paid ? 'is-paid' : 'is-not-paid' }}">{{ $log->is_paid ? 'Paid' : 'Not Paid' }}</span>
                                    </td>
                                    <td>
                                        <div class="fishport-actions-cell">
                                            <button type="button"
                                                class="fishport-btn fishport-btn-sm fishport-btn-primary view-saved-log-btn"
                                                title="View transaction details"
                                                data-log-id="{{ $log->id }}">Details</button>
                                            <a href="{{ route('fishport.records.receipt', $log) }}" title="Save or Print Receipt"
                                                class="fishport-btn fishport-btn-sm fishport-btn-outline fishport-btn-icon"><i class="fa-solid fa-print"></i></a>
                                            <a href="{{ route('fishport.records.edit', $log) }}" title="Edit Transaction"
                                                class="fishport-btn fishport-btn-sm fishport-btn-outline fishport-btn-icon"><i class="fa-solid fa-pen"></i></a>
                                            @if (!$log->is_paid)
                                                <form action="{{ route('fishport.records.mark_paid', $log) }}" method="POST"
                                                    class="fishport-inline-form js-mark-paid-form"
                                                    data-log-number="{{ $log->log_number }}"
                                                    data-vessel="{{ $log->vessel?->name ?? '-' }}"
                                                    data-origin="{{ $log->origin?->name ?? '-' }}" data-arr-dep="{{ $log->arr_dep }}"
                                                    data-total="{{ number_format($rowTotal, 2, '.', '') }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="payer_name" class="js-payer-name-input" value="">
                                                    <button title="Mark as Paid" class="fishport-btn fishport-btn-sm fishport-btn-success fishport-btn-icon"><i class="fa-solid fa-check-double"></i></button>
                                                </form>
                                            @else
                                                <form action="{{ route('fishport.records.cancel_payment', $log) }}" method="POST"
                                                    class="fishport-inline-form js-cancel-payment-form"
                                                    data-log-number="{{ $log->log_number }}"
                                                    data-vessel="{{ $log->vessel?->name ?? '-' }}"
                                                    data-origin="{{ $log->origin?->name ?? '-' }}" data-arr-dep="{{ $log->arr_dep }}"
                                                    data-total="{{ number_format($rowTotal, 2, '.', '') }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button title="Cancel Payment / Mark Unpaid" class="fishport-btn fishport-btn-sm fishport-btn-warning fishport-btn-icon"><i class="fa-solid fa-rotate-left"></i></button>
                                                </form>
                                            @endif
                                            <form action="{{ route('fishport.records.destroy', $log) }}" method="POST"
                                                class="fishport-inline-form js-delete-log-form"
                                                data-log-number="{{ $log->log_number }}"
                                                data-vessel="{{ $log->vessel?->name ?? '-' }}"
                                                data-origin="{{ $log->origin?->name ?? '-' }}" data-arr-dep="{{ $log->arr_dep }}"
                                                data-total="{{ number_format($rowTotal, 2, '.', '') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button title="Delete Log" class="fishport-btn fishport-btn-sm fishport-btn-danger fishport-btn-icon"><i class="fa-regular fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="fishport-empty">No saved transactions match your current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($logs->hasPages())
                    <div class="fishport-pagination">
                        @if ($logs->previousPageUrl())
                            <a href="{{ $logs->previousPageUrl() }}" class="fishport-page-link">Previous</a>
                        @else
                            <span class="fishport-page-link is-disabled">Previous</span>
                        @endif
                        @if ($logs->nextPageUrl())
                            <a href="{{ $logs->nextPageUrl() }}" class="fishport-page-link">Next</a>
                        @else
                            <span class="fishport-page-link is-disabled">Next</span>
                        @endif
                    </div>
                @endif
            </section>
        </div>

        <div id="saveActionModal" class="save-action-modal" aria-hidden="true">
            <div class="save-action-card" role="dialog" aria-modal="true" aria-labelledby="saveActionTitle">
                <div class="save-action-head">
                    <h4 id="saveActionTitle">Choose Save Action</h4>
                </div>
                <div class="save-action-body">
                    <p class="save-action-desc">How do you want to proceed with this transaction?</p>
                    <button type="button" id="saveOnlyBtn" class="fishport-btn fishport-btn-primary">Save Only</button>
                    <button type="button" id="saveAndPrintBtn" class="fishport-btn fishport-btn-outline">Save And Print
                        Receipt</button>
                </div>
                <div class="save-action-foot">
                    <button type="button" id="cancelSaveActionBtn" class="fishport-btn fishport-btn-muted">Cancel</button>
                </div>
            </div>
        </div>

        <div id="markPaidModal" class="save-action-modal mark-paid-modal" aria-hidden="true">
            <div class="save-action-card mark-paid-card" role="dialog" aria-modal="true" aria-labelledby="markPaidTitle">
                <div class="save-action-head mark-paid-head">
                    <h4 id="markPaidTitle">Confirm Mark as Paid</h4>
                </div>
                <div class="save-action-body">
                    <p class="save-action-desc">Please confirm this fishport transaction is fully paid.</p>
                    <div class="mark-paid-preview">
                        <article>
                            <span>Log No.</span>
                            <strong id="markPaidLogNumber">-</strong>
                        </article>
                        <article>
                            <span>Vessel</span>
                            <strong id="markPaidVessel">-</strong>
                        </article>
                        <article>
                            <span>Route</span>
                            <strong id="markPaidRoute">-</strong>
                        </article>
                        <article>
                            <span>Total</span>
                            <strong id="markPaidTotal">PHP 0.00</strong>
                        </article>
                    </div>
                    <div class="mark-paid-payer-field">
                        <label for="markPaidPayerName">Name of Payer <span aria-hidden="true">*</span></label>
                        <input id="markPaidPayerName" type="text" class="fishport-input" maxlength="150"
                            placeholder="Enter the name of the person who paid this bill" autocomplete="name" required>
                        <div class="fishport-field-hint">Required before marking this transaction as paid.</div>
                    </div>
                </div>
                <div class="save-action-foot">
                    <button type="button" id="cancelMarkPaidBtn" class="fishport-btn fishport-btn-muted">Cancel</button>
                    <button type="button" id="confirmMarkPaidBtn" class="fishport-btn fishport-btn-primary">Yes, Mark as
                        Paid</button>
                </div>
            </div>
        </div>

        <div id="cancelPaymentModal" class="save-action-modal mark-paid-modal" aria-hidden="true">
            <div class="save-action-card mark-paid-card" role="dialog" aria-modal="true"
                aria-labelledby="cancelPaymentTitle">
                <div class="save-action-head cancel-payment-head">
                    <h4 id="cancelPaymentTitle">Confirm Cancel Payment</h4>
                </div>
                <div class="save-action-body">
                    <p class="save-action-desc">This will move the transaction back to <strong>Not Paid</strong>. Continue?
                    </p>
                    <div class="mark-paid-preview">
                        <article>
                            <span>Log No.</span>
                            <strong id="cancelPaymentLogNumber">-</strong>
                        </article>
                        <article>
                            <span>Vessel</span>
                            <strong id="cancelPaymentVessel">-</strong>
                        </article>
                        <article>
                            <span>Route</span>
                            <strong id="cancelPaymentRoute">-</strong>
                        </article>
                        <article>
                            <span>Total</span>
                            <strong id="cancelPaymentTotal">PHP 0.00</strong>
                        </article>
                    </div>
                </div>
                <div class="save-action-foot">
                    <button type="button" id="cancelCancelPaymentBtn" class="fishport-btn fishport-btn-muted">Keep as
                        Paid</button>
                    <button type="button" id="confirmCancelPaymentBtn" class="fishport-btn fishport-btn-danger">Yes, Cancel
                        Payment</button>
                </div>
            </div>
        </div>

        <div id="deleteLogModal" class="save-action-modal mark-paid-modal" aria-hidden="true">
            <div class="save-action-card mark-paid-card" role="dialog" aria-modal="true" aria-labelledby="deleteLogTitle">
                <div class="save-action-head delete-log-head">
                    <h4 id="deleteLogTitle">Confirm Delete Transaction</h4>
                </div>
                <div class="save-action-body">
                    <p class="save-action-desc">This action is permanent. Delete this fishport transaction now?</p>
                    <div class="mark-paid-preview">
                        <article>
                            <span>Log No.</span>
                            <strong id="deleteLogNumber">-</strong>
                        </article>
                        <article>
                            <span>Vessel</span>
                            <strong id="deleteLogVessel">-</strong>
                        </article>
                        <article>
                            <span>Route</span>
                            <strong id="deleteLogRoute">-</strong>
                        </article>
                        <article>
                            <span>Total</span>
                            <strong id="deleteLogTotal">PHP 0.00</strong>
                        </article>
                    </div>
                </div>
                <div class="save-action-foot">
                    <button type="button" id="cancelDeleteLogBtn" class="fishport-btn fishport-btn-muted">Cancel</button>
                    <button type="button" id="confirmDeleteLogBtn" class="fishport-btn fishport-btn-danger">Yes,
                        Delete</button>
                </div>
            </div>
        </div>

        <div id="savedLogViewModal" class="saved-log-view-modal" aria-hidden="true">
            <div class="saved-log-view-card" role="dialog" aria-modal="true" aria-labelledby="savedLogViewTitle">
                <div class="saved-log-view-head">
                    <div>
                        <h4 id="savedLogViewTitle">Saved Transaction</h4>
                        <p id="savedLogViewSub" class="save-action-desc">Review full commodity and payment details.</p>
                    </div>
                    <button type="button" id="closeSavedLogViewBtn" class="fishport-btn fishport-btn-muted">Close</button>
                </div>
                <div id="savedLogViewMeta" class="saved-log-view-grid"></div>
                <div class="saved-log-view-body">
                    <section class="saved-log-card">
                        <div class="saved-log-card-head">
                            <h5 class="saved-log-card-title">Commodity Entries</h5>
                            <strong id="savedLogItemsSummary">0 lines</strong>
                        </div>
                        <table class="saved-log-simple-table">
                            <thead>
                                <tr>
                                    <th>Commodity</th>
                                    <th>Classification</th>
                                    <th class="text-right">Qty</th>
                                    <th>Unit</th>
                                    <th class="text-right">Conv.</th>
                                    <th class="text-right">Volume</th>
                                </tr>
                            </thead>
                            <tbody id="savedLogItemsBody"></tbody>
                        </table>
                    </section>
                    <section class="saved-log-card">
                        <div class="saved-log-card-head">
                            <h5 class="saved-log-card-title">Payment Breakdown</h5>
                            <strong id="savedLogPaymentSummary">PHP 0.00</strong>
                        </div>
                        <table class="saved-log-simple-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-right">Fee</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody id="savedLogPaymentsBody"></tbody>
                        </table>
                    </section>
                    <section class="saved-log-card">
                        <div class="saved-log-card-head">
                            <h5 class="saved-log-card-title">Remarks</h5>
                        </div>
                        <div id="savedLogRemarksBody" class="saved-log-note">No remarks.</div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script id="fishportRecordsBootstrap" type="application/json">@json($clientState)</script>
    <script>
                            (() => {
                    const commodityRows = document.getElementById('commodityRows');
                    const paymentRows = document.getElementById('paymentRows');
                    const arrDepSelect = document.getElementById('arrDep');
                    const addBtn = document.getElementById('addCommodityRowBtn');
                    const addPaymentTypeBtn = document.getElementById('addPaymentTypeBtn');
                    const paymentTypeToAdd = document.getElementById('paymentTypeToAdd');
                    const openSaveActionBtn = document.getElementById('openSaveActionBtn');
                    const saveActionModal = document.getElementById('saveActionModal');
                    const saveOnlyBtn = document.getElementById('saveOnlyBtn');
                    const saveAndPrintBtn = document.getElementById('saveAndPrintBtn');
                    const cancelSaveActionBtn = document.getElementById('cancelSaveActionBtn');
                    const statusToast = document.getElementById('fishportStatusToast');
                    const markPaidModal = document.getElementById('markPaidModal');
                    const markPaidForms = Array.from(document.querySelectorAll('.js-mark-paid-form'));
                    const cancelMarkPaidBtn = document.getElementById('cancelMarkPaidBtn');
                    const confirmMarkPaidBtn = document.getElementById('confirmMarkPaidBtn');
                    const markPaidLogNumber = document.getElementById('markPaidLogNumber');
                    const markPaidVessel = document.getElementById('markPaidVessel');
                    const markPaidRoute = document.getElementById('markPaidRoute');
                    const markPaidTotal = document.getElementById('markPaidTotal');
                    const markPaidPayerName = document.getElementById('markPaidPayerName');
                    const cancelPaymentModal = document.getElementById('cancelPaymentModal');
                    const cancelPaymentForms = Array.from(document.querySelectorAll('.js-cancel-payment-form'));
                    const cancelCancelPaymentBtn = document.getElementById('cancelCancelPaymentBtn');
                    const confirmCancelPaymentBtn = document.getElementById('confirmCancelPaymentBtn');
                    const cancelPaymentLogNumber = document.getElementById('cancelPaymentLogNumber');
                    const cancelPaymentVessel = document.getElementById('cancelPaymentVessel');
                    const cancelPaymentRoute = document.getElementById('cancelPaymentRoute');
                    const cancelPaymentTotal = document.getElementById('cancelPaymentTotal');
                    const deleteLogModal = document.getElementById('deleteLogModal');
                    const deleteLogForms = Array.from(document.querySelectorAll('.js-delete-log-form'));
                    const cancelDeleteLogBtn = document.getElementById('cancelDeleteLogBtn');
                    const confirmDeleteLogBtn = document.getElementById('confirmDeleteLogBtn');
                    const deleteLogNumber = document.getElementById('deleteLogNumber');
                    const deleteLogVessel = document.getElementById('deleteLogVessel');
                    const deleteLogRoute = document.getElementById('deleteLogRoute');
                    const deleteLogTotal = document.getElementById('deleteLogTotal');
                    const clearBtn = document.getElementById('clearFormBtn');
                    const form = document.getElementById('fishportLogForm');
                    const sourceLogSelect = document.getElementById('sourceLogSelect');
                    const sourceLogIdInput = document.getElementById('sourceLogIdInput');
                    const paymentNumberPreview = document.getElementById('paymentNumberPreview');
                    const logNumberPreview = document.getElementById('logNumberPreview');
                    const vesselNamePreview = document.getElementById('vesselNamePreview');
                    const logDateInput = document.getElementById('logDate');
                    const logTimeInput = document.getElementById('logTime');
                    const vesselIdInput = document.getElementById('vesselId');
                    const originIdInput = document.getElementById('originId');
                    const remarksInput = document.getElementById('remarks');
                    const itemsPayload = document.getElementById('itemsPayloadInput');
                    const paymentsPayload = document.getElementById('paymentsPayloadInput');
                    const printReceiptInput = document.getElementById('printReceiptInput');
                    const totalQuantityBadge = document.getElementById('totalQuantityBadge');
                    const totalVolumeBadge = document.getElementById('totalVolumeBadge');
                    const iceQuantityBadge = document.getElementById('iceQuantityBadge');
                    const grandTotalValue = document.getElementById('grandTotalValue');
                    const bootstrapNode = document.getElementById('fishportRecordsBootstrap');
                    const bootstrap = bootstrapNode ? JSON.parse(bootstrapNode.textContent || '{}') : {};
                    const tabTriggers = Array.from(document.querySelectorAll('[data-fishport-tab-trigger]'));
                    const savedLogViewModal = document.getElementById('savedLogViewModal');
                    const closeSavedLogViewBtn = document.getElementById('closeSavedLogViewBtn');
                    const savedLogViewTitle = document.getElementById('savedLogViewTitle');
                    const savedLogViewSub = document.getElementById('savedLogViewSub');
                    const savedLogViewMeta = document.getElementById('savedLogViewMeta');
                    const savedLogItemsSummary = document.getElementById('savedLogItemsSummary');
                    const savedLogItemsBody = document.getElementById('savedLogItemsBody');
                    const savedLogPaymentSummary = document.getElementById('savedLogPaymentSummary');
                    const savedLogPaymentsBody = document.getElementById('savedLogPaymentsBody');
                    const savedLogRemarksBody = document.getElementById('savedLogRemarksBody');

                    const commodities = Array.isArray(bootstrap.commodities) ? bootstrap.commodities : [];
                    const units = Array.isArray(bootstrap.units) ? bootstrap.units : [];
                    const paymentTypes = Array.isArray(bootstrap.paymentTypes) ? bootstrap.paymentTypes : [];
                    const baseFees = bootstrap.baseFees && typeof bootstrap.baseFees === 'object' ? bootstrap.baseFees : {};
                    const oldItems = bootstrap.oldItems ?? null;
                    const oldPayments = bootstrap.oldPayments ?? null;
                    const editingItems = Array.isArray(bootstrap.editingItems) ? bootstrap.editingItems : [];
                    const editingPayments = Array.isArray(bootstrap.editingPayments) ? bootstrap.editingPayments : [];
                    const isEditing = Boolean(bootstrap.isEditing);
                    const hasStatus = Boolean(bootstrap.hasStatus);
                    const statusMessage = typeof bootstrap.statusMessage === 'string' ? bootstrap.statusMessage : '';
                    const savedHasFilters = Boolean(bootstrap.savedHasFilters);
                    const printReceiptData = bootstrap.printReceipt && typeof bootstrap.printReceipt === 'object' ? bootstrap.printReceipt : null;
                    let savedLogLookup = bootstrap.savedLogLookup && typeof bootstrap.savedLogLookup === 'object' ? bootstrap.savedLogLookup : {};
                    const pendingLogs = Array.isArray(bootstrap.pendingLogs) ? bootstrap.pendingLogs : [];
                    const oldSourceLogId = bootstrap.oldSourceLogId !== null && bootstrap.oldSourceLogId !== undefined
                        ? String(bootstrap.oldSourceLogId)
                        : '';
                    const hasPendingLogs = Boolean(bootstrap.hasPendingLogs);
                    const manualPaymentCodes = new Set(['ENTRANCE', 'DOCKING']);
                    const commodityAutoCodes = new Set(['UNLOADING', 'TRANSSHIPMENT', 'ICE_CONVEYANCE']);
                    const removedPaymentTypeIds = new Set();
                    const manualPaymentTypeIds = new Set();
                    const DRAFT_KEY = 'fishport_records_draft_v1';
                    const TAB_KEY = 'fishport_records_tab_v1';
                    let pendingMarkPaidForm = null;
                    let pendingCancelPaymentForm = null;
                    let pendingDeleteLogForm = null;
                    let isRestoringDraft = false;
                    let draftSaveTimer = null;
                    let savedFilterSubmitTimer = null;
                    let savedFilterRequestId = 0;
                    const storageAvailable = (() => {
                        try {
                            localStorage.setItem('__fishport_draft_test__', '1');
                            localStorage.removeItem('__fishport_draft_test__');
                            return true;
                        } catch {
                            return false;
                        }
                    })();

                    const toNum = (value) => Number.isFinite(Number(value)) ? Number(value) : 0;
                    const fmt = (value, d = 2) => toNum(value).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
                    const commodityById = (id) => commodities.find((item) => String(item.id) === String(id));
                    const commodityByName = (name) => commodities.find((item) => item.name.toLowerCase() === String(name || '').trim().toLowerCase());
                    const paymentTypeById = (id) => paymentTypes.find((item) => String(item.id) === String(id));
                    const typeByCode = (code) => paymentTypes.find((type) => type.code === code);
                    const pendingLogById = (id) => pendingLogs.find((item) => String(item.id) === String(id));
                    const parseArr = (value) => {
                        if (Array.isArray(value)) return value;
                        if (typeof value !== 'string' || value.trim() === '') return null;
                        try {
                            const parsed = JSON.parse(value);
                            return Array.isArray(parsed) ? parsed : null;
                        } catch {
                            return null;
                        }
                    };
                    const readDraft = () => {
                        if (!storageAvailable) return null;
                        try {
                            const raw = localStorage.getItem(DRAFT_KEY);
                            if (!raw) return null;
                            const parsed = JSON.parse(raw);
                            return parsed && typeof parsed === 'object' ? parsed : null;
                        } catch {
                            return null;
                        }
                    };
                    const writeDraft = (draft) => {
                        if (!storageAvailable) return;
                        try {
                            localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
                        } catch {
                            // Ignore local storage errors.
                        }
                    };
                    const clearDraft = () => {
                        if (!storageAvailable) return;
                        try {
                            localStorage.removeItem(DRAFT_KEY);
                        } catch {
                            // Ignore local storage errors.
                        }
                    };
                    const readTab = () => {
                        try {
                            return sessionStorage.getItem(TAB_KEY);
                        } catch {
                            return null;
                        }
                    };
                    const writeTab = (tab) => {
                        try {
                            sessionStorage.setItem(TAB_KEY, tab);
                        } catch {
                            // Ignore storage errors.
                        }
                    };

                    function setActiveFishportTab(tabId, persist = true) {
                        const normalizedTab = tabId === 'saved' ? 'saved' : 'entry';
                        const liveTabTriggers = Array.from(document.querySelectorAll('[data-fishport-tab-trigger]'));
                        const liveTabPanels = Array.from(document.querySelectorAll('[data-fishport-tab-panel]'));

                        liveTabTriggers.forEach((trigger) => {
                            const isActive = trigger.dataset.fishportTabTrigger === normalizedTab;
                            trigger.classList.toggle('is-active', isActive);
                            trigger.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        });
                        liveTabPanels.forEach((panel) => {
                            const isActive = panel.dataset.fishportTabPanel === normalizedTab;
                            panel.classList.toggle('is-active', isActive);
                        });

                        if (persist) writeTab(normalizedTab);
                    }

                    tabTriggers.forEach((trigger) => {
                        trigger.addEventListener('click', () => {
                            setActiveFishportTab(trigger.dataset.fishportTabTrigger || 'entry');
                        });
                    });

                    const savedFiltersAction = @json(route('fishport.records'));
                    const currentSavedNodes = () => {
                        const panel = document.getElementById('fishportSavedPanel');
                        const form = document.getElementById('savedFiltersForm');
                        return {
                            panel,
                            form,
                            statusInput: document.getElementById('savedStatusInput'),
                            searchInput: document.getElementById('savedSearchInput'),
                            dateFromInput: document.getElementById('savedDateFromInput'),
                            dateToInput: document.getElementById('savedDateToInput'),
                            statusTabs: Array.from(document.querySelectorAll('#fishportSavedPanel [data-server-status-filter]')),
                        };
                    };

                    const captureSavedFilterState = () => {
                        const { statusInput, searchInput, dateFromInput, dateToInput } = currentSavedNodes();
                        const active = document.activeElement;
                        const focusField = active === searchInput
                            ? 'search'
                            : (active === dateFromInput ? 'from' : (active === dateToInput ? 'to' : null));
                        return {
                            status: statusInput ? String(statusInput.value || 'all') : 'all',
                            search: searchInput ? String(searchInput.value || '') : '',
                            from: dateFromInput ? String(dateFromInput.value || '') : '',
                            to: dateToInput ? String(dateToInput.value || '') : '',
                            focusField,
                            start: focusField === 'search' && typeof active.selectionStart === 'number' ? active.selectionStart : null,
                            end: focusField === 'search' && typeof active.selectionEnd === 'number' ? active.selectionEnd : null,
                        };
                    };

                    const restoreSavedFilterState = (state) => {
                        if (!state) return;
                        const { statusInput, searchInput, dateFromInput, dateToInput, statusTabs } = currentSavedNodes();
                        if (statusInput) statusInput.value = state.status || 'all';
                        if (searchInput) searchInput.value = state.search || '';
                        if (dateFromInput) dateFromInput.value = state.from || '';
                        if (dateToInput) dateToInput.value = state.to || '';
                        statusTabs.forEach((tab) => {
                            const isActive = (tab.dataset.serverStatusFilter || 'all') === (state.status || 'all');
                            tab.classList.toggle('is-active', isActive);
                            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        });

                        let focusTarget = null;
                        if (state.focusField === 'search' && searchInput) focusTarget = searchInput;
                        if (state.focusField === 'from' && dateFromInput) focusTarget = dateFromInput;
                        if (state.focusField === 'to' && dateToInput) focusTarget = dateToInput;
                        if (!focusTarget) return;

                        focusTarget.focus({ preventScroll: true });
                        if (
                            focusTarget === searchInput &&
                            typeof state.start === 'number' &&
                            typeof state.end === 'number' &&
                            typeof focusTarget.setSelectionRange === 'function'
                        ) {
                            const len = focusTarget.value.length;
                            const start = Math.max(0, Math.min(state.start, len));
                            const end = Math.max(0, Math.min(state.end, len));
                            focusTarget.setSelectionRange(start, end);
                        }
                    };

                    const replaceSavedPanelFromHtml = (html) => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const incomingPanel = doc.getElementById('fishportSavedPanel');
                        const currentPanel = document.getElementById('fishportSavedPanel');
                        if (!incomingPanel || !currentPanel) return false;
                        currentPanel.replaceWith(incomingPanel);
                        const nextBootstrapNode = doc.getElementById('fishportRecordsBootstrap');
                        if (nextBootstrapNode) {
                            try {
                                const nextBootstrap = JSON.parse(nextBootstrapNode.textContent || '{}');
                                savedLogLookup = nextBootstrap.savedLogLookup && typeof nextBootstrap.savedLogLookup === 'object'
                                    ? nextBootstrap.savedLogLookup
                                    : {};
                            } catch {
                                savedLogLookup = {};
                            }
                        }
                        return true;
                    };

                    const requestSavedFilter = (query, delayMs = 0, state = null) => {
                        if (savedFilterSubmitTimer) {
                            window.clearTimeout(savedFilterSubmitTimer);
                            savedFilterSubmitTimer = null;
                        }

                        const run = () => {
                            const requestId = ++savedFilterRequestId;
                            const url = `${savedFiltersAction}?${query.toString()}`;
                            fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            })
                                .then((response) => response.text())
                                .then((html) => {
                                    if (requestId !== savedFilterRequestId) return;
                                    if (!replaceSavedPanelFromHtml(html)) {
                                        window.location.assign(url);
                                        return;
                                    }
                                    history.replaceState({}, '', url);
                                    restoreSavedFilterState(state);
                                    setActiveFishportTab('saved');
                                })
                                .catch(() => {
                                    window.location.assign(url);
                                });
                        };

                        if (delayMs > 0) {
                            savedFilterSubmitTimer = window.setTimeout(run, delayMs);
                            return;
                        }

                        run();
                    };

                    document.addEventListener('click', (event) => {
                        const statusTab = event.target.closest('#fishportSavedPanel [data-server-status-filter]');
                        if (statusTab) {
                            const { form, statusInput } = currentSavedNodes();
                            if (!form || !statusInput) return;
                            statusInput.value = statusTab.dataset.serverStatusFilter || 'all';
                            const query = new URLSearchParams(new FormData(form));
                            const state = captureSavedFilterState();
                            requestSavedFilter(query, 0, state);
                            return;
                        }

                        const viewButton = event.target.closest('#fishportSavedPanel .view-saved-log-btn');
                        if (viewButton) {
                            const logId = viewButton.getAttribute('data-log-id');
                            if (!logId) return;
                            openSavedLogView(logId);
                            return;
                        }

                        const paginationLink = event.target.closest('#fishportSavedPanel .fishport-pagination .fishport-page-link[href]');
                        if (!paginationLink) return;

                        event.preventDefault();
                        const href = paginationLink.getAttribute('href');
                        if (!href) return;
                        const state = captureSavedFilterState();
                        fetch(href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        })
                            .then((response) => response.text())
                            .then((html) => {
                                if (!replaceSavedPanelFromHtml(html)) {
                                    window.location.assign(href);
                                    return;
                                }
                                history.replaceState({}, '', href);
                                restoreSavedFilterState(state);
                                setActiveFishportTab('saved');
                            })
                            .catch(() => {
                                window.location.assign(href);
                            });
                    });

                    document.addEventListener('input', (event) => {
                        const { form, searchInput } = currentSavedNodes();
                        if (!form || !searchInput || event.target !== searchInput) return;
                        const query = new URLSearchParams(new FormData(form));
                        const state = captureSavedFilterState();
                        requestSavedFilter(query, 260, state);
                    });

                    document.addEventListener('change', (event) => {
                        const { form, dateFromInput, dateToInput } = currentSavedNodes();
                        if (!form) return;
                        if (event.target !== dateFromInput && event.target !== dateToInput) return;
                        const query = new URLSearchParams(new FormData(form));
                        const state = captureSavedFilterState();
                        requestSavedFilter(query, 0, state);
                    });

                    document.addEventListener('submit', (event) => {
                        const targetForm = event.target;
                        if (!(targetForm instanceof HTMLFormElement)) return;
                        if (targetForm.id !== 'savedFiltersForm') return;
                        event.preventDefault();
                        const query = new URLSearchParams(new FormData(targetForm));
                        const state = captureSavedFilterState();
                        requestSavedFilter(query, 0, state);
                    });

                    function openSaveActionModal() {
                        if (!saveActionModal) return;
                        saveActionModal.classList.add('is-open');
                        saveActionModal.setAttribute('aria-hidden', 'false');
                    }

                    function autoHideStatusToast() {
                        if (!statusToast) return;
                        window.setTimeout(() => {
                            statusToast.classList.add('is-hiding');
                            window.setTimeout(() => {
                                statusToast.remove();
                            }, 240);
                        }, 2000);
                    }

                    function closeSaveActionModal() {
                        if (!saveActionModal) return;
                        saveActionModal.classList.remove('is-open');
                        saveActionModal.setAttribute('aria-hidden', 'true');
                    }

                    function closeMarkPaidModal() {
                        if (!markPaidModal) return;
                        markPaidModal.classList.remove('is-open');
                        markPaidModal.setAttribute('aria-hidden', 'true');
                        if (markPaidPayerName) {
                            markPaidPayerName.value = '';
                        }
                        pendingMarkPaidForm = null;
                    }

                    function openMarkPaidModal(formElement) {
                        if (!markPaidModal || !formElement) return;
                        pendingMarkPaidForm = formElement;
                        const logNumber = String(formElement.dataset.logNumber || '-');
                        const vessel = String(formElement.dataset.vessel || '-');
                        const origin = String(formElement.dataset.origin || '-');
                        const arrDep = String(formElement.dataset.arrDep || '-');
                        const total = toNum(formElement.dataset.total || 0);

                        if (markPaidLogNumber) markPaidLogNumber.textContent = logNumber;
                        if (markPaidVessel) markPaidVessel.textContent = vessel;
                        if (markPaidRoute) markPaidRoute.textContent = `${origin} (${arrDep})`;
                        if (markPaidTotal) markPaidTotal.textContent = `PHP ${money(total)}`;
                        if (markPaidPayerName) {
                            markPaidPayerName.value = '';
                        }

                        markPaidModal.classList.add('is-open');
                        markPaidModal.setAttribute('aria-hidden', 'false');
                        if (markPaidPayerName) {
                            window.setTimeout(() => markPaidPayerName.focus(), 30);
                        }
                    }

                    function closeCancelPaymentModal() {
                        if (!cancelPaymentModal) return;
                        cancelPaymentModal.classList.remove('is-open');
                        cancelPaymentModal.setAttribute('aria-hidden', 'true');
                        pendingCancelPaymentForm = null;
                    }

                    function openCancelPaymentModal(formElement) {
                        if (!cancelPaymentModal || !formElement) return;
                        pendingCancelPaymentForm = formElement;
                        const logNumber = String(formElement.dataset.logNumber || '-');
                        const vessel = String(formElement.dataset.vessel || '-');
                        const origin = String(formElement.dataset.origin || '-');
                        const arrDep = String(formElement.dataset.arrDep || '-');
                        const total = toNum(formElement.dataset.total || 0);

                        if (cancelPaymentLogNumber) cancelPaymentLogNumber.textContent = logNumber;
                        if (cancelPaymentVessel) cancelPaymentVessel.textContent = vessel;
                        if (cancelPaymentRoute) cancelPaymentRoute.textContent = `${origin} (${arrDep})`;
                        if (cancelPaymentTotal) cancelPaymentTotal.textContent = `PHP ${money(total)}`;

                        cancelPaymentModal.classList.add('is-open');
                        cancelPaymentModal.setAttribute('aria-hidden', 'false');
                    }

                    function closeDeleteLogModal() {
                        if (!deleteLogModal) return;
                        deleteLogModal.classList.remove('is-open');
                        deleteLogModal.setAttribute('aria-hidden', 'true');
                        pendingDeleteLogForm = null;
                    }

                    function openDeleteLogModal(formElement) {
                        if (!deleteLogModal || !formElement) return;
                        pendingDeleteLogForm = formElement;
                        const logNumber = String(formElement.dataset.logNumber || '-');
                        const vessel = String(formElement.dataset.vessel || '-');
                        const origin = String(formElement.dataset.origin || '-');
                        const arrDep = String(formElement.dataset.arrDep || '-');
                        const total = toNum(formElement.dataset.total || 0);

                        if (deleteLogNumber) deleteLogNumber.textContent = logNumber;
                        if (deleteLogVessel) deleteLogVessel.textContent = vessel;
                        if (deleteLogRoute) deleteLogRoute.textContent = `${origin} (${arrDep})`;
                        if (deleteLogTotal) deleteLogTotal.textContent = `PHP ${money(total)}`;

                        deleteLogModal.classList.add('is-open');
                        deleteLogModal.setAttribute('aria-hidden', 'false');
                    }

                    function closeSavedLogViewModal() {
                        if (!savedLogViewModal) return;
                        savedLogViewModal.classList.remove('is-open');
                        savedLogViewModal.setAttribute('aria-hidden', 'true');
                    }

                    function renderSavedLogRows(log, mode) {
                        if (mode === 'payments') {
                            const payments = Array.isArray(log?.payments) ? log.payments : [];
                            if (payments.length === 0) {
                                return '<tr><td colspan="4" class="saved-log-empty">No payment entries.</td></tr>';
                            }

                            return payments.map((line) => `<tr>
                            <td data-label="Item">${esc(line.item || '-')}</td>
                            <td data-label="Fee" class="text-right">PHP ${money(line.fee)}</td>
                            <td data-label="Qty" class="text-right">${qty(line.quantity)}</td>
                            <td data-label="Total" class="text-right">PHP ${money(line.total)}</td>
                        </tr>`).join('');
                        }

                        const items = Array.isArray(log?.items) ? log.items : [];
                        if (items.length === 0) {
                            return '<tr><td colspan="6" class="saved-log-empty">No commodity entries.</td></tr>';
                        }

                        return items.map((line) => `<tr>
                        <td data-label="Commodity">${esc(line.commodity || '-')}</td>
                        <td data-label="Classification">${esc(line.classification || '-')}</td>
                        <td data-label="Qty" class="text-right">${qty(line.quantity)}</td>
                        <td data-label="Unit">${esc(line.unit || '-')}</td>
                        <td data-label="Conv." class="text-right">${fmt(line.conversion, 4)}</td>
                        <td data-label="Volume" class="text-right">${fmt(line.volume, 4)}</td>
                    </tr>`).join('');
                    }

                    function openSavedLogView(logId) {
                        const log = savedLogLookup[String(logId)];
                        if (!log || !savedLogViewModal) return;

                        if (savedLogViewTitle) {
                            savedLogViewTitle.textContent = `Saved Transaction ${log.payment_number || log.log_number || ''}`.trim();
                        }

                        if (savedLogViewSub) {
                            const logDateTime = `${log.log_date || '-'} ${log.log_time || ''}`.trim();
                            savedLogViewSub.textContent = `${logDateTime} • ${log.arr_dep || '-'} • ${log.vessel || '-'}`;
                        }

                        if (savedLogViewMeta) {
                            savedLogViewMeta.innerHTML = [
                                { label: 'Payment No', value: log.payment_number || '-' },
                                { label: 'Log No', value: log.log_number || '-' },
                                { label: 'Payment Status', value: log.paid_label || 'Not Paid' },
                                { label: 'Payer Name', value: log.payer_name || '-' },
                                { label: 'Paid At', value: log.paid_at || '-' },
                                { label: 'Origin', value: log.origin || '-' },
                                { label: 'Encoder', value: log.encoder || '-' },
                                { label: 'Commodity Lines', value: qty(log.line_count) },
                                { label: 'Total Volume', value: fmt(log.total_volume, 2) },
                                { label: 'Grand Total', value: `PHP ${money(log.grand_total)}` },
                            ].map((meta) => `<article class="saved-log-meta"><span>${esc(meta.label)}</span><strong>${esc(meta.value)}</strong></article>`).join('');
                        }

                        if (savedLogItemsSummary) {
                            savedLogItemsSummary.textContent = `${qty(log.line_count)} line(s) • Volume ${fmt(log.total_volume, 2)}`;
                        }

                        if (savedLogPaymentSummary) {
                            savedLogPaymentSummary.textContent = `PHP ${money(log.grand_total)}`;
                        }

                        if (savedLogItemsBody) {
                            savedLogItemsBody.innerHTML = renderSavedLogRows(log, 'items');
                        }

                        if (savedLogPaymentsBody) {
                            savedLogPaymentsBody.innerHTML = renderSavedLogRows(log, 'payments');
                        }

                        if (savedLogRemarksBody) {
                            savedLogRemarksBody.textContent = String(log.remarks || '').trim() || 'No remarks.';
                        }

                        savedLogViewModal.classList.add('is-open');
                        savedLogViewModal.setAttribute('aria-hidden', 'false');
                    }

                    function submitWithAction(shouldPrint) {
                        if (printReceiptInput) {
                            printReceiptInput.value = shouldPrint ? '1' : '0';
                        }
                        closeSaveActionModal();
                        form.requestSubmit();
                    }

                    function esc(value) {
                        return String(value ?? '')
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#39;');
                    }

                    function money(value) {
                        return toNum(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }

                    function qty(value) {
                        return toNum(value).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                    }

                    function buildReceiptHtml(data) {
                        const lines = Array.isArray(data?.charges) ? data.charges : [];
                        const lineRows = lines.length > 0
                            ? lines.map((line) => `<tr><td>${esc(line.item)}</td><td style="text-align:center;">${qty(line.qty)}</td><td style="text-align:right;">${money(line.total)}</td></tr>`).join('')
                            : `<tr><td colspan="3" style="text-align:center;">No charges</td></tr>`;

                        return `<!doctype html>
            <html>
            <head>
            <meta charset="utf-8">
            <title>${esc(data?.payment_number || data?.reference || 'Receipt')}</title>
            <style>
            body{font-family:"Courier New",monospace;background:#f5f5f5;margin:0;padding:20px;color:#111}
            .r{max-width:360px;margin:0 auto;background:#fff;padding:16px;border:1px solid #ddd}
            h1{font-size:34px;letter-spacing:.03em;text-align:center;margin:0 0 8px}
            .m{text-align:center;line-height:1.35;margin-bottom:10px}
            .hr{border-top:2px dashed #222;margin:10px 0}
            table{width:100%;border-collapse:collapse}
            th,td{padding:4px 0;font-size:27px}
            .s td{padding-top:8px}
            .t{font-size:44px;font-weight:700}
            @media print {body{background:#fff;padding:0}.r{border:none;max-width:none}}
            </style>
            </head>
            <body>
            <div class="r">
            <h1>${esc(data?.business_name || 'FISHPORT')}</h1>
            <div class="m">${esc(data?.address || '')}<br>TIN: ${esc(data?.tin || 'N/A')}</div>
            <div>Payment No: ${esc(data?.payment_number || data?.reference || '-')}</div>
            <div>Log No: ${esc(data?.log_number || '-')}</div>
            <div>Date: ${esc(data?.date || '-')}</div>
            <div>Handled By: ${esc(data?.cashier || '-')}</div>
            <div>Payer: ${esc(data?.payer_name || '-')}</div>
            <div>Vessel: ${esc(data?.vessel || '-')}</div>
            <div>Origin: ${esc(data?.origin || '-')} (${esc(data?.arr_dep || '-')})</div>
            <div class="hr"></div>
            <table>
            <thead><tr><th style="text-align:left;">Item</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Total</th></tr></thead>
            <tbody>${lineRows}</tbody>
            </table>
            <div class="hr"></div>
            <table class="s">
            <tr><td>Subtotal:</td><td style="text-align:right;">${money(data?.subtotal)}</td></tr>
            <tr><td class="t">Total Due:</td><td class="t" style="text-align:right;">${money(data?.total_due)}</td></tr>
            </table>
            <div class="hr"></div>
            <div class="m" style="margin-top:12px">Thank you!<br>Please come again.</div>
            </div>
            </body>
            </html>`;
                    }

                    function openAndPrintReceipt(data) {
                        if (!data || typeof data !== 'object') return;
                        const popup = window.open('', `fishport_receipt_${Date.now()}`, 'width=450,height=900');
                        if (!popup) {
                            alert('Saved successfully, but popup was blocked. Please allow popups to print receipt.');
                            return;
                        }
                        popup.document.open();
                        popup.document.write(buildReceiptHtml(data));
                        popup.document.close();
                        popup.focus();
                        setTimeout(() => {
                            popup.print();
                        }, 260);
                    }

                    function validateBeforeSaveAction() {
                        if (!isEditing) {
                            if (!hasPendingLogs) {
                                alert('No logged vessel entry is available yet. Please create one first in Vessel Logs.');
                                return false;
                            }

                            if (!sourceLogSelect || !sourceLogSelect.value) {
                                alert('Please select a logged vessel entry first.');
                                if (sourceLogSelect) sourceLogSelect.focus();
                                return false;
                            }
                        }

                        if (!form.checkValidity()) {
                            form.reportValidity();
                            return false;
                        }

                        const state = collectItems();
                        if (state.invalidCommodityRows.length > 0) {
                            alert(`Please select a valid commodity from the dropdown for row(s): ${state.invalidCommodityRows.join(', ')}`);
                            return false;
                        }

                        if (state.items.length === 0) {
                            alert('Please enter at least one commodity with quantity.');
                            return false;
                        }

                        const paymentState = collectPaymentStateOnly();
                        const generatedPayments = buildPayments(paymentState);
                        if (!Array.isArray(generatedPayments) || generatedPayments.length === 0) {
                            alert('No payment items generated. Please check ARR/DEP and commodity entries.');
                            return false;
                        }

                        return true;
                    }

                    function refreshNumbers() {
                        commodityRows.querySelectorAll('tr').forEach((row, i) => row.querySelector('.row-no').textContent = i + 1);
                    }

                    function addCommodityRow(initial = null) {
                        const defaultCommodity = commodityById(initial?.commodity_id) || commodityByName(initial?.commodity_name) || commodities[0];
                        if (!defaultCommodity) return;
                        const qty = toNum(initial?.quantity ?? 0);
                        const conv = toNum(initial?.unit_conversion ?? defaultCommodity.default_conversion);
                        const unitId = initial?.unit_id ?? defaultCommodity.default_unit_id ?? units[0]?.id;
                        const commodityName = String(initial?.commodity_name || defaultCommodity.name || '');
                        const classification = String(initial?.classification || defaultCommodity.classification || '');
                        const isClassManual = Boolean(initial?.class_manual) && String(initial?.classification || '').trim() !== '';
                        const row = document.createElement('tr');
                        row.dataset.classManual = isClassManual ? '1' : '0';
                        row.innerHTML = `<td class="row-no"></td><td><div class="commodity-picker"><input type="text" class="fishport-input fishport-input-sm commodity-name" list="commodityOptionsList" autocomplete="off" spellcheck="false" value="${commodityName}" placeholder="Type commodity"><select class="fishport-select fishport-select-sm commodity-id"><option value="">Select</option>${commodities.map(c => `<option value="${c.id}" ${String(c.id) === String(defaultCommodity.id) ? 'selected' : ''}>${c.name}</option>`).join('')}</select></div></td><td><input class="fishport-input fishport-input-sm class-name" value="${classification}" placeholder="Auto/Type classification"></td><td><input type="number" class="fishport-input fishport-input-sm qty" min="0" step="0.01" value="${qty}"></td><td><select class="fishport-select fishport-select-sm unit-id">${units.map(u => `<option value="${u.id}" ${String(u.id) === String(unitId) ? 'selected' : ''}>${u.name}</option>`).join('')}</select></td><td><input type="number" class="fishport-input fishport-input-sm conv" min="0" step="0.0001" value="${conv}"></td><td><input class="fishport-input fishport-input-sm vol text-right" readonly></td><td><button type="button" class="fishport-btn fishport-btn-sm fishport-btn-danger remove-row">Remove</button></td>`;
                        commodityRows.appendChild(row);
                        refreshNumbers();
                        recalc();
                    }

                    function resetSingleCommodityRow(row) {
                        if (!row) return;

                        const fallbackCommodity = commodities[0] || null;
                        const nameInput = row.querySelector('.commodity-name');
                        const idSelect = row.querySelector('.commodity-id');
                        const classInput = row.querySelector('.class-name');
                        const qtyInput = row.querySelector('.qty');
                        const unitSelect = row.querySelector('.unit-id');
                        const convInput = row.querySelector('.conv');
                        const volInput = row.querySelector('.vol');

                        row.dataset.classManual = '0';

                        if (fallbackCommodity) {
                            nameInput.value = fallbackCommodity.name;
                            idSelect.value = String(fallbackCommodity.id);
                            classInput.value = fallbackCommodity.classification || '';
                            if (fallbackCommodity.default_unit_id) unitSelect.value = String(fallbackCommodity.default_unit_id);
                            convInput.value = toNum(fallbackCommodity.default_conversion);
                        } else {
                            nameInput.value = '';
                            idSelect.value = '';
                            classInput.value = '';
                            convInput.value = 0;
                        }

                        qtyInput.value = 0;
                        volInput.value = fmt(0, 4);
                    }

                    function syncCommoditySelection(row, source = 'input', applyDefaults = false) {
                        const nameInput = row.querySelector('.commodity-name');
                        const idInput = row.querySelector('.commodity-id');
                        const unitSelect = row.querySelector('.unit-id');
                        const convInput = row.querySelector('.conv');
                        const classInput = row.querySelector('.class-name');
                        const commodity = source === 'select'
                            ? commodityById(idInput.value)
                            : commodityByName(nameInput.value);

                        if (!commodity) {
                            if (source === 'input' && !nameInput.value.trim()) idInput.value = '';
                            if (row.dataset.classManual !== '1') classInput.value = '';
                            return null;
                        }

                        idInput.value = String(commodity.id);
                        nameInput.value = commodity.name;
                        if (applyDefaults) {
                            classInput.value = commodity.classification || '';
                            row.dataset.classManual = '0';
                        } else if (row.dataset.classManual !== '1' || !String(classInput.value).trim()) {
                            classInput.value = commodity.classification || '';
                            row.dataset.classManual = '0';
                        }

                        if (applyDefaults && unitSelect && convInput) {
                            if (commodity.default_unit_id) unitSelect.value = String(commodity.default_unit_id);
                            convInput.value = toNum(commodity.default_conversion);
                        }

                        return commodity;
                    }

                    function collectItems() {
                        let totalQty = 0, totalVol = 0, iceQty = 0, marineQty = 0;
                        const items = [];
                        const invalidCommodityRows = [];
                        commodityRows.querySelectorAll('tr').forEach((row) => {
                            const commodityName = row.querySelector('.commodity-name').value;
                            let commodityId = row.querySelector('.commodity-id').value;
                            let commodity = commodityById(commodityId);
                            if (!commodity && commodityName) {
                                commodity = syncCommoditySelection(row, 'input', false);
                                commodityId = row.querySelector('.commodity-id').value;
                            }
                            const unitId = row.querySelector('.unit-id').value;
                            const qty = toNum(row.querySelector('.qty').value);
                            const conv = toNum(row.querySelector('.conv').value);
                            const vol = qty * conv;
                            row.querySelector('.vol').value = fmt(vol, 4);
                            totalQty += qty; totalVol += vol;
                            const typedClass = String(row.querySelector('.class-name').value || '').trim().toLowerCase();
                            const fallbackClass = String(commodity?.classification || '').trim().toLowerCase();
                            const classification = typedClass || fallbackClass;
                            if (classification.includes('ice')) iceQty += qty;
                            else if (classification.includes('marine')) marineQty += qty;
                            if (qty > 0) {
                                if (!commodity || !commodityId) {
                                    invalidCommodityRows.push(row.querySelector('.row-no').textContent || '?');
                                } else {
                                    items.push({ commodity_id: Number(commodityId), unit_id: Number(unitId), quantity: Number(qty.toFixed(2)), unit_conversion: Number(conv.toFixed(4)) });
                                }
                            }
                        });
                        return { items, totalQty, totalVol, iceQty, marineQty, invalidCommodityRows };
                    }

                    function collectPaymentStateOnly() {
                        let iceQty = 0;
                        let marineQty = 0;

                        commodityRows.querySelectorAll('tr').forEach((row) => {
                            const qty = toNum(row.querySelector('.qty')?.value);
                            if (qty <= 0) return;

                            const classInput = row.querySelector('.class-name');
                            const rawClass = String(classInput?.value || '').trim().toLowerCase();

                            if (rawClass.includes('ice')) iceQty += qty;
                            else if (rawClass.includes('marine')) marineQty += qty;
                        });

                        return { iceQty, marineQty };
                    }

                    function buildDraftPayload() {
                        const commodityDraftRows = Array.from(commodityRows.querySelectorAll('tr')).map((row) => ({
                            commodity_name: String(row.querySelector('.commodity-name')?.value || '').trim(),
                            commodity_id: Number(row.querySelector('.commodity-id')?.value || 0) || null,
                            classification: String(row.querySelector('.class-name')?.value || '').trim(),
                            class_manual: row.dataset.classManual === '1',
                            quantity: toNum(row.querySelector('.qty')?.value),
                            unit_id: Number(row.querySelector('.unit-id')?.value || 0) || null,
                            unit_conversion: toNum(row.querySelector('.conv')?.value),
                        }));

                        return {
                            header: {
                                source_log_id: sourceLogIdInput?.value || '',
                                log_date: logDateInput?.value || '',
                                log_time: logTimeInput?.value || '',
                                arr_dep: arrDepSelect?.value || 'ARR',
                                vessel_id: vesselIdInput?.value || '',
                                origin_id: originIdInput?.value || '',
                                remarks: remarksInput?.value || '',
                            },
                            commodities: commodityDraftRows,
                            payment_overrides: {
                                removed: Array.from(removedPaymentTypeIds),
                                manual: Array.from(manualPaymentTypeIds),
                            },
                            saved_at: new Date().toISOString(),
                        };
                    }

                    function queueDraftSave() {
                        if (!storageAvailable || isEditing || isRestoringDraft) return;
                        if (draftSaveTimer) clearTimeout(draftSaveTimer);
                        draftSaveTimer = setTimeout(() => {
                            writeDraft(buildDraftPayload());
                        }, 180);
                    }

                    function applyHeaderDraft(header) {
                        if (!header || typeof header !== 'object') return;
                        if (sourceLogIdInput && typeof header.source_log_id === 'string') sourceLogIdInput.value = header.source_log_id;
                        if (sourceLogSelect && typeof header.source_log_id === 'string') {
                            sourceLogSelect.value = header.source_log_id;
                            if (header.source_log_id !== '') {
                                const selectedOption = sourceLogSelect.selectedOptions[0] || null;
                                const sourceLog = readSourceLogFromOption(selectedOption) ?? pendingLogById(header.source_log_id) ?? null;
                                applySourceLogToHeader(sourceLog);
                            } else {
                                applySourceLogToHeader(null);
                            }
                        }
                        if (logDateInput && typeof header.log_date === 'string') logDateInput.value = header.log_date;
                        if (logTimeInput && typeof header.log_time === 'string') logTimeInput.value = header.log_time;
                        if (arrDepSelect && typeof header.arr_dep === 'string') arrDepSelect.value = header.arr_dep;
                        if (vesselIdInput && typeof header.vessel_id === 'string') vesselIdInput.value = header.vessel_id;
                        if (originIdInput && typeof header.origin_id === 'string') originIdInput.value = header.origin_id;
                        if (remarksInput && typeof header.remarks === 'string') remarksInput.value = header.remarks;
                    }

                    function applyDraft(draft) {
                        if (!draft || typeof draft !== 'object') return false;
                        isRestoringDraft = true;

                        applyHeaderDraft(draft.header);

                        removedPaymentTypeIds.clear();
                        manualPaymentTypeIds.clear();

                        const removed = Array.isArray(draft.payment_overrides?.removed) ? draft.payment_overrides.removed : [];
                        const manual = Array.isArray(draft.payment_overrides?.manual) ? draft.payment_overrides.manual : [];

                        removed.forEach((id) => {
                            const type = paymentTypeById(id);
                            if (!type || !manualPaymentCodes.has(type.code)) return;
                            removedPaymentTypeIds.add(String(type.id));
                        });

                        manual.forEach((id) => {
                            const type = paymentTypeById(id);
                            if (!type || !manualPaymentCodes.has(type.code)) return;
                            manualPaymentTypeIds.add(String(type.id));
                        });

                        commodityRows.innerHTML = '';
                        const commodityEntries = Array.isArray(draft.commodities) ? draft.commodities : [];
                        if (commodityEntries.length > 0) {
                            commodityEntries.forEach((entry) => addCommodityRow(entry));
                        } else {
                            addCommodityRow();
                        }

                        isRestoringDraft = false;
                        recalc();
                        return true;
                    }

                    function currentFees() {
                        const map = {};
                        paymentRows.querySelectorAll('tr').forEach((row) => { map[row.dataset.typeId] = toNum(row.querySelector('.fee').value); });
                        return map;
                    }

                    function readSourceLogFromOption(option) {
                        if (!option) return null;
                        const logId = Number(option.value || 0);
                        if (!logId) return null;
                        const vesselNameFromLabel = String(option.textContent || '').trim();
                        return {
                            id: logId,
                            payment_number: option.dataset.paymentNumber || '',
                            log_number: option.dataset.logNumber || '',
                            log_date: option.dataset.logDate || '',
                            log_time: option.dataset.logTime || '',
                            arr_dep: option.dataset.arrDep || 'ARR',
                            vessel_id: Number(option.dataset.vesselId || 0) || null,
                            vessel_name: String(option.dataset.vesselName || '').trim() || vesselNameFromLabel,
                            origin_id: Number(option.dataset.originId || 0) || null,
                            remarks: option.dataset.remarks || '',
                        };
                    }

                    function applySourceLogToHeader(sourceLog) {
                        if (!sourceLog) {
                            if (sourceLogIdInput) sourceLogIdInput.value = '';
                            if (logNumberPreview && !isEditing) logNumberPreview.value = 'Select logged entry first';
                            if (paymentNumberPreview && !isEditing) paymentNumberPreview.value = 'Auto-generated on save';
                            if (vesselNamePreview && !isEditing) vesselNamePreview.value = 'Select logged entry first';
                            return;
                        }

                        if (sourceLogIdInput) sourceLogIdInput.value = String(sourceLog.id);
                        if (logNumberPreview && !isEditing) logNumberPreview.value = sourceLog.log_number || 'Select logged entry first';
                        if (paymentNumberPreview && !isEditing) paymentNumberPreview.value = sourceLog.payment_number || 'Auto-generated on save';
                        if (vesselNamePreview && !isEditing) vesselNamePreview.value = sourceLog.vessel_name || 'Select logged entry first';
                        if (logDateInput && sourceLog.log_date) logDateInput.value = sourceLog.log_date;
                        if (logTimeInput && sourceLog.log_time) logTimeInput.value = sourceLog.log_time;
                        if (arrDepSelect && sourceLog.arr_dep) arrDepSelect.value = sourceLog.arr_dep;
                        if (vesselIdInput && sourceLog.vessel_id) vesselIdInput.value = String(sourceLog.vessel_id);
                        if (originIdInput && sourceLog.origin_id) originIdInput.value = String(sourceLog.origin_id);
                        if (remarksInput && sourceLog.remarks !== undefined && sourceLog.remarks !== null) remarksInput.value = sourceLog.remarks;
                    }

                    function sanitizePaymentOverrides() {
                        removedPaymentTypeIds.forEach((id) => {
                            const type = paymentTypes.find((item) => String(item.id) === String(id));
                            if (type && ['ENTRANCE', 'DOCKING'].includes(String(type.code || ''))) {
                                removedPaymentTypeIds.delete(id);
                                return;
                            }
                            if (!type || !manualPaymentCodes.has(String(type.code || ''))) {
                                removedPaymentTypeIds.delete(id);
                            }
                        });

                        manualPaymentTypeIds.forEach((id) => {
                            const type = paymentTypes.find((item) => String(item.id) === String(id));
                            if (type && ['ENTRANCE', 'DOCKING'].includes(String(type.code || ''))) {
                                manualPaymentTypeIds.delete(id);
                                return;
                            }
                            if (!type || !manualPaymentCodes.has(String(type.code || ''))) {
                                manualPaymentTypeIds.delete(id);
                            }
                        });
                    }

                    function restoreCommodityAutoFees() {
                        paymentTypes.forEach((type) => {
                            if (!commodityAutoCodes.has(String(type.code || ''))) return;
                            const typeId = String(type.id);
                            removedPaymentTypeIds.delete(typeId);
                            manualPaymentTypeIds.delete(typeId);
                        });
                    }

                    function buildPayments(state) {
                        sanitizePaymentOverrides();
                        const fees = currentFees();
                        const entries = [];
                        const add = (code, qty, label = null) => {
                            const type = typeByCode(code);
                            if (!type) return;
                            if (manualPaymentCodes.has(code) && removedPaymentTypeIds.has(String(type.id))) return;
                            const fee = fees[type.id] ?? baseFees[code] ?? type.fee ?? 0;
                            entries.push({
                                payment_type_id: Number(type.id),
                                code: type.code,
                                label: label || type.name,
                                fee: Number(fee),
                                quantity: Number(qty),
                                total: Number((fee * qty).toFixed(2)),
                                source: 'auto',
                            });
                        };
                        add('ENTRANCE', 1);
                        add('DOCKING', 1, 'Docking');
                        if (state.marineQty > 0) { add('UNLOADING', state.marineQty); add('TRANSSHIPMENT', state.marineQty); }
                        if (state.iceQty > 0) add('ICE_CONVEYANCE', state.iceQty);

                        manualPaymentTypeIds.forEach((typeId) => {
                            const numericTypeId = Number(typeId);
                            if (!numericTypeId) return;
                            if (entries.some((entry) => entry.payment_type_id === numericTypeId)) return;
                            const type = paymentTypes.find((item) => Number(item.id) === numericTypeId);
                            if (!type) return;
                            if (!manualPaymentCodes.has(type.code)) return;
                            if (removedPaymentTypeIds.has(String(numericTypeId))) return;
                            const fee = fees[type.id] ?? baseFees[type.code] ?? type.fee ?? 0;
                            entries.push({
                                payment_type_id: Number(type.id),
                                code: type.code,
                                label: type.name,
                                fee: Number(fee),
                                quantity: 1,
                                total: Number((fee * 1).toFixed(2)),
                                source: 'manual',
                            });
                        });

                        return entries;
                    }

                    function renderPayments(entries) {
                        paymentRows.innerHTML = '';
                        entries.forEach((entry, i) => {
                            const row = document.createElement('tr');
                            row.dataset.typeId = String(entry.payment_type_id);
                            row.dataset.code = entry.code || '';
                            const canRemove = manualPaymentCodes.has(String(entry.code || '')) && entry.source === 'manual';
                            row.innerHTML = `<td>${i + 1}</td><td>${entry.label}</td><td><div class="money-input"><span>PHP</span><input type="number" class="fishport-input fishport-input-sm fee" min="0" step="0.01" value="${entry.fee}" readonly></div></td><td><input class="fishport-input fishport-input-sm qty text-right" readonly value="${fmt(entry.quantity)}"></td><td class="line-total text-right">PHP ${fmt(entry.total)}</td><td>${canRemove ? '<button type="button" class="fishport-btn fishport-btn-sm fishport-btn-danger remove-payment">Remove</button>' : '<span class="text-muted">Auto</span>'}</td>`;
                            paymentRows.appendChild(row);
                        });
                    }

                    function applyInitialPaymentOverrides(initialPayments) {
                        const state = collectItems();
                        const autoPaymentEntries = buildPayments(state);
                        const autoCoreIds = new Set(
                            autoPaymentEntries
                                .filter((entry) => manualPaymentCodes.has(String(entry.code || '')))
                                .map((entry) => String(entry.payment_type_id))
                        );
                        const initialCoreIds = new Set(
                            initialPayments
                                .filter((entry) => {
                                    const type = paymentTypes.find((item) => Number(item.id) === Number(entry.payment_type_id));
                                    return manualPaymentCodes.has(String(type?.code || ''));
                                })
                                .map((entry) => String(entry.payment_type_id))
                        );

                        autoCoreIds.forEach((id) => {
                            if (!initialCoreIds.has(id)) removedPaymentTypeIds.add(id);
                        });

                        initialCoreIds.forEach((id) => {
                            if (!autoCoreIds.has(id)) manualPaymentTypeIds.add(id);
                        });
                    }

                    function recalcGrand() {
                        let total = 0;
                        paymentRows.querySelectorAll('tr').forEach((row) => {
                            const fee = toNum(row.querySelector('.fee').value);
                            const qty = toNum(row.querySelector('.qty').value);
                            const line = fee * qty;
                            row.querySelector('.line-total').textContent = `PHP ${fmt(line)}`;
                            total += line;
                        });
                        grandTotalValue.textContent = fmt(total);
                        return total;
                    }

                    function recalc() {
                        restoreCommodityAutoFees();
                        const state = collectItems();
                        totalQuantityBadge.textContent = fmt(state.totalQty);
                        totalVolumeBadge.textContent = fmt(state.totalVol);
                        iceQuantityBadge.textContent = fmt(state.iceQty);
                        renderPayments(buildPayments(state));
                        recalcGrand();
                        queueDraftSave();
                    }

                    function recalcPaymentsOnly() {
                        const paymentState = collectPaymentStateOnly();
                        renderPayments(buildPayments(paymentState));
                        recalcGrand();
                        queueDraftSave();
                    }

                    commodityRows.addEventListener('input', (e) => {
                        if (e.target.matches('.qty,.conv')) recalc();
                        if (e.target.matches('.commodity-name')) { syncCommoditySelection(e.target.closest('tr'), 'input', false); recalc(); }
                        if (e.target.matches('.class-name')) { e.target.closest('tr').dataset.classManual = '1'; recalc(); }
                    });
                    commodityRows.addEventListener('change', (e) => {
                        if (e.target.matches('.commodity-name')) { syncCommoditySelection(e.target.closest('tr'), 'input', true); recalc(); }
                        if (e.target.matches('.commodity-id')) { syncCommoditySelection(e.target.closest('tr'), 'select', true); recalc(); }
                        if (e.target.matches('.unit-id')) recalc();
                    });
                    commodityRows.addEventListener('click', (e) => {
                        if (!e.target.matches('.remove-row')) return;
                        const rows = commodityRows.querySelectorAll('tr');
                        if (rows.length > 1) {
                            e.target.closest('tr').remove();
                            refreshNumbers();
                            recalc();
                            return;
                        }

                        resetSingleCommodityRow(rows[0]);
                        refreshNumbers();
                        recalc();
                    });
                    markPaidForms.forEach((markPaidForm) => {
                        markPaidForm.addEventListener('submit', (event) => {
                            if (markPaidForm.dataset.confirmed === '1') {
                                markPaidForm.dataset.confirmed = '0';
                                return;
                            }

                            event.preventDefault();
                            openMarkPaidModal(markPaidForm);
                        });
                    });
                    cancelPaymentForms.forEach((cancelPaymentForm) => {
                        cancelPaymentForm.addEventListener('submit', (event) => {
                            if (cancelPaymentForm.dataset.confirmed === '1') {
                                cancelPaymentForm.dataset.confirmed = '0';
                                return;
                            }

                            event.preventDefault();
                            openCancelPaymentModal(cancelPaymentForm);
                        });
                    });
                    deleteLogForms.forEach((deleteForm) => {
                        deleteForm.addEventListener('submit', (event) => {
                            if (deleteForm.dataset.confirmed === '1') {
                                deleteForm.dataset.confirmed = '0';
                                return;
                            }

                            event.preventDefault();
                            openDeleteLogModal(deleteForm);
                        });
                    });
                    document.addEventListener('submit', (event) => {
                        if (event.defaultPrevented) return;
                        const targetForm = event.target;
                        if (!(targetForm instanceof HTMLFormElement)) return;

                        if (targetForm.classList.contains('js-mark-paid-form')) {
                            if (targetForm.dataset.confirmed === '1') {
                                targetForm.dataset.confirmed = '0';
                                return;
                            }
                            event.preventDefault();
                            openMarkPaidModal(targetForm);
                            return;
                        }

                        if (targetForm.classList.contains('js-cancel-payment-form')) {
                            if (targetForm.dataset.confirmed === '1') {
                                targetForm.dataset.confirmed = '0';
                                return;
                            }
                            event.preventDefault();
                            openCancelPaymentModal(targetForm);
                            return;
                        }

                        if (!targetForm.classList.contains('js-delete-log-form')) return;
                        if (targetForm.dataset.confirmed === '1') {
                            targetForm.dataset.confirmed = '0';
                            return;
                        }
                        event.preventDefault();
                        openDeleteLogModal(targetForm);
                    });
                    if (cancelMarkPaidBtn) {
                        cancelMarkPaidBtn.addEventListener('click', () => closeMarkPaidModal());
                    }
                    if (confirmMarkPaidBtn) {
                        confirmMarkPaidBtn.addEventListener('click', () => {
                            if (!pendingMarkPaidForm) return;
                            const payerName = String(markPaidPayerName?.value || '').trim();
                            if (payerName === '') {
                                alert('Please enter the Name of Payer before marking as paid.');
                                if (markPaidPayerName) markPaidPayerName.focus();
                                return;
                            }

                            const targetForm = pendingMarkPaidForm;
                            const payerInput = targetForm.querySelector('.js-payer-name-input');
                            if (payerInput) {
                                payerInput.value = payerName;
                            }
                            targetForm.dataset.confirmed = '1';
                            closeMarkPaidModal();
                            if (typeof targetForm.requestSubmit === 'function') {
                                targetForm.requestSubmit();
                                return;
                            }
                            targetForm.submit();
                        });
                    }
                    if (cancelCancelPaymentBtn) {
                        cancelCancelPaymentBtn.addEventListener('click', () => closeCancelPaymentModal());
                    }
                    if (confirmCancelPaymentBtn) {
                        confirmCancelPaymentBtn.addEventListener('click', () => {
                            if (!pendingCancelPaymentForm) return;
                            const targetForm = pendingCancelPaymentForm;
                            targetForm.dataset.confirmed = '1';
                            closeCancelPaymentModal();
                            if (typeof targetForm.requestSubmit === 'function') {
                                targetForm.requestSubmit();
                                return;
                            }
                            targetForm.submit();
                        });
                    }
                    if (cancelDeleteLogBtn) {
                        cancelDeleteLogBtn.addEventListener('click', () => closeDeleteLogModal());
                    }
                    if (confirmDeleteLogBtn) {
                        confirmDeleteLogBtn.addEventListener('click', () => {
                            if (!pendingDeleteLogForm) return;
                            const targetForm = pendingDeleteLogForm;
                            targetForm.dataset.confirmed = '1';
                            closeDeleteLogModal();
                            if (typeof targetForm.requestSubmit === 'function') {
                                targetForm.requestSubmit();
                                return;
                            }
                            targetForm.submit();
                        });
                    }
                    if (openSaveActionBtn) {
                        openSaveActionBtn.addEventListener('click', () => {
                            if (openSaveActionBtn.disabled) return;
                            if (!validateBeforeSaveAction()) return;
                            openSaveActionModal();
                        });
                    }
                    if (saveOnlyBtn) {
                        saveOnlyBtn.addEventListener('click', () => submitWithAction(false));
                    }
                    if (saveAndPrintBtn) {
                        saveAndPrintBtn.addEventListener('click', () => submitWithAction(true));
                    }
                    if (cancelSaveActionBtn) {
                        cancelSaveActionBtn.addEventListener('click', () => closeSaveActionModal());
                    }
                    if (saveActionModal) {
                        saveActionModal.addEventListener('click', (e) => {
                            if (e.target === saveActionModal) closeSaveActionModal();
                        });
                    }
                    if (markPaidModal) {
                        markPaidModal.addEventListener('click', (event) => {
                            if (event.target === markPaidModal) closeMarkPaidModal();
                        });
                    }
                    if (cancelPaymentModal) {
                        cancelPaymentModal.addEventListener('click', (event) => {
                            if (event.target === cancelPaymentModal) closeCancelPaymentModal();
                        });
                    }
                    if (deleteLogModal) {
                        deleteLogModal.addEventListener('click', (event) => {
                            if (event.target === deleteLogModal) closeDeleteLogModal();
                        });
                    }
                    if (closeSavedLogViewBtn) {
                        closeSavedLogViewBtn.addEventListener('click', () => closeSavedLogViewModal());
                    }
                    if (savedLogViewModal) {
                        savedLogViewModal.addEventListener('click', (event) => {
                            if (event.target === savedLogViewModal) closeSavedLogViewModal();
                        });
                    }
                    document.addEventListener('keydown', (e) => {
                        if (e.key !== 'Escape') return;
                        closeSaveActionModal();
                        closeMarkPaidModal();
                        closeCancelPaymentModal();
                        closeDeleteLogModal();
                        closeSavedLogViewModal();
                    });
                    paymentRows.addEventListener('click', (e) => {
                        if (!e.target.matches('.remove-payment')) return;
                        const row = e.target.closest('tr');
                        const typeId = row?.dataset.typeId;
                        const code = String(row?.dataset.code || '');
                        if (!typeId) return;
                        if (!manualPaymentCodes.has(code)) return;
                        removedPaymentTypeIds.add(String(typeId));
                        manualPaymentTypeIds.delete(String(typeId));
                        recalcPaymentsOnly();
                    });
                    addBtn.addEventListener('click', () => addCommodityRow());
                    addPaymentTypeBtn.addEventListener('click', () => {
                        const typeId = paymentTypeToAdd.value;
                        if (!typeId) return;
                        const type = paymentTypes.find((item) => String(item.id) === String(typeId));
                        if (!type || !manualPaymentCodes.has(type.code)) return;
                        removedPaymentTypeIds.delete(String(typeId));
                        manualPaymentTypeIds.add(String(typeId));
                        paymentTypeToAdd.value = '';
                        recalcPaymentsOnly();
                    });
                    arrDepSelect.addEventListener('change', () => recalcPaymentsOnly());
                    if (sourceLogSelect && !isEditing) {
                        sourceLogSelect.addEventListener('change', () => {
                            const selectedOption = sourceLogSelect.selectedOptions[0] || null;
                            const sourceLog = readSourceLogFromOption(selectedOption) ?? pendingLogById(sourceLogSelect.value) ?? null;
                            applySourceLogToHeader(sourceLog);
                            recalcPaymentsOnly();
                        });
                    }
                    [logDateInput, logTimeInput, remarksInput].forEach((input) => {
                        if (!input) return;
                        input.addEventListener('input', () => queueDraftSave());
                    });
                    [vesselIdInput, originIdInput].forEach((select) => {
                        if (!select) return;
                        select.addEventListener('change', () => queueDraftSave());
                    });
                    clearBtn.addEventListener('click', () => {
                        form.reset();
                        removedPaymentTypeIds.clear();
                        manualPaymentTypeIds.clear();
                        paymentTypeToAdd.value = '';
                        if (printReceiptInput) printReceiptInput.value = '0';
                        if (sourceLogSelect) sourceLogSelect.value = '';
                        if (sourceLogIdInput) sourceLogIdInput.value = '';
                        if (logNumberPreview && !isEditing) logNumberPreview.value = 'Select logged entry first';
                        if (paymentNumberPreview && !isEditing) paymentNumberPreview.value = 'Auto-generated on save';
                        if (vesselNamePreview && !isEditing) vesselNamePreview.value = 'Select logged entry first';
                        clearDraft();
                        commodityRows.innerHTML = '';
                        addCommodityRow();
                        recalc();
                    });

                    form.addEventListener('submit', (e) => {
                        if (sourceLogIdInput && sourceLogSelect && !isEditing) {
                            sourceLogIdInput.value = sourceLogSelect.value || '';
                        }
                        const state = collectItems();
                        if (state.invalidCommodityRows.length > 0) {
                            e.preventDefault();
                            alert(`Please select a valid commodity from the dropdown for row(s): ${state.invalidCommodityRows.join(', ')}`);
                            return;
                        }
                        if (state.items.length === 0) { e.preventDefault(); alert('Please enter at least one commodity with quantity.'); return; }
                        const payments = [];
                        paymentRows.querySelectorAll('tr').forEach((row) => {
                            const typeId = Number(row.dataset.typeId);
                            const fee = toNum(row.querySelector('.fee').value);
                            const qty = toNum(row.querySelector('.qty').value);
                            if (typeId) payments.push({ payment_type_id: typeId, fee: Number(fee.toFixed(2)), quantity: Number(qty.toFixed(2)) });
                        });
                        if (payments.length === 0) { e.preventDefault(); alert('No payment items generated.'); return; }
                        itemsPayload.value = JSON.stringify(state.items);
                        paymentsPayload.value = JSON.stringify(payments);
                    });

                    const savedTab = readTab();
                    const loweredStatus = statusMessage.toLowerCase();
                    const tabFromStatus = loweredStatus.includes('deleted') ? 'saved' : null;
                    const initialFishportTab = isEditing
                        ? 'entry'
                        : (tabFromStatus || (savedHasFilters ? 'saved' : (savedTab === 'saved' ? 'saved' : 'entry')));
                    setActiveFishportTab(initialFishportTab, false);
                    if (hasStatus) autoHideStatusToast();
                    if (printReceiptData) {
                        setTimeout(() => openAndPrintReceipt(printReceiptData), 350);
                    }

                    if (hasStatus) clearDraft();

                    const initialItems = parseArr(oldItems) ?? editingItems;
                    const initialPayments = parseArr(oldPayments) ?? editingPayments;
                    const hasServerInitialState = (Array.isArray(initialItems) && initialItems.length > 0)
                        || (Array.isArray(initialPayments) && initialPayments.length > 0)
                        || isEditing;

                    let draftApplied = false;
                    if (!hasStatus && !hasServerInitialState) {
                        const savedDraft = readDraft();
                        draftApplied = applyDraft(savedDraft);
                    }

                    if (!isEditing && sourceLogSelect) {
                        const initialSourceLogId = oldSourceLogId || sourceLogIdInput?.value || '';
                        if (initialSourceLogId !== '') {
                            sourceLogSelect.value = String(initialSourceLogId);
                            const selectedOption = sourceLogSelect.selectedOptions[0] || null;
                            const sourceLog = readSourceLogFromOption(selectedOption) ?? pendingLogById(initialSourceLogId) ?? null;
                            applySourceLogToHeader(sourceLog);
                        }
                    }

                    if (!draftApplied) {
                        if (Array.isArray(initialItems) && initialItems.length > 0) initialItems.forEach((item) => addCommodityRow(item)); else addCommodityRow();
                        if (Array.isArray(initialPayments) && initialPayments.length > 0) applyInitialPaymentOverrides(initialPayments);
                        recalc();
                    }
                })();
            </script>
@endsection
