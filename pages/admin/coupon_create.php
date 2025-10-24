<?php
// pages/admin/coupon_create.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(ROLE_ADMIN);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/admin/coupons.php');
}

$code = strtoupper(trim(input('code')));
$discount = floatval(input('discount'));
$usageLimit = input('usage_limit') ? intval(input('usage_limit')) : null;
$expireDate = input('expire_date') ? input('expire_date') . ' 23:59:59' : null;

// Validasyon
if (empty($code)) {
    redirect('/pages/admin/coupons.php', 'Kupon kodu boş olamaz.', 'error');
}

if (strlen($code) < 3) {
    redirect('/pages/admin/coupons.php', 'Kupon kodu en az 3 karakter olmalıdır.', 'error');
}

if ($discount <= 0 || $discount > 100) {
    redirect('/pages/admin/coupons.php', 'İndirim oranı 1-100 arasında olmalıdır.', 'error');
}

// Kod kontrolü
$existingCoupon = fetchOne("SELECT id FROM Coupons WHERE code = ?", [$code]);
if ($existingCoupon) {
    redirect('/pages/admin/coupons.php', 'Bu kupon kodu zaten kullanılıyor.', 'error');
}

// Kupon oluştur
try {
    $couponId = generateUUID();
    insert("
        INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, company_id)
        VALUES (?, ?, ?, ?, ?, NULL)
    ", [$couponId, $code, $discount, $usageLimit, $expireDate]);
    
    redirect('/pages/admin/coupons.php', 'Sistem kuponu başarıyla oluşturuldu.');
} catch (Exception $e) {
    redirect('/pages/admin/coupons.php', 'Bir hata oluştu: ' . $e->getMessage(), 'error');
}
?>

