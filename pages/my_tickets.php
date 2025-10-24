<?php
$pageTitle = "Biletlerim";
require_once __DIR__ . '/../includes/header.php';

requireRole(ROLE_USER);

$currentUser = getCurrentUser();

// Kullanıcının biletlerini getir
$tickets = fetchAll("
    SELECT 
        t.id as ticket_id,
        t.status,
        t.total_price,
        t.created_at,
        tr.departure_city,
        tr.destination_city,
        tr.departure_time,
        tr.arrival_time,
        tr.price as seat_price,
        bc.name as company_name,
        bc.logo_path,
        GROUP_CONCAT(bs.seat_number) as seat_numbers
    FROM Tickets t
    INNER JOIN Trips tr ON t.trip_id = tr.id
    INNER JOIN Bus_Company bc ON tr.company_id = bc.id
    LEFT JOIN Booked_Seats bs ON bs.ticket_id = t.id
    WHERE t.user_id = ?
    GROUP BY t.id
    ORDER BY tr.departure_time DESC
", [$currentUser['id']]);

// Biletleri kategorize et
$activeTickets = [];
$pastTickets = [];

foreach ($tickets as $ticket) {
    if ($ticket['status'] === 'active' && strtotime($ticket['departure_time']) > time()) {
        $activeTickets[] = $ticket;
    } else {
        $pastTickets[] = $ticket;
    }
}
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-ticket-alt"></i> Biletlerim</h2>
        <hr>
    </div>
</div>

<!-- Aktif Biletler -->
<div class="row">
    <div class="col-12">
        <h4 class="mb-3"><i class="fas fa-clock text-primary"></i> Yaklaşan Seferler</h4>
        
        <?php if (empty($activeTickets)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Henüz aktif biletiniz bulunmuyor. 
                <a href="/index.php" class="alert-link">Sefer aramaya başlayın</a>
            </div>
        <?php else: ?>
            <?php foreach ($activeTickets as $ticket): ?>
                <?php 
                    $canCancel = canCancelTicket($ticket);
                    $seatNumbers = explode(',', $ticket['seat_numbers']);
                ?>
                <div class="card ticket-card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="<?php echo $ticket['logo_path'] ? '/uploads/logos/' . $ticket['logo_path'] : '/assets/images/bus-placeholder.png'; ?>" 
                                     alt="<?php echo e($ticket['company_name']); ?>" 
                                     class="img-fluid mb-2" style="max-height: 50px;">
                                <small class="d-block"><?php echo e($ticket['company_name']); ?></small>
                            </div>
                            
                            <div class="col-md-5">
                                <div class="trip-route">
                                    <div>
                                        <div class="city"><?php echo e($ticket['departure_city']); ?></div>
                                        <small class="text-muted">
                                            <?php echo formatDate($ticket['departure_time'], 'd.m.Y H:i'); ?>
                                        </small>
                                    </div>
                                    <div class="arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                    <div>
                                        <div class="city"><?php echo e($ticket['destination_city']); ?></div>
                                        <small class="text-muted">
                                            <?php echo formatDate($ticket['arrival_time'], 'H:i'); ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="mt-2">
                                    <span class="badge bg-info">
                                        <i class="fas fa-chair"></i> 
                                        Koltuk: <?php echo implode(', ', $seatNumbers); ?>
                                    </span>
                                    <span class="badge bg-secondary">
                                        <?php echo count($seatNumbers); ?> Yolcu
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-md-2 text-center">
                                <h4 class="text-primary mb-0"><?php echo formatMoney($ticket['total_price']); ?></h4>
                                <small class="text-muted">Toplam</small>
                            </div>
                            
                            <div class="col-md-3 text-center">
                                <a href="/api/generate_pdf.php?ticket_id=<?php echo $ticket['ticket_id']; ?>" 
                                   class="btn btn-outline-primary btn-sm mb-2 w-100" target="_blank">
                                    <i class="fas fa-download"></i> PDF İndir
                                </a>
                                
                                <?php if ($canCancel): ?>
                                    <button onclick="confirmCancelTicket('<?php echo $ticket['ticket_id']; ?>')" 
                                            class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-times"></i> İptal Et
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm w-100" disabled 
                                            title="Kalkışa 1 saatten az kaldığı için iptal edilemez">
                                        <i class="fas fa-ban"></i> İptal Edilemez
                                    </button>
                                <?php endif; ?>
                                
                                <small class="text-muted d-block mt-2">
                                    <?php echo formatDate($ticket['created_at'], 'd.m.Y H:i'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Geçmiş Biletler -->
<?php if (!empty($pastTickets)): ?>
<div class="row mt-4">
    <div class="col-12">
        <h4 class="mb-3"><i class="fas fa-history text-secondary"></i> Geçmiş Biletler</h4>
        
        <?php foreach ($pastTickets as $ticket): ?>
            <?php 
                $seatNumbers = explode(',', $ticket['seat_numbers']);
                $statusClass = $ticket['status'] === 'cancelled' ? 'cancelled' : 'expired';
                $statusText = $ticket['status'] === 'cancelled' ? 'İptal Edildi' : 'Tamamlandı';
                $statusBadge = $ticket['status'] === 'cancelled' ? 'bg-danger' : 'bg-secondary';
            ?>
            <div class="card ticket-card <?php echo $statusClass; ?> mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="<?php echo $ticket['logo_path'] ? '/uploads/logos/' . $ticket['logo_path'] : '/assets/images/bus-placeholder.png'; ?>" 
                                 alt="<?php echo e($ticket['company_name']); ?>" 
                                 class="img-fluid mb-2" style="max-height: 50px;">
                            <small class="d-block"><?php echo e($ticket['company_name']); ?></small>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="trip-route">
                                <div>
                                    <div class="city"><?php echo e($ticket['departure_city']); ?></div>
                                    <small class="text-muted">
                                        <?php echo formatDate($ticket['departure_time'], 'd.m.Y H:i'); ?>
                                    </small>
                                </div>
                                <div class="arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div>
                                    <div class="city"><?php echo e($ticket['destination_city']); ?></div>
                                    <small class="text-muted">
                                        <?php echo formatDate($ticket['arrival_time'], 'H:i'); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <span class="badge <?php echo $statusBadge; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                                <span class="badge bg-info">
                                    Koltuk: <?php echo implode(', ', $seatNumbers); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-2 text-center">
                            <h5 class="text-muted mb-0"><?php echo formatMoney($ticket['total_price']); ?></h5>
                        </div>
                        
                        <div class="col-md-2 text-center">
                            <small class="text-muted">
                                <?php echo formatDate($ticket['created_at'], 'd.m.Y'); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Bakiye Bilgisi -->
<div class="row mt-4">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5><i class="fas fa-wallet"></i> Hesap Bakiyeniz</h5>
                <h2 class="text-primary"><?php echo formatMoney($currentUser['balance']); ?></h2>
                <p class="text-muted mb-0">Bilet satın almak için bakiyenizi kullanabilirsiniz</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

