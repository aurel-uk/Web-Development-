<?php

/**

 * FUNKSIONE NDIHMËSE

 * ==================

 * Këto funksione përdoren në të gjithë aplikacionin.

 * Janë si "vegla" që na ndihmojnë të bëjmë gjëra të zakonshme.

 */

 

// ============================================

// FUNKSIONE PËR SESIONIN

// ============================================

 

/**

 * Kontrollon nëse përdoruesi është i loguar

 *

 * @return bool

 */

function isLoggedIn(): bool

{

    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

}

 

/**

 * Kontrollon nëse përdoruesi është admin

 *

 * @return bool

 */

function isAdmin(): bool

{

    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

}

 

/**

 * Merr ID e përdoruesit aktual

 *

 * @return int|null

 */

function getCurrentUserId(): ?int

{

    return $_SESSION['user_id'] ?? null;

}

 

/**

 * Kontrollon dhe skadon sesionin pas 15 minutash mosveprim

 */

function checkSessionTimeout(): void

{

    if (isLoggedIn()) {

        $timeout = SESSION_LIFETIME; // 15 minuta

 

        if (isset($_SESSION['last_activity'])) {

            $inactiveTime = time() - $_SESSION['last_activity'];

 

            if ($inactiveTime > $timeout) {

                // Sesioni ka skaduar

                session_unset();

                session_destroy();

                redirect('views/auth/login.php?timeout=1');

            }

        }

 

        // Përditëso kohën e fundit të aktivitetit

        $_SESSION['last_activity'] = time();

    }

}

 

/**

 * Kryen login automatik me "Remember Me" token

 */

function autoLogin(): void

{

    if (!isset($_COOKIE['remember_token'])) {

        return;

    }

 

    $token = $_COOKIE['remember_token'];

    $db = Database::getInstance();

 

    // Gjej token-in në databazë

    $tokenData = $db->fetchOne(

        "SELECT rt.*, u.* FROM remember_tokens rt

         JOIN users u ON rt.user_id = u.id

         WHERE rt.token = ? AND rt.expires_at > NOW()",

        [hash('sha256', $token)]

    );

 

    if ($tokenData) {

        // Krijo sesion të ri

        $_SESSION['user_id'] = $tokenData['user_id'];

        $_SESSION['user_email'] = $tokenData['email'];

        $_SESSION['user_name'] = $tokenData['first_name'] . ' ' . $tokenData['last_name'];

        $_SESSION['user_role'] = getRoleName($tokenData['role_id']);

        $_SESSION['last_activity'] = time();

 

        // Rifresko token për siguri

        refreshRememberToken($tokenData['user_id']);

 

        // Logo veprimin

        logUserAction($tokenData['user_id'], 'auto_login', 'Login automatik me Remember Me');

    } else {

        // Token i pavlefshëm, fshije cookie

        setcookie('remember_token', '', time() - 3600, '/');

    }

}

 

/**

 * Rifresko token-in "Remember Me"

 *

 * @param int $userId

 */

function refreshRememberToken(int $userId): void

{

    $db = Database::getInstance();

 

    // Fshi token-in e vjetër

    $db->delete('remember_tokens', 'user_id = ?', [$userId]);

 

    // Krijo token të ri

    $token = bin2hex(random_bytes(32));

    $hashedToken = hash('sha256', $token);

 

    $db->insert('remember_tokens', [

        'user_id' => $userId,

        'token' => $hashedToken,

        'expires_at' => date('Y-m-d H:i:s', time() + REMEMBER_ME_EXPIRY)

    ]);

 

    // Vendos cookie

    setcookie('remember_token', $token, time() + REMEMBER_ME_EXPIRY, '/', '', false, true);

}

 

/**

 * Merr emrin e rolit nga ID

 *

 * @param int $roleId

 * @return string

 */

function getRoleName(int $roleId): string

{

    $db = Database::getInstance();

    $role = $db->fetchOne("SELECT name FROM roles WHERE id = ?", [$roleId]);

    return $role ? $role['name'] : 'user';

}

 

// ============================================

// FUNKSIONE PËR SIGURI

// ============================================

 

/**

 * Pastron input nga sulmet XSS

 *

 * SHPJEGIM: XSS (Cross-Site Scripting) është kur dikush fut

 * kod JavaScript keqdashës në faqen tënde. Kjo funksion e parandalon.

 *

 * @param string $data

 * @return string

 */

function sanitize(string $data): string

{

    $data = trim($data);                    // Heq hapësirat në fillim/fund

    $data = stripslashes($data);            // Heq backslash-et

    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');  // Konverton < > në &lt; &gt;

    return $data;

}

 

/**

 * Pastron një array

 *

 * @param array $data

 * @return array

 */

function sanitizeArray(array $data): array

{

    return array_map(function ($item) {

        return is_string($item) ? sanitize($item) : $item;

    }, $data);

}

 

/**

 * Gjeneron një token CSRF

 *

 * SHPJEGIM: CSRF (Cross-Site Request Forgery) është kur dikush

 * të bën të kryesh veprime pa dëshirën tënde. Token e parandalon.

 *

 * @return string

 */

function generateCSRFToken(): string

{

    if (!isset($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    }

    return $_SESSION['csrf_token'];

}

 

/**

 * Verifikon token CSRF

 *

 * @param string $token

 * @return bool

 */

function verifyCSRFToken(string $token): bool

{

    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);

}

 

/**

 * Gjeneron një input hidden me CSRF token

 *

 * @return string HTML

 */

function csrfField(): string

{

    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';

}

 

// ============================================

// FUNKSIONE PËR VALIDIM

// ============================================

 

/**

 * Validon email

 *

 * @param string $email

 * @return bool

 */

function isValidEmail(string $email): bool

{

    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

}

 

/**

 * Validon fjalëkalimin

 * Duhet të ketë: 8+ karaktere, 1 shkronjë të madhe, 1 numër

 *

 * @param string $password

 * @return array ['valid' => bool, 'errors' => array]

 */

function validatePassword(string $password): array

{

    $errors = [];

 

    if (strlen($password) < 8) {

        $errors[] = 'Fjalëkalimi duhet të ketë të paktën 8 karaktere';

    }

    if (!preg_match('/[A-Z]/', $password)) {

        $errors[] = 'Fjalëkalimi duhet të ketë të paktën një shkronjë të madhe';

    }

    if (!preg_match('/[a-z]/', $password)) {

        $errors[] = 'Fjalëkalimi duhet të ketë të paktën një shkronjë të vogël';

    }

    if (!preg_match('/[0-9]/', $password)) {

        $errors[] = 'Fjalëkalimi duhet të ketë të paktën një numër';

    }

 

    return [

        'valid' => empty($errors),

        'errors' => $errors

    ];

}

 

/**

 * Validon numrin e telefonit

 *

 * @param string $phone

 * @return bool

 */

function isValidPhone(string $phone): bool

{

    // Pranon formatet: +355123456789, 0691234567, etj.

    return preg_match('/^[+]?[0-9]{9,15}$/', preg_replace('/\s+/', '', $phone));

}

 

// ============================================

// FUNKSIONE PËR RIDREJTIM DHE MESAZHE

// ============================================

 

/**

 * Ridrejton në një URL tjetër

 *

 * @param string $url

 */

function redirect(string $url): void

{

    // Nëse URL nuk fillon me http, shto SITE_URL

    if (!preg_match('/^https?:\/\//', $url)) {

        $url = SITE_URL . '/' . ltrim($url, '/');

    }

    header("Location: $url");

    exit;

}

 

/**

 * Vendos një mesazh flash (shfaqet një herë)

 *

 * @param string $type - success, error, warning, info

 * @param string $message

 */

function setFlash(string $type, string $message): void

{

    $_SESSION['flash'] = [

        'type' => $type,

        'message' => $message

    ];

}

 

/**

 * Merr dhe fshin mesazhin flash

 *

 * @return array|null

 */

function getFlash(): ?array

{

    if (isset($_SESSION['flash'])) {

        $flash = $_SESSION['flash'];

        unset($_SESSION['flash']);

        return $flash;

    }

    return null;

}

 

/**

 * Shfaq mesazhin flash si HTML

 *

 * @return string

 */

function displayFlash(): string

{

    $flash = getFlash();

    if (!$flash) return '';

 

    $typeClasses = [

        'success' => 'alert-success',

        'error' => 'alert-danger',

        'warning' => 'alert-warning',

        'info' => 'alert-info'

    ];

 

    $class = $typeClasses[$flash['type']] ?? 'alert-info';

 

    return sprintf(

        '<div class="alert %s alert-dismissible fade show" role="alert">

            %s

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>',

        $class,

        htmlspecialchars($flash['message'])

    );

}

 

// ============================================

// FUNKSIONE PËR LOGGING

// ============================================

 

/**

 * Regjistron një veprim të përdoruesit

 *

 * @param int|null $userId

 * @param string $action

 * @param string $description

 */

function logUserAction(?int $userId, string $action, string $description = ''): void

{

    try {

        $db = Database::getInstance();

        $db->insert('user_logs', [

            'user_id' => $userId,

            'action' => $action,

            'description' => $description,

            'ip_address' => getClientIP(),

            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''

        ]);

    } catch (Exception $e) {

        // Mos ndërprit aplikacionin nëse logging dështon

        error_log("Log error: " . $e->getMessage());

    }

}

 

/**

 * Merr IP-në e klientit

 *

 * @return string

 */

function getClientIP(): string

{

    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

 

    foreach ($ipKeys as $key) {

        if (!empty($_SERVER[$key])) {

            $ip = explode(',', $_SERVER[$key])[0];

            if (filter_var($ip, FILTER_VALIDATE_IP)) {

                return trim($ip);

            }

        }

    }

 

    return '0.0.0.0';

}

 

// ============================================

// FUNKSIONE PËR SKEDARË

// ============================================

 

/**

 * Ngarkon një imazh dhe kthen emrin e skedarit

 *

 * @param array $file - $_FILES['input_name']

 * @param string $directory - Subdosja në uploads

 * @return array ['success' => bool, 'filename' => string, 'error' => string]

 */

function uploadImage(array $file, string $directory = ''): array

{

    // Kontrollo nëse ka gabim

    if ($file['error'] !== UPLOAD_ERR_OK) {

        return ['success' => false, 'error' => 'Gabim në ngarkimin e skedarit'];

    }

 

    // Kontrollo madhësinë

    if ($file['size'] > MAX_FILE_SIZE) {

        return ['success' => false, 'error' => 'Skedari është shumë i madh (max 5MB)'];

    }

 

    // Kontrollo tipin

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    $mimeType = finfo_file($finfo, $file['tmp_name']);

    finfo_close($finfo);

 

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {

        return ['success' => false, 'error' => 'Tipi i skedarit nuk lejohet. Përdor JPG, PNG ose GIF'];

    }

 

    // Gjenero emër unik

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

    $filename = uniqid('img_', true) . '.' . strtolower($extension);

 

    // Krijo dosjen nëse nuk ekziston

    $uploadPath = UPLOAD_PATH . ($directory ? $directory . '/' : '');

    if (!is_dir($uploadPath)) {

        mkdir($uploadPath, 0755, true);

    }

 

    // Lëviz skedarin

    $destination = $uploadPath . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {

        return [

            'success' => true,

            'filename' => ($directory ? $directory . '/' : '') . $filename

        ];

    }

 

    return ['success' => false, 'error' => 'Dështoi ruajtja e skedarit'];

}

 

/**

 * Fshin një imazh

 *

 * @param string $filename

 * @return bool

 */

function deleteImage(string $filename): bool

{

    if ($filename === 'default.png') {

        return true; // Mos fshi imazhin default

    }

 

    $filepath = UPLOAD_PATH . $filename;

    if (file_exists($filepath)) {

        return unlink($filepath);

    }

    return false;

}

 

// ============================================

// FUNKSIONE NDIHMËSE TË TJERA

// ============================================

 

/**

 * Formon datën në shqip

 *

 * @param string $date

 * @param string $format

 * @return string

 */

function formatDate(string $date, string $format = 'd/m/Y H:i'): string

{

    return date($format, strtotime($date));

}

 

/**

 * Gjeneron një slug nga teksti

 *

 * @param string $text

 * @return string

 */

function slugify(string $text): string

{

    // Zëvendëso karakteret jo-alfanumerike me viza

    $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);

    $text = strtolower(trim($text, '-'));

    return $text;

}

 

/**

 * Formon çmimin

 *

 * @param float $price

 * @param string $currency

 * @return string

 */

function formatPrice(float $price, string $currency = '€'): string

{

    return number_format($price, 2, ',', '.') . ' ' . $currency;

}

 

/**

 * Shkurton tekstin

 *

 * @param string $text

 * @param int $length

 * @return string

 */

function truncate(string $text, int $length = 100): string

{

    if (strlen($text) <= $length) {

        return $text;

    }

    return substr($text, 0, $length) . '...';

}

 

/**

 * Gjeneron numër porosie unik

 *

 * @return string

 */

function generateOrderNumber(): string

{

    return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

}