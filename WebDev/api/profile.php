<?php
/**
 * API - PROFILI
 * =============
 * Endpoint për përditësimin e profilit.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';

// Kërko autentikim
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Duhet të jeni të loguar.']);
    exit;
}

// Vetëm POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodë e palejuar.']);
    exit;
}

$db = Database::getInstance();
$userId = getCurrentUserId();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_profile':
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $phone = sanitize($_POST['phone'] ?? '');

        // Validime
        if (strlen($firstName) < 2 || strlen($lastName) < 2) {
            echo json_encode(['success' => false, 'message' => 'Emri dhe mbiemri duhet të kenë të paktën 2 karaktere.']);
            exit;
        }

        if (!isValidEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Email i pavlefshëm.']);
            exit;
        }

        // Kontrollo nëse email-i ekziston (për përdorues tjetër)
        $existingUser = $db->fetchOne(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$email, $userId]
        );

        if ($existingUser) {
            echo json_encode(['success' => false, 'message' => 'Ky email përdoret nga dikush tjetër.']);
            exit;
        }

        // Përpuno avatar nëse u ngarkua
        $avatar = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['avatar'], 'users');
            if ($uploadResult['success']) {
                $avatar = $uploadResult['filename'];

                // Fshi avatarin e vjetër
                $oldAvatar = $db->fetchOne("SELECT avatar FROM users WHERE id = ?", [$userId]);
                if ($oldAvatar && $oldAvatar['avatar'] && $oldAvatar['avatar'] !== 'default.png') {
                    deleteImage($oldAvatar['avatar']);
                }
            }
        }

        // Përditëso
        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone ?: null
        ];

        if ($avatar) {
            $updateData['avatar'] = $avatar;
        }

        $db->update('users', $updateData, 'id = ?', [$userId]);

        // Përditëso sesionin
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_email'] = $email;

        logUserAction($userId, 'profile_update', 'Profili u përditësua');

        echo json_encode(['success' => true, 'message' => 'Profili u përditësua me sukses.']);
        break;

    case 'change_password':
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Plotësoni të gjitha fushat.']);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Fjalëkalimet nuk përputhen.']);
            exit;
        }

        $passwordValidation = validatePassword($newPassword);
        if (!$passwordValidation['valid']) {
            echo json_encode(['success' => false, 'message' => implode(' ', $passwordValidation['errors'])]);
            exit;
        }

        // Verifiko fjalëkalimin aktual
        $user = $db->fetchOne("SELECT password FROM users WHERE id = ?", [$userId]);

        if (!password_verify($currentPassword, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Fjalëkalimi aktual është gabim.']);
            exit;
        }

        // Përditëso fjalëkalimin
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $db->update('users', ['password' => $hashedPassword], 'id = ?', [$userId]);

        logUserAction($userId, 'password_change', 'Fjalëkalimi u ndryshua');

        echo json_encode(['success' => true, 'message' => 'Fjalëkalimi u ndryshua me sukses.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Veprim i pavlefshëm.']);
}
