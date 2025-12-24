<?php
/**
 * FAQJA E REGJISTRIMIT
 * ====================
 * Këtu përdoruesit e rinj krijojnë llogarinë e tyre.
 *
 * SHPJEGIM:
 * 1. Kur hapet faqja, shfaqet forma
 * 2. Kur dërgohet forma (POST), të dhënat validohen
 * 3. Nëse janë të sakta, krijohet llogaria
 * 4. Dërgohet email verifikimi
 */

// Vendos titullin para header
$pageTitle = 'Regjistrohu - ' . SITE_NAME;

// Përfshi header-in
require_once __DIR__ . '/../partials/header.php';

// Inicializo variablat
$errors = [];
$success = false;
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => ''
];

// Nëse forma është dërguar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifiko CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesioni ka skaduar. Ju lutem rifreskoni faqen.';
    } else {
        // Ruaj të dhënat për ri-plotësim nëse ka gabim
        $formData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];

        // Krijo instancën User dhe regjistro
        $user = new User();
        $result = $user->register($_POST);

        if ($result['success']) {
            $success = true;
            setFlash('success', $result['message']);

            // Në mode debug, shfaq linkun e verifikimit
            if (DEBUG_MODE && isset($_SESSION['debug_verify_link'])) {
                $debugLink = $_SESSION['debug_verify_link'];
            }
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <?php if ($success): ?>
                <!-- Sukses Message -->
                <div class="card auth-card shadow fade-in">
                    <div class="card-header text-center">
                        <i class="bi bi-check-circle fs-1 mb-2 d-block"></i>
                        <h4 class="mb-0">Regjistrimi u Krye!</h4>
                    </div>
                    <div class="card-body text-center">
                        <p class="lead">
                            <i class="bi bi-envelope-check me-2"></i>
                            Kontrollo email-in tënd për të verifikuar llogarinë.
                        </p>
                        <p class="text-muted">
                            Dërguam një email te adresa juaj me linkun e verifikimit.
                            Nëse nuk e gjen, kontrollo dosjen SPAM.
                        </p>

                        <?php if (DEBUG_MODE && isset($debugLink)): ?>
                            <div class="alert alert-info mt-3">
                                <strong><i class="bi bi-bug me-2"></i>Mode Debug:</strong><br>
                                <small>Link verifikimi (vetëm për testim):</small><br>
                                <a href="<?= $debugLink ?>" class="small"><?= $debugLink ?></a>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a href="login.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Vazhdo te Login
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Registration Form -->
                <div class="card auth-card shadow fade-in">
                    <div class="card-header text-center">
                        <i class="bi bi-person-plus fs-1 mb-2 d-block"></i>
                        <h4 class="mb-0">Krijo Llogarinë</h4>
                        <p class="mb-0 opacity-75 small">Plotëso të dhënat për tu regjistruar</p>
                    </div>

                    <div class="card-body p-4">
                        <!-- Shfaq gabimet -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="registerForm" novalidate>
                            <!-- CSRF Token -->
                            <?= csrfField() ?>

                            <!-- Emri dhe Mbiemri në një rresht -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">
                                        <i class="bi bi-person me-1"></i>Emri *
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="first_name"
                                           name="first_name"
                                           value="<?= htmlspecialchars($formData['first_name']) ?>"
                                           placeholder="Emri juaj"
                                           required
                                           minlength="2"
                                           maxlength="50">
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">
                                        <i class="bi bi-person me-1"></i>Mbiemri *
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="last_name"
                                           name="last_name"
                                           value="<?= htmlspecialchars($formData['last_name']) ?>"
                                           placeholder="Mbiemri juaj"
                                           required
                                           minlength="2"
                                           maxlength="50">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>Email *
                                </label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       value="<?= htmlspecialchars($formData['email']) ?>"
                                       placeholder="emri@shembull.com"
                                       required>
                                <div class="form-text">
                                    Do të dërgojmë një kod verifikimi në këtë adresë
                                </div>
                            </div>

                            <!-- Fjalëkalimi -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Fjalëkalimi *
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           placeholder="Fjalëkalimi juaj"
                                           required
                                           minlength="8">
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <!-- Password Strength Indicator -->
                                <div class="password-strength mt-2">
                                    <div class="password-strength-bar"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Min. 8 karaktere, 1 shkronjë e madhe, 1 numër</small>
                                    <small class="password-strength-text"></small>
                                </div>
                            </div>

                            <!-- Konfirmo Fjalëkalimin -->
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill me-1"></i>Konfirmo Fjalëkalimin *
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="confirm_password"
                                           name="confirm_password"
                                           placeholder="Përsërit fjalëkalimin"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Termat & Kushtet -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="terms"
                                           name="terms"
                                           required>
                                    <label class="form-check-label" for="terms">
                                        Pranoj <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Termat & Kushtet</a> *
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-person-plus me-2"></i>Regjistrohu
                            </button>
                        </form>
                    </div>

                    <div class="card-footer text-center py-3 bg-light">
                        <span class="text-muted">Ke tashmë një llogari?</span>
                        <a href="login.php" class="ms-2">Hyr këtu</a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Termat & Kushtet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Pranimi i Kushteve</h6>
                <p>Duke u regjistruar në platformën tonë, ju pranoni këto terma dhe kushte të përdorimit.</p>

                <h6>2. Llogaria Juaj</h6>
                <p>Ju jeni përgjegjës për ruajtjen e konfidencialitetit të llogarisë suaj dhe fjalëkalimit.</p>

                <h6>3. Privatësia</h6>
                <p>Ne mbrojmë të dhënat tuaja personale sipas ligjeve në fuqi. Nuk do të ndajmë të dhënat tuaja me palë të treta pa pëlqimin tuaj.</p>

                <h6>4. Përdorimi i Shërbimit</h6>
                <p>Përdorimi i platformës duhet të jetë në përputhje me ligjet dhe rregullat në fuqi.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">E kuptova</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
