<?php
// pages/admin/company_delete.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(ROLE_ADMIN);

$companyId = input('id');

if (!$companyId) {
    redirect('/pages/admin/companies.php', 'Geçersiz firma.', 'error');
}

// Firmayı getir
$company = fetchOne("SELECT * FROM Bus_Company WHERE id = ?", [$companyId]);

if (!$company) {
    redirect('/pages/admin/companies.php', 'Firma bulunamadı.', 'error');
}

// İlişkili veri var mı kontrol et
$tripCount = fetchOne("SELECT COUNT(*) as count FROM Trips WHERE company_id = ?", [$companyId])['count'];
$adminCount = fetchOne("SELECT COUNT(*) as count FROM User WHERE company_id = ? AND role = ?", [$companyId, ROLE_COMPANY_ADMIN])['count'];

if ($tripCount > 0 || $adminCount > 0) {
    redirect('/pages/admin/companies.php', 'Bu firmaya ait sefer veya admin var. Firma silinemez.', 'error');
}

// Firmayı sil
try {
    // Logoyu sil
    if ($company['logo_path'] && file_exists(LOGO_PATH . $company['logo_path'])) {
        unlink(LOGO_PATH . $company['logo_path']);
    }
    
    execute("DELETE FROM Bus_Company WHERE id = ?", [$companyId]);
    redirect('/pages/admin/companies.php', 'Firma başarıyla silindi.');
} catch (Exception $e) {
    redirect('/pages/admin/companies.php', 'Firma silinirken bir hata oluştu.', 'error');
}
?>

