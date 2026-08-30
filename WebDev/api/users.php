<?php
/**
 * API ENDPOINTS - PËRDORUESIT
 * ===========================
 * Endpoint standalone (jo vetëm përmes routerit api/index.php) që përdoret
 * drejtpërdrejt nga paneli i adminit (admin/users.php -> POST /api/users.php).
 *
 * Veprime (action nga $_GET/$_POST):
 * - GET  ?action=profile     - Merr profilin (përdoruesi i loguar)
 * - PUT  action=profile      - Përditëso profilin (JSON body)
 * - PUT  action=password     - Ndrysho fjalëkalimin (JSON body)
 * - POST action=delete       - Fshi përdoruesin (admin)
 * - POST action=status       - Ndrysho statusin (admin)
 * - POST action=role         - Ndrysho rolin (admin)
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/api_helpers.php';

header('Content-Type: application/json; charset=utf-8');

$jsonBody = getJsonInput();
$action = $_GET['action'] ?? $_POST['action'] ?? $jsonBody['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$user = new User();

switch ($action) {
    // ============================================
    // GET / UPDATE PROFILE
    // ============================================
    case 'profile':
        if ($method === 'GET') {
            $userId = requireAuth();
            $userData = $user->getUser($userId);

            if ($userData) {
                unset($userData['password']);
                jsonResponse([
                    'success' => true,
                    'data' => $userData
                ]);
            } else {
                jsonResponse([
                    'success' => false,
                    'message' => 'Përdoruesi nuk u gjet'
                ], 404);
            }
        } elseif ($method === 'PUT' || $method === 'POST') {
            $userId = requireAuth();
            requireCsrf();
            $data = getJsonInput();

            $result = $user->updateProfile($userId, $data);

            jsonResponse([
                'success' => $result['success'],
                'message' => $result['message']
            ], $result['success'] ? 200 : 400);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'Metoda e gabuar'
            ], 405);
        }
        break;

    // ============================================
    // CHANGE PASSWORD
    // ============================================
    case 'password':
        if (!in_array($method, ['PUT', 'POST'], true)) {
            jsonResponse(['success' => false, 'message' => 'Metoda e gabuar'], 405);
        }
        $userId = requireAuth();
        requireCsrf();

        $data = getJsonInput();
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        $result = $user->changePassword($userId, $currentPassword, $newPassword, $confirmPassword);

        jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ], $result['success'] ? 200 : 400);
        break;

    // ============================================
    // LIST ALL USERS (Admin)
    // ============================================
    case '':
    case 'list':
        requireMethod('GET');
        requireAdmin();

        $page = (int)($_GET['page'] ?? 1);
        $perPage = clampPerPage((int)($_GET['per_page'] ?? 10));
        $search = $_GET['search'] ?? '';

        $result = $user->getAllUsers($page, $perPage, $search);

        foreach ($result['users'] as &$u) {
            unset($u['password']);
        }
        unset($u);

        jsonResponse([
            'success' => true,
            'data' => $result['users'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total_items' => $result['total'],
                'per_page' => $perPage
            ]
        ]);
        break;

    // ============================================
    // GET SINGLE USER (Admin)
    // ============================================
    case 'single':
        requireMethod('GET');
        requireAdmin();

        $userId = (int)($_GET['id'] ?? 0);
        $userData = $userId ? $user->getUser($userId) : null;

        if ($userData) {
            unset($userData['password']);
            jsonResponse([
                'success' => true,
                'data' => $userData
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'Përdoruesi nuk u gjet'
            ], 404);
        }
        break;

    // ============================================
    // CHANGE USER STATUS (Admin)
    // ============================================
    case 'status':
        requireMethod('POST');
        requireAdmin();
        requireCsrf();

        $data = getJsonInput();
        $userId = (int)($_POST['user_id'] ?? $data['user_id'] ?? 0);
        $active = filter_var($_POST['active'] ?? $data['active'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$userId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e përdoruesit mungon'
            ], 400);
        }

        $result = $user->toggleUserStatus($userId, $active);

        jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ], $result['success'] ? 200 : 400);
        break;

    // ============================================
    // CHANGE USER ROLE (Admin)
    // ============================================
    case 'role':
        requireMethod('POST');
        requireAdmin();
        requireCsrf();

        $data = getJsonInput();
        $userId = (int)($_POST['user_id'] ?? $data['user_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? $data['role_id'] ?? 0);

        if (!$userId || !$roleId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e përdoruesit dhe roli janë të detyrueshëm'
            ], 400);
        }

        $result = $user->changeUserRole($userId, $roleId);

        jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ], $result['success'] ? 200 : 400);
        break;

    // ============================================
    // DELETE USER (Admin)
    // ============================================
    case 'delete':
        // Formularët HTML nuk dërgojnë dot metodën DELETE, prandaj pranojmë POST
        if (!in_array($method, ['POST', 'DELETE'], true)) {
            jsonResponse([
                'success' => false,
                'message' => 'Metoda HTTP e gabuar. Pritej: POST ose DELETE'
            ], 405);
        }

        requireAdmin();
        requireCsrf();

        $userId = (int)($_POST['user_id'] ?? $_GET['id'] ?? 0);

        if (!$userId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e përdoruesit mungon'
            ], 400);
        }

        if ($userId === getCurrentUserId()) {
            jsonResponse([
                'success' => false,
                'message' => 'Nuk mund të fshini llogarinë tuaj'
            ], 400);
        }

        $result = $user->deleteUser($userId);

        jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ], $result['success'] ? 200 : 400);
        break;

    default:
        jsonResponse([
            'success' => false,
            'message' => 'Veprim i panjohur: ' . $action
        ], 404);
}
