<?php
$pageTitle = "Sefer Detayı";
require_once __DIR__ . '/../includes/header.php';

$tripId = input('trip_id');

if (!$tripId) {
    redirect('/index.php', 'Geçersiz sefer.', 'error');
}

// Sefer bilgilerini getir
$trip = fetchOne("
    SELECT t.*, bc.name as company_name, bc.logo_path
    FROM Trips t
    INNER JOIN Bus_Company bc ON t.company_id = bc.id
    WHERE t.id = ?
", [$tripId]);

if (!$trip) {
    redirect('/index.php', 'Sefer bulunamadı.', 'error');
}

// Dolu koltukları getir
$bookedSeats = getBookedSeats($tripId);
$availableSeats = $trip['capacity'] - count($bookedSeats);
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-bus"></i> Sefer Detayları</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="<?php echo $trip['logo_path'] ? '/uploads/logos/' . $trip['logo_path'] : '/assets/images/bus-placeholder.png'; ?>" 
                         alt="<?php echo e($trip['company_name']); ?>" 
                         class="img-fluid" style="max-height: 80px;">
                    <h5 class="mt-2"><?php echo e($trip['company_name']); ?></h5>
                </div>
                
                <div class="trip-route">
                    <div>
                        <div class="city"><?php echo e($trip['departure_city']); ?></div>
                        <small class="text-muted">
                            <?php echo formatDate($trip['departure_time'], 'd.m.Y H:i'); ?>
                        </small>
                    </div>
                    <div class="arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div>
                        <div class="city"><?php echo e($trip['destination_city']); ?></div>
                        <small class="text-muted">
                            <?php echo formatDate($trip['arrival_time'], 'd.m.Y H:i'); ?>
                        </small>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-calendar"></i> Kalkış Tarihi:</strong><br>
                        <?php echo formatDate($trip['departure_time'], 'd F Y, l'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-clock"></i> Kalkış Saati:</strong><br>
                        <?php echo formatDate($trip['departure_time'], 'H:i'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-clock"></i> Varış Saati:</strong><br>
                        <?php echo formatDate($trip['arrival_time'], 'H:i'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-chair"></i> Toplam Koltuk:</strong><br>
                        <?php echo $trip['capacity']; ?> koltuk</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-check-circle"></i> Müsait Koltuk:</strong><br>
                        <span class="badge bg-success"><?php echo $availableSeats; ?> koltuk</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-times-circle"></i> Dolu Koltuk:</strong><br>
                        <span class="badge bg-secondary"><?php echo count($bookedSeats); ?> koltuk</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-ticket-alt"></i> Fiyat Bilgisi</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-primary"><?php echo formatMoney($trip['price']); ?></h2>
                <p class="text-muted">Kişi başı fiyat</p>
                
                <?php if (isLoggedIn() && hasRole(ROLE_USER)): ?>
                    <?php if ($availableSeats > 0): ?>
                        <a href="/pages/buy_ticket.php?trip_id=<?php echo $trip['id']; ?>" 
                           class="btn btn-success btn-lg w-100">
                            <i class="fas fa-shopping-cart"></i> Bilet Al
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="fas fa-times"></i> Bu Sefer Dolu
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/pages/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                       class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-sign-in-alt"></i> Giriş Yapın
                    </a>
                    <p class="mt-2 mb-0 text-muted">
                        <small>Bilet almak için giriş yapmalısınız</small>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="fas fa-info-circle"></i> Önemli Bilgiler</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Biletler anında e-posta ile gönderilir
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Kalkıştan 1 saat öncesine kadar iptal edilebilir
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        İptal edilen biletlerin ücreti iade edilir
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i> 
                        Kampanya kuponları kullanabilirsiniz
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="/index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Sefer Aramaya Dön
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

