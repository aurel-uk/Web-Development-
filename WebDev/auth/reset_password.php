<?php
/**
 * RIVENDOSJA E FJALËKALIMIT
 * =========================
 * Faqja ku përdoruesi vendos fjalëkalimin e ri.
 */

$pageTitle = 'Rivendos Fjalëkalimin';
require_once __DIR__ . '/../includes/header.php';

// Merr tokenin nga URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    setFlash('error', 'Link i pavlefshëm.');
    redirect('auth/forgot_password.php');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card auth-card shadow">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>Fjalëkalim i Ri
                    </h4>
                </div>
                <div class="card-body p-4">
                    <!-- Alert -->
                    <div id="reset-alert" class="alert d-none"></div>

                    <form id="resetForm" class="needs-validation" novalidate>
                        <?= csrfField() ?>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <!-- Fjalëkalimi i ri -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Fjalëkalimi i Ri</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Minimum 8 karaktere" required minlength="8">
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="password-strength-bar"></div>
                            </div>
                            <div class="invalid-feedback">Fjalëkalimi duhet të ketë të paktën 8 karaktere.</div>
                        </div>

                        <!-- Konfirmo Fjalëkalimin -->
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Konfirmo Fjalëkalimin</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                       placeholder="Përsërit fjalëkalimin" required>
                            </div>
                            <div class="invalid-feedback">Fjalëkalimet nuk përputhen.</div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
                            <i class="bi bi-check-lg me-2"></i>Rivendos Fjalëkalimin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('resetForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const alert = document.getElementById('reset-alert');
    const submitBtn = document.getElementById('submitBtn');

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

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Duke rivendosur...';

    try {
        const formData = new FormData(form);
        formData.append('action', 'reset_password');

        const response = await fetch('<?= SITE_URL ?>/api/auth.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert.className = 'alert alert-success';
            alert.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
            alert.classList.remove('d-none');

            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            alert.className = 'alert alert-danger';
            alert.innerHTML = '<i class="bi bi-x-circle me-2"></i>' + data.message;
            alert.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Rivendos Fjalëkalimin';
        }
    } catch (error) {
        alert.className = 'alert alert-danger';
        alert.innerHTML = '<i class="bi bi-x-circle me-2"></i>Gabim në komunikim me serverin.';
        alert.classList.remove('d-none');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Rivendos Fjalëkalimin';
    }
});

document.getElementById('password_confirm').addEventListener('input', function() {
    this.setCustomValidity('');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
