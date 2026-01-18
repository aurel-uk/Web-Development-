<?php
/**
 * FAQJA E KONTAKTIT
 * =================
 * Faqja ku përdoruesit mund të na kontaktojnë.
 */

$pageTitle = 'Kontakt';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Na Kontaktoni</h2>
                <p class="text-muted">Kemi kënaqësi t'ju ndihmojmë me çdo pyetje që mund të keni.</p>
            </div>

            <div class="row g-4">
                <!-- Contact Info -->
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-geo-alt text-primary fs-1"></i>
                            </div>
                            <h5 class="card-title">Adresa</h5>
                            <p class="card-text text-muted">
                                Rruga Kryesore, Nr. 123<br>
                                Tiranë, Shqipëri
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-telephone text-success fs-1"></i>
                            </div>
                            <h5 class="card-title">Telefoni</h5>
                            <p class="card-text text-muted">
                                +355 69 123 4567<br>
                                +355 4 234 5678
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-envelope text-info fs-1"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                info@webplatform.com<br>
                                support@webplatform.com
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-send me-2"></i>Dërgoni Mesazh</h5>
                </div>
                <div class="card-body">
                    <div id="contact-alert" class="alert d-none"></div>

                    <form id="contactForm">
                        <?= csrfField() ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Emri Juaj</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_email'] ?? '') : '' ?>">
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label">Subjekti</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Zgjidhni...</option>
                                    <option value="general">Pyetje e Përgjithshme</option>
                                    <option value="order">Për Porosinë</option>
                                    <option value="product">Për Produktet</option>
                                    <option value="support">Mbështetje Teknike</option>
                                    <option value="other">Tjetër</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Mesazhi</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required
                                          placeholder="Shkruani mesazhin tuaj këtu..."></textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send me-1"></i>Dërgo Mesazhin
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Working Hours -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Orari i Punës</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>E Hënë - E Premte:</strong> 08:00 - 18:00</p>
                            <p class="mb-2"><strong>E Shtunë:</strong> 09:00 - 15:00</p>
                            <p class="mb-0"><strong>E Dielë:</strong> <span class="text-danger">Mbyllur</span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Mbështetja Online:</strong> 24/7</p>
                            <p class="mb-0 text-muted small">Përgjigje brenda 24 orëve në ditët e punës.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const alertBox = document.getElementById('contact-alert');
    const submitBtn = document.getElementById('submitBtn');

    if (!form || !alertBox || !submitBtn) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Duke dërguar...';

        try {
            const formData = new FormData(form);
            const response = await fetch('<?= SITE_URL ?>/api/contact.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alertBox.className = 'alert alert-success';
                alertBox.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                form.reset();
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

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send me-1"></i>Dërgo Mesazhin';
    });
});
</script>
