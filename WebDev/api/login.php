<?php
/**
 * API - HYRJA E PËRDORUESIT
 * =========================
 * Endpoint për identifikimin e përdoruesve.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';

// Vetëm POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodë e palejuar.']);
    exit;
}

// Verifiko CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token i pavlefshëm.']);
    exit;
}

$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Plotësoni të gjitha fushat.']);
    exit;
}

$db = Database::getInstance();

// Kontrollo nëse llogaria është bllokuar
$lockout = $db->fetchOne(
    "SELECT * FROM login_attempts WHERE email = ? AND attempts >= ? AND locked_until > NOW()",
    [$email, MAX_LOGIN_ATTEMPTS]
);

if ($lockout) {
    $remainingTime = ceil((strtotime($lockout['locked_until']) - time()) / 60);
    echo json_encode([
        'success' => false,
        'message' => "Llogaria është bllokuar. Provoni përsëri pas {$remainingTime} minutash.",
        'locked' => true
    ]);
    exit;
}

// Gjej përdoruesin
$user = $db->fetchOne(
    "SELECT u.*, r.name as role_name FROM users u
     LEFT JOIN roles r ON u.role_id = r.id
     WHERE u.email = ?",
    [$email]
);

// Verifiko fjalëkalimin
if (!$user || !password_verify($password, $user['password'])) {
    // Regjistro tentativën e dështuar
    $attempt = $db->fetchOne("SELECT * FROM login_attempts WHERE email = ?", [$email]);

    if ($attempt) {
        $newAttempts = $attempt['attempts'] + 1;
        $lockUntil = $newAttempts >= MAX_LOGIN_ATTEMPTS
            ? date('Y-m-d H:i:s', time() + LOCKOUT_TIME)
            : null;

        $db->update('login_attempts',
            ['attempts' => $newAttempts, 'locked_until' => $lockUntil],
            'email = ?',
            [$email]
        );
    } else {
        $db->insert('login_attempts', [
            'email' => $email,
            'attempts' => 1
        ]);
    }

    logUserAction(null, 'login_failed', "Tentativë e dështuar për: {$email}");

    $remainingAttempts = MAX_LOGIN_ATTEMPTS - (($attempt['attempts'] ?? 0) + 1);

    if ($remainingAttempts <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Shumë tentativa të dështuara. Llogaria u bllokua për 30 minuta.',
            'locked' => true
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Email ose fjalëkalim i gabuar. Ju kanë mbetur {$remainingAttempts} tentativa."
        ]);
    }
    exit;
}

// Login i suksesshëm - pastro tentativat
$db->delete('login_attempts', 'email = ?', [$email]);

// Krijo sesionin
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_role'] = $user['role_name'] ?? 'user';
$_SESSION['last_activity'] = time();
$_SESSION['email_verified'] = (bool) $user['email_verified'];

// Remember Me
if ($remember) {
    refreshRememberToken($user['id']);
}

// Logo hyrjen
logUserAction($user['id'], 'login', 'Hyrje e suksesshme');

// Përditëso last_login
$db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

// Kontrollo nëse ka redirect të ruajtur
$redirect = $_SESSION['redirect_after_login'] ?? SITE_URL;
unset($_SESSION['redirect_after_login']);

echo json_encode([
    'success' => true,
    'message' => 'Mirë se erdhe, ' . htmlspecialchars($user['first_name']) . '!',
    'redirect' => $redirect
]);
