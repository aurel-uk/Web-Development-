<?php
/**
 * PROFILI I PËRDORUESIT
 * =====================
 * Faqja ku përdoruesi shikon dhe modifikon profilin e tij.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Profili Im';
$db = Database::getInstance();
$userId = getCurrentUserId();

// Merr të dhënat e përdoruesit
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Profile Header -->
<div class="profile-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="profile-avatar-upload">
                    <img src="<?= IMAGES_URL ?>/users/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>"
                         alt="Avatar" class="profile-avatar" id="avatarPreview">
                    <button type="button" class="upload-btn" onclick="document.getElementById('avatarInput').click()">
                        <i class="bi bi-camera"></i>
                    </button>
                </div>
            </div>
            <div class="col">
                <h2 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email']) ?>
                </p>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-calendar me-2"></i>Anëtar që nga <?= formatDate($user['created_at'], 'd/m/Y') ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="list-group list-group-flush">
                    <a href="#personal" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="bi bi-person me-2"></i>Të Dhënat Personale
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-shield-lock me-2"></i>Siguria
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-bag me-2"></i>Porositë e Mia
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- Personal Info Tab -->
                <div class="tab-pane fade show active" id="personal">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Të Dhënat Personale</h5>
                        </div>
                        <div class="card-body">
                            <form id="profileForm">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="update_profile">

                                <!-- Hidden avatar input -->
                                <input type="file" id="avatarInput" name="avatar" accept="image/*" class="d-none">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">Emri</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                               value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Mbiemri</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                               value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Telefoni</label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check me-1"></i>Ruaj Ndryshimet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Ndrysho Fjalëkalimin</h5>
                        </div>
                        <div class="card-body">
                            <form id="passwordForm">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="change_password">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="current_password" class="form-label">Fjalëkalimi Aktual</label>
                                        <input type="password" class="form-control" id="current_password"
                                               name="current_password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label">Fjalëkalimi i Ri</label>
                                        <input type="password" class="form-control" id="new_password"
                                               name="new_password" required minlength="8">
                                        <div class="password-strength mt-2">
                                            <div class="password-strength-bar"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Konfirmo Fjalëkalimin</label>
                                        <input type="password" class="form-control" id="confirm_password"
                                               name="confirm_password" required>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-shield-check me-1"></i>Ndrysho Fjalëkalimin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Verifikimi me Dy Hapa (2FA)</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Autentikimi me Email</h6>
                                    <p class="text-muted mb-0 small">
                                        Kur aktivizohet, do t'ju dërgohet një kod 6-shifror në email çdo herë që hyni.
                                    </p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="twoFactorToggle" style="width: 3em; height: 1.5em;"
                                           <?= (isset($user['two_factor_enabled']) && $user['two_factor_enabled']) ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div id="2fa-status" class="mt-3">
                                <?php if (isset($user['two_factor_enabled']) && $user['two_factor_enabled']): ?>
                                    <div class="alert alert-success mb-0">
                                        <i class="bi bi-check-circle me-2"></i>
                                        <strong>2FA është aktivizuar.</strong> Llogaria juaj ka mbrojtje shtesë.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>2FA nuk është aktivizuar.</strong> Rekomandojmë ta aktivizoni për siguri më të lartë.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Log -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Aktiviteti i Fundit</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php
                            $logs = $db->fetchAll(
                                "SELECT * FROM user_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
                                [$userId]
                            );
                            ?>
                            <?php if (empty($logs)): ?>
                                <p class="text-muted text-center py-4">Nuk ka aktivitet të regjistruar.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($logs as $log): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-circle-fill text-primary me-2" style="font-size: 0.5rem;"></i>
                                                <?= htmlspecialchars($log['description'] ?: $log['action']) ?>
                                            </div>
                                            <small class="text-muted"><?= formatDate($log['created_at']) ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
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
    // Avatar preview
    const avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Profile form
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const avatarInput = document.getElementById('avatarInput');
            if (avatarInput && avatarInput.files[0]) {
                formData.append('avatar', avatarInput.files[0]);
            }

            try {
                const response = await fetch('<?= SITE_URL ?>/api/profile.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    if (typeof AppUtils !== 'undefined') AppUtils.showToast('success', data.message);
                } else {
                    if (typeof AppUtils !== 'undefined') AppUtils.showToast('error', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof AppUtils !== 'undefined') AppUtils.showToast('error', 'Gabim në komunikim me serverin.');
            }
        });
    }

    // Password form
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            if (newPass !== confirmPass) {
                if (typeof AppUtils !== 'undefined') AppUtils.showToast('error', 'Fjalëkalimet nuk përputhen.');
                return;
            }

            const formData = new FormData(this);

            try {
                const response = await fetch('<?= SITE_URL ?>/api/profile.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    if (typeof AppUtils !== 'undefined') AppUtils.showToast('success', data.message);
                    this.reset();
                } else {
                    if (typeof AppUtils !== 'undefined') AppUtils.showToast('error', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof AppUtils !== 'undefined') AppUtils.showToast('error', 'Gabim në komunikim me serverin.');
            }
        });
    }

    // Two-Factor Authentication Toggle
    const twoFactorToggle = document.getElementById('twoFactorToggle');
    if (twoFactorToggle) {
        twoFactorToggle.addEventListener('change', async function() {
            const enabled = this.checked;
            const statusDiv = document.getElementById('2fa-status');

            try {
                const formData = new FormData();
                formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);
                formData.append('action', 'toggle_2fa');
                formData.append('enabled', enabled ? '1' : '0');

                const response = await fetch('<?= SITE_URL ?>/api/profile.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    if (typeof AppUtils !== 'undefined') AppUtils.showToast('success', data.message);

                    // Përditëso statusin
                    if (enabled) {
                        statusDiv.innerHTML = `
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>2FA është aktivizuar.</strong> Llogaria juaj ka mbrojtje shtesë.
                            </div>
                        `;
                    } else {
                        statusDiv.innerHTML = `
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>2FA nuk është aktivizuar.</strong> Rekomandojmë ta aktivizoni për siguri më të lartë.
                            </div>
                        `;
                    }
                } else {
                    if (typeof AppUtils !== 'undefined') AppUtils.showToast('error', data.message);
                    // Kthe toggle në gjendjen e mëparshme
                    this.checked = !enabled;
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof AppUtils !== 'undefined') AppUtils.showToast('error', 'Gabim në komunikim me serverin.');
                // Kthe toggle në gjendjen e mëparshme
                this.checked = !enabled;
            }
        });
    }
});
</script>
