<?php
// pages/admin/admin_delete.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(ROLE_ADMIN);

$adminId = input('id');

if (!$adminId) {
    redirect('/pages/admin/company_admins.php', 'Geçersiz admin.', 'error');
}

// Admin'i getir ve kontrol et
$admin = fetchOne("SELECT * FROM User WHERE id = ? AND role = ?", [$adminId, ROLE_COMPANY_ADMIN]);

if (!$admin) {
    redirect('/pages/admin/company_admins.php', 'Admin bulunamadı.', 'error');
}

// Admin'i sil
try {
    execute("DELETE FROM User WHERE id = ?", [$adminId]);
    redirect('/pages/admin/company_admins.php', 'Firma admin başarıyla silindi.');
} catch (Exception $e) {
    redirect('/pages/admin/company_admins.php', 'Admin silinirken bir hata oluştu.', 'error');
}
?>

