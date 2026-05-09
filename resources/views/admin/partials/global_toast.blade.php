@php
    $adminToasts = [];

    if (session('status')) {
        $adminToasts[] = ['type' => 'success', 'message' => (string) session('status')];
    }
    if (session('error')) {
        $adminToasts[] = ['type' => 'error', 'message' => (string) session('error')];
    }
    if ($errors->any()) {
        $adminToasts[] = ['type' => 'error', 'message' => (string) $errors->first()];
    }
@endphp

@once
    <style>
        .admin-toast-stack {
            position: fixed;
            top: 14px;
            right: 14px;
            z-index: 4000;
            display: grid;
            gap: 8px;
            justify-items: end;
            pointer-events: none;
        }
        .admin-toast {
            position: relative;
            display: flex;
            align-items: stretch;
            gap: 8px;
            border-radius: 8px;
            padding: 0;
            border: 1px solid transparent;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.2);
            font-size: 0.82rem;
            line-height: 1.35;
            width: min(400px, calc(100vw - 28px));
            pointer-events: auto;
            animation: adminToastIn 0.2s ease both;
            overflow: hidden;
            background: #0a8c8d;
            border-color: #0a7f80;
            color: #ffffff;
        }
        .admin-toast, .admin-toast * {
            pointer-events: auto;
        }
        .admin-toast.admin-toast-error {
            background: #b91c1c;
            border-color: #991b1b;
        }
        .admin-toast-content {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 11px 12px;
        }
        .admin-toast i.mx-3 {
            margin-right: 6px;
            font-size: 1rem;
            opacity: 0.98;
        }
        .admin-toast-message {
            color: inherit;
            font-weight: 700;
            font-size: 0.95rem;
            white-space: normal;
            word-break: break-word;
        }
        .admin-toast-close {
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
        .admin-toast-close:hover {
            background: rgba(255, 255, 255, 0.12);
            opacity: 1;
        }
        .admin-toast::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 100%;
            background: #f59e0b;
            transform-origin: left;
            animation: adminToastTimer var(--admin-toast-duration, 3000ms) linear forwards;
            pointer-events: none;
        }
        .admin-toast.is-exit {
            animation: adminToastOut 0.18s ease forwards;
        }
        @keyframes adminToastIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes adminToastOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-6px); }
        }
        @keyframes adminToastTimer {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }
    </style>

    <script>
        (function () {
            if (!window.Toaster) {
                window.Toaster = {};
            }

            if (!window.Toaster.ToastManager) {
                window.Toaster.ToastManager = class ToastManager {
                    constructor() {
                        this.container = document.querySelector('.admin-toast-stack');
                        if (!this.container) {
                            this.container = document.createElement('div');
                            this.container.className = 'admin-toast-stack';
                            this.container.setAttribute('role', 'status');
                            this.container.setAttribute('aria-live', 'polite');
                            document.body.appendChild(this.container);
                        }
                    }

                    addToast(message, type, options) {
                        const resolvedType = String(type || '').toLowerCase() === 'error' ? 'error' : 'success';
                        const merged = Object.assign({
                            autoClose: true,
                            duration: 3000,
                            styles: {},
                            onClose: null,
                            allowHtml: false
                        }, options || {});

                        const toast = document.createElement('div');
                        toast.className = 'admin-toast ' + (resolvedType === 'error' ? 'admin-toast-error' : 'admin-toast-success');
                        toast.style.setProperty('--admin-toast-duration', (Math.max(0, Number(merged.duration) || 0)) + 'ms');

                        const content = document.createElement('div');
                        content.className = 'admin-toast-content';
                        if (merged.allowHtml) {
                            content.innerHTML = message;
                        } else {
                            const messageSpan = document.createElement('span');
                            messageSpan.className = 'admin-toast-message';
                            messageSpan.textContent = message;
                            content.appendChild(messageSpan);
                        }

                        const closeBtn = document.createElement('button');
                        closeBtn.type = 'button';
                        closeBtn.className = 'admin-toast-close';
                        closeBtn.setAttribute('aria-label', 'Close notification');
                        closeBtn.innerHTML = '&times;';
                        closeBtn.addEventListener('click', () => {
                            this.closeToast(toast, merged.onClose);
                        });

                        toast.appendChild(content);
                        toast.appendChild(closeBtn);

                        Object.keys(merged.styles || {}).forEach(function (key) {
                            toast.style[key] = merged.styles[key];
                        });

                        this.container.appendChild(toast);

                        if (merged.autoClose) {
                            window.setTimeout(() => {
                                this.closeToast(toast, merged.onClose);
                            }, Math.max(0, Number(merged.duration) || 0));
                        }

                        return toast;
                    }

                    closeToast(toast, onClose) {
                        if (!toast || !toast.parentNode) {
                            return;
                        }

                        toast.classList.add('is-exit');
                        window.setTimeout(function () {
                            if (toast.parentNode) {
                                toast.parentNode.removeChild(toast);
                            }
                            if (typeof onClose === 'function') {
                                onClose();
                            }
                        }, 220);
                    }
                };
            }

            if (!window.adminEscapeHtml) {
                window.adminEscapeHtml = function (value) {
                    const source = value == null ? '' : String(value);
                    return source
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };
            }
        })();
    </script>
@endonce

@if ($adminToasts !== [])
    <script>
        (function () {
            const toastManager = window.adminGlobalToastManager || new Toaster.ToastManager();
            window.adminGlobalToastManager = toastManager;

            const toasts = @json($adminToasts);
            toasts.forEach(function (toastData) {
                const isSuccess = (toastData.type || '').toLowerCase() === 'success';
                const iconClass = isSuccess
                    ? 'fa-solid fa-circle-check mx-3'
                    : 'fa-solid fa-circle-exclamation mx-3';
                const html = '<i class="' + iconClass + '"></i><span class="admin-toast-message">' + window.adminEscapeHtml(toastData.message || '') + '</span>';

                toastManager.addToast(html, isSuccess ? 'success' : 'error', {
                    autoClose: true,
                    duration: 3000,
                    styles: {},
                    onClose: function () {
                        console.log('Toast closed!');
                    },
                    allowHtml: true
                });
            });

            document.querySelectorAll([
                '.rate-alert',
                '.um-alert',
                '.ledger-alert',
                '.rpm-alert',
                '.sp-alert',
                '.fishport-toast',
                '.fishport-alert-success',
                '.fishport-alert-danger',
                '.vl-status-toast',
                '.vr-status-toast',
                '.cpm-alert',
                '.cp-alert',
                '.col-alert',
                '.fp-alert-ok',
                '.fp-alert-error',
                '.atr-flash',
                '.atr-profile-alert-ok',
                '.alert.alert-success'
            ].join(',')).forEach(function (el) {
                el.style.display = 'none';
            });
        })();
    </script>
@endif
