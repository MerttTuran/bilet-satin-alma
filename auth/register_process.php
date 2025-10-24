<?php
// auth/register_process.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/functions.php';

// Session config.php'de zaten başlatıldı

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/register.php');
}

$fullName = trim(input('full_name'));
$email = trim(input('email'));
$password = input('password');
$passwordConfirm = input('password_confirm');

// Validasyon
if (empty($fullName) || empty($email) || empty($password)) {
    redirect('/pages/register.php', 'Lütfen tüm alanları doldurun.', 'error');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/pages/register.php', 'Geçerli bir e-posta adresi girin.', 'error');
}

if (strlen($password) < 6) {
    redirect('/pages/register.php', 'Şifre en az 6 karakter olmalıdır.', 'error');
}

if ($password !== $passwordConfirm) {
    redirect('/pages/register.php', 'Şifreler eşleşmiyor.', 'error');
}

// E-posta kontrolü
$existingUser = fetchOne("SELECT id FROM User WHERE email = ?", [$email]);
if ($existingUser) {
    redirect('/pages/register.php', 'Bu e-posta adresi zaten kayıtlı.', 'error');
}

// Kullanıcı oluştur
$userId = generateUUID();
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    insert(
        "INSERT INTO User (id, full_name, email, role, password, balance) VALUES (?, ?, ?, ?, ?, ?)",
        [$userId, $fullName, $email, ROLE_USER, $hashedPassword, DEFAULT_BALANCE]
    );
    
    // Otomatik giriş yap
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = ROLE_USER;
    $_SESSION['user_name'] = $fullName;
    
    redirect('/index.php', 'Kayıt başarılı! Hoş geldiniz.');
} catch (Exception $e) {
    redirect('/pages/register.php', 'Kayıt sırasında bir hata oluştu.', 'error');
}
?>

