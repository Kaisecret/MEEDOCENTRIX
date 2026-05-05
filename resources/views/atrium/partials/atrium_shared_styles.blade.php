<style>
    .atr {
        --atr-primary: var(--sidebar-bg, #155e8f);
        --atr-primary-deep: #124b73;
        --atr-accent: #3b93da;
        --atr-surface: #ffffff;
        --atr-soft: #f8fafc;
        --atr-border: #e2e8f0;
        --atr-text: #334155;
        --atr-muted: #64748b;
        --atr-head: #0f172a;
        --atr-green: #047857;
        --atr-amber: #b45309;
        --atr-red: #b91c1c;
        --atr-blue: #1d4ed8;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: var(--atr-text);
        display: grid;
        gap: 16px;
    }

    .atr-hero {
        background:
            linear-gradient(90deg, var(--atr-primary-deep) 0%, var(--atr-primary) 58%, var(--atr-accent) 100%);
        color: #fff; border-radius: 20px; padding: 1.35rem 1.45rem;
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 12px; box-shadow: 0 8px 22px rgba(21, 94, 143, .24);
    }
    .atr-hero h2 { margin: 0 0 .35rem; font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; }
    .atr-hero p { margin: 0; font-size: .92rem; color: rgba(255,255,255,.88); max-width: 680px; }
    .atr-hero-meta { display: grid; justify-items: end; gap: 2px; }

    .atr-flash {
        background: #f0fdf4; border: 1px solid #a7f3d0; color: #047857;
        padding: .7rem 1rem; border-radius: 10px; font-weight: 600; font-size: .88rem;
    }

    .atr-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; }
    .atr-kpi { border: 1px solid var(--atr-border); border-radius: 13px; background: var(--atr-surface); box-shadow: 0 1px 3px rgba(0,0,0,.04); padding: .95rem 1rem; display: grid; gap: 6px; }
    .atr-kpi-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .atr-kpi-title { font-size: .76rem; text-transform: uppercase; letter-spacing: .04em; color: var(--atr-muted); font-weight: 800; }
    .atr-kpi-icon { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: .98rem; }
    .atr-kpi-icon.purple { background: #eaf3fb; color: var(--atr-primary); }
    .atr-kpi-icon.blue { background: #eff6ff; color: var(--atr-blue); }
    .atr-kpi-icon.green { background: #ecfdf5; color: var(--atr-green); }
    .atr-kpi-icon.amber { background: #fffbeb; color: var(--atr-amber); }
    .atr-kpi-icon.red { background: #fef2f2; color: var(--atr-red); }
    .atr-kpi-value { font-size: 1.45rem; line-height: 1.05; letter-spacing: -.02em; color: var(--atr-head); font-weight: 800; }
    .atr-kpi-sub { font-size: .8rem; color: var(--atr-muted); }

    .atr-card { border: 1px solid var(--atr-border); border-radius: 14px; background: var(--atr-surface); box-shadow: 0 1px 3px rgba(0,0,0,.04); overflow: hidden; }
    .atr-card-head { border-bottom: 1px solid var(--atr-border); padding: .9rem 1rem; display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; }
    .atr-card-head h3 { margin: 0; color: var(--atr-head); font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; }
    .atr-card-head span { color: var(--atr-muted); font-size: .8rem; font-weight: 600; }
    .atr-card-body { padding: 1rem; }

    .atr-filter-bar { display: flex; flex-wrap: wrap; gap: 8px; padding: .9rem 1rem; border-top: 1px solid var(--atr-border); align-items: center; }
    .atr-input { min-height: 36px; border: 1px solid #cbd5e1; border-radius: 9px; background: #fff; padding: 0 .72rem; font-size: .86rem; color: var(--atr-text); }
    .atr-input:focus { outline: none; border-color: var(--atr-primary); box-shadow: 0 0 0 3px rgba(15,95,168,.1); }
    .atr-input--grow { flex: 1; min-width: 220px; }
    .atr-range-bar { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .atr-pill { border: 1px solid #cbd5e1; background: #fff; color: var(--atr-primary); border-radius: 999px; min-height: 35px; padding: 0 .92rem; font-size: .82rem; font-weight: 700; cursor: pointer; transition: all .18s ease; }
    .atr-pill:hover { background: #f0f7ff; }
    .atr-pill.is-active { background: var(--atr-primary); border-color: var(--atr-primary); color: #fff; box-shadow: 0 6px 16px rgba(15,95,168,.2); }
    .atr-range-fields { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .atr-range-fields[hidden] { display: none !important; }

    .atr-table-wrap { overflow: auto; }
    .atr-table { width: 100%; border-collapse: collapse; min-width: 720px; }
    .atr-table th { background: #eef5fb; color: #103250; border-bottom: 1px solid var(--atr-border); font-size: .76rem; text-transform: uppercase; letter-spacing: .04em; font-weight: 800; padding: .78rem .95rem; text-align: left; }
    .atr-table td { border-bottom: 1px solid #f1f5f9; padding: .78rem .95rem; font-size: .87rem; color: var(--atr-text); vertical-align: middle; }
    .atr-table tbody tr:hover td { background: #f8fafc; }

    .atr-tag { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; border: 1px solid transparent; padding: .18rem .56rem; font-size: .68rem; text-transform: uppercase; letter-spacing: .03em; font-weight: 800; }
    .atr-tag-reserved { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .atr-tag-confirmed { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .atr-tag-completed { background: #eaf3fb; border-color: #bfdbfe; color: #0a4880; }
    .atr-tag-cancelled { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .atr-tag-paid { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .atr-tag-partial { background: #fffbeb; border-color: #fde68a; color: #b45309; }
    .atr-tag-unpaid { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .atr-tag-pending { background: #fffbeb; border-color: #fde68a; color: #b45309; }
    .atr-tag-approved { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .atr-tag-fulfilled { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .atr-tag-rejected { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

    .atr-link { color: var(--atr-primary); text-decoration: none; font-weight: 700; font-size: .82rem; }
    .atr-link:hover { text-decoration: underline; }
    .atr-empty { text-align: center; color: var(--atr-muted); padding: 2rem 1rem; font-size: .9rem; }

    .atr-btn-primary { background: var(--atr-primary); border: 1px solid var(--atr-primary); color: #fff; border-radius: 9px; padding: .55rem .95rem; font-size: .85rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .atr-btn-primary:hover { background: var(--atr-primary-deep); }
    .atr-btn-outline { background: #fff; border: 1px solid var(--atr-primary); color: var(--atr-primary); border-radius: 9px; padding: .55rem .95rem; font-size: .85rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .atr-btn-outline:hover { background: #f0f7ff; }
    .atr-btn-danger { background: #fff; border: 1px solid #fecaca; color: #b91c1c; border-radius: 9px; padding: .55rem .95rem; font-size: .85rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .atr-btn-danger:hover { background: #fef2f2; }

    .atr-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 14px; padding: 1rem; }
    .atr-form-grid.atr-form-grid--wide { grid-template-columns: repeat(3, minmax(0,1fr)); }
    .atr-form-grid .full { grid-column: 1 / -1; }
    .atr-field { display: grid; gap: 6px; }
    .atr-field label { font-size: .8rem; font-weight: 700; color: var(--atr-head); }
    .atr-field .atr-help { font-size: .75rem; color: var(--atr-muted); }
    .atr-field textarea.atr-input { min-height: 90px; padding: .55rem .72rem; resize: vertical; }
    .atr-field .atr-err { color: var(--atr-red); font-size: .75rem; font-weight: 600; }

    .atr-addon-row { display: grid; grid-template-columns: 1fr 160px auto; gap: 8px; align-items: center; padding: .5rem 1rem; }
    .atr-addon-row .atr-btn-danger { padding: .4rem .7rem; }
    .atr-addon-head { display: flex; justify-content: space-between; padding: .8rem 1rem; border-top: 1px solid var(--atr-border); }

    .atr-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .48);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        z-index: 1800;
    }
    .atr-modal-backdrop.is-open { display: flex; }
    .atr-modal {
        width: min(1080px, 100%);
        max-height: calc(100vh - 2rem);
        border-radius: 16px;
        border: 1px solid var(--atr-border);
        background: #fff;
        box-shadow: 0 24px 60px rgba(2, 12, 27, .28);
        overflow: hidden;
        animation: atrModalIn .2s ease;
        display: grid;
        grid-template-rows: auto 1fr;
    }
    .atr-modal-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--atr-border);
        background: #f8fbff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .atr-modal-head h3 {
        margin: 0;
        color: var(--atr-head);
        font-size: 1.05rem;
        font-weight: 800;
        display: inline-flex;
        gap: 8px;
        align-items: center;
    }
    .atr-modal-head p {
        margin: .2rem 0 0;
        font-size: .82rem;
        color: var(--atr-muted);
    }
    .atr-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        border: 1px solid var(--atr-border);
        background: #fff;
        color: var(--atr-text);
        font-size: 1.1rem;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .atr-modal-close:hover { background: #f1f5f9; }
    .atr-modal-body {
        overflow: auto;
    }
    .atr-modal-form {
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }
    .atr-modal.atr-modal-wide { width: min(1080px, 100%); }
    .atr-modal.atr-modal-medium { width: min(860px, 100%); }
    .atr-modal.atr-modal-small { width: min(460px, 100%); }
    .atr-modal-error {
        margin: .9rem 1rem 0;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        border-radius: 10px;
        padding: .7rem .95rem;
        font-size: .86rem;
        font-weight: 600;
    }
    .atr-modal-error ul { margin: .4rem 0 0 1rem; }

    .atr-actions {
        display: inline-flex;
        gap: 6px;
        align-items: center;
    }
    .atr-icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid var(--atr-border);
        background: #fff;
        color: var(--atr-muted);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .16s ease;
    }
    .atr-icon-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 16px rgba(2, 6, 23, .08);
    }
    .atr-icon-btn.view:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .atr-icon-btn.edit:hover {
        border-color: #cfe7fb;
        background: #eaf3fb;
        color: var(--atr-primary);
    }
    .atr-icon-btn.delete:hover {
        border-color: #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .atr-view-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: .35rem;
    }
    .atr-view-item {
        border: 1px solid var(--atr-border);
        border-radius: 10px;
        background: #fff;
        padding: .7rem .8rem;
    }
    .atr-view-label {
        display: block;
        color: var(--atr-muted);
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: .22rem;
    }
    .atr-view-value {
        color: var(--atr-head);
        font-size: .9rem;
        font-weight: 700;
        line-height: 1.35;
        word-break: break-word;
    }
    .atr-view-item.full {
        grid-column: 1 / -1;
    }
    .atr-view-addon-list {
        margin: 0;
        padding-left: 1rem;
        color: var(--atr-text);
        font-size: .85rem;
        display: grid;
        gap: 4px;
    }
    .atr-view-empty {
        color: var(--atr-muted);
        font-size: .85rem;
        font-style: italic;
    }
    .atr-confirm-box {
        margin: .25rem 0 0;
        border: 1px solid #fecaca;
        background: #fff5f5;
        border-radius: 10px;
        padding: .7rem .8rem;
        color: #991b1b;
        font-size: .86rem;
        font-weight: 600;
    }

    @keyframes atrModalIn {
        from { opacity: 0; transform: translateY(8px) scale(.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @media (max-width: 1100px) {
        .atr-kpi-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .atr-form-grid, .atr-form-grid.atr-form-grid--wide { grid-template-columns: 1fr; }
    }
    @media (max-width: 680px) {
        .atr-kpi-grid { grid-template-columns: 1fr; }
        .atr-hero h2 { font-size: 1.35rem; }
        .atr-view-grid { grid-template-columns: 1fr; }
    }
</style>
