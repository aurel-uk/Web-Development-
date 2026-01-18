<?php
/**
 * API ENDPOINTS - PËRDORUESIT
 * ===========================
 * Menaxhon profilet dhe admin funksionet.
 *
 * Endpoints:
 * - GET /api/users/profile - Merr profilin
 * - PUT /api/users/profile - Përditëso profilin
 * - PUT /api/users/password - Ndrysho fjalëkalimin
 * - GET /api/users - Lista e përdoruesve (admin)
 * - GET /api/users/{id} - Detajet e përdoruesit (admin)
 * - PUT /api/users/{id}/status - Ndrysho statusin (admin)
 * - PUT /api/users/{id}/role - Ndrysho rolin (admin)
 * - DELETE /api/users/{id} - Fshi përdoruesin (admin)
 */

$user = new User();
$method = $_SERVER['REQUEST_METHOD'];

// Nëse action është numër, atëherë është ID
if (is_numeric($action)) {
    $id = (int)$action;
    $action = 'single';
}

switch ($action) {
    // ============================================
    // GET PROFILE
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
        }
        // UPDATE PROFILE
        elseif ($method === 'PUT') {
            $userId = requireAuth();
            $data = getJsonInput();

            $result = $user->updateProfile($userId, $data);

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
        requireMethod('PUT');
        $userId = requireAuth();

        $data = getJsonInput();
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        $result = $user->changePassword($userId, $currentPassword, $newPassword, $confirmPassword);

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
    // LIST ALL USERS (Admin)
    // ============================================
    case '':
    case 'list':
        requireMethod('GET');
        requireAdmin();

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 10);
        $search = $_GET['search'] ?? '';

        $result = $user->getAllUsers($page, $perPage, $search);

        // Hiq fjalëkalimet
        foreach ($result['users'] as &$u) {
            unset($u['password']);
        }

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

        $userData = $user->getUser($id);

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
        requireMethod('PUT');
        requireAdmin();

        $data = getJsonInput();
        $userId = (int)($data['user_id'] ?? $id ?? 0);
        $active = (bool)($data['active'] ?? false);

        if (!$userId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e përdoruesit mungon'
            ], 400);
        }

        $result = $user->toggleUserStatus($userId, $active);

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
    // CHANGE USER ROLE (Admin)
    // ============================================
    case 'role':
        requireMethod('PUT');
        requireAdmin();

        $data = getJsonInput();
        $userId = (int)($data['user_id'] ?? $id ?? 0);
        $roleId = (int)($data['role_id'] ?? 0);

        if (!$userId || !$roleId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e përdoruesit dhe roli janë të detyrueshëm'
            ], 400);
        }

        $result = $user->changeUserRole($userId, $roleId);

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
    // DELETE USER (Admin)
    // ============================================
    case 'delete':
        requireMethod('DELETE');
        requireAdmin();

        $userId = (int)($id ?? $_GET['id'] ?? 0);

        if (!$userId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e përdoruesit mungon'
            ], 400);
        }

        $result = $user->deleteUser($userId);

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

    default:
        jsonResponse([
            'success' => false,
            'message' => 'Veprim i panjohur: ' . $action
        ], 404);
}
