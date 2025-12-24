<?php
/**
 * FAQJA - RIVENDOS FJALËKALIMIN
 * =============================
 * Kur përdoruesi klikon linkun nga email, vjen këtu për të vendosur
 * fjalëkalimin e ri.
 */

$pageTitle = 'Fjalëkalimi i Ri - ' . SITE_NAME;
require_once __DIR__ . '/../partials/header.php';

$code = $_GET['code'] ?? '';
$success = false;
$error = '';

// Kontrollo nëse kodi ekziston
if (empty($code)) {
    $error = 'Linku është i pavlefshëm.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Sesioni ka skaduar.';
    } else {
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $user = new User();
        $result = $user->resetPassword($code, $newPassword, $confirmPassword);

        if ($result['success']) {
            $success = true;
            setFlash('success', $result['message']);
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card shadow fade-in">
                <div class="card-header text-center">
                    <?php if ($success): ?>
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                        <h4 class="mb-0 text-success">Fjalëkalimi u Ndryshua!</h4>
                    <?php else: ?>
                        <i class="bi bi-lock-fill fs-1 mb-2 d-block"></i>
                        <h4 class="mb-0">Vendos Fjalëkalimin e Ri</h4>
                    <?php endif; ?>
                </div>

                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <p class="text-center">Fjalëkalimi u rivendos me sukses. Tani mund të hysh me fjalëkalimin e ri.</p>
                        <a href="login.php" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Hyr në Llogari
                        </a>

                    <?php elseif (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <a href="forgot-password.php" class="btn btn-outline-primary w-100">
                            Kërko Link të Ri
                        </a>

                    <?php else: ?>
                        <form method="POST" action="">
                            <?= csrfField() ?>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Fjalëkalimi i Ri
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           required
                                           minlength="8">
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength mt-2">
                                    <div class="password-strength-bar"></div>
                                </div>
                                <small class="text-muted">Min. 8 karaktere, 1 shkronjë e madhe, 1 numër</small>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>Konfirmo Fjalëkalimin
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="confirm_password"
                                       name="confirm_password"
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg me-2"></i>Ruaj Fjalëkalimin
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
