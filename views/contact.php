<?php
/**
 * FAQJA E KONTAKTIT
 * ==================
 * Formë kontakti për vizitorët.
 */

$pageTitle = 'Na Kontaktoni - ' . SITE_NAME;
require_once __DIR__ . '/partials/header.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesioni ka skaduar';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        // Validimi
        if (empty($name)) $errors[] = 'Emri është i detyrueshëm';
        if (!isValidEmail($email)) $errors[] = 'Email i pavlefshëm';
        if (empty($message)) $errors[] = 'Mesazhi është i detyrueshëm';

        if (empty($errors)) {
            $db = Database::getInstance();
            $db->insert('contact_messages', [
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);

            $success = true;
            setFlash('success', 'Mesazhi u dërgua me sukses! Do të ju kontaktojmë së shpejti.');
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold">Na Kontaktoni</h1>
                <p class="lead text-muted">
                    Keni pyetje? Na shkruani dhe do të ju përgjigjemi brenda 24 orëve.
                </p>
            </div>

            <div class="row g-5">
                <!-- Contact Form -->
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <?php if ($success): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle text-success display-3"></i>
                                    <h4 class="mt-3">Faleminderit!</h4>
                                    <p class="text-muted">Mesazhi juaj u dërgua me sukses.</p>
                                </div>
                            <?php else: ?>
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <?= implode('<br>', $errors) ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="">
                                    <?= csrfField() ?>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Emri Juaj *</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="name"
                                                   name="name"
                                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                                   required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email"
                                                   class="form-control"
                                                   id="email"
                                                   name="email"
                                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                                   required>
                                        </div>
                                        <div class="col-12">
                                            <label for="subject" class="form-label">Subjekti</label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="subject"
                                                   name="subject"
                                                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                                        </div>
                                        <div class="col-12">
                                            <label for="message" class="form-label">Mesazhi *</label>
                                            <textarea class="form-control"
                                                      id="message"
                                                      name="message"
                                                      rows="5"
                                                      required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="bi bi-send me-2"></i>Dërgo Mesazhin
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-5">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Informacione Kontakti</h4>

                            <div class="mb-4">
                                <i class="bi bi-geo-alt fs-4 me-3"></i>
                                <span>
                                    <strong>Adresa</strong><br>
                                    Rruga Kryesore, Nr. 123<br>
                                    Tiranë, Shqipëri
                                </span>
                            </div>

                            <div class="mb-4">
                                <i class="bi bi-envelope fs-4 me-3"></i>
                                <span>
                                    <strong>Email</strong><br>
                                    info@webplatform.com<br>
                                    support@webplatform.com
                                </span>
                            </div>

                            <div class="mb-4">
                                <i class="bi bi-telephone fs-4 me-3"></i>
                                <span>
                                    <strong>Telefon</strong><br>
                                    +355 69 123 4567<br>
                                    +355 4 234 5678
                                </span>
                            </div>

                            <div class="mb-4">
                                <i class="bi bi-clock fs-4 me-3"></i>
                                <span>
                                    <strong>Orari i Punës</strong><br>
                                    E Hënë - E Premte: 09:00 - 18:00<br>
                                    E Shtunë: 10:00 - 14:00
                                </span>
                            </div>

                            <hr class="border-light">

                            <h5 class="mb-3">Na Ndiqni</h5>
                            <div class="d-flex gap-3">
                                <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="text-white fs-4"><i class="bi bi-twitter-x"></i></a>
                                <a href="#" class="text-white fs-4"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map (Placeholder) -->
            <div class="card mt-5">
                <div class="card-body p-0">
                    <div class="bg-secondary" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                        <div class="text-white text-center">
                            <i class="bi bi-map display-4"></i>
                            <p class="mt-2">Harta do të shfaqet këtu<br><small>Google Maps Integration</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
