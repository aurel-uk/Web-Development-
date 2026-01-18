<?php
/**
 * API - REGJISTRIMI I PËRDORUESIT
 * ================================
 * Endpoint për regjistrimin e përdoruesve të rinj.
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

// Merr dhe valido të dhënat
$firstName = sanitize($_POST['first_name'] ?? '');
$lastName = sanitize($_POST['last_name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone = sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';
$terms = isset($_POST['terms']);

$errors = [];

// Validime
if (strlen($firstName) < 2) {
    $errors[] = 'Emri duhet të ketë të paktën 2 karaktere.';
}

if (strlen($lastName) < 2) {
    $errors[] = 'Mbiemri duhet të ketë të paktën 2 karaktere.';
}

if (!isValidEmail($email)) {
    $errors[] = 'Email i pavlefshëm.';
}

if (!empty($phone) && !isValidPhone($phone)) {
    $errors[] = 'Numri i telefonit është i pavlefshëm.';
}

$passwordValidation = validatePassword($password);
if (!$passwordValidation['valid']) {
    $errors = array_merge($errors, $passwordValidation['errors']);
}

if ($password !== $passwordConfirm) {
    $errors[] = 'Fjalëkalimet nuk përputhen.';
}

if (!$terms) {
    $errors[] = 'Duhet të pranoni kushtet e përdorimit.';
}

// Nëse ka gabime, kthe
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$db = Database::getInstance();

// Kontrollo nëse email ekziston
$existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
if ($existingUser) {
    echo json_encode(['success' => false, 'message' => 'Ky email është i regjistruar. Hyni ose përdorni email tjetër.']);
    exit;
}

try {
    $db->beginTransaction();

    // Krijo përdoruesin
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Merr rolin default (user)
    $userRole = $db->fetchOne("SELECT id FROM roles WHERE name = 'user'");
    $roleId = $userRole ? $userRole['id'] : 2; // Default role ID

    $userId = $db->insert('users', [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone ?: null,
        'password' => $hashedPassword,
        'role_id' => $roleId,
        'email_verified' => 0
    ]);

    // Krijo token verifikimi
    $verificationToken = bin2hex(random_bytes(32));
    $db->insert('email_verifications', [
        'user_id' => $userId,
        'token' => hash('sha256', $verificationToken),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
    ]);

    // Logo regjistrimin
    logUserAction($userId, 'register', 'Regjistrim i ri');

    $db->commit();

    // TODO: Dërgo email verifikimi
    // sendVerificationEmail($email, $verificationToken);

    echo json_encode([
        'success' => true,
        'message' => 'Regjistrimi u krye me sukses! Tani mund të hyni.',
        'redirect' => 'login.php'
    ]);

} catch (Exception $e) {
    $db->rollback();

    if (DEBUG_MODE) {
        echo json_encode(['success' => false, 'message' => 'Gabim: ' . $e->getMessage()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gabim në regjistrim. Provoni përsëri.']);
    }
}
