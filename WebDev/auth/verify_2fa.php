<?php
/**
 * VERIFIKIMI 2FA
 * ==============
 * Faqja ku përdoruesi fut kodin 6-shifror për Two-Factor Authentication.
 */

require_once __DIR__ . '/../includes/init.php';

// Kontrollo nëse ka sesion 2FA aktiv
if (!isset($_SESSION['2fa_user_id']) || !isset($_SESSION['2fa_expires'])) {
    redirect('auth/login.php');
}

// Kontrollo nëse sesioni 2FA ka skaduar
if (time() > $_SESSION['2fa_expires']) {
    unset($_SESSION['2fa_user_id'], $_SESSION['2fa_remember'], $_SESSION['2fa_expires']);
    setFlash('error', 'Sesioni ka skaduar. Ju lutem provoni përsëri.');
    redirect('auth/login.php');
}

$pageTitle = 'Verifikimi 2FA';
require_once __DIR__ . '/../includes/header.php';

// Për debug mode, merr kodin nga sesioni
$debugCode = $_SESSION['debug_2fa_code'] ?? null;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card auth-card shadow">
                <div class="card-header text-center">
                    <i class="bi bi-shield-lock display-4"></i>
                    <h4 class="mt-2 mb-0">Verifikimi me Dy Hapa</h4>
                </div>
                <div class="card-body p-4">
                    <div id="verify-alert" class="alert d-none"></div>

                    <p class="text-muted text-center mb-4">
                        <i class="bi bi-envelope me-1"></i>
                        Kemi dërguar një kod 6-shifror në email-in tuaj.
                        Futni kodin më poshtë për të vazhduar.
                    </p>

                    <?php if ($debugCode && defined('DEBUG_MODE') && DEBUG_MODE): ?>
                        <div class="alert alert-info small">
                            <i class="bi bi-bug me-1"></i>
                            <strong>Debug Mode:</strong> Kodi është <code><?= $debugCode ?></code>
                        </div>
                    <?php endif; ?>

                    <form id="verify2faForm" class="needs-validation" novalidate>
                        <?= csrfField() ?>

                        <!-- Kodi 2FA -->
                        <div class="mb-4">
                            <label for="code" class="form-label">Kodi i Verifikimit</label>
                            <div class="d-flex justify-content-center gap-2">
                                <input type="text" class="form-control form-control-lg text-center code-input"
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" required autofocus>
                                <input type="text" class="form-control form-control-lg text-center code-input"
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                <input type="text" class="form-control form-control-lg text-center code-input"
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                <input type="text" class="form-control form-control-lg text-center code-input"
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                <input type="text" class="form-control form-control-lg text-center code-input"
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                <input type="text" class="form-control form-control-lg text-center code-input"
                                       maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            </div>
                            <input type="hidden" name="code" id="fullCode">
                            <div class="invalid-feedback text-center">Ju lutem futni kodin 6-shifror.</div>
                        </div>

                        <!-- Koha e mbetur -->
                        <div class="text-center mb-4">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                Kodi skadon për: <span id="countdown" class="fw-bold text-primary">10:00</span>
                            </small>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Verifiko
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- Ridërgo kodin -->
                    <div class="text-center">
                        <p class="text-muted mb-2">Nuk e morët kodin?</p>
                        <button type="button" class="btn btn-outline-secondary" id="resendBtn">
                            <i class="bi bi-arrow-repeat me-1"></i>Ridërgo Kodin
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <a href="<?= SITE_URL ?>/auth/login.php" class="text-muted">
                            <i class="bi bi-arrow-left me-1"></i>Kthehu te Hyrja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.code-input {
    width: 50px !important;
    height: 60px !important;
    font-size: 1.5rem !important;
    font-weight: bold;
}
.code-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verify2faForm');
    const alertBox = document.getElementById('verify-alert');
    const submitBtn = document.getElementById('submitBtn');
    const resendBtn = document.getElementById('resendBtn');
    const codeInputs = document.querySelectorAll('.code-input');
    const fullCodeInput = document.getElementById('fullCode');
    const countdownEl = document.getElementById('countdown');

    // Countdown timer
    let expiresAt = <?= $_SESSION['2fa_expires'] ?>;

    function updateCountdown() {
        const now = Math.floor(Date.now() / 1000);
        const remaining = expiresAt - now;

        if (remaining <= 0) {
            countdownEl.textContent = '00:00';
            countdownEl.classList.remove('text-primary');
            countdownEl.classList.add('text-danger');
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Kodi ka skaduar. Ridërgoni një kod të ri.';
            alertBox.classList.remove('d-none');
            submitBtn.disabled = true;
            return;
        }

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        countdownEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        if (remaining <= 60) {
            countdownEl.classList.remove('text-primary');
            countdownEl.classList.add('text-danger');
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);

    // Auto-focus dhe navigim mes input-eve
    codeInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            // Vetëm numra
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value.length === 1 && index < codeInputs.length - 1) {
                codeInputs[index + 1].focus();
            }

            // Përditëso kodin e plotë
            updateFullCode();

            // Auto-submit kur plotësohet
            if (getFullCode().length === 6) {
                form.dispatchEvent(new Event('submit'));
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                codeInputs[index - 1].focus();
            }
        });

        // Paste support
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);

            for (let i = 0; i < pastedData.length && i < codeInputs.length; i++) {
                codeInputs[i].value = pastedData[i];
            }

            updateFullCode();

            if (pastedData.length >= 6) {
                form.dispatchEvent(new Event('submit'));
            }
        });
    });

    function getFullCode() {
        return Array.from(codeInputs).map(input => input.value).join('');
    }

    function updateFullCode() {
        fullCodeInput.value = getFullCode();
    }

    // Form submit
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const code = getFullCode();

        if (code.length !== 6) {
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>Ju lutem futni kodin e plotë 6-shifror.';
            alertBox.classList.remove('d-none');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Duke verifikuar...';

        try {
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);
            formData.append('code', code);

            const response = await fetch('<?= SITE_URL ?>/api/verify_2fa.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alertBox.className = 'alert alert-success';
                alertBox.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                alertBox.classList.remove('d-none');

                setTimeout(function() {
                    window.location.href = data.redirect || '<?= SITE_URL ?>';
                }, 1000);
            } else {
                alertBox.className = 'alert alert-danger';
                alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>' + data.message;
                alertBox.classList.remove('d-none');

                // Pastro inputet
                codeInputs.forEach(input => input.value = '');
                codeInputs[0].focus();

                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Verifiko';
            }
        } catch (error) {
            console.error('Error:', error);
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>Gabim në komunikim me serverin.';
            alertBox.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Verifiko';
        }
    });

    // Ridërgo kodin
    resendBtn.addEventListener('click', async function() {
        resendBtn.disabled = true;
        resendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Duke dërguar...';

        try {
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);
            formData.append('action', 'resend');

            const response = await fetch('<?= SITE_URL ?>/api/verify_2fa.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alertBox.className = 'alert alert-success';
                alertBox.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;

                // Përditëso countdown
                expiresAt = data.expires_at || (Math.floor(Date.now() / 1000) + 600);
                countdownEl.classList.remove('text-danger');
                countdownEl.classList.add('text-primary');
                submitBtn.disabled = false;

                // Përditëso debug code nëse ka
                if (data.debug_code) {
                    const debugAlert = document.querySelector('.alert-info');
                    if (debugAlert) {
                        debugAlert.innerHTML = '<i class="bi bi-bug me-1"></i><strong>Debug Mode:</strong> Kodi është <code>' + data.debug_code + '</code>';
                    }
                }
            } else {
                alertBox.className = 'alert alert-danger';
                alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>' + data.message;
            }

            alertBox.classList.remove('d-none');
        } catch (error) {
            console.error('Error:', error);
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>Gabim në komunikim me serverin.';
            alertBox.classList.remove('d-none');
        }

        resendBtn.disabled = false;
        resendBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Ridërgo Kodin';
    });
});
</script>
