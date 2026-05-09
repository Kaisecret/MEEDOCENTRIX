@php
    $terminalToasts = [];

    if (session('status')) {
        $terminalToasts[] = ['type' => 'success', 'message' => (string) session('status')];
    }
    if (session('error')) {
        $terminalToasts[] = ['type' => 'error', 'message' => (string) session('error')];
    }
    if ($errors->any()) {
        $terminalToasts[] = ['type' => 'error', 'message' => (string) $errors->first()];
    }
@endphp

@once
    <script>
        (function () {
            if (!window.Toaster) {
                window.Toaster = {};
            }

            if (!window.Toaster.ToastManager) {
                window.Toaster.ToastManager = class ToastManager {
                    constructor() {
                        this.container = document.querySelector('.tm-toast-stack');
                        if (!this.container) {
                            this.container = document.createElement('div');
                            this.container.className = 'tm-toast-stack';
                            this.container.setAttribute('role', 'status');
                            this.container.setAttribute('aria-live', 'polite');
                            document.body.appendChild(this.container);
                        }
                    }

                    addToast(message, type, options) {
                        const resolvedType = type === 'error' ? 'error' : 'success';
                        const merged = Object.assign({
                            autoClose: true,
                            duration: 3000,
                            styles: {},
                            onClose: null,
                            allowHtml: false
                        }, options || {});

                        const toast = document.createElement('div');
                        toast.className = 'tm-toast ' + (resolvedType === 'success' ? 'tm-toast-success' : 'tm-toast-error');
                        toast.style.setProperty('--tm-toast-duration', (Math.max(0, Number(merged.duration) || 0)) + 'ms');

                        const content = document.createElement('div');
                        content.className = 'tm-toast-content';
                        if (merged.allowHtml) {
                            content.innerHTML = message;
                        } else {
                            const messageSpan = document.createElement('span');
                            messageSpan.className = 'tm-toast-message';
                            messageSpan.textContent = message;
                            content.appendChild(messageSpan);
                        }

                        const closeBtn = document.createElement('button');
                        closeBtn.type = 'button';
                        closeBtn.className = 'tm-toast-close';
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

            if (!window.tfcoEscapeHtml) {
                window.tfcoEscapeHtml = function (value) {
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

@if ($terminalToasts !== [])
    <script>
        (function () {
            const toastManager = window.tfcoTerminalToastManager || new Toaster.ToastManager();
            window.tfcoTerminalToastManager = toastManager;

            const toasts = @json($terminalToasts);
            toasts.forEach(function (toastData) {
                const isSuccess = (toastData.type || '').toLowerCase() === 'success';
                const iconClass = isSuccess
                    ? 'fa-solid fa-circle-check mx-3'
                    : 'fa-solid fa-circle-exclamation mx-3';
                const html = '<i class="' + iconClass + '"></i><span class="tm-toast-message">' + window.tfcoEscapeHtml(toastData.message || '') + '</span>';

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
        })();
    </script>
@endif
