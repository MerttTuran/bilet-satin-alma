<?php
// pages/admin/admin_create.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(ROLE_ADMIN);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/admin/company_admins.php');
}

$fullName = trim(input('full_name'));
$email = trim(input('email'));
$password = input('password');
$companyId = input('company_id');

// Validasyon
if (empty($fullName) || empty($email) || empty($password) || empty($companyId)) {
    redirect('/pages/admin/company_admins.php', 'Lütfen tüm alanları doldurun.', 'error');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('/pages/admin/company_admins.php', 'Geçerli bir e-posta adresi girin.', 'error');
}

if (strlen($password) < 6) {
    redirect('/pages/admin/company_admins.php', 'Şifre en az 6 karakter olmalıdır.', 'error');
}

// E-posta kontrolü
$existingUser = fetchOne("SELECT id FROM User WHERE email = ?", [$email]);
if ($existingUser) {
    redirect('/pages/admin/company_admins.php', 'Bu e-posta adresi zaten kayıtlı.', 'error');
}

// Firma kontrolü
$company = fetchOne("SELECT id FROM Bus_Company WHERE id = ?", [$companyId]);
if (!$company) {
    redirect('/pages/admin/company_admins.php', 'Geçersiz firma.', 'error');
}

// Admin oluştur
try {
    $adminId = generateUUID();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    insert("
        INSERT INTO User (id, full_name, email, role, password, company_id, balance)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ", [$adminId, $fullName, $email, ROLE_COMPANY_ADMIN, $hashedPassword, $companyId, 5000]);
    
    redirect('/pages/admin/company_admins.php', 'Firma admin başarıyla oluşturuldu.');
} catch (Exception $e) {
    redirect('/pages/admin/company_admins.php', 'Bir hata oluştu: ' . $e->getMessage(), 'error');
}
?>

