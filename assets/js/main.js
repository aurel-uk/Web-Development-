/**
 * JAVASCRIPT KRYESOR
 * ==================
 * Ky skedar përmban funksionalitetet JS të aplikacionit.
 *
 * SHPJEGIM për fillestarët:
 * - $(document).ready() ekzekutohet kur faqja ngarkohet plotësisht
 * - $ është shkurtim për jQuery
 * - AJAX na lejon të dërgojmë/marrim të dhëna pa rifreskuar faqen
 */

$(document).ready(function() {

    // ============================================
    // BACK TO TOP BUTTON
    // ============================================
    const backToTopBtn = $('#btn-back-to-top');

    // Shfaq/fshih butonin kur scroll-ohet
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            backToTopBtn.addClass('show');
        } else {
            backToTopBtn.removeClass('show');
        }
    });

    // Kur klikohet, kthehu lart
    backToTopBtn.click(function() {
        $('html, body').animate({ scrollTop: 0 }, 500);
    });

    // ============================================
    // FORM VALIDATION (Front-end)
    // ============================================

    // Validimi i formës së regjistrimit
    $('#registerForm').on('submit', function(e) {
        let isValid = true;
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        // Pastro gabimet e mëparshme
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Kontrollo fjalëkalimin
        const passwordValidation = validatePassword(password);
        if (!passwordValidation.valid) {
            $('#password').addClass('is-invalid');
            $('#password').after('<div class="invalid-feedback">' + passwordValidation.errors.join('<br>') + '</div>');
            isValid = false;
        }

        // Kontrollo konfirmimin
        if (password !== confirmPassword) {
            $('#confirm_password').addClass('is-invalid');
            $('#confirm_password').after('<div class="invalid-feedback">Fjalëkalimet nuk përputhen</div>');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Funksion për validimin e fjalëkalimit
    function validatePassword(password) {
        const errors = [];

        if (password.length < 8) {
            errors.push('Të paktën 8 karaktere');
        }
        if (!/[A-Z]/.test(password)) {
            errors.push('Të paktën një shkronjë të madhe');
        }
        if (!/[a-z]/.test(password)) {
            errors.push('Të paktën një shkronjë të vogël');
        }
        if (!/[0-9]/.test(password)) {
            errors.push('Të paktën një numër');
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }

    // ============================================
    // PASSWORD STRENGTH INDICATOR
    // ============================================
    $('#password').on('input', function() {
        const password = $(this).val();
        const strengthBar = $('.password-strength-bar');
        const strengthText = $('.password-strength-text');

        // Llogarit forcën
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        // Përditëso UI
        strengthBar.removeClass('weak medium strong');

        if (password.length === 0) {
            strengthBar.css('width', '0');
            strengthText.text('');
        } else if (strength < 3) {
            strengthBar.addClass('weak');
            strengthText.text('E dobët').css('color', '#dc3545');
        } else if (strength < 5) {
            strengthBar.addClass('medium');
            strengthText.text('Mesatare').css('color', '#ffc107');
        } else {
            strengthBar.addClass('strong');
            strengthText.text('E fortë').css('color', '#198754');
        }
    });

    // ============================================
    // SHOW/HIDE PASSWORD
    // ============================================
    $('.toggle-password').click(function() {
        const passwordInput = $(this).siblings('input');
        const icon = $(this).find('i');

        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // ============================================
    // ALERT AUTO-DISMISS
    // ============================================
    setTimeout(function() {
        $('.alert-dismissible').fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);

    // ============================================
    // CONFIRM DELETE
    // ============================================
    $('.btn-delete').click(function(e) {
        if (!confirm('Je i sigurt që dëshiron ta fshish? Ky veprim nuk mund të zhbëhet.')) {
            e.preventDefault();
        }
    });

    // ============================================
    // IMAGE PREVIEW
    // ============================================
    $('input[type="file"][accept*="image"]').change(function() {
        const file = this.files[0];
        const preview = $(this).data('preview');

        if (file && preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(preview).attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // ============================================
    // CART FUNCTIONS
    // ============================================

    // Përditëso numrin e artikujve në shportë
    function updateCartCount() {
        $.get(window.SITE_URL + '/api/cart-count.php', function(response) {
            if (response.success) {
                $('#cart-count').text(response.count);
            }
        });
    }

    // Shto në shportë
    $(document).on('click', '.add-to-cart', function() {
        const productId = $(this).data('product-id');
        const quantity = $(this).data('quantity') || 1;

        $.ajax({
            url: window.SITE_URL + '/api/cart.php',
            method: 'POST',
            data: {
                action: 'add',
                product_id: productId,
                quantity: quantity,
                csrf_token: window.CSRF_TOKEN
            },
            success: function(response) {
                if (response.success) {
                    updateCartCount();
                    showToast('success', 'Produkti u shtua në shportë!');
                } else {
                    showToast('error', response.message || 'Ndodhi një gabim');
                }
            },
            error: function() {
                showToast('error', 'Gabim në komunikim me serverin');
            }
        });
    });

    // Fshi nga shporta
    $(document).on('click', '.remove-from-cart', function() {
        const itemId = $(this).data('item-id');

        if (confirm('Dëshiron ta heqësh këtë produkt nga shporta?')) {
            $.ajax({
                url: window.SITE_URL + '/api/cart.php',
                method: 'POST',
                data: {
                    action: 'remove',
                    item_id: itemId,
                    csrf_token: window.CSRF_TOKEN
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        showToast('error', response.message);
                    }
                }
            });
        }
    });

    // Përditëso sasinë
    $(document).on('change', '.cart-quantity', function() {
        const itemId = $(this).data('item-id');
        const quantity = $(this).val();

        $.ajax({
            url: window.SITE_URL + '/api/cart.php',
            method: 'POST',
            data: {
                action: 'update',
                item_id: itemId,
                quantity: quantity,
                csrf_token: window.CSRF_TOKEN
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    function showToast(type, message) {
        const bgClass = {
            success: 'bg-success',
            error: 'bg-danger',
            warning: 'bg-warning',
            info: 'bg-info'
        };

        const toast = $(`
            <div class="toast align-items-center text-white ${bgClass[type]} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        // Krijo container nëse nuk ekziston
        if (!$('#toast-container').length) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }

        $('#toast-container').append(toast);
        const bsToast = new bootstrap.Toast(toast[0], { autohide: true, delay: 3000 });
        bsToast.show();

        // Fshi pas mbylljes
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Eksporto funksionin globalisht
    window.showToast = showToast;

    // ============================================
    // SEARCH AUTOCOMPLETE
    // ============================================
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();

        if (query.length < 2) {
            $('#searchResults').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.get(window.SITE_URL + '/api/search.php', { q: query }, function(response) {
                if (response.results && response.results.length > 0) {
                    let html = '';
                    response.results.forEach(function(item) {
                        html += `
                            <a href="${item.url}" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <img src="${item.image}" class="me-3" width="40" height="40" style="object-fit: cover; border-radius: 4px;">
                                    <div>
                                        <div class="fw-bold">${item.name}</div>
                                        <small class="text-muted">${item.price}</small>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    $('#searchResults').html(html).show();
                } else {
                    $('#searchResults').html('<div class="list-group-item">Asnjë rezultat</div>').show();
                }
            });
        }, 300);
    });

    // Fshih rezultatet kur klikohet jashtë
    $(document).click(function(e) {
        if (!$(e.target).closest('.search-container').length) {
            $('#searchResults').hide();
        }
    });

    // ============================================
    // DATA TABLES INITIALIZATION
    // ============================================
    if ($.fn.DataTable && $('.datatable').length) {
        $('.datatable').DataTable({
            language: {
                search: "Kërko:",
                lengthMenu: "Shfaq _MENU_ rreshta",
                info: "Duke shfaqur _START_ deri _END_ nga _TOTAL_ gjithsej",
                paginate: {
                    first: "Fillimi",
                    last: "Fundi",
                    next: "Para",
                    previous: "Pas"
                },
                zeroRecords: "Asnjë rezultat"
            }
        });
    }

    // ============================================
    // FORM AJAX SUBMISSION
    // ============================================
    $('form[data-ajax="true"]').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('[type="submit"]');
        const originalText = submitBtn.html();

        // Disable button dhe shfaq loading
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Duke procesuar...');

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method') || 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1000);
                    }
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Gabim në komunikim me serverin');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ============================================
    // INITIALIZE TOOLTIPS & POPOVERS
    // ============================================
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

    // ============================================
    // LOADING STATES
    // ============================================
    window.showLoading = function() {
        if (!$('.spinner-overlay').length) {
            $('body').append(`
                <div class="spinner-overlay">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                </div>
            `);
        }
    };

    window.hideLoading = function() {
        $('.spinner-overlay').fadeOut(300, function() {
            $(this).remove();
        });
    };

});

// ============================================
// UTILITY FUNCTIONS (Global)
// ============================================

/**
 * Formon numrin si çmim
 */
function formatPrice(price, currency = '€') {
    return parseFloat(price).toFixed(2).replace('.', ',') + ' ' + currency;
}

/**
 * Formon datën
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('sq-AL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Kopjon tekst në clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('success', 'U kopjua!');
    });
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
