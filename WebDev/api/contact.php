<?php
/**
 * API - KONTAKTI
 * ==============
 * Endpoint për formularin e kontaktit.
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

// Merr të dhënat
$name = sanitize($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

// Validime
if (strlen($name) < 2) {
    echo json_encode(['success' => false, 'message' => 'Emri duhet të ketë të paktën 2 karaktere.']);
    exit;
}

if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Email i pavlefshëm.']);
    exit;
}

if (empty($subject)) {
    echo json_encode(['success' => false, 'message' => 'Zgjidhni subjektin.']);
    exit;
}

if (strlen($message) < 10) {
    echo json_encode(['success' => false, 'message' => 'Mesazhi duhet të ketë të paktën 10 karaktere.']);
    exit;
}

$db = Database::getInstance();

try {
    // Ruaj mesazhin në databazë
    $db->insert('contact_messages', [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'ip_address' => getClientIP(),
        'user_id' => getCurrentUserId()
    ]);

    // TODO: Dërgo email notifikimi
    // sendContactNotification($name, $email, $subject, $message);

    // Logo veprimin
    logUserAction(getCurrentUserId(), 'contact_form', "Mesazh kontakti: {$subject}");

    echo json_encode([
        'success' => true,
        'message' => 'Mesazhi juaj u dërgua me sukses! Do t\'ju kontaktojmë së shpejti.'
    ]);

} catch (Exception $e) {
    if (DEBUG_MODE) {
        echo json_encode(['success' => false, 'message' => 'Gabim: ' . $e->getMessage()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ndodhi një gabim. Provoni përsëri.']);
    }
}
