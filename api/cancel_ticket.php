<?php
// api/cancel_ticket.php - Bilet İptal İşlemi

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(ROLE_USER);

$ticketId = input('ticket_id');

if (!$ticketId) {
    redirect('/pages/my_tickets.php', 'Geçersiz bilet.', 'error');
}

$currentUser = getCurrentUser();

// Bileti getir
$ticket = fetchOne("
    SELECT t.*, tr.departure_time
    FROM Tickets t
    INNER JOIN Trips tr ON t.trip_id = tr.id
    WHERE t.id = ? AND t.user_id = ?
", [$ticketId, $currentUser['id']]);

if (!$ticket) {
    redirect('/pages/my_tickets.php', 'Bilet bulunamadı veya size ait değil.', 'error');
}

// Zaten iptal edilmiş mi kontrol et
if ($ticket['status'] === 'cancelled') {
    redirect('/pages/my_tickets.php', 'Bu bilet zaten iptal edilmiş.', 'error');
}

// İptal edilebilir mi kontrol et
if (!canCancelTicket($ticket)) {
    redirect('/pages/my_tickets.php', 
             'Kalkış saatine 1 saatten az kaldığı için bilet iptal edilemez.', 'error');
}

try {
    $db = getDB();
    $db->beginTransaction();
    
    // Bileti iptal et
    execute("UPDATE Tickets SET status = 'cancelled' WHERE id = ?", [$ticketId]);
    
    // Parayı iade et
    execute("UPDATE User SET balance = balance + ? WHERE id = ?", 
            [$ticket['total_price'], $currentUser['id']]);
    
    $db->commit();
    
    redirect('/pages/my_tickets.php', 
             'Biletiniz başarıyla iptal edildi. ' . formatMoney($ticket['total_price']) . ' hesabınıza iade edildi.');
             
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    redirect('/pages/my_tickets.php', 'Bilet iptal edilirken bir hata oluştu.', 'error');
}
?>

