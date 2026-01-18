<?php
/**
 * VERIFIKIMI I EMAIL-IT
 * =====================
 * Faqja që konfirmon email-in e përdoruesit.
 */

require_once __DIR__ . '/../includes/init.php';

$token = $_GET['token'] ?? '';
$success = false;
$message = '';

if (!empty($token)) {
    $db = Database::getInstance();

    // Gjej tokenin
    $verification = $db->fetchOne(
        "SELECT * FROM email_verifications WHERE token = ? AND expires_at > NOW()",
        [hash('sha256', $token)]
    );

    if ($verification) {
        // Përditëso përdoruesin si të verifikuar
        $db->update('users', ['email_verified' => 1], 'id = ?', [$verification['user_id']]);

        // Fshi tokenin
        $db->delete('email_verifications', 'user_id = ?', [$verification['user_id']]);

        // Logo veprimin
        logUserAction($verification['user_id'], 'email_verified', 'Email u verifikua me sukses');

        $success = true;
        $message = 'Email-i juaj u verifikua me sukses! Tani mund të hyni në llogarinë tuaj.';
    } else {
        $message = 'Linku i verifikimit është i pavlefshëm ose ka skaduar.';
    }
} else {
    $message = 'Linku i verifikimit është i pavlefshëm.';
}

$pageTitle = 'Verifikimi i Email-it';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card shadow text-center">
                <div class="card-body p-5">
                    <?php if ($success): ?>
                        <div class="text-success mb-4">
                            <i class="bi bi-check-circle" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="mb-3">Verifikimi u Krye!</h3>
                        <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                        <a href="login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Hyr në Llogari
                        </a>
                    <?php else: ?>
                        <div class="text-danger mb-4">
                            <i class="bi bi-x-circle" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="mb-3">Gabim në Verifikim</h3>
                        <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                        <a href="<?= SITE_URL ?>" class="btn btn-primary">
                            <i class="bi bi-house me-2"></i>Kryefaqja
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
