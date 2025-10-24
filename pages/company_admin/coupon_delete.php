<?php
// pages/company_admin/coupon_delete.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(ROLE_COMPANY_ADMIN);

$currentUser = getCurrentUser();
$couponId = input('id');

if (!$couponId) {
    redirect('/pages/company_admin/coupons.php', 'Geçersiz kupon.', 'error');
}

// Kuponu getir
$coupon = fetchOne("SELECT * FROM Coupons WHERE id = ? AND company_id = ?", [$couponId, $currentUser['company_id']]);

if (!$coupon) {
    redirect('/pages/company_admin/coupons.php', 'Kupon bulunamadı veya size ait değil.', 'error');
}

// Kuponu sil
try {
    execute("DELETE FROM Coupons WHERE id = ?", [$couponId]);
    redirect('/pages/company_admin/coupons.php', 'Kupon başarıyla silindi.');
} catch (Exception $e) {
    redirect('/pages/company_admin/coupons.php', 'Kupon silinirken bir hata oluştu.', 'error');
}
?>

