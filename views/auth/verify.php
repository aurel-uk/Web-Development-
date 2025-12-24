<?php
/**
 * FAQJA E VERIFIKIMIT TË EMAIL
 * ============================
 * Kur përdoruesi klikon linkun e verifikimit, vjen këtu.
 */

$pageTitle = 'Verifiko Email - ' . SITE_NAME;
require_once __DIR__ . '/../partials/header.php';

$success = false;
$message = '';

// Merr kodin nga URL
$code = $_GET['code'] ?? '';

if (empty($code)) {
    $message = 'Linku i verifikimit është i pavlefshëm.';
} else {
    $user = new User();
    $result = $user->verifyEmail($code);

    $success = $result['success'];
    $message = $result['message'];
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card shadow text-center fade-in">
                <div class="card-header">
                    <?php if ($success): ?>
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                        <h4 class="mb-0 text-success">Verifikimi i Suksesshëm!</h4>
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill text-danger fs-1 mb-2 d-block"></i>
                        <h4 class="mb-0 text-danger">Verifikimi Dështoi</h4>
                    <?php endif; ?>
                </div>

                <div class="card-body p-4">
                    <p class="lead"><?= htmlspecialchars($message) ?></p>

                    <?php if ($success): ?>
                        <a href="login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Hyr në Llogari
                        </a>
                    <?php else: ?>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="register.php" class="btn btn-outline-primary">Regjistrohu Përsëri</a>
                            <a href="login.php" class="btn btn-primary">Hyr</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
