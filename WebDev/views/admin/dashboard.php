<?php
/**
 * ADMIN DASHBOARD - REDIRECT
 * ===========================
 * Paneli i plotë i administratorit jeton te admin/index.php;
 * kjo faqe ekziston vetëm sepse navigimi (header/footer) lidhet këtu.
 */

require_once __DIR__ . '/../../includes/admin_check.php';

redirect('admin/index.php');
