<?php
/**
 * FAQJA E REGJISTRIMIT
 * ====================
 * Faqja ku përdoruesit e rinj krijojnë llogari.
 */

$pageTitle = 'Regjistrohu';
require_once __DIR__ . '/../includes/header.php';

// Nëse është i loguar, ridrejto në homepage
if (isLoggedIn()) {
    redirect('');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card shadow">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i>Krijo Llogari
                    </h4>
                </div>
                <div class="card-body p-4">
                    <!-- Alert për gabime -->
                    <div id="register-alert" class="alert d-none"></div>

                    <form id="registerForm" class="needs-validation" novalidate>
                        <?= csrfField() ?>

                        <!-- Emri -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Emri</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                           placeholder="Emri" required minlength="2">
                                </div>
                                <div class="invalid-feedback">Ju lutem shkruani emrin tuaj.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Mbiemri</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       placeholder="Mbiemri" required minlength="2">
                                <div class="invalid-feedback">Ju lutem shkruani mbiemrin tuaj.</div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="email@shembull.com" required>
                            </div>
                            <div class="invalid-feedback">Ju lutem shkruani një email të vlefshëm.</div>
                        </div>

                        <!-- Telefoni -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefoni <small class="text-muted">(opsional)</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       placeholder="+355 69 123 4567">
                            </div>
                        </div>

                        <!-- Fjalëkalimi -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Fjalëkalimi</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Minimum 8 karaktere" required minlength="8">
                                <button class="btn btn-outline-secondary password-toggle" type="button" onclick="togglePassword('password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Fjalëkalimi duhet të ketë të paktën 8 karaktere.</div>
                        </div>

                        <!-- Konfirmo Fjalëkalimin -->
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Konfirmo Fjalëkalimin</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                       placeholder="Përsërit fjalëkalimin" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button" onclick="togglePassword('password_confirm', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Fjalëkalimet nuk përputhen.</div>
                        </div>

                        <!-- Terms -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Pranoj <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Kushtet e Përdorimit</a>
                                </label>
                                <div class="invalid-feedback">Duhet të pranoni kushtet për të vazhduar.</div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
                            <i class="bi bi-person-plus me-2"></i>Regjistrohu
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Keni llogari?
                            <a href="login.php" class="text-decoration-none">Hyni këtu</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kushtet e Përdorimit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Pranimi i Kushteve</h6>
                <p>Duke krijuar një llogari, ju pranoni të respektoni këto kushte përdorimi.</p>

                <h6>2. Privatësia</h6>
                <p>Të dhënat tuaja do të ruhen në përputhje me ligjin për mbrojtjen e të dhënave personale.</p>

                <h6>3. Përgjegjësitë</h6>
                <p>Ju jeni përgjegjës për sigurinë e llogarisë tuaj dhe fjalëkalimit.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">E kuptova</button>
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
    const form = document.getElementById('registerForm');
    const alertBox = document.getElementById('register-alert');
    const submitBtn = document.getElementById('submitBtn');

    if (!form || !alertBox || !submitBtn) {
        console.error('Required elements not found');
        return;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validim bazë
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Kontrollo nëse fjalëkalimet përputhen
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;

        if (password !== passwordConfirm) {
            document.getElementById('password_confirm').setCustomValidity('Fjalëkalimet nuk përputhen');
            form.classList.add('was-validated');
            return;
        }

        // Disable button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Duke regjistruar...';

        try {
            const formData = new FormData(form);
            const response = await fetch('<?= SITE_URL ?>/api/register.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alertBox.className = 'alert alert-success';
                alertBox.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                alertBox.classList.remove('d-none');

                // Ridrejto pas 2 sekondash
                setTimeout(function() {
                    window.location.href = data.redirect || 'login.php';
                }, 2000);
            } else {
                alertBox.className = 'alert alert-danger';
                alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>' + data.message;
                alertBox.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-person-plus me-2"></i>Regjistrohu';
            }
        } catch (error) {
            console.error('Error:', error);
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="bi bi-x-circle me-2"></i>Gabim në komunikim me serverin.';
            alertBox.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-person-plus me-2"></i>Regjistrohu';
        }
    });

    // Reset custom validity kur ndryshon password confirm
    document.getElementById('password_confirm').addEventListener('input', function() {
        this.setCustomValidity('');
    });
});
</script>
