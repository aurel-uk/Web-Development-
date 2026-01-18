<?php
/**
 * FAQJA E HYRJES
 * ==============
 * Faqja ku përdoruesit identifikohen.
 */

$pageTitle = 'Hyr';
require_once __DIR__ . '/../includes/header.php';

// Nëse është i loguar, ridrejto në homepage
if (isLoggedIn()) {
    redirect('');
}

// Kontrollo nëse sesioni ka skaduar
$timeout = isset($_GET['timeout']) && $_GET['timeout'] == 1;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card auth-card shadow">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Hyr në Llogari
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($timeout): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-clock me-2"></i>Sesioni juaj ka skaduar. Ju lutem identifikohuni përsëri.
                        </div>
                    <?php endif; ?>

                    <!-- Alert për gabime -->
                    <div id="login-alert" class="alert d-none"></div>

                    <form id="loginForm" class="needs-validation" novalidate>
                        <?= csrfField() ?>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="email@shembull.com" required autofocus>
                            </div>
                            <div class="invalid-feedback">Ju lutem shkruani email-in tuaj.</div>
                        </div>

                        <!-- Fjalëkalimi -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Fjalëkalimi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Fjalëkalimi juaj" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Ju lutem shkruani fjalëkalimin.</div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Më mbaj mend
                                </label>
                            </div>
                            <a href="forgot_password.php" class="text-decoration-none small">
                                Harrove fjalëkalimin?
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Hyr
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Nuk keni llogari?
                            <a href="register.php" class="text-decoration-none">Regjistrohu këtu</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// Toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const alertBox = document.getElementById('login-alert');
    const submitBtn = document.getElementById('submitBtn');
    const passwordToggle = document.querySelector('.password-toggle');

    if (!form || !alertBox || !submitBtn) {
        console.error('Required elements not found');
        return;
    }

    // Password toggle
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            togglePassword('password', this);
        });
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validim bazë
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Disable button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Duke hyrë...';

        try {
            const formData = new FormData(form);
            const response = await fetch('<?= SITE_URL ?>/api/login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alertBox.className = 'alert alert-success';
                alertBox.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                alertBox.classList.remove('d-none');

                // Ridrejto
                setTimeout(function() {
                    window.location.href = data.redirect || '<?= SITE_URL ?>';
                }, 1000);
            } else {
                alertBox.className = 'alert alert-danger';
                alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>' + data.message;
                alertBox.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Hyr';

                // Nëse llogaria është bllokuar
                if (data.locked) {
                    document.getElementById('email').disabled = true;
                    document.getElementById('password').disabled = true;
                    submitBtn.disabled = true;
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>Gabim në komunikim me serverin.';
            alertBox.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Hyr';
        }
    });
});
</script>
