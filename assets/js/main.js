/**
 * ============================================================================
 * Al Amin's Math Care — Next-Gen (2026+ Standards) Client JavaScript
 * Brand: alaminmathcare.com
 * Features: Frosted Header Scroll, Dynamic CSRF Injection, Micro-Animations,
 *           Toast Notifications, Phone Formatter & Async Lead Submissions
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    // ── 1. Frosted Header Elevation on Scroll ──
    const header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 20) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    // ── 2. Helper: HTML Escape ──
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── 3. Helper: Retrieve CSRF Token from Form or Session ──
    function getCsrfToken(form) {
        if (form) {
            const input = form.querySelector('input[name="_csrf_token"]');
            if (input && input.value) return input.value;
        }
        const metaCsrf = document.querySelector('meta[name="csrf-token"]');
        return metaCsrf ? metaCsrf.getAttribute('content') : '';
    }

    // ── 4. Next-Gen Toast Notification System ──
    function showToast(message, type = 'success') {
        const existing = document.querySelector('.mathcare-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `mathcare-toast toast-${type}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');

        const icons = {
            success: '✨',
            error: '⚠️',
            info: '💡'
        };

        const bgColors = {
            success: 'linear-gradient(135deg, #0b192c, #1e3e62)',
            error: 'linear-gradient(135deg, #7f1d1d, #991b1b)',
            info: 'linear-gradient(135deg, #075985, #0284c7)'
        };

        const borderColors = {
            success: '#10b981',
            error: '#ef4444',
            info: '#008dda'
        };

        toast.style.cssText = `
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 1099;
            background: ${bgColors[type] || bgColors.info};
            color: #ffffff;
            border-left: 5px solid ${borderColors[type] || borderColors.info};
            border-radius: 12px;
            padding: 1rem 1.4rem;
            box-shadow: 0 12px 32px rgba(11, 25, 44, 0.25);
            backdrop-filter: blur(16px);
            font-size: 0.95rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            max-width: 420px;
            animation: toast-entrance 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        `;

        toast.innerHTML = `
            <span style="font-size: 1.3rem;">${icons[type] || '✨'}</span>
            <div style="flex-grow: 1;">${escapeHtml(message)}</div>
            <button type="button" class="toast-close-btn" aria-label="Close" style="background: none; border: none; color: rgba(255,255,255,0.7); font-size: 1.3rem; cursor: pointer; padding: 0 0 0 0.5rem; line-height: 1;">&times;</button>
        `;

        document.body.appendChild(toast);

        // Auto-remove after 5 seconds
        const timer = setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 5000);

        toast.querySelector('.toast-close-btn')?.addEventListener('click', () => {
            clearTimeout(timer);
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        });
    }

    // ── 5. Button Loading States ──
    function setButtonLoading(button, isLoading) {
        if (!button) return;
        if (isLoading) {
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...`;
        } else {
            button.disabled = false;
            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }
        }
    }

    // ── 6. Admission & Lead Form Async Handler ──
    const leadForms = document.querySelectorAll('.lead-form, .admission-form, .demo-form');
    leadForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const btn = form.querySelector('button[type="submit"]');
            e.preventDefault();

            setButtonLoading(btn, true);

            const formData = new FormData(form);
            const csrfToken = getCsrfToken(form);
            if (csrfToken && !formData.has('_csrf_token')) {
                formData.append('_csrf_token', csrfToken);
            }

            fetch(form.getAttribute('action') || 'api/submit-lead.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            try { return JSON.parse(text); } catch (_) { throw new Error(text || 'Submission Error'); }
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    setButtonLoading(btn, false);
                    if (data.success) {
                        showToast(data.message || 'Application submitted successfully! We will contact you shortly.', 'success');
                        form.reset();

                        if (data.data && data.data.whatsapp_link) {
                            setTimeout(() => {
                                window.location.href = data.data.whatsapp_link;
                            }, 1800);
                        }
                    } else {
                        showToast(data.message || 'Submission failed. Please verify your entries.', 'error');
                    }
                })
                .catch(err => {
                    setButtonLoading(btn, false);
                    console.error('Lead Submit Error:', err);
                    showToast(err.message || 'Connection error. Please try again.', 'error');
                });
        });
    });

    // ── 7. Bangladeshi Phone Number Inline Formatter ──
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name*="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', function () {
            let val = input.value.trim().replace(/\D/g, '');
            if (val.startsWith('880')) {
                val = val.substring(2);
            }
            if (val.length === 11 && val.startsWith('01')) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else if (val.length > 0) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
        });
    });
});