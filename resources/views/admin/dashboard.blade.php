@extends('layouts.app')

@section('content')
    @php
        /** @var array<string, mixed> $filters */
        /** @var array<string, mixed> $summaryCards */
        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $departmentSummaries */
        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $recentTransactions */

        $money = static fn(float $value): string => 'PHP ' . number_format($value, 2);
        $growthClass = static fn(float $value): string => $value >= 0 ? 'is-up' : 'is-down';
        $growthIcon = static fn(float $value): string => $value >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
        $growthLabel = static fn(float $value): string => ($value >= 0 ? '+' : '') . number_format($value, 1) . '%';
        $queryParams = [
            'period' => $filters['period'],
            'start_date' => $filters['start_date_input'],
            'end_date' => $filters['end_date_input'],
        ];
        $pageTitle = match ($mode) {
            'all' => 'All Department Dashboards',
            'department' => ($selectedDepartmentConfig['name'] ?? 'Department') . ' Dashboard',
            default => 'Revenue Analytics Dashboard',
        };

    @endphp

    <style>
        .revenue-dashboard {
            --rd-ink: #0f172a;
            --rd-muted: #64748b;
            --rd-line: #e2e8f0;
            --rd-panel: #ffffff;
            --rd-soft: #f8fafc;
            --rd-primary: #2563eb;
            --rd-success: #10b981;
            --rd-danger: #ef4444;
            --rd-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            display: grid;
            gap: 20px;
        }

        .rd-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 56%, #eef6ff 100%);
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            flex-wrap: wrap;
        }

        .rd-header > div {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex-wrap: wrap;
        }

        .rd-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--rd-primary);
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 10px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 999px;
            margin: 0;
        }

        .rd-header h2 {
            margin: 0;
            color: var(--rd-ink);
            font-size: 1.05rem;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rd-header p {
            display: none;
        }

        .rd-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .rd-action-primary {
            background: #0f172a;
            color: #ffffff;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.12);
        }

        .rd-action-primary:hover {
            background: #1e293b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .rd-filter-panel,
        .rd-panel {
            background: linear-gradient(145deg, #ffffff, #fdfdff);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 18px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.02), 0 12px 30px -4px rgba(15, 23, 42, 0.04);
            transition: box-shadow 0.3s ease, transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .rd-panel:hover {
            box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.04), 0 20px 40px -8px rgba(15, 23, 42, 0.08);
        }

        .rd-filter-panel {
            padding: 16px;
            position: relative;
            overflow: hidden;
        }

        .rd-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
            gap: 12px;
            align-items: end;
        }

        .rd-field label {
            display: block;
            margin-bottom: 6px;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .rd-field input,
        .rd-field select {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            color: var(--rd-ink);
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .rd-field input:focus,
        .rd-field select:focus {
            outline: none;
            border-color: var(--rd-primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .rd-loading {
            display: none;
            position: absolute;
            inset: 0;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.76);
            color: var(--rd-primary);
            font-weight: 800;
            backdrop-filter: blur(2px);
            z-index: 2;
        }

        .rd-filter-panel.is-loading .rd-loading {
            display: flex;
        }

        .rd-summary-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
        }

        .rd-kpi {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 142px;
            padding: 16px;
            background: linear-gradient(145deg, #ffffff, #fdfdff);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.02), 0 12px 24px -4px rgba(15, 23, 42, 0.04);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .rd-kpi:hover {
            transform: translateY(-3px) scale(1.02);
            border-color: rgba(148, 163, 184, 0.3);
            box-shadow: 0 8px 12px -3px rgba(15, 23, 42, 0.04), 0 16px 32px -8px rgba(15, 23, 42, 0.08);
        }

        .rd-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }

        .rd-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: var(--rd-primary);
            background: #eff6ff;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        }

        .rd-kpi-label {
            color: var(--rd-muted);
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .rd-kpi-value {
            margin-top: 4px;
            color: var(--rd-ink);
            font-size: clamp(1.15rem, 1.3vw, 1.4rem);
            font-weight: 850;
            line-height: 1.15;
            overflow-wrap: anywhere;
            letter-spacing: -0.01em;
        }

        .rd-kpi-note {
            margin-top: 6px;
            color: var(--rd-muted);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .rd-delta {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .rd-delta.is-up {
            background: #dcfce7;
            color: #15803d;
        }

        .rd-delta.is-down {
            background: #fee2e2;
            color: #b91c1c;
        }

        .rd-chart-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(300px, 0.9fr);
            gap: 20px;
        }

        .rd-chart-grid .rd-panel {
            position: relative;
            overflow: hidden;
        }

        .rd-chart-grid .rd-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 50%, #2563eb 100%);
            opacity: 0.85;
        }

        .rd-chart-grid .rd-panel:nth-child(2)::before {
            background: linear-gradient(90deg, #14b8a6 0%, #2563eb 50%, #8b5cf6 100%);
        }

        .rd-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 22px 22px 0;
        }

        .rd-panel-title {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .rd-panel-title h3 {
            margin: 0;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--rd-ink);
            font-size: 1.15rem;
            font-weight: 850;
            letter-spacing: -0.01em;
        }

        .rd-panel-title h3::before {
            content: '';
            width: 4px;
            height: 18px;
            border-radius: 3px;
            background: linear-gradient(180deg, #2563eb, #60a5fa);
        }

        .rd-chart-grid .rd-panel:nth-child(2) .rd-panel-title h3::before {
            background: linear-gradient(180deg, #14b8a6, #8b5cf6);
        }

        .rd-panel-title p {
            margin: 0 0 0 14px;
            color: var(--rd-muted);
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .rd-chart-body {
            position: relative;
            height: 340px;
            padding: 18px 22px 22px;
        }

        .rd-chart-body.is-small {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rd-chart-body canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .rd-chart-grid .rd-panel:hover {
            transform: translateY(-2px);
        }

        .rd-donut-center {
            position: absolute;
            top: calc(50% - 18px);
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }

        .rd-donut-center small {
            display: block;
            color: var(--rd-muted);
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .rd-donut-center strong {
            display: block;
            margin-top: 2px;
            color: var(--rd-ink);
            font-size: 1.05rem;
            font-weight: 850;
            letter-spacing: -0.01em;
        }

        .rd-compare-panel {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid rgba(148, 163, 184, 0.24);
        }

        .rd-compare-glow {
            position: absolute;
            top: -120px;
            right: -120px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.18) 0%, rgba(37, 99, 235, 0) 70%);
            pointer-events: none;
        }

        .rd-compare-header {
            align-items: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .rd-compare-eyebrow {
            margin-bottom: 6px;
        }

        .rd-compare-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .rd-compare-chip {
            display: inline-flex;
            flex-direction: column;
            gap: 2px;
            padding: 8px 14px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.28);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
            min-width: 130px;
        }

        .rd-compare-chip span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--rd-muted);
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .rd-compare-chip strong {
            color: var(--rd-ink);
            font-size: 0.95rem;
            font-weight: 850;
        }

        .rd-compare-chip.is-total {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            border-color: transparent;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.28);
        }

        .rd-compare-chip.is-total span,
        .rd-compare-chip.is-total strong {
            color: #ffffff;
        }

        .rd-compare-chip.is-top {
            border-left: 3px solid var(--chip-color, var(--rd-primary));
        }

        .rd-compare-chip.is-top span i {
            color: #f59e0b;
        }

        .rd-compare-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 0 18px;
            margin-top: 14px;
            position: relative;
            z-index: 1;
        }

        .rd-compare-stat {
            padding: 10px 14px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .rd-compare-stat span {
            display: block;
            color: var(--rd-muted);
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .rd-compare-stat strong {
            display: block;
            margin-top: 4px;
            color: var(--rd-ink);
            font-size: 1.02rem;
            font-weight: 850;
        }

        .rd-compare-chart-body {
            height: 360px;
            padding: 18px;
            position: relative;
            z-index: 1;
        }

        .rd-compare-legend {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            padding: 0 18px 18px;
            position: relative;
            z-index: 1;
        }

        .rd-compare-legend-item {
            display: flex;
            flex-direction: column;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-left: 3px solid var(--lg-color, var(--rd-primary));
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .rd-compare-legend-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
        }

        .rd-compare-legend-head {
            display: grid;
            grid-template-columns: auto 1fr;
            grid-template-rows: auto auto;
            column-gap: 10px;
            row-gap: 4px;
            align-items: center;
        }

        .rd-compare-dot {
            grid-column: 1;
            grid-row: 1 / span 2;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--lg-color, var(--rd-primary));
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--lg-color, #2563eb) 18%, transparent);
            flex-shrink: 0;
            align-self: center;
        }

        .rd-compare-legend-name {
            grid-column: 2;
            grid-row: 1;
            color: var(--rd-muted);
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rd-compare-legend-value {
            grid-column: 2;
            grid-row: 2;
            color: var(--rd-ink);
            font-size: 1.05rem;
            font-weight: 850;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
        }

        .rd-compare-bar {
            margin-top: 12px;
            height: 8px;
            background: rgba(148, 163, 184, 0.18);
            border-radius: 999px;
            overflow: hidden;
        }

        .rd-compare-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--lg-color, var(--rd-primary)) 0%, color-mix(in srgb, var(--lg-color, #2563eb) 65%, white) 100%);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: width 0.6s ease;
            min-width: 4px;
            width: var(--share-width, 0%);
        }

        .rd-compare-legend-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            color: var(--rd-muted);
            font-size: 0.74rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 720px) {
            .rd-compare-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rd-compare-meta {
                width: 100%;
            }

            .rd-compare-chip {
                flex: 1;
            }
        }

        .rd-department-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        .rd-department-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
            min-height: 200px;
            padding: 20px;
            color: inherit;
            background: linear-gradient(145deg, #ffffff, #fdfdff);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 18px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.02), 0 12px 30px -4px rgba(15, 23, 42, 0.04);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .rd-department-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--dept-color, var(--rd-primary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .rd-department-card:hover {
            transform: translateY(-4px) scale(1.01);
            border-color: rgba(148, 163, 184, 0.3);
            box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.04), 0 20px 40px -8px rgba(15, 23, 42, 0.08);
        }

        .rd-department-card:hover::before {
            opacity: 1;
        }

        .rd-department-card.is-active {
            border-color: rgba(37, 99, 235, 0.5);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1), 0 14px 30px rgba(37, 99, 235, 0.12);
        }

        .rd-department-card.is-active::before {
            opacity: 1;
        }

        .rd-dept-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .rd-dept-head .rd-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            background: var(--dept-surface, #eff6ff);
            color: var(--dept-color, var(--rd-primary));
        }

        .rd-dept-title {
            color: var(--rd-ink);
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            margin-bottom: 4px;
        }

        .rd-dept-description {
            color: var(--rd-muted);
            font-size: 0.82rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .rd-dept-value {
            color: var(--rd-ink);
            font-size: 1.35rem;
            font-weight: 850;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .rd-dept-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: var(--rd-muted);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .rd-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .rd-section-title h3 {
            margin: 0;
            color: var(--rd-ink);
            font-size: 1.05rem;
            font-weight: 850;
        }

        .rd-department-detail {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(260px, 0.8fr);
            gap: 18px;
            padding: 18px;
        }

        .rd-mini-kpis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .rd-mini-kpi {
            padding: 13px;
            border-radius: 12px;
            background: var(--rd-soft);
            border: 1px solid #e5edf6;
        }

        .rd-mini-kpi span {
            display: block;
            color: var(--rd-muted);
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .rd-mini-kpi strong {
            display: block;
            margin-top: 5px;
            color: var(--rd-ink);
            font-size: 1rem;
        }

        .rd-insight-list {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }

        .rd-insight {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            color: #334155;
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .rd-insight-icon {
            margin-top: 2px;
            color: var(--dept-color, var(--rd-primary));
        }

        .rd-table-wrap {
            overflow-x: auto;
        }

        .rd-empty,
        .rd-error {
            padding: 28px;
            text-align: center;
            color: var(--rd-muted);
        }

        .rd-empty i,
        .rd-error i {
            display: block;
            margin-bottom: 10px;
            color: #94a3b8;
            font-size: 1.8rem;
        }

        .rd-muted-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            color: #1e3a8a;
            background: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.18);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .rd-muted-pill i {
            color: #2563eb;
            font-size: 0.78rem;
        }

        .rd-fade-in {
            animation: rdFadeIn 0.45s ease both;
        }

        @keyframes rdFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1400px) {
            .rd-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .rd-department-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 1050px) {

            .rd-header,
            .rd-chart-grid,
            .rd-department-detail {
                grid-template-columns: 1fr;
            }

            .rd-actions {
                justify-content: flex-start;
            }

            .rd-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .rd-header {
                padding: 18px;
            }

            .rd-filter-grid,
            .rd-summary-grid,
            .rd-department-grid,
            .rd-mini-kpis {
                grid-template-columns: 1fr;
            }

            .rd-chart-body {
                height: 260px;
            }
        }
    </style>

    <div class="revenue-dashboard rd-fade-in" data-server-rendered-page="dashboard"
        data-page-title="Admin Revenue Analytics" data-live-refresh-disabled>
        <section class="rd-header">
            <div>
                <span class="rd-eyebrow">
                    <i class="fas fa-chart-line"></i>
                    Revenue
                </span>
                <h2>{{ $pageTitle }}</h2>
            </div>
            <div class="rd-actions">
                <a href="{{ route('admin.dashboard.all', $queryParams) }}" class="btn rd-action-primary">
                    <i class="fas fa-table-cells-large"></i>
                    View All Dashboard
                </a>
            </div>
        </section>

        <section class="rd-filter-panel" id="revenueFilterPanel">
            <form method="GET" action="{{ url()->current() }}" class="rd-filter-grid" id="revenueFilterForm">
                <div class="rd-field">
                    <label for="period">Date Filter</label>
                    <select id="period" name="period" data-auto-submit>
                        @foreach ($filters['period_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rd-field" data-custom-field>
                    <label for="start_date">Start Date</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $filters['start_date_input'] }}">
                </div>
                <div class="rd-field" data-custom-field>
                    <label for="end_date">End Date</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $filters['end_date_input'] }}">
                </div>
                <div class="rd-field">
                    <label>Selected Range</label>
                    <input type="text" value="{{ $filters['range_label'] }}" readonly>
                </div>
                <button type="submit" class="btn btn-primary" style="min-height:42px;">
                    <i class="fas fa-rotate"></i>
                    Update
                </button>
            </form>
            <div class="rd-loading">
                <i class="fas fa-circle-notch fa-spin"></i>
                Updating analytics
            </div>
        </section>

        <section class="rd-summary-grid" aria-label="Revenue summary cards">
            <article class="rd-kpi">
                <div class="rd-kpi-top">
                    <span class="rd-icon"><i class="fas fa-coins"></i></span>
                    <span class="rd-muted-pill">{{ number_format((int) $summaryCards['transaction_count']) }} txns</span>
                </div>
                <div class="rd-kpi-label">Total Revenue</div>
                <div class="rd-kpi-value">{{ $money((float) $summaryCards['total_revenue']) }}</div>
                <div class="rd-kpi-note">{{ $filters['range_label'] }}</div>
            </article>

            <article class="rd-kpi">
                <div class="rd-kpi-top">
                    <span class="rd-icon"><i
                            class="fas {{ $growthIcon((float) $summaryCards['growth_percentage']) }}"></i></span>
                    <span class="rd-delta {{ $growthClass((float) $summaryCards['growth_percentage']) }}">
                        {{ $growthLabel((float) $summaryCards['growth_percentage']) }}
                    </span>
                </div>
                <div class="rd-kpi-label">Revenue Growth</div>
                <div class="rd-kpi-value">{{ $growthLabel((float) $summaryCards['growth_percentage']) }}</div>
                <div class="rd-kpi-note">Compared with previous period</div>
            </article>

            <article class="rd-kpi">
                <div class="rd-kpi-top">
                    <span class="rd-icon"><i class="fas fa-crown"></i></span>
                </div>
                <div class="rd-kpi-label">Best Department</div>
                <div class="rd-kpi-value">{{ $summaryCards['best_department']['name'] ?? 'N/A' }}</div>
                <div class="rd-kpi-note">{{ $money((float) ($summaryCards['best_department']['revenue'] ?? 0)) }}</div>
            </article>

            <article class="rd-kpi">
                <div class="rd-kpi-top">
                    <span class="rd-icon"><i class="fas fa-arrow-down-short-wide"></i></span>
                </div>
                <div class="rd-kpi-label">Lowest Department</div>
                <div class="rd-kpi-value">{{ $summaryCards['lowest_department']['name'] ?? 'N/A' }}</div>
                <div class="rd-kpi-note">{{ $money((float) ($summaryCards['lowest_department']['revenue'] ?? 0)) }}</div>
            </article>

            <article class="rd-kpi">
                <div class="rd-kpi-top">
                    <span class="rd-icon"><i class="fas fa-calendar-day"></i></span>
                </div>
                <div class="rd-kpi-label">Average Daily Revenue</div>
                <div class="rd-kpi-value">{{ $money((float) $summaryCards['average_daily_revenue']) }}</div>
                <div class="rd-kpi-note">Across {{ number_format((int) $filters['days']) }} day(s)</div>
            </article>

            <article class="rd-kpi">
                <div class="rd-kpi-top">
                    <span class="rd-icon"><i class="fas fa-building-user"></i></span>
                </div>
                <div class="rd-kpi-label">Active Departments</div>
                <div class="rd-kpi-value">{{ number_format((int) $summaryCards['active_departments']) }} / 5</div>
                <div class="rd-kpi-note">Departments with revenue</div>
            </article>
        </section>

        @unless ($hasAnyRevenueData)
            <section class="rd-panel">
                <div class="rd-empty">
                    <i class="fas fa-chart-simple"></i>
                    <strong>No revenue records found for this filter.</strong>
                    <div>Try a wider date range or confirm that department payments have been recorded.</div>
                </div>
            </section>
        @endunless

        <section class="rd-chart-grid">
            <article class="rd-panel">
                <div class="rd-panel-header">
                    <div class="rd-panel-title">
                        <h3>Revenue Trend</h3>
                        <p>Daily revenue movement in the selected range.</p>
                    </div>
                    <span class="rd-muted-pill"><i class="fas fa-calendar"></i>{{ $filters['period_label'] }}</span>
                </div>
                <div class="rd-chart-body">
                    <canvas id="revenueTrendChart" aria-label="Revenue trend over time"></canvas>
                </div>
            </article>

            <article class="rd-panel">
                <div class="rd-panel-header">
                    <div class="rd-panel-title">
                        <h3>Revenue Share</h3>
                        <p>Department contribution to total revenue.</p>
                    </div>
                </div>
                <div class="rd-chart-body is-small">
                    <canvas id="revenueShareChart" aria-label="Revenue share per department"></canvas>
                    @php
                        $shareTotal = (float) ($departmentSummaries->sum('revenue') ?? 0);
                    @endphp
                    <div class="rd-donut-center">
                        <small>Total</small>
                        <strong>{{ $money($shareTotal) }}</strong>
                    </div>
                </div>
            </article>
        </section>

        @php
            $deptCompare = $departmentSummaries->sortByDesc('revenue')->values();
            $deptCompareTotal = (float) $deptCompare->sum('revenue');
            $deptCompareTop = $deptCompare->first();
            $deptCompareLow = $deptCompare->reverse()->first();
            $deptCompareAvg = $deptCompare->count() ? $deptCompareTotal / $deptCompare->count() : 0.0;
        @endphp
        <section class="rd-panel rd-compare-panel">
            <div class="rd-compare-glow" aria-hidden="true"></div>
            <div class="rd-panel-header rd-compare-header">
                <div class="rd-panel-title">
                    <span class="rd-eyebrow rd-compare-eyebrow"><i class="fas fa-chart-column"></i>Performance
                        Snapshot</span>
                    <h3>Department Comparison</h3>
                    <p>Revenue across the five operating departments - sorted highest to lowest.</p>
                </div>
                <div class="rd-compare-meta">
                    <div class="rd-compare-chip is-total">
                        <span>Total</span>
                        <strong>{{ $money($deptCompareTotal) }}</strong>
                    </div>
                    @if ($deptCompareTop)
                        <div class="rd-compare-chip is-top" data-chip-color="{{ $deptCompareTop['color'] }}">
                            <span><i class="fas fa-crown"></i>Top performer</span>
                            <strong>{{ $deptCompareTop['name'] }}</strong>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rd-compare-stats">
                <div class="rd-compare-stat">
                    <span>Average</span>
                    <strong>{{ $money($deptCompareAvg) }}</strong>
                </div>
                <div class="rd-compare-stat">
                    <span>Highest</span>
                    <strong>{{ $deptCompareTop ? $money((float) $deptCompareTop['revenue']) : $money(0) }}</strong>
                </div>
                <div class="rd-compare-stat">
                    <span>Lowest</span>
                    <strong>{{ $deptCompareLow ? $money((float) $deptCompareLow['revenue']) : $money(0) }}</strong>
                </div>
                <div class="rd-compare-stat">
                    <span>Departments</span>
                    <strong>{{ $deptCompare->count() }}</strong>
                </div>
            </div>

            <div class="rd-chart-body rd-compare-chart-body">
                <canvas id="departmentBarChart" aria-label="Department revenue comparison"></canvas>
            </div>

            <div class="rd-compare-legend">
                @foreach ($deptCompare as $dept)
                    @php
                        $share = $deptCompareTotal > 0 ? ((float) $dept['revenue'] / $deptCompareTotal) * 100 : 0;
                    @endphp
                    <div class="rd-compare-legend-item" data-legend-color="{{ $dept['color'] }}">
                        <div class="rd-compare-legend-head">
                            <span class="rd-compare-dot"></span>
                            <span class="rd-compare-legend-name">{{ $dept['name'] }}</span>
                            <span class="rd-compare-legend-value">{{ $money((float) $dept['revenue']) }}</span>
                        </div>
                        <div class="rd-compare-bar">
                            <div class="rd-compare-bar-fill" data-share-width="{{ number_format($share, 2, '.', '') }}"></div>
                        </div>
                        <div class="rd-compare-legend-foot">
                            <span>{{ number_format($share, 1) }}% share</span>
                            <span class="rd-delta {{ $growthClass((float) $dept['growth_percentage']) }}">
                                <i class="fas {{ $growthIcon((float) $dept['growth_percentage']) }}"></i>
                                {{ $growthLabel((float) $dept['growth_percentage']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section>
            <div class="rd-section-title mb-4">
                <h3>Department Dashboards</h3>
                <span class="rd-muted-pill"><i class="fas fa-hand-pointer"></i>Click a department to drill down</span>
            </div>
            <div class="rd-department-grid">
                @foreach ($departmentSummaries as $department)
                    <a class="rd-department-card {{ $selectedDepartment === $department['code'] ? 'is-active' : '' }}"
                        href="{{ route('admin.dashboard.department', ['department' => $department['code']] + $queryParams) }}"
                        data-dept-color="{{ $department['color'] }}"
                        data-dept-surface="{{ $department['surface'] }}">
                        <div class="rd-dept-head">
                            <span class="rd-icon">
                                <i class="{{ $department['icon'] }}"></i>
                            </span>
                            <span class="rd-delta {{ $growthClass((float) $department['growth_percentage']) }}">
                                {{ $growthLabel((float) $department['growth_percentage']) }}
                            </span>
                        </div>
                        <div>
                            <div class="rd-dept-title">{{ $department['name'] }} Dashboard</div>
                            <div class="rd-dept-description">{{ $department['description'] }}</div>
                        </div>
                        <div>
                            <div class="rd-dept-value">{{ $money((float) $department['revenue']) }}</div>
                            <div class="rd-dept-footer">
                                <span>{{ number_format((int) $department['transaction_count']) }} transactions</span>
                                <span>{{ number_format((float) $department['share_percentage'], 1) }}% share</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        @if ($mode === 'department' && $selectedDepartment)
            @php $detailDepartments = $departmentSummaries->where('code', $selectedDepartment)->values(); @endphp
        @elseif ($mode === 'all')
            @php $detailDepartments = $departmentSummaries; @endphp
        @else
            @php $detailDepartments = collect(); @endphp
        @endif

        @foreach ($detailDepartments as $department)
            <section class="rd-panel" id="department-{{ $department['code'] }}" data-detail-color="{{ $department['color'] }}">
                <div class="rd-panel-header">
                    <div class="rd-panel-title">
                        <h3>{{ $department['name'] }} Revenue Dashboard</h3>
                        <p>{{ $department['description'] }}</p>
                    </div>
                    <a href="{{ route('admin.dashboard.department', ['department' => $department['code']] + $queryParams) }}"
                        class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-up-right-from-square"></i>
                        Open
                    </a>
                </div>
                <div class="rd-department-detail">
                    <div>
                        <div class="rd-mini-kpis">
                            <div class="rd-mini-kpi">
                                <span>Department Revenue</span>
                                <strong>{{ $money((float) $department['revenue']) }}</strong>
                            </div>
                            <div class="rd-mini-kpi">
                                <span>Growth</span>
                                <strong
                                    class="{{ (float) $department['growth_percentage'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $growthLabel((float) $department['growth_percentage']) }}
                                </strong>
                            </div>
                            <div class="rd-mini-kpi">
                                <span>Average Daily</span>
                                <strong>{{ $money((float) $department['average_daily_revenue']) }}</strong>
                            </div>
                        </div>
                        <div class="rd-chart-body is-small" style="padding:0;">
                            <canvas id="departmentTrendChart_{{ $department['code'] }}"
                                aria-label="{{ $department['name'] }} revenue trend"></canvas>
                        </div>
                    </div>
                    <aside>
                        <div class="rd-mini-kpis" style="grid-template-columns:1fr;">
                            <div class="rd-mini-kpi">
                                <span>Best Day</span>
                                <strong>{{ $department['best_day_label'] }}</strong>
                                <div class="rd-kpi-note">{{ $money((float) $department['best_day_revenue']) }}</div>
                            </div>
                            <div class="rd-mini-kpi">
                                <span>Latest Payment</span>
                                <strong>{{ $department['latest_payment_label'] }}</strong>
                            </div>
                        </div>
                        <div class="rd-insight-list">
                            @foreach ($department['insights'] as $insight)
                                <div class="rd-insight">
                                    <i class="fas fa-circle-info rd-insight-icon"></i>
                                    <span>{{ $insight }}</span>
                                </div>
                            @endforeach
                        </div>
                    </aside>
                </div>
            </section>
        @endforeach

        <section class="rd-panel">
            <div class="rd-panel-header">
                <div class="rd-panel-title">
                    <h3>Recent Revenue Activity</h3>
                    <p>Latest payment records inside the selected filter.</p>
                </div>
            </div>
            <div class="rd-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Revenue Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTransactions as $transaction)
                            <tr>
                                <td>
                                    <strong>{{ $transaction['department_name'] }}</strong>
                                </td>
                                <td>{{ $transaction['reference'] }}</td>
                                <td>{{ $money((float) $transaction['amount']) }}</td>
                                <td>
                                    {{ $transaction['occurred_at']->format('M d, Y h:i A') }}
                                    <div class="text-muted text-sm">{{ $transaction['occurred_at']->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="rd-empty" style="padding:20px;">
                                        <i class="fas fa-receipt"></i>
                                        No recent revenue activity for this filter.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script type="application/json" id="rdChartData">@json($chartData)</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('revenueFilterForm');
            const filterPanel = document.getElementById('revenueFilterPanel');
            const periodSelect = document.getElementById('period');
            const customFields = document.querySelectorAll('[data-custom-field] input');
            const chartDataNode = document.getElementById('rdChartData');
            let chartData = {};

            try {
                chartData = chartDataNode ? JSON.parse(chartDataNode.textContent || '{}') : {};
            } catch (error) {
                chartData = {};
            }

            chartData = {
                trend: {
                    labels: Array.isArray(chartData?.trend?.labels) ? chartData.trend.labels : [],
                    values: Array.isArray(chartData?.trend?.values) ? chartData.trend.values : [],
                },
                departmentBar: {
                    labels: Array.isArray(chartData?.departmentBar?.labels) ? chartData.departmentBar.labels : [],
                    values: Array.isArray(chartData?.departmentBar?.values) ? chartData.departmentBar.values : [],
                    colors: Array.isArray(chartData?.departmentBar?.colors) ? chartData.departmentBar.colors : [],
                },
                share: {
                    labels: Array.isArray(chartData?.share?.labels) ? chartData.share.labels : [],
                    values: Array.isArray(chartData?.share?.values) ? chartData.share.values : [],
                    colors: Array.isArray(chartData?.share?.colors) ? chartData.share.colors : [],
                },
                departmentTrends: chartData?.departmentTrends && typeof chartData.departmentTrends === 'object'
                    ? chartData.departmentTrends
                    : {},
            };

            document.querySelectorAll('[data-chip-color]').forEach(function (element) {
                element.style.setProperty('--chip-color', element.dataset.chipColor || '#2563eb');
            });

            document.querySelectorAll('[data-legend-color]').forEach(function (element) {
                element.style.setProperty('--lg-color', element.dataset.legendColor || '#2563eb');
            });

            document.querySelectorAll('[data-share-width]').forEach(function (element) {
                const value = Number.parseFloat(element.dataset.shareWidth || '0') || 0;
                element.style.setProperty('--share-width', value + '%');
            });

            document.querySelectorAll('[data-dept-color]').forEach(function (element) {
                element.style.setProperty('--dept-color', element.dataset.deptColor || '#2563eb');
                element.style.setProperty('--dept-surface', element.dataset.deptSurface || '#eff6ff');
            });

            document.querySelectorAll('[data-detail-color]').forEach(function (element) {
                element.style.setProperty('--dept-color', element.dataset.detailColor || '#2563eb');
            });

            function syncCustomFields() {
                const isCustom = periodSelect && periodSelect.value === 'custom';
                customFields.forEach(function (field) {
                    field.disabled = !isCustom;
                    field.closest('[data-custom-field]').style.opacity = isCustom ? '1' : '0.5';
                });
            }

            syncCustomFields();

            if (periodSelect) {
                periodSelect.addEventListener('change', function () {
                    syncCustomFields();
                    if (periodSelect.value !== 'custom' && filterForm) {
                        filterPanel.classList.add('is-loading');
                        filterForm.submit();
                    }
                });
            }

            if (filterForm) {
                filterForm.addEventListener('submit', function () {
                    filterPanel.classList.add('is-loading');
                });
            }

            if (typeof window.Chart === 'undefined') {
                const chartBodies = document.querySelectorAll('.rd-chart-body');
                chartBodies.forEach(function (body) {
                    body.innerHTML = '<div class="rd-error"><i class="fas fa-triangle-exclamation"></i>Charts could not load. Please check the network connection and refresh.</div>';
                });
                return;
            }

            const moneyTick = function (value) {
                return 'PHP ' + Number(value).toLocaleString();
            };

            const moneyTickCompact = function (value) {
                return 'PHP ' + Number(value).toLocaleString(undefined, {
                    notation: 'compact',
                    maximumFractionDigits: 1
                });
            };

            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': PHP ' + Number(context.parsed.y ?? context.parsed).toLocaleString();
                            }
                        }
                    }
                }
            };

            const trendCanvas = document.getElementById('revenueTrendChart');
            if (trendCanvas) {
                const trendCtx = trendCanvas.getContext('2d');
                const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 340);
                trendGradient.addColorStop(0, 'rgba(37, 99, 235, 0.32)');
                trendGradient.addColorStop(0.55, 'rgba(37, 99, 235, 0.08)');
                trendGradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: chartData.trend.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: chartData.trend.values,
                            borderColor: '#2563eb',
                            backgroundColor: trendGradient,
                            borderWidth: 2.5,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#2563eb',
                            pointBorderWidth: 2.5,
                            pointHoverBackgroundColor: '#2563eb',
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 3
                        }]
                    },
                    options: {
                        ...defaultOptions,
                        layout: { padding: { top: 14, right: 8, left: 0, bottom: 4 } },
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        plugins: {
                            ...defaultOptions.plugins,
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.94)',
                                titleColor: '#f8fafc',
                                bodyColor: '#e2e8f0',
                                padding: 12,
                                cornerRadius: 10,
                                displayColors: false,
                                titleFont: { weight: '700', size: 12 },
                                bodyFont: { weight: '600', size: 12 },
                                callbacks: {
                                    label: function (context) {
                                        return ' PHP ' + Number(context.parsed.y ?? context.parsed).toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: moneyTickCompact,
                                    color: '#94a3b8',
                                    font: { size: 11, weight: '600' },
                                    padding: 8
                                },
                                grid: { color: 'rgba(148, 163, 184, 0.14)', drawBorder: false }
                            },
                            x: {
                                grid: { display: false, drawBorder: false },
                                ticks: { color: '#94a3b8', font: { size: 11, weight: '600' }, maxRotation: 0, autoSkipPadding: 16 }
                            }
                        }
                    }
                });
            }

            const barCanvas = document.getElementById('departmentBarChart');
            if (barCanvas) {
                const ctx = barCanvas.getContext('2d');
                const baseColors = chartData.departmentBar.colors || [];
                const barValues = (chartData.departmentBar.values || []).map(function (value) {
                    return Number(value || 0);
                });
                const maxDepartmentValue = Math.max.apply(null, barValues.length ? barValues : [0]);
                const suggestedMax = maxDepartmentValue > 0
                    ? Math.ceil((maxDepartmentValue * 1.15) / 1000) * 1000
                    : 1000;

                const hexToRgba = function (hex, alpha) {
                    if (!hex || typeof hex !== 'string') return 'rgba(37, 99, 235, ' + alpha + ')';
                    const value = hex.replace('#', '');
                    const bigint = parseInt(value.length === 3
                        ? value.split('').map(function (c) { return c + c; }).join('')
                        : value, 16);
                    const r = (bigint >> 16) & 255;
                    const g = (bigint >> 8) & 255;
                    const b = bigint & 255;
                    return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
                };

                const buildGradient = function (color) {
                    const gradient = ctx.createLinearGradient(0, 0, 0, 360);
                    gradient.addColorStop(0, hexToRgba(color, 1));
                    gradient.addColorStop(1, hexToRgba(color, 0.55));
                    return gradient;
                };

                const valueLabelPlugin = {
                    id: 'rdValueLabel',
                    afterDatasetsDraw: function (chart) {
                        const { ctx: c } = chart;
                        const meta = chart.getDatasetMeta(0);
                        if (!meta) return;
                        c.save();
                        c.font = '700 11px "Inter", system-ui, sans-serif';
                        c.fillStyle = '#0f172a';
                        c.textAlign = 'left';
                        c.textBaseline = 'middle';
                        meta.data.forEach(function (bar, i) {
                            const value = chart.data.datasets[0].data[i];
                            if (value === null || value === undefined || Number(value) <= 0) return;
                            const formatted = Number(value) >= 1000
                                ? 'PHP ' + (Number(value) / 1000).toLocaleString(undefined, { maximumFractionDigits: 1 }) + 'k'
                                : 'PHP ' + Number(value).toLocaleString();
                            c.fillText(formatted, bar.x + 8, bar.y);
                        });
                        c.restore();
                    }
                };

                new Chart(barCanvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.departmentBar.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: chartData.departmentBar.values,
                            backgroundColor: baseColors.map(buildGradient),
                            hoverBackgroundColor: baseColors.map(function (c) { return hexToRgba(c, 0.92); }),
                            borderColor: baseColors,
                            borderWidth: 0,
                            borderRadius: 12,
                            borderSkipped: false,
                            maxBarThickness: 32,
                            minBarLength: 8,
                            barPercentage: 0.64,
                            categoryPercentage: 0.9
                        }]
                    },
                    options: {
                        ...defaultOptions,
                        indexAxis: 'y',
                        layout: { padding: { top: 8, right: 90, left: 2, bottom: 2 } },
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        plugins: {
                            ...defaultOptions.plugins,
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.94)',
                                titleColor: '#f8fafc',
                                bodyColor: '#e2e8f0',
                                padding: 12,
                                cornerRadius: 10,
                                displayColors: true,
                                boxPadding: 6,
                                callbacks: {
                                    label: function (context) {
                                        return ' Revenue: PHP ' + Number(context.parsed.y ?? context.parsed).toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                suggestedMax: suggestedMax,
                                ticks: { callback: moneyTickCompact, color: '#64748b', font: { size: 11, weight: '600' } },
                                grid: { color: 'rgba(148, 163, 184, 0.18)', drawBorder: false }
                            },
                            y: {
                                grid: { display: false, drawBorder: false },
                                ticks: { color: '#0f172a', font: { size: 12, weight: '700' } }
                            }
                        }
                    },
                    plugins: [valueLabelPlugin]
                });
            }

            const shareCanvas = document.getElementById('revenueShareChart');
            if (shareCanvas) {
                new Chart(shareCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.share.labels,
                        datasets: [{
                            data: chartData.share.values,
                            backgroundColor: chartData.share.colors,
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverBorderWidth: 4,
                            hoverOffset: 10,
                            spacing: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        radius: '92%',
                        layout: { padding: 8 },
                        animation: { animateRotate: true, animateScale: true, duration: 900, easing: 'easeOutQuart' },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    boxHeight: 8,
                                    padding: 14,
                                    color: '#475569',
                                    font: { size: 11, weight: '700' }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.94)',
                                titleColor: '#f8fafc',
                                bodyColor: '#e2e8f0',
                                padding: 12,
                                cornerRadius: 10,
                                displayColors: true,
                                boxPadding: 6,
                                callbacks: {
                                    label: function (context) {
                                        if (context.label === 'No revenue') {
                                            return 'No revenue in this range';
                                        }

                                        const dataset = context.dataset.data || [];
                                        const total = dataset.reduce(function (sum, v) { return sum + Number(v || 0); }, 0);
                                        const value = Number(context.parsed) || 0;
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                        return ' ' + context.label + ': PHP ' + value.toLocaleString() + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            Object.entries(chartData.departmentTrends || {}).forEach(function ([code, data]) {
                const canvas = document.getElementById('departmentTrendChart_' + code);
                if (!canvas) return;

                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: data.values,
                            borderColor: data.color,
                            backgroundColor: data.color + '22',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 2.5
                        }]
                    },
                    options: {
                        ...defaultOptions,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: moneyTick } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const appContainer = document.getElementById('appContainer');
            const authRoleKey = (appContainer?.dataset?.authRoleKey || '').toLowerCase();
            if (authRoleKey !== 'administrator') {
                return;
            }

            window.setInterval(function () {
                if (document.visibilityState === 'visible') {
                    window.location.reload();
                }
            }, 10000);
        });
    </script>
@endsection
