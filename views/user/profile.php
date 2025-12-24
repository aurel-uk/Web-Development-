<?php
/**
 * PROFILI I PËRDORUESIT
 * =====================
 * Faqja ku përdoruesi menaxhon të dhënat e tij personale.
 *
 * FUNKSIONALITETET:
 * - Shiko/Ndrysho të dhënat personale
 * - Ngarko foto profili
 * - Ndrysho fjalëkalimin
 */

$pageTitle = 'Profili Im - ' . SITE_NAME;
require_once __DIR__ . '/../partials/header.php';

// Kontrollo aksesin
if (!isLoggedIn()) {
    setFlash('warning', 'Duhet të jesh i loguar.');
    redirect('views/auth/login.php');
}

$userId = getCurrentUserId();
$userObj = new User();
$userData = $userObj->getUser($userId);

$errors = [];
$success = '';

// Proceso format
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesioni ka skaduar.';
    } else {
        $action = $_POST['action'] ?? '';

        // Përditësimi i profilit
        if ($action === 'update_profile') {
            $result = $userObj->updateProfile($userId, $_POST);
            if ($result['success']) {
                $success = $result['message'];
                $userData = $userObj->getUser($userId); // Rifresko të dhënat
            } else {
                $errors[] = $result['message'];
            }
        }

        // Ngarkimi i fotos
        elseif ($action === 'update_image' && isset($_FILES['profile_image'])) {
            $result = $userObj->updateProfileImage($userId, $_FILES['profile_image']);
            if ($result['success']) {
                $success = $result['message'];
                $userData = $userObj->getUser($userId);
            } else {
                $errors[] = $result['message'];
            }
        }

        // Ndryshimi i fjalëkalimit
        elseif ($action === 'change_password') {
            $result = $userObj->changePassword(
                $userId,
                $_POST['current_password'] ?? '',
                $_POST['new_password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

// Path i imazhit
$imagePath = SITE_URL . '/assets/images/uploads/' . ($userData['profile_image'] ?? 'default.png');
?>

<div class="container py-4">
    <!-- Profile Header -->
    <div class="profile-header rounded-3 shadow mb-4">
        <div class="container">
            <div class="row align-items-center py-4">
                <div class="col-md-3 text-center">
                    <div class="profile-avatar-upload">
                        <img src="<?= $imagePath ?>"
                             alt="Profile"
                             class="profile-avatar"
                             id="profileImagePreview">
                        <form method="POST" enctype="multipart/form-data" id="imageForm">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update_image">
                            <input type="file"
                                   name="profile_image"
                                   id="profileImageInput"
                                   accept="image/*"
                                   class="d-none"
                                   data-preview="#profileImagePreview">
                            <button type="button"
                                    class="upload-btn"
                                    onclick="document.getElementById('profileImageInput').click()">
                                <i class="bi bi-camera"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md-9 mt-3 mt-md-0">
                    <h2 class="mb-1"><?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></h2>
                    <p class="mb-2 opacity-75">
                        <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($userData['email']) ?>
                    </p>
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-person-badge me-1"></i>
                        <?= ucfirst($userData['role_name']) ?>
                    </span>
                    <span class="badge bg-light text-dark ms-2">
                        <i class="bi bi-calendar me-1"></i>
                        Anëtar që nga <?= formatDate($userData['created_at'], 'F Y') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Informacionet Personale</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Emri</label>
                                <input type="text"
                                       class="form-control"
                                       id="first_name"
                                       name="first_name"
                                       value="<?= htmlspecialchars($userData['first_name']) ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Mbiemri</label>
                                <input type="text"
                                       class="form-control"
                                       id="last_name"
                                       name="last_name"
                                       value="<?= htmlspecialchars($userData['last_name']) ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       value="<?= htmlspecialchars($userData['email']) ?>"
                                       disabled>
                                <small class="text-muted">Email nuk mund të ndryshohet</small>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefoni</label>
                                <input type="tel"
                                       class="form-control"
                                       id="phone"
                                       name="phone"
                                       value="<?= htmlspecialchars($userData['phone'] ?? '') ?>"
                                       placeholder="+355 69 123 4567">
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Adresa</label>
                                <input type="text"
                                       class="form-control"
                                       id="address"
                                       name="address"
                                       value="<?= htmlspecialchars($userData['address'] ?? '') ?>"
                                       placeholder="Rruga, numri, pallati">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">Qyteti</label>
                                <input type="text"
                                       class="form-control"
                                       id="city"
                                       name="city"
                                       value="<?= htmlspecialchars($userData['city'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control"
                                          id="bio"
                                          name="bio"
                                          rows="3"
                                          placeholder="Shkruaj diçka për veten..."><?= htmlspecialchars($userData['bio'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check me-2"></i>Ruaj Ndryshimet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Change Password -->
            <div class="card shadow-sm mb-4" id="change-password">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lock me-2"></i>Ndrysho Fjalëkalimin</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Fjalëkalimi Aktual</label>
                            <input type="password"
                                   class="form-control"
                                   id="current_password"
                                   name="current_password"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Fjalëkalimi i Ri</label>
                            <input type="password"
                                   class="form-control"
                                   id="new_password"
                                   name="new_password"
                                   required
                                   minlength="8">
                            <small class="text-muted">Min. 8 karaktere</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmo Fjalëkalimin</label>
                            <input type="password"
                                   class="form-control"
                                   id="confirm_password"
                                   name="confirm_password"
                                   required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-lock me-2"></i>Ndrysho
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Detaje Llogarie</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Statusi</span>
                            <span class="badge bg-success">Aktiv</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Email Verifikuar</span>
                            <span>
                                <?php if ($userData['is_verified']): ?>
                                    <i class="bi bi-check-circle text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle text-danger"></i>
                                <?php endif; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Login i Fundit</span>
                            <span class="text-muted small">
                                <?= $userData['last_login'] ? formatDate($userData['last_login']) : 'N/A' ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-submit kur zgjidhet foto
document.getElementById('profileImageInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImagePreview').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);

        // Submit
        document.getElementById('imageForm').submit();
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
