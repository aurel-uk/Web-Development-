<?php
/**
 * FAQJA - HARROVA FJALËKALIMIN
 * ============================
 * Përdoruesi kërkon link për të rivendosur fjalëkalimin.
 */

$pageTitle = 'Rivendos Fjalëkalimin - ' . SITE_NAME;
require_once __DIR__ . '/../partials/header.php';

$success = false;
$message = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesioni ka skaduar.';
    } else {
        $email = $_POST['email'] ?? '';
        $user = new User();
        $result = $user->requestPasswordReset($email);

        $success = $result['success'];
        $message = $result['message'];

        // Debug link
        if (DEBUG_MODE && isset($_SESSION['debug_reset_link'])) {
            $debugLink = $_SESSION['debug_reset_link'];
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card shadow fade-in">
                <div class="card-header text-center">
                    <i class="bi bi-key fs-1 mb-2 d-block"></i>
                    <h4 class="mb-0">Rivendos Fjalëkalimin</h4>
                    <p class="mb-0 opacity-75 small">Shkruaj email-in për të marrë linkun</p>
                </div>

                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= htmlspecialchars($message) ?>
                        </div>

                        <?php if (DEBUG_MODE && isset($debugLink)): ?>
                            <div class="alert alert-info">
                                <strong>Mode Debug:</strong><br>
                                <a href="<?= $debugLink ?>" class="small"><?= $debugLink ?></a>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <?= csrfField() ?>

                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>Email
                                </label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       value="<?= htmlspecialchars($email) ?>"
                                       placeholder="emri@shembull.com"
                                       required
                                       autofocus>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-envelope me-2"></i>Dërgo Linkun
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="card-footer text-center py-3 bg-light">
                    <a href="login.php"><i class="bi bi-arrow-left me-1"></i>Kthehu te Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
