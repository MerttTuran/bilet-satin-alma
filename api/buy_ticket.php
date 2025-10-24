<?php
// api/buy_ticket.php - Bilet Satın Alma İşlemi

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(ROLE_USER);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

$tripId = input('trip_id');
$selectedSeats = input('selected_seats');
$appliedCoupon = input('applied_coupon');
$basePrice = floatval(input('base_price'));

// Validasyon
if (!$tripId || !$selectedSeats) {
    redirect('/pages/buy_ticket.php?trip_id=' . $tripId, 'Lütfen en az bir koltuk seçin.', 'error');
}

// Sefer bilgilerini getir
$trip = fetchOne("SELECT * FROM Trips WHERE id = ?", [$tripId]);
if (!$trip) {
    redirect('/index.php', 'Sefer bulunamadı.', 'error');
}

// Koltuk listesini parse et
$seats = array_map('intval', explode(',', $selectedSeats));
$seatCount = count($seats);

// Kullanıcı bilgilerini getir
$currentUser = getCurrentUser();

// Fiyat hesaplama
$totalPrice = $basePrice * $seatCount;

// Kupon varsa uygula
if ($appliedCoupon) {
    $coupon = fetchOne("SELECT * FROM Coupons WHERE code = ?", [$appliedCoupon]);
    if ($coupon) {
        $discount = $coupon['discount'] / 100;
        $totalPrice = $totalPrice - ($trip['price'] * $seatCount * $discount);
    }
}

// Bakiye kontrolü
if ($currentUser['balance'] < $totalPrice) {
    redirect('/pages/buy_ticket.php?trip_id=' . $tripId, 
             'Yetersiz bakiye. Bakiyeniz: ' . formatMoney($currentUser['balance']) . 
             ', Toplam tutar: ' . formatMoney($totalPrice), 'error');
}

// Seçilen koltuklar müsait mi kontrol et
$bookedSeats = getBookedSeats($tripId);
$conflictSeats = array_intersect($seats, $bookedSeats);
if (!empty($conflictSeats)) {
    redirect('/pages/buy_ticket.php?trip_id=' . $tripId, 
             'Seçtiğiniz koltuklar başka bir kullanıcı tarafından alınmış. Lütfen başka koltuk seçin.', 'error');
}

try {
    $db = getDB();
    $db->beginTransaction();
    
    // Bilet oluştur
    $ticketId = generateUUID();
    insert(
        "INSERT INTO Tickets (id, trip_id, user_id, status, total_price) VALUES (?, ?, ?, ?, ?)",
        [$ticketId, $tripId, $currentUser['id'], 'active', $totalPrice]
    );
    
    // Koltukları kaydet
    foreach ($seats as $seatNumber) {
        $seatId = generateUUID();
        insert(
            "INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (?, ?, ?)",
            [$seatId, $ticketId, $seatNumber]
        );
    }
    
    // Kullanıcının bakiyesinden düş
    execute(
        "UPDATE User SET balance = balance - ? WHERE id = ?",
        [$totalPrice, $currentUser['id']]
    );
    
    // Kupon kullanıldıysa kaydet
    if ($appliedCoupon && isset($coupon)) {
        $userCouponId = generateUUID();
        insert(
            "INSERT INTO User_Coupons (id, coupon_id, user_id) VALUES (?, ?, ?)",
            [$userCouponId, $coupon['id'], $currentUser['id']]
        );
    }
    
    $db->commit();
    
    redirect('/pages/my_tickets.php', 
             'Biletiniz başarıyla satın alındı! Toplam: ' . formatMoney($totalPrice));
             
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    redirect('/pages/buy_ticket.php?trip_id=' . $tripId, 
             'Bilet satın alınırken bir hata oluştu: ' . $e->getMessage(), 'error');
}
?>

