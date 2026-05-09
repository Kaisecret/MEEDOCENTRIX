<style>
    :root {
        --tm-primary: #0f5fa8;
        --tm-primary-deep: #0a4880;
        --tm-accent: #1a7fd4;
        --tm-surface: #ffffff;
        --tm-soft: #f8fafc;
        --tm-border: #e2e8f0;
        --tm-text: #334155;
        --tm-muted: #64748b;
        --tm-head: #0f172a;
        --tm-green: #047857;
        --tm-amber: #b45309;
        --tm-red: #b91c1c;
        --tm-blue: #1d4ed8;
    }

    .tm {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: var(--tm-text);
        display: grid;
        gap: 10px;
    }

    .tm-hero {
        background: transparent;
        color: var(--tm-head);
        border-radius: 0;
        padding: 10px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        box-shadow: none;
    }
    .tm-hero h2 { margin: 0 0 10px; font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; }
    .tm-hero p { margin: 0; font-size: .92rem; color: var(--tm-muted); max-width: 680px; }
    .tm-hero-meta { display: grid; justify-items: end; gap: 10px; }
    .tm-hero-clock { font-size: 1.42rem; font-weight: 800; letter-spacing: -.01em; color: var(--tm-head); }

    .tm-toast-stack {
        position: fixed;
        top: 14px;
        right: 14px;
        z-index: 3000;
        display: grid;
        gap: 8px;
        justify-items: end;
        pointer-events: none;
    }
    .tm-toast {
        position: relative;
        display: flex;
        align-items: stretch;
        gap: 8px;
        border-radius: 8px;
        padding: 0;
        border: 1px solid transparent;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.2);
        font-size: 0.8rem;
        line-height: 1.35;
        width: min(380px, calc(100vw - 28px));
        pointer-events: auto;
        animation: tmToastIn 0.2s ease both;
        overflow: hidden;
    }
    .tm-toast, .tm-toast * {
        pointer-events: auto;
    }
    .tm-toast-success {
        background: #0a8c8d;
        border-color: #0a7f80;
        color: #ffffff;
    }
    .tm-toast-error {
        background: #b91c1c;
        border-color: #991b1b;
        color: #ffffff;
    }
    .tm-toast-content {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        padding: 11px 12px 11px 12px;
    }
    .tm-toast-message {
        color: inherit;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0;
        white-space: normal;
        word-break: break-word;
    }
    .tm-toast-close {
        position: relative;
        z-index: 2;
        width: 36px;
        border: 0;
        border-left: 1px solid rgba(255, 255, 255, 0.16);
        background: transparent;
        color: #ffffff;
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        opacity: 0.92;
        transition: background 0.15s ease, opacity 0.15s ease;
    }
    .tm-toast-close:hover {
        background: rgba(255, 255, 255, 0.12);
        opacity: 1;
    }
    .tm-toast i.mx-3 {
        margin-right: 6px;
        font-size: 1rem;
        opacity: 0.98;
    }
    .tm-toast::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        width: 100%;
        background: #f59e0b;
        transform-origin: left;
        animation: tmToastTimer var(--tm-toast-duration, 3000ms) linear forwards;
        pointer-events: none;
    }
    .tm-toast.is-exit {
        animation: tmToastOut 0.18s ease forwards;
    }
    @keyframes tmToastIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes tmToastOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-6px); }
    }
    @keyframes tmToastTimer {
        from { transform: scaleX(1); }
        to { transform: scaleX(0); }
    }

    .tm-action-row { display: flex; gap: 8px; flex-wrap: wrap; }

    .tm-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; }
    .tm-kpi {
        border: 1px solid var(--tm-border);
        border-radius: 13px;
        background: var(--tm-surface);
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        padding: .95rem 1rem;
        display: grid;
        gap: 6px;
    }
    .tm-kpi-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .tm-kpi-title { font-size: .76rem; text-transform: uppercase; letter-spacing: .04em; color: var(--tm-muted); font-weight: 800; }
    .tm-kpi-icon { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: .98rem; }
    .tm-kpi-icon.purple { background: #eaf3fb; color: var(--tm-primary); }
    .tm-kpi-icon.blue { background: #eff6ff; color: var(--tm-blue); }
    .tm-kpi-icon.green { background: #ecfdf5; color: var(--tm-green); }
    .tm-kpi-icon.amber { background: #fffbeb; color: var(--tm-amber); }
    .tm-kpi-icon.red { background: #fef2f2; color: var(--tm-red); }
    .tm-kpi-value { font-size: 1.45rem; line-height: 1.05; letter-spacing: -.02em; color: var(--tm-head); font-weight: 800; }
    .tm-kpi-sub { font-size: .8rem; color: var(--tm-muted); }

    .tm-twin { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); gap: 12px; }
    .tm-card {
        border: 1px solid var(--tm-border);
        border-radius: 14px;
        background: var(--tm-surface);
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .tm-card-head {
        border-bottom: 1px solid var(--tm-border);
        padding: .9rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .tm-card-head h3 {
        margin: 0;
        color: var(--tm-head);
        font-size: 1rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .tm-card-head span { color: var(--tm-muted); font-size: .8rem; font-weight: 600; }
    .tm-card-body { padding: 1rem; }

    .tm-filter-bar { display: flex; flex-wrap: wrap; gap: 8px; padding: .9rem 1rem; border-top: 1px solid var(--tm-border); align-items: center; }
    .tm-input {
        min-height: 36px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        padding: 0 .72rem;
        font-size: .86rem;
        color: var(--tm-text);
    }
    .tm-input:focus { outline: none; border-color: var(--tm-primary); box-shadow: 0 0 0 3px rgba(15,95,168,.1); }
    .tm-input--grow { flex: 1; min-width: 220px; }
    .tm-help { font-size: .76rem; color: var(--tm-muted); }

    .tm-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
    .tm-field { display: grid; gap: 6px; }
    .tm-field.full { grid-column: 1 / -1; }
    .tm-field label { font-size: .8rem; font-weight: 700; color: var(--tm-head); }
    .tm-field textarea.tm-input { min-height: 84px; resize: vertical; padding: .55rem .72rem; }

    .tm-table-wrap { overflow: auto; }
    .tm-table { width: 100%; border-collapse: collapse; min-width: 720px; }
    .tm-table th {
        background: #eef5fb;
        color: #103250;
        border-bottom: 1px solid var(--tm-border);
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 800;
        padding: .78rem .95rem;
        text-align: left;
    }
    .tm-table td {
        border-bottom: 1px solid #f1f5f9;
        padding: .78rem .95rem;
        font-size: .87rem;
        color: var(--tm-text);
        vertical-align: middle;
    }
    .tm-table tbody tr:hover td { background: #f8fafc; }
    .tm-empty { text-align: center; color: var(--tm-muted); padding: 2rem 1rem; font-size: .9rem; }

    .tm-tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid transparent;
        padding: .18rem .56rem;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        font-weight: 800;
    }
    .tm-tag-parked { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .tm-tag-ready { background: #fffbeb; border-color: #fde68a; color: #b45309; }
    .tm-tag-paid { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .tm-tag-inactive { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .tm-tag-active { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }

    .tm-btn-primary,
    .tm-btn-outline,
    .tm-btn-danger {
        border-radius: 9px;
        padding: .55rem .95rem;
        font-size: .85rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid transparent;
        background: #fff;
    }
    .tm-btn-primary { background: var(--tm-primary, #0f5fa8); border-color: var(--tm-primary, #0f5fa8); color: #fff; }
    .tm-btn-primary:hover { background: var(--tm-primary-deep, #0a4880); }
    .tm-btn-outline { border-color: var(--tm-primary, #0f5fa8); color: var(--tm-primary, #0f5fa8); }
    .tm-btn-outline:hover { background: #f0f7ff; }
    .tm-btn-danger { border-color: #fecaca; color: #b91c1c; }
    .tm-btn-danger:hover { background: #fef2f2; }

    .tm-chart-wrap { position: relative; width: 100%; }
    .tm-chart-wrap canvas { width: 100% !important; display: block; }

    @media (max-width: 1100px) {
        .tm-kpi-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .tm-twin { grid-template-columns: 1fr; }
        .tm-form-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 680px) {
        .tm-kpi-grid { grid-template-columns: 1fr; }
        .tm-hero h2 { font-size: 1.35rem; }
        .tm-hero-meta { justify-items: start; }
    }
</style>
