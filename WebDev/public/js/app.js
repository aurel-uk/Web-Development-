/**
 * SKEDARI KRYESOR JAVASCRIPT
 * ==========================
 * Funksionalitete të përgjithshme për aplikacionin.
 */

// ============================================
// KONFIGURIMI GLOBAL
// ============================================

// URL bazë e API-së (merret nga data attribute në body ose default)
const API_BASE = document.body.dataset.apiUrl || '/api';
const SITE_URL = document.body.dataset.siteUrl || '';

// ============================================
// DOCUMENT READY
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    initBackToTop();
    initFormValidation();
    initPasswordToggle();
    initPasswordStrength();
    initCartCounter();
    initAutoHideAlerts();
    initConfirmDelete();
    initQuantityButtons();
});

// ============================================
// BACK TO TOP BUTTON
// ============================================
function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    if (!backToTopBtn) return;

    // Shfaq/fshih butonin bazuar në scroll
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    // Scroll lart kur klikohet
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ============================================
// FORM VALIDATION (Bootstrap 5)
// ============================================
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// ============================================
// PASSWORD TOGGLE (shfaq/fshih)
// ============================================
function initPasswordToggle() {
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
}

// ============================================
// PASSWORD STRENGTH INDICATOR
// ============================================
function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    const strengthBar = document.querySelector('.password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');

    if (!passwordInput || !strengthBar) return;

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);

        strengthBar.className = 'password-strength-bar';

        if (password.length === 0) {
            strengthBar.style.width = '0';
            if (strengthText) strengthText.textContent = '';
        } else if (strength < 2) {
            strengthBar.classList.add('weak');
            if (strengthText) strengthText.textContent = 'E dobët';
        } else if (strength < 4) {
            strengthBar.classList.add('medium');
            if (strengthText) strengthText.textContent = 'Mesatare';
        } else {
            strengthBar.classList.add('strong');
            if (strengthText) strengthText.textContent = 'E fortë';
        }
    });
}

function calculatePasswordStrength(password) {
    let strength = 0;

    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    return strength;
}

// ============================================
// CART COUNTER
// ============================================
function initCartCounter() {
    updateCartCounter();
}

async function updateCartCounter() {
    const cartCountEl = document.getElementById('cart-count');
    if (!cartCountEl) return;

    try {
        const response = await fetch(`${SITE_URL}/api/cart.php?action=count`);
        const data = await response.json();

        if (data.success) {
            cartCountEl.textContent = data.count;
            cartCountEl.style.display = data.count > 0 ? 'inline' : 'none';
        }
    } catch (error) {
        console.error('Gabim në marrjen e numrit të cart:', error);
    }
}

// ============================================
// AUTO HIDE ALERTS
// ============================================
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');

    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000); // Fshihu pas 5 sekondash
    });
}

// ============================================
// CONFIRM DELETE
// ============================================
function initConfirmDelete() {
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Jeni të sigurt?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

// ============================================
// QUANTITY BUTTONS (për cart)
// ============================================
function initQuantityButtons() {
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.quantity-control').querySelector('.cart-quantity');
            let value = parseInt(input.value) || 1;

            if (this.dataset.action === 'increase') {
                value++;
            } else if (this.dataset.action === 'decrease' && value > 1) {
                value--;
            }

            input.value = value;
            input.dispatchEvent(new Event('change'));
        });
    });
}

// ============================================
// AJAX HELPER FUNCTIONS
// ============================================

/**
 * Bën një kërkesë POST me JSON
 */
async function postJSON(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    });
    return response.json();
}

/**
 * Bën një kërkesë GET
 */
async function getJSON(url) {
    const response = await fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    return response.json();
}

// ============================================
// CART FUNCTIONS
// ============================================

/**
 * Shton produkt në cart
 */
async function addToCart(productId, quantity = 1) {
    try {
        const data = await postJSON(`${SITE_URL}/api/cart.php`, {
            action: 'add',
            product_id: productId,
            quantity: quantity
        });

        if (data.success) {
            showToast('success', 'Produkti u shtua në shportë!');
            updateCartCounter();
        } else {
            showToast('error', data.message || 'Dështoi shtimi në shportë');
        }
    } catch (error) {
        showToast('error', 'Gabim në komunikim me serverin');
    }
}

/**
 * Heq produkt nga cart
 */
async function removeFromCart(productId) {
    try {
        const data = await postJSON(`${SITE_URL}/api/cart.php`, {
            action: 'remove',
            product_id: productId
        });

        if (data.success) {
            showToast('success', 'Produkti u hoq nga shporta!');
            updateCartCounter();
            // Rifresko faqen nëse jemi në cart
            if (window.location.pathname.includes('cart')) {
                location.reload();
            }
        } else {
            showToast('error', data.message || 'Dështoi heqja nga shporta');
        }
    } catch (error) {
        showToast('error', 'Gabim në komunikim me serverin');
    }
}

/**
 * Përditëson sasinë në cart
 */
async function updateCartQuantity(productId, quantity) {
    try {
        const data = await postJSON(`${SITE_URL}/api/cart.php`, {
            action: 'update',
            product_id: productId,
            quantity: quantity
        });

        if (data.success) {
            updateCartCounter();
            // Përditëso totalin nëse ekziston elementi
            if (data.total !== undefined) {
                const totalEl = document.getElementById('cart-total');
                if (totalEl) totalEl.textContent = formatPrice(data.total);
            }
        } else {
            showToast('error', data.message || 'Dështoi përditësimi');
        }
    } catch (error) {
        showToast('error', 'Gabim në komunikim me serverin');
    }
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================

/**
 * Shfaq një toast notification
 */
function showToast(type, message) {
    // Krijo container nëse nuk ekziston
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }

    // Përcakto ikonën dhe ngjyrën bazuar në tip
    const icons = {
        success: 'bi-check-circle-fill text-success',
        error: 'bi-x-circle-fill text-danger',
        warning: 'bi-exclamation-triangle-fill text-warning',
        info: 'bi-info-circle-fill text-info'
    };

    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi ${icons[type] || icons.info} me-2"></i>
                <strong class="me-auto">${type === 'error' ? 'Gabim' : type === 'success' ? 'Sukses' : 'Njoftim'}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHTML);

    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();

    // Fshij elementin pas mbylljes
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Formon çmimin
 */
function formatPrice(price, currency = '€') {
    return parseFloat(price).toFixed(2).replace('.', ',') + ' ' + currency;
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Kopjon tekst në clipboard
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('success', 'U kopjua në clipboard!');
    } catch (err) {
        showToast('error', 'Dështoi kopjimi');
    }
}

/**
 * Loading spinner
 */
function showLoading() {
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.className = 'spinner-overlay';
    overlay.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Po ngarkohet...</span>
        </div>
    `;
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.remove();
}

// ============================================
// FORM HELPERS
// ============================================

/**
 * Serialize form data to JSON
 */
function serializeForm(form) {
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    return data;
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Validate phone format
 */
function isValidPhone(phone) {
    return /^[+]?[0-9]{9,15}$/.test(phone.replace(/\s+/g, ''));
}

// ============================================
// IMAGE PREVIEW
// ============================================

/**
 * Shfaq preview të imazhit para ngarkimit
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Export functions for use in other modules
window.AppUtils = {
    postJSON,
    getJSON,
    showToast,
    showLoading,
    hideLoading,
    formatPrice,
    debounce,
    addToCart,
    removeFromCart,
    updateCartQuantity,
    copyToClipboard,
    previewImage,
    isValidEmail,
    isValidPhone
};
