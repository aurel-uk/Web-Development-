<?php
/**
 * FAQJA E LOGIN
 * ==============
 * Këtu përdoruesit hyjnë në llogarinë e tyre.
 *
 * FUNKSIONALITETET:
 * - Login me email/password
 * - Remember Me (të mbaj mend)
 * - Bllokimi pas 7 tentativash të dështuara
 * - Ridrejtimi sipas rolit (user/admin)
 */

// Vendos titullin
$pageTitle = 'Hyr - ' . SITE_NAME;

// Përfshi header-in
require_once __DIR__ . '/../partials/header.php';

// Nëse përdoruesi është tashmë i loguar, ridrejto
if (isLoggedIn()) {
    redirect(isAdmin() ? 'views/admin/dashboard.php' : 'views/user/dashboard.php');
}

// Inicializo variablat
$errors = [];
$email = '';

// Kontrollo nëse sesioni ka skaduar
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    setFlash('warning', 'Sesioni juaj ka skaduar. Ju lutem hyni përsëri.');
}

// Nëse forma është dërguar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifiko CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesioni ka skaduar. Rifresko faqen.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        // Provo login
        $user = new User();
        $result = $user->login($email, $password, $rememberMe);

        if ($result['success']) {
            setFlash('success', $result['message']);
            redirect($result['redirect']);
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card auth-card shadow fade-in">
                <div class="card-header text-center">
                    <i class="bi bi-box-arrow-in-right fs-1 mb-2 d-block"></i>
                    <h4 class="mb-0">Mirësevjen!</h4>
                    <p class="mb-0 opacity-75 small">Hyr në llogarinë tënde</p>
                </div>

                <div class="card-body p-4">
                    <!-- Gabimet -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="loginForm">
                        <!-- CSRF Token -->
                        <?= csrfField() ?>

                        <!-- Email -->
                        <div class="mb-3">
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

                        <!-- Fjalëkalimi -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i>Fjalëkalimi
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                       placeholder="Fjalëkalimi juaj"
                                       required>
                                <button type="button" class="btn btn-outline-secondary toggle-password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="remember_me"
                                       name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Më mbaj mend
                                </label>
                            </div>
                            <a href="forgot-password.php" class="small">Harrove fjalëkalimin?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Hyr
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1">
                        <span class="px-3 text-muted small">ose</span>
                        <hr class="flex-grow-1">
                    </div>

                    <!-- Social Login (placeholder) -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark" disabled>
                            <i class="bi bi-google me-2"></i>Vazhdo me Google
                        </button>
                        <button type="button" class="btn btn-outline-primary" disabled>
                            <i class="bi bi-facebook me-2"></i>Vazhdo me Facebook
                        </button>
                    </div>
                    <p class="text-center text-muted small mt-2">
                        <i class="bi bi-info-circle me-1"></i>Social login së shpejti
                    </p>
                </div>

                <div class="card-footer text-center py-3 bg-light">
                    <span class="text-muted">Nuk ke llogari?</span>
                    <a href="register.php" class="ms-2">Regjistrohu këtu</a>
                </div>
            </div>

            <!-- Demo Credentials (vetëm për testim) -->
            <?php if (DEBUG_MODE): ?>
                <div class="card mt-3 border-info">
                    <div class="card-body">
                        <h6 class="card-title text-info">
                            <i class="bi bi-bug me-2"></i>Mode Debug - Kredencialet Test:
                        </h6>
                        <div class="row">
                            <div class="col-6">
                                <strong>Admin:</strong><br>
                                <small>admin@webplatform.com</small><br>
                                <small>password</small>
                            </div>
                            <div class="col-6">
                                <strong>User:</strong><br>
                                <small>Regjistrohu për test</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
