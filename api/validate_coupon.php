<?php
// api/validate_coupon.php - Kupon Doğrulama API

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

requireLogin();

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';
$tripId = $data['trip_id'] ?? '';

if (!$code || !$tripId) {
    echo json_encode(['valid' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

// Sefer bilgilerini al
$trip = fetchOne("SELECT company_id FROM Trips WHERE id = ?", [$tripId]);
if (!$trip) {
    echo json_encode(['valid' => false, 'message' => 'Sefer bulunamadı.']);
    exit;
}

// Kuponu doğrula
$result = validateCoupon($code, $trip['company_id']);

if ($result['valid']) {
    $currentUser = getCurrentUser();
    
    // Kullanıcı bu kuponu daha önce kullandı mı?
    if (hasUsedCoupon($currentUser['id'], $result['coupon']['id'])) {
        echo json_encode(['valid' => false, 'message' => 'Bu kuponu daha önce kullandınız.']);
        exit;
    }
    
    echo json_encode([
        'valid' => true,
        'coupon' => $result['coupon']
    ]);
} else {
    echo json_encode($result);
}
?>

