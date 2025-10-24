<?php
// auth/login_process.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/functions.php';

// Session config.php'de zaten başlatıldı

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/login.php');
}

$email = input('email');
$password = input('password');
$redirect = input('redirect', '/index.php');

// Validasyon
if (empty($email) || empty($password)) {
    redirect('/pages/login.php?error=empty', 'Lütfen tüm alanları doldurun.', 'error');
}

// Kullanıcıyı bul
$user = fetchOne("SELECT * FROM User WHERE email = ?", [$email]);

if (!$user || !password_verify($password, $user['password'])) {
    redirect('/pages/login.php?error=invalid', 'E-posta veya şifre hatalı.', 'error');
}

// Session'a kaydet
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_name'] = $user['full_name'];

// Rol bazlı yönlendirme
if ($user['role'] === ROLE_ADMIN) {
    redirect('/pages/admin/dashboard.php', 'Hoş geldiniz, ' . $user['full_name']);
} elseif ($user['role'] === ROLE_COMPANY_ADMIN) {
    redirect('/pages/company_admin/dashboard.php', 'Hoş geldiniz, ' . $user['full_name']);
} else {
    redirect($redirect, 'Hoş geldiniz, ' . $user['full_name']);
}
?>

