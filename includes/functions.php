<?php
// includes/functions.php - Genel Yardımcı Fonksiyonlar

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';

/**
 * Session başlat (artık config.php'de otomatik başlatılıyor)
 */
function startSession() {
    // Session config.php'de zaten başlatıldı
    return true;
}

/**
 * Kullanıcı giriş yapmış mı kontrol et
 */
function isLoggedIn() {
    // Session config.php'de zaten başlatıldı
    return isset($_SESSION['user_id']);
}

/**
 * Kullanıcının rolünü kontrol et
 */
function hasRole($role) {
    // Session config.php'de zaten başlatıldı
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Kullanıcının herhangi bir rolü var mı kontrol et
 */
function hasAnyRole($roles) {
    // Session config.php'de zaten başlatıldı
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $roles);
}

/**
 * Giriş yapılmış kullanıcıyı getir
 */
function getCurrentUser() {
    // Session config.php'de zaten başlatıldı
    if (!isLoggedIn()) {
        return null;
    }
    
    return fetchOne("SELECT * FROM User WHERE id = ?", [$_SESSION['user_id']]);
}

/**
 * Yetkilendirme kontrolü - giriş gerekli
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Yetkilendirme kontrolü - belirli rol gerekli
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /index.php?error=unauthorized');
        exit;
    }
}

/**
 * Yetkilendirme kontrolü - belirli rollerden biri gerekli
 */
function requireAnyRole($roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        header('Location: /index.php?error=unauthorized');
        exit;
    }
}

/**
 * Güvenli HTML çıktısı
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect yap
 */
function redirect($url, $message = null, $type = 'success') {
    // Session config.php'de zaten başlatıldı
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

/**
 * Flash mesaj göster
 */
function getFlashMessage() {
    // Session config.php'de zaten başlatıldı
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Form input değerini güvenli şekilde al
 */
function input($key, $default = '') {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Tarih formatla
 */
function formatDate($date, $format = 'd.m.Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Para formatla
 */
function formatMoney($amount) {
    return number_format($amount, 2, ',', '.') . ' ₺';
}

/**
 * Dolu koltukları getir
 */
function getBookedSeats($tripId) {
    $sql = "SELECT bs.seat_number 
            FROM Booked_Seats bs
            INNER JOIN Tickets t ON bs.ticket_id = t.id
            WHERE t.trip_id = ? AND t.status = 'active'";
    
    $seats = fetchAll($sql, [$tripId]);
    return array_column($seats, 'seat_number');
}

/**
 * Kuponu kontrol et ve geçerli mi dön
 */
function validateCoupon($code, $companyId = null) {
    $sql = "SELECT * FROM Coupons 
            WHERE code = ? 
            AND (expire_date IS NULL OR expire_date > datetime('now'))
            AND (company_id IS NULL OR company_id = ?)";
    
    $coupon = fetchOne($sql, [$code, $companyId]);
    
    if (!$coupon) {
        return ['valid' => false, 'message' => 'Kupon geçersiz veya süresi dolmuş.'];
    }
    
    // Kullanım limitini kontrol et
    if ($coupon['usage_limit']) {
        $usageCount = fetchOne("SELECT COUNT(*) as count FROM User_Coupons WHERE coupon_id = ?", [$coupon['id']]);
        if ($usageCount['count'] >= $coupon['usage_limit']) {
            return ['valid' => false, 'message' => 'Kupon kullanım limiti dolmuş.'];
        }
    }
    
    return ['valid' => true, 'coupon' => $coupon];
}

/**
 * Bilet iptal edilebilir mi kontrol et
 */
function canCancelTicket($ticket) {
    $trip = fetchOne("SELECT departure_time FROM Trips WHERE id = ?", [$ticket['trip_id']]);
    
    if (!$trip) {
        return false;
    }
    
    $departureTime = strtotime($trip['departure_time']);
    $currentTime = time();
    $timeDiff = ($departureTime - $currentTime) / 60; // Dakika cinsinden
    
    return $timeDiff >= CANCEL_TIME_LIMIT;
}

/**
 * Kullanıcı bu kuponu kullandı mı kontrol et
 */
function hasUsedCoupon($userId, $couponId) {
    $result = fetchOne("SELECT COUNT(*) as count FROM User_Coupons WHERE user_id = ? AND coupon_id = ?", 
                       [$userId, $couponId]);
    return $result['count'] > 0;
}

/**
 * Dosya yükleme
 */
function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Dosya yüklenirken hata oluştu.'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Geçersiz dosya türü.'];
    }
    
    $fileName = generateUUID() . '.' . $extension;
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $fileName, 'path' => $targetPath];
    }
    
    return ['success' => false, 'message' => 'Dosya kaydedilemedi.'];
}

/**
 * Sefer için müsait koltuk sayısını getir
 */
function getAvailableSeats($tripId) {
    $trip = fetchOne("SELECT capacity FROM Trips WHERE id = ?", [$tripId]);
    if (!$trip) {
        return 0;
    }
    
    $bookedCount = fetchOne(
        "SELECT COUNT(*) as count FROM Booked_Seats bs
         INNER JOIN Tickets t ON bs.ticket_id = t.id
         WHERE t.trip_id = ? AND t.status = 'active'",
        [$tripId]
    );
    
    return $trip['capacity'] - $bookedCount['count'];
}
?>

