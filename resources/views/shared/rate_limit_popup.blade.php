@php
    $rateLimitWarning = session('rate_limit_warning');
@endphp

@if (is_array($rateLimitWarning) && !empty($rateLimitWarning['message']))
    @once
        <style>
            .rl-modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.56);
                backdrop-filter: blur(2px);
                z-index: 5000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 14px;
            }
            .rl-modal-overlay.is-open { display: flex; }
            .rl-modal {
                width: min(460px, 96vw);
                border-radius: 14px;
                border: 1px solid #fecaca;
                background: #ffffff;
                box-shadow: 0 26px 50px rgba(2, 6, 23, 0.28);
                overflow: hidden;
            }
            .rl-modal-head {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 14px 16px;
                border-bottom: 1px solid #fee2e2;
                background: #fff1f2;
                color: #9f1239;
            }
            .rl-modal-head i {
                font-size: 1.05rem;
            }
            .rl-modal-head h4 {
                margin: 0;
                font-size: 1rem;
                font-weight: 800;
            }
            .rl-modal-body {
                padding: 14px 16px 10px;
                color: #334155;
            }
            .rl-modal-body p {
                margin: 0;
                font-size: 0.93rem;
                line-height: 1.45;
            }
            .rl-modal-retry {
                margin-top: 10px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 0.82rem;
                font-weight: 700;
                color: #9f1239;
                background: #fff1f2;
                border: 1px solid #fecdd3;
                border-radius: 999px;
                padding: 4px 10px;
            }
            .rl-modal-foot {
                padding: 10px 16px 14px;
                display: flex;
                justify-content: flex-end;
            }
            .rl-modal-btn {
                border: 1px solid #cbd5e1;
                border-radius: 10px;
                background: #ffffff;
                color: #0f172a;
                min-height: 38px;
                padding: 0 14px;
                font-size: 0.88rem;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.16s ease;
            }
            .rl-modal-btn:hover {
                background: #f8fafc;
                border-color: #94a3b8;
            }
        </style>
    @endonce

    <div id="rateLimitModalOverlay" class="rl-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="rateLimitModalTitle">
        <div class="rl-modal">
            <div class="rl-modal-head">
                <i class="fa-solid fa-shield-halved"></i>
                <h4 id="rateLimitModalTitle">{{ (string) ($rateLimitWarning['title'] ?? 'Too Many Requests') }}</h4>
            </div>
            <div class="rl-modal-body">
                <p>{{ (string) ($rateLimitWarning['message'] ?? 'Please wait a while and try again.') }}</p>
                @if (!empty($rateLimitWarning['retry_after']))
                    <div class="rl-modal-retry">
                        <i class="fa-regular fa-clock"></i>
                        Retry in about {{ (int) $rateLimitWarning['retry_after'] }} second(s)
                    </div>
                @endif
            </div>
            <div class="rl-modal-foot">
                <button type="button" class="rl-modal-btn" id="rateLimitModalCloseBtn">OK</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const overlay = document.getElementById('rateLimitModalOverlay');
            const closeBtn = document.getElementById('rateLimitModalCloseBtn');
            if (!overlay || !closeBtn) return;

            const open = () => overlay.classList.add('is-open');
            const close = () => overlay.classList.remove('is-open');

            closeBtn.addEventListener('click', close);
            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) close();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') close();
            });

            open();
        })();
    </script>
@endif
