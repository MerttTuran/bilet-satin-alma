<?php
// pages/admin/coupon_delete.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(ROLE_ADMIN);

$couponId = input('id');

if (!$couponId) {
    redirect('/pages/admin/coupons.php', 'Geçersiz kupon.', 'error');
}

// Kuponu getir
$coupon = fetchOne("SELECT * FROM Coupons WHERE id = ? AND company_id IS NULL", [$couponId]);

if (!$coupon) {
    redirect('/pages/admin/coupons.php', 'Kupon bulunamadı veya sistem kuponu değil.', 'error');
}

// Kuponu sil
try {
    execute("DELETE FROM Coupons WHERE id = ?", [$couponId]);
    redirect('/pages/admin/coupons.php', 'Kupon başarıyla silindi.');
} catch (Exception $e) {
    redirect('/pages/admin/coupons.php', 'Kupon silinirken bir hata oluştu.', 'error');
}
?>

