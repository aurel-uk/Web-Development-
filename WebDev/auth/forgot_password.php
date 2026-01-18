<?php
/**
 * HARROVA FJALËKALIMIN
 * ====================
 * Faqja ku përdoruesi kërkon rivendosjen e fjalëkalimit.
 */

$pageTitle = 'Harrova Fjalëkalimin';
require_once __DIR__ . '/../includes/header.php';

// Nëse është i loguar, ridrejto
if (isLoggedIn()) {
    redirect('');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card auth-card shadow">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-key me-2"></i>Rivendos Fjalëkalimin
                    </h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted text-center mb-4">
                        Shkruani email-in tuaj dhe do t'ju dërgojmë një link për të rivendosur fjalëkalimin.
                    </p>

                    <!-- Alert -->
                    <div id="forgot-alert" class="alert d-none"></div>

                    <form id="forgotForm" class="needs-validation" novalidate>
                        <?= csrfField() ?>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="email@shembull.com" required autofocus>
                            </div>
                            <div class="invalid-feedback">Ju lutem shkruani email-in tuaj.</div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
                            <i class="bi bi-send me-2"></i>Dërgo Linkun
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Kthehu te Hyrja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('forgotForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const alert = document.getElementById('forgot-alert');
    const submitBtn = document.getElementById('submitBtn');

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Duke dërguar...';

    try {
        const formData = new FormData(form);
        formData.append('action', 'forgot_password');

        const response = await fetch('<?= SITE_URL ?>/api/auth.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert.className = 'alert alert-success';
            alert.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
            form.reset();
        } else {
            alert.className = 'alert alert-danger';
            alert.innerHTML = '<i class="bi bi-x-circle me-2"></i>' + data.message;
        }

        alert.classList.remove('d-none');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Dërgo Linkun';
    } catch (error) {
        alert.className = 'alert alert-danger';
        alert.innerHTML = '<i class="bi bi-x-circle me-2"></i>Gabim në komunikim me serverin.';
        alert.classList.remove('d-none');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Dërgo Linkun';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
