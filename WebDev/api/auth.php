<?php
/**
 * API ENDPOINTS - AUTENTIKIMI
 * ===========================
 * Menaxhon login, logout, regjistrim, etj.
 *
 * Endpoints:
 * - POST /api/auth/login - Login
 * - POST /api/auth/register - Regjistrim
 * - POST /api/auth/logout - Logout
 * - POST /api/auth/forgot-password - Kërko rivendosje fjalëkalimi
 * - POST /api/auth/reset-password - Rivendos fjalëkalimin
 * - GET /api/auth/me - Merr përdoruesin aktual
 */

$user = new User();

switch ($action) {
    // ============================================
    // LOGIN
    // ============================================
    case 'login':
        requireMethod('POST');
        $data = getJsonInput();

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $rememberMe = $data['remember_me'] ?? false;

        $result = $user->login($email, $password, $rememberMe);

        if ($result['success']) {
            jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['user_name'],
                    'email' => $_SESSION['user_email'],
                    'role' => $_SESSION['user_role']
                ],
                'redirect' => $result['redirect']
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 401);
        }
        break;

    // ============================================
    // REGISTER
    // ============================================
    case 'register':
        requireMethod('POST');
        $data = getJsonInput();

        $result = $user->register($data);

        if ($result['success']) {
            jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'user_id' => $result['user_id']
            ], 201);
        } else {
            jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        break;

    // ============================================
    // LOGOUT
    // ============================================
    case 'logout':
        requireMethod('POST');
        $user->logout();
        jsonResponse([
            'success' => true,
            'message' => 'U dolët me sukses'
        ]);
        break;

    // ============================================
    // FORGOT PASSWORD
    // ============================================
    case 'forgot-password':
        requireMethod('POST');
        $data = getJsonInput();

        $email = $data['email'] ?? '';
        $result = $user->requestPasswordReset($email);

        jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
        break;

    // ============================================
    // RESET PASSWORD
    // ============================================
    case 'reset-password':
        requireMethod('POST');
        $data = getJsonInput();

        $code = $data['code'] ?? '';
        $newPassword = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        $result = $user->resetPassword($code, $newPassword, $confirmPassword);

        if ($result['success']) {
            jsonResponse([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        break;

    // ============================================
    // GET CURRENT USER
    // ============================================
    case 'me':
        requireMethod('GET');
        $userId = requireAuth();

        $userData = $user->getUser($userId);

        if ($userData) {
            // Hiq fjalëkalimin nga përgjigja
            unset($userData['password']);

            jsonResponse([
                'success' => true,
                'user' => $userData
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'Përdoruesi nuk u gjet'
            ], 404);
        }
        break;

    // ============================================
    // VERIFY EMAIL
    // ============================================
    case 'verify':
        requireMethod('POST');
        $data = getJsonInput();

        $code = $data['code'] ?? '';
        $result = $user->verifyEmail($code);

        if ($result['success']) {
            jsonResponse([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        break;

    // ============================================
    // CHECK AUTH STATUS
    // ============================================
    case 'check':
        requireMethod('GET');
        jsonResponse([
            'success' => true,
            'authenticated' => isLoggedIn(),
            'user' => isLoggedIn() ? [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role']
            ] : null
        ]);
        break;

    default:
        jsonResponse([
            'success' => false,
            'message' => 'Veprim i panjohur: ' . $action
        ], 404);
}
