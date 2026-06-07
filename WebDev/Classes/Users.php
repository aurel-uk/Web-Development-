<?php
/**
 * KLASA USER
 * ===========
 * Menaxhon të gjitha veprimet që lidhen me përdoruesit:
 * - Regjistrimi
 * - Login/Logout
 * - Verifikimi me email
 * - Reset fjalëkalimi
 * - Menaxhimi i profilit
 *
 * SHPJEGIM për fillestarët:
 * Kjo klasë përmban të gjitha funksionet për përdoruesit.
 * Në vend që të shkruash të njëjtin kod disa herë,
 * e shkruan një herë këtu dhe e përdor kudo.
 */

class User
{
    private Database $db;
    private ?array $userData = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // REGJISTRIMI
    // ============================================

    /**
     * Regjistron një përdorues të ri
     *
     * @param array $data - Të dhënat e formës
     * @return array ['success' => bool, 'message' => string, 'user_id' => int]
     */
    public function register(array $data): array
    {
        // Pastro të dhënat
        $firstName = sanitize($data['first_name'] ?? '');
        $lastName = sanitize($data['last_name'] ?? '');
        $email = sanitize($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        // VALIDIMI

        // 1. Kontrollo fushat e zbrazëta
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Të gjitha fushat janë të detyrueshme'];
        }

        // 2. Valido email-in
        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Email-i nuk është i vlefshëm'];
        }

        // 3. Kontrollo nëse email ekziston
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Ky email është regjistruar tashmë'];
        }

        // 4. Valido fjalëkalimin
        $passwordValidation = validatePassword($password);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'message' => implode('. ', $passwordValidation['errors'])];
        }

        // 5. Kontrollo konfirmimin e fjalëkalimit
        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Fjalëkalimet nuk përputhen'];
        }

        try {
            // Hasho fjalëkalimin (SHUMË E RËNDËSISHME!)
            // password_hash përdor algoritmin bcrypt që është shumë i sigurt
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Shto në databazë
            $userId = $this->db->insert('users', [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $hashedPassword,
                'role_id' => 1,  // Default: user
                'is_verified' => false,
                'is_active' => true
            ]);

            // Krijo dhe dërgo kodin e verifikimit
            $this->sendVerificationEmail($userId, $email);

            // Logo veprimin
            logUserAction($userId, 'register', 'Regjistrimi i përdoruesit të ri');

            return [
                'success' => true,
                'message' => 'Regjistrimi u krye me sukses! Kontrollo email-in për të verifikuar llogarinë.',
                'user_id' => $userId
            ];

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ndodhi një gabim. Provoni përsëri.'];
        }
    }

    /**
     * Kontrollon nëse email-i ekziston
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        return $this->db->count('users', 'email = ?', [$email]) > 0;
    }

    /**
     * Dërgon email verifikimi
     *
     * @param int $userId
     * @param string $email
     * @return bool
     */
    public function sendVerificationEmail(int $userId, string $email): bool
    {
        // Gjenero kod unik
        $code = bin2hex(random_bytes(32));

        // Ruaje në databazë (skadon pas 24 orësh)
        $this->db->insert('verification_codes', [
            'user_id' => $userId,
            'code' => $code,
            'type' => 'email_verify',
            'expires_at' => date('Y-m-d H:i:s', time() + 86400)  // 24 orë
        ]);

        // Krijo linkun e verifikimit
        $verifyLink = SITE_URL . '/views/Auth/Verify.php?code=' . $code;

        // Në zhvillim, ruaje kodin në sesion për testim
        if (DEBUG_MODE) {
            $_SESSION['debug_verify_link'] = $verifyLink;
        }

        // Dërgo email (në prodhim do përdorej PHPMailer)
        $subject = 'Verifiko llogarinë tënde - ' . SITE_NAME;
        $message = "
            <h2>Mirësevjen në " . SITE_NAME . "!</h2>
            <p>Faleminderit për regjistrimin. Kliko linkun më poshtë për të verifikuar email-in:</p>
            <p><a href='{$verifyLink}'>Verifiko Email-in</a></p>
            <p>Ky link skadon pas 24 orësh.</p>
            <p>Nëse nuk e ke kërkuar këtë, injoro këtë email.</p>
        ";

        // Për momentin, kthejmë true (email do implementohet me PHPMailer)
        return true;
    }

    /**
     * Verifikon email-in me kod
     *
     * @param string $code
     * @return array
     */
    public function verifyEmail(string $code): array
    {
        // Gjej kodin në databazë
        $verification = $this->db->fetchOne(
            "SELECT * FROM verification_codes
             WHERE code = ? AND type = 'email_verify' AND expires_at > NOW() AND used_at IS NULL",
            [$code]
        );

        if (!$verification) {
            return ['success' => false, 'message' => 'Kodi i verifikimit është i pavlefshëm ose ka skaduar'];
        }

        try {
            $this->db->beginTransaction();

            // Përditëso përdoruesin si të verifikuar
            $this->db->update('users', ['is_verified' => true], 'id = ?', [$verification['user_id']]);

            // Shëno kodin si të përdorur
            $this->db->update('verification_codes', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$verification['id']]);

            $this->db->commit();

            logUserAction($verification['user_id'], 'email_verified', 'Email u verifikua');

            return ['success' => true, 'message' => 'Email-i u verifikua me sukses! Tani mund të logohesh.'];

        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Ndodhi një gabim gjatë verifikimit'];
        }
    }

    // ============================================
    // LOGIN / LOGOUT
    // ============================================

    /**
     * Logohet në sistem
     *
     * @param string $email
     * @param string $password
     * @param bool $rememberMe
     * @return array
     */
    public function login(string $email, string $password, bool $rememberMe = false): array
    {
        $email = sanitize($email);

        // Kontrollo nëse email është bosh
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email dhe fjalëkalimi janë të detyrueshëm'];
        }

        // Kontrollo tentativat e dështuara (anti brute-force)
        if ($this->isLockedOut($email)) {
            $remainingTime = $this->getLockoutRemainingTime($email);
            return [
                'success' => false,
                'message' => "Llogaria është bllokuar për shkak të tentativave të shumta. Provo pas {$remainingTime} minutash."
            ];
        }

        // Gjej përdoruesin
        $user = $this->db->fetchOne(
            "SELECT u.*, r.name as role_name FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.email = ?",
            [$email]
        );

        // Regjistro tentativën
        $this->logLoginAttempt($email, $user['id'] ?? null, false);

        // Kontrollo nëse përdoruesi ekziston
        if (!$user) {
            return ['success' => false, 'message' => 'Email ose fjalëkalimi i gabuar'];
        }

        // Kontrollo nëse llogaria është aktive
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Llogaria juaj është çaktivizuar'];
        }

        // Kontrollo nëse email është verifikuar
        if (!$user['is_verified']) {
            return ['success' => false, 'message' => 'Ju lutem verifikoni email-in para se të logoheni'];
        }

        // Verifiko fjalëkalimin
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email ose fjalëkalimi i gabuar'];
        }

        // LOGIN ME SUKSES!

        // Përditëso tentativën si të suksesshme
        $this->db->update(
            'login_attempts',
            ['success' => true],
            'email = ? ORDER BY id DESC LIMIT 1',
            [$email]
        );

        // Reset tentativat e dështuara
        $this->resetLoginAttempts($email);

        // Krijo sesionin
        $this->createSession($user);

        // Përditëso last_login
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        // Remember Me
        if ($rememberMe) {
            refreshRememberToken($user['id']);
        }

        // Logo veprimin
        logUserAction($user['id'], 'login', 'Login i suksesshëm');

        return [
            'success' => true,
            'message' => 'U loguat me sukses!',
            'redirect' => $user['role_name'] === 'admin' ? 'views/admin/dashboard.php' : 'views/user/dashboard.php'
        ];
    }

    /**
     * Krijon sesionin e përdoruesit
     *
     * @param array $user
     */
    private function createSession(array $user): void
    {
        // Rigjenero ID-në e sesionit për siguri
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['last_activity'] = time();

        // Ruaj sesionin në databazë
        $this->db->insert('sessions', [
            'user_id' => $user['id'],
            'session_id' => session_id(),
            'ip_address' => getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    /**
     * Regjistron tentativën e login
     *
     * @param string $email
     * @param int|null $userId
     * @param bool $success
     */
    private function logLoginAttempt(string $email, ?int $userId, bool $success): void
    {
        $this->db->insert('login_attempts', [
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'success' => $success
        ]);
    }

    /**
     * Kontrollon nëse llogaria është e bllokuar
     *
     * @param string $email
     * @return bool
     */
    public function isLockedOut(string $email): bool
    {
        $lockoutTime = date('Y-m-d H:i:s', time() - LOCKOUT_TIME);

        // Numëro tentativat e dështuara në 30 minutat e fundit
        $failedAttempts = $this->db->count(
            'login_attempts',
            'email = ? AND success = FALSE AND attempted_at > ?',
            [$email, $lockoutTime]
        );

        return $failedAttempts >= MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Merr kohën e mbetur të bllokimit në minuta
     *
     * @param string $email
     * @return int
     */
    private function getLockoutRemainingTime(string $email): int
    {
        $lastAttempt = $this->db->fetchOne(
            "SELECT attempted_at FROM login_attempts
             WHERE email = ? AND success = FALSE
             ORDER BY attempted_at DESC LIMIT 1",
            [$email]
        );

        if (!$lastAttempt) {
            return 0;
        }

        $unlockTime = strtotime($lastAttempt['attempted_at']) + LOCKOUT_TIME;
        $remaining = $unlockTime - time();

        return max(0, ceil($remaining / 60));
    }

    /**
     * Reseton tentativat e dështuara pas login të suksesshëm
     *
     * @param string $email
     */
    private function resetLoginAttempts(string $email): void
    {
        // Fshi tentativat e vjetra (mban vetëm të suksesshmet për log)
        $this->db->query(
            "DELETE FROM login_attempts WHERE email = ? AND success = FALSE",
            [$email]
        );
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        $userId = getCurrentUserId();

        if ($userId) {
            // Fshi sesionin nga databaza
            $this->db->delete('sessions', 'user_id = ? AND session_id = ?', [$userId, session_id()]);

            // Fshi remember token
            $this->db->delete('remember_tokens', 'user_id = ?', [$userId]);

            // Logo veprimin
            logUserAction($userId, 'logout', 'Dalje nga sistemi');
        }

        // Fshi cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Shkatërro sesionin
        session_unset();
        session_destroy();
    }

    // ============================================
    // FORGOT PASSWORD
    // ============================================

    /**
     * Kërkon rivendosjen e fjalëkalimit
     *
     * @param string $email
     * @return array
     */
    public function requestPasswordReset(string $email): array
    {
        $email = sanitize($email);

        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Email i pavlefshëm'];
        }

        // Gjej përdoruesin
        $user = $this->db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);

        // Mos trego nëse email ekziston (siguri)
        if (!$user) {
            return ['success' => true, 'message' => 'Nëse email-i ekziston, do të marrësh një link për rivendosje'];
        }

        // Fshi kërkesat e vjetra
        $this->db->delete('verification_codes', "user_id = ? AND type = 'password_reset'", [$user['id']]);

        // Gjenero kod të ri
        $code = bin2hex(random_bytes(32));

        $this->db->insert('verification_codes', [
            'user_id' => $user['id'],
            'code' => $code,
            'type' => 'password_reset',
            'expires_at' => date('Y-m-d H:i:s', time() + 3600)  // 1 orë
        ]);

        // Link për rivendosje
        $resetLink = SITE_URL . '/views/Auth/reset-password.php?code=' . $code;

        if (DEBUG_MODE) {
            $_SESSION['debug_reset_link'] = $resetLink;
        }

        logUserAction($user['id'], 'password_reset_request', 'Kërkesë për rivendosje fjalëkalimi');

        return ['success' => true, 'message' => 'Nëse email-i ekziston, do të marrësh një link për rivendosje'];
    }

    /**
     * Rivendos fjalëkalimin
     *
     * @param string $code
     * @param string $newPassword
     * @param string $confirmPassword
     * @return array
     */
    public function resetPassword(string $code, string $newPassword, string $confirmPassword): array
    {
        // Gjej kodin
        $verification = $this->db->fetchOne(
            "SELECT * FROM verification_codes
             WHERE code = ? AND type = 'password_reset' AND expires_at > NOW() AND used_at IS NULL",
            [$code]
        );

        if (!$verification) {
            return ['success' => false, 'message' => 'Linku i rivendosjes është i pavlefshëm ose ka skaduar'];
        }

        // Valido fjalëkalimin e ri
        $validation = validatePassword($newPassword);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode('. ', $validation['errors'])];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Fjalëkalimet nuk përputhen'];
        }

        try {
            $this->db->beginTransaction();

            // Përditëso fjalëkalimin
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update('users', ['password' => $hashedPassword], 'id = ?', [$verification['user_id']]);

            // Shëno kodin si të përdorur
            $this->db->update('verification_codes', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$verification['id']]);

            // Fshi të gjitha sesionet dhe tokens (për siguri)
            $this->db->delete('sessions', 'user_id = ?', [$verification['user_id']]);
            $this->db->delete('remember_tokens', 'user_id = ?', [$verification['user_id']]);

            $this->db->commit();

            logUserAction($verification['user_id'], 'password_reset', 'Fjalëkalimi u rivendos');

            return ['success' => true, 'message' => 'Fjalëkalimi u ndryshua me sukses! Tani mund të logohesh.'];

        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Ndodhi një gabim'];
        }
    }

    // ============================================
    // PROFILI
    // ============================================

    /**
     * Merr të dhënat e përdoruesit
     *
     * @param int $userId
     * @return array|null
     */
    public function getUser(int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT u.*, r.name as role_name FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE u.id = ?",
            [$userId]
        );
    }

    /**
     * Përditëson profilin
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile(int $userId, array $data): array
    {
        // Pastro të dhënat
        $updateData = [];

        if (isset($data['first_name'])) {
            $updateData['first_name'] = sanitize($data['first_name']);
        }
        if (isset($data['last_name'])) {
            $updateData['last_name'] = sanitize($data['last_name']);
        }
        if (isset($data['phone'])) {
            $phone = sanitize($data['phone']);
            if (!empty($phone) && !isValidPhone($phone)) {
                return ['success' => false, 'message' => 'Numri i telefonit nuk është i vlefshëm'];
            }
            $updateData['phone'] = $phone;
        }
        if (isset($data['address'])) {
            $updateData['address'] = sanitize($data['address']);
        }
        if (isset($data['city'])) {
            $updateData['city'] = sanitize($data['city']);
        }
        if (isset($data['bio'])) {
            $updateData['bio'] = sanitize($data['bio']);
        }

        if (empty($updateData)) {
            return ['success' => false, 'message' => 'Asnjë e dhënë për përditësim'];
        }

        try {
            $this->db->update('users', $updateData, 'id = ?', [$userId]);

            // Përditëso emrin në sesion nëse ndryshoi
            if (isset($updateData['first_name']) || isset($updateData['last_name'])) {
                $user = $this->getUser($userId);
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            }

            logUserAction($userId, 'profile_update', 'Përditësim profili');

            return ['success' => true, 'message' => 'Profili u përditësua me sukses'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ndodhi një gabim gjatë përditësimit'];
        }
    }

    /**
     * Përditëson foton e profilit
     *
     * @param int $userId
     * @param array $file - $_FILES['profile_image']
     * @return array
     */
    public function updateProfileImage(int $userId, array $file): array
    {
        // Merr përdoruesin për të marrë foton aktuale
        $user = $this->getUser($userId);

        // Ngarko imazhin e ri
        $upload = uploadImage($file, 'profiles');

        if (!$upload['success']) {
            return ['success' => false, 'message' => $upload['error']];
        }

        try {
            // Përditëso në databazë
            $this->db->update('users', ['profile_image' => $upload['filename']], 'id = ?', [$userId]);

            // Fshi foton e vjetër
            if ($user && $user['profile_image'] !== 'default.png') {
                deleteImage($user['profile_image']);
            }

            logUserAction($userId, 'profile_image_update', 'Foto e profilit u përditësua');

            return ['success' => true, 'message' => 'Foto u përditësua me sukses', 'filename' => $upload['filename']];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ndodhi një gabim gjatë ruajtjes'];
        }
    }

    /**
     * Ndryshon fjalëkalimin
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @return array
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        $user = $this->getUser($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'Përdoruesi nuk u gjet'];
        }

        // Verifiko fjalëkalimin aktual
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Fjalëkalimi aktual është i gabuar'];
        }

        // Valido fjalëkalimin e ri
        $validation = validatePassword($newPassword);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode('. ', $validation['errors'])];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Fjalëkalimet nuk përputhen'];
        }

        // Kontrollo që të mos jetë i njëjtë me të vjetrin
        if (password_verify($newPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Fjalëkalimi i ri duhet të jetë i ndryshëm nga i vjetri'];
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update('users', ['password' => $hashedPassword], 'id = ?', [$userId]);

            logUserAction($userId, 'password_change', 'Fjalëkalimi u ndryshua');

            return ['success' => true, 'message' => 'Fjalëkalimi u ndryshua me sukses'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ndodhi një gabim'];
        }
    }

    // ============================================
    // FUNKSIONE ADMIN
    // ============================================

    /**
     * Merr të gjithë përdoruesit (vetëm për admin)
     *
     * @param int $page
     * @param int $perPage
     * @param string $search
     * @return array
     */
    public function getAllUsers(int $page = 1, int $perPage = 10, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = '1';

        if (!empty($search)) {
            $where = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }

        $users = $this->db->fetchAll(
            "SELECT u.*, r.name as role_name FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE {$where}
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $total = $this->db->count('users', $where, $params);

        return [
            'users' => $users,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    /**
     * Ndryshon rolin e përdoruesit
     *
     * @param int $userId
     * @param int $roleId
     * @return array
     */
    public function changeUserRole(int $userId, int $roleId): array
    {
        try {
            $this->db->update('users', ['role_id' => $roleId], 'id = ?', [$userId]);
            logUserAction(getCurrentUserId(), 'role_change', "Roli i përdoruesit #{$userId} u ndryshua në #{$roleId}");
            return ['success' => true, 'message' => 'Roli u ndryshua me sukses'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ndodhi një gabim'];
        }
    }

    /**
     * Aktivizon/Çaktivizon përdoruesin
     *
     * @param int $userId
     * @param bool $active
     * @return array
     */
    public function toggleUserStatus(int $userId, bool $active): array
    {
        try {
            $this->db->update('users', ['is_active' => $active], 'id = ?', [$userId]);

            if (!$active) {
                // Fshi sesionet nëse çaktivizohet
                $this->db->delete('sessions', 'user_id = ?', [$userId]);
            }

            $status = $active ? 'aktivizuar' : 'çaktivizuar';
            logUserAction(getCurrentUserId(), 'user_status_change', "Përdoruesi #{$userId} u {$status}");

            return ['success' => true, 'message' => "Përdoruesi u {$status} me sukses"];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ndodhi një gabim'];
        }
    }

    /**
     * Fshin përdoruesin
     *
     * @param int $userId
     * @return array
     */
    public function deleteUser(int $userId): array
    {
        // Mos lejo fshirjen e vetvetes
        if ($userId === getCurrentUserId()) {
            return ['success' => false, 'message' => 'Nuk mund të fshish llogarinë tënde nga këtu'];
        }

        try {
            $user = $this->getUser($userId);

            // Fshi foton e profilit
            if ($user && $user['profile_image'] !== 'default.png') {
                deleteImage($user['profile_image']);
            }

            $this->db->delete('users', 'id = ?', [$userId]);

            logUserAction(getCurrentUserId(), 'user_delete', "Përdoruesi #{$userId} u fshi");

            return ['success' => true, 'message' => 'Përdoruesi u fshi me sukses'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ndodhi një gabim gjatë fshirjes'];
        }
    }
}
