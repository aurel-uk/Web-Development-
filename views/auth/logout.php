<?php
/**
 * LOGOUT
 * =======
 * Nxjerr përdoruesin nga sistemi.
 */

require_once __DIR__ . '/../../includes/init.php';

// Krijo instancën User dhe bëj logout
$user = new User();
$user->logout();

// Vendos mesazh dhe ridrejto
setFlash('success', 'U nxorre me sukses nga sistemi.');
redirect('views/auth/login.php');
