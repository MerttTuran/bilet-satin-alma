<?php
// pages/company_admin/trip_delete.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole(ROLE_COMPANY_ADMIN);

$currentUser = getCurrentUser();
$tripId = input('id');

if (!$tripId) {
    redirect('/pages/company_admin/trips.php', 'Geçersiz sefer.', 'error');
}

// Seferi getir
$trip = fetchOne("SELECT * FROM Trips WHERE id = ? AND company_id = ?", [$tripId, $currentUser['company_id']]);

if (!$trip) {
    redirect('/pages/company_admin/trips.php', 'Sefer bulunamadı veya size ait değil.', 'error');
}

// Satılan bilet var mı kontrol et
$ticketCount = fetchOne("
    SELECT COUNT(*) as count 
    FROM Tickets 
    WHERE trip_id = ? AND status = 'active'
", [$tripId])['count'];

if ($ticketCount > 0) {
    redirect('/pages/company_admin/trips.php', 
             'Bu sefere ait aktif biletler var. Sefer silinemez.', 'error');
}

// Seferi sil
try {
    execute("DELETE FROM Trips WHERE id = ?", [$tripId]);
    redirect('/pages/company_admin/trips.php', 'Sefer başarıyla silindi.');
} catch (Exception $e) {
    redirect('/pages/company_admin/trips.php', 'Sefer silinirken bir hata oluştu.', 'error');
}
?>

