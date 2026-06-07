<?php
/**
 * API - HYRJA E PËRDORUESIT (me 2FA)
 * ==================================
 * Endpoint për identifikimin e përdoruesve me Two-Factor Authentication.
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

// Kontrollo nëse llogaria është aktive
if (!$user['is_active']) {
    echo json_encode(['success' => false, 'message' => 'Llogaria juaj është çaktivizuar.']);
    exit;
}

// Kontrollo nëse 2FA është aktivizuar
$twoFactorEnabled = isset($user['two_factor_enabled']) ? (bool)$user['two_factor_enabled'] : true;

if ($twoFactorEnabled) {
    // Gjenero kod 6-shifror
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Fshi kodet e vjetra për këtë përdorues
    $db->delete('two_factor_codes', 'user_id = ?', [$user['id']]);

    // Ruaj kodin e ri (skadon pas 10 minutash)
    $db->insert('two_factor_codes', [
        'user_id' => $user['id'],
        'code' => $code,
        'expires_at' => date('Y-m-d H:i:s', time() + 600) // 10 minuta
    ]);

    // Ruaj të dhënat e përkohshme në sesion
    $_SESSION['2fa_user_id'] = $user['id'];
    $_SESSION['2fa_remember'] = $remember;
    $_SESSION['2fa_expires'] = time() + 600;

    // Dërgo email me kodin (simulim - në prodhim përdor mail server)
    $emailSent = send2FAEmail($user['email'], $user['first_name'], $code);

    // Logo tentativën
    logUserAction($user['id'], '2fa_code_sent', 'Kod 2FA u dërgua në email');

    echo json_encode([
        'success' => true,
        'requires_2fa' => true,
        'message' => 'Kodi i verifikimit u dërgua në email-in tuaj.',
        'redirect' => SITE_URL . '/auth/verify_2fa.php'
    ]);
    exit;
}

// Nëse 2FA nuk është aktivizuar, krijo sesionin direkt
completeLogin($user, $remember, $db);

/**
 * Përfundon login-in dhe krijon sesionin
 */
function completeLogin($user, $remember, $db) {
    // Pastro tentativat
    $db->delete('login_attempts', 'email = ?', [$user['email']]);

    // Krijo sesionin
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = $user['role_name'] ?? 'user';
    $_SESSION['last_activity'] = time();
    $_SESSION['email_verified'] = (bool) $user['email_verified'];

    // Pastro të dhënat 2FA nga sesioni
    unset($_SESSION['2fa_user_id'], $_SESSION['2fa_remember'], $_SESSION['2fa_expires']);

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
}

/**
 * Dërgon email me kodin 2FA
 */
function send2FAEmail($email, $firstName, $code) {
    $to = $email;
    $subject = SITE_NAME . ' - Kodi i Verifikimit';

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .code { font-size: 32px; font-weight: bold; color: #0d6efd; letter-spacing: 5px;
                    background: #f8f9fa; padding: 20px; text-align: center; border-radius: 10px; }
            .warning { color: #dc3545; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Përshëndetje, {$firstName}!</h2>
            <p>Ju po tentoni të hyni në llogarinë tuaj. Përdorni kodin e mëposhtëm për të vazhduar:</p>
            <div class='code'>{$code}</div>
            <p><strong>Ky kod skadon pas 10 minutash.</strong></p>
            <p class='warning'>Nëse nuk keni kërkuar këtë kod, injoroni këtë email ose kontaktoni mbështetjen.</p>
            <hr>
            <p style='color: #6c757d; font-size: 12px;'>Ky email u dërgua automatikisht nga " . SITE_NAME . "</p>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <noreply@webplatform.com>\r\n";

    // Në Docker/development, logo kodin në vend të dërgimit
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("2FA Code for {$email}: {$code}");
        // Për testim, ruaje kodin në sesion (vetëm në development!)
        $_SESSION['debug_2fa_code'] = $code;
        return true;
    }

    return @mail($to, $subject, $message, $headers);
}
