<?php
$pageTitle = "Ana Sayfa - Sefer Ara";
require_once __DIR__ . '/includes/header.php';

// Arama parametreleri
$departure = input('departure');
$destination = input('destination');
$date = input('date');

// Şehir listesi
$cities = ['İstanbul', 'Ankara', 'İzmir', 'Antalya', 'Bursa', 'Adana', 'Gaziantep', 'Konya', 'Kayseri', 'Trabzon'];

// Sefer arama
$trips = [];
if ($departure && $destination && $date) {
    $sql = "SELECT t.*, bc.name as company_name, bc.logo_path,
            (SELECT COUNT(*) FROM Booked_Seats bs
             INNER JOIN Tickets ti ON bs.ticket_id = ti.id
             WHERE ti.trip_id = t.id AND ti.status = 'active') as booked_count
            FROM Trips t
            INNER JOIN Bus_Company bc ON t.company_id = bc.id
            WHERE t.departure_city = ? 
            AND t.destination_city = ?
            AND DATE(t.departure_time) = ?
            AND t.departure_time > datetime('now')
            ORDER BY t.departure_time ASC";
    
    $trips = fetchAll($sql, [$departure, $destination, $date]);
}
?>

<div class="row">
    <!-- Arama Formu -->
    <div class="col-12">
        <div class="card shadow-lg mb-4">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-search"></i> Otobüs Bileti Ara</h4>
            </div>
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="departure" class="form-label">Nereden</label>
                        <select class="form-select" id="departure" name="departure" required>
                            <option value="">Kalkış şehri seçin</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo e($city); ?>" <?php echo $departure === $city ? 'selected' : ''; ?>>
                                    <?php echo e($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="destination" class="form-label">Nereye</label>
                        <select class="form-select" id="destination" name="destination" required>
                            <option value="">Varış şehri seçin</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo e($city); ?>" <?php echo $destination === $city ? 'selected' : ''; ?>>
                                    <?php echo e($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="date" class="form-label">Tarih</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo e($date); ?>" required
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search"></i> Sefer Ara
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sefer Sonuçları -->
    <?php if ($departure && $destination && $date): ?>
        <div class="col-12">
            <h4 class="mb-3">
                <i class="fas fa-list"></i> 
                <?php echo e($departure); ?> - <?php echo e($destination); ?> Seferleri
                <small class="text-muted">(<?php echo formatDate($date, 'd.m.Y'); ?>)</small>
            </h4>
            
            <?php if (empty($trips)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Aradığınız kriterlere uygun sefer bulunamadı.
                </div>
            <?php else: ?>
                <?php foreach ($trips as $trip): ?>
                    <?php 
                        $availableSeats = $trip['capacity'] - $trip['booked_count'];
                        $isAlmostFull = $availableSeats <= 5;
                    ?>
                    <div class="card trip-card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <img src="<?php echo $trip['logo_path'] ? '/uploads/logos/' . $trip['logo_path'] : '/assets/images/bus-placeholder.png'; ?>" 
                                         alt="<?php echo e($trip['company_name']); ?>" 
                                         class="img-fluid" style="max-height: 60px;">
                                    <h6 class="mt-2 mb-0"><?php echo e($trip['company_name']); ?></h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="trip-route">
                                        <div>
                                            <div class="city"><?php echo e($trip['departure_city']); ?></div>
                                            <small class="text-muted">
                                                <?php echo formatDate($trip['departure_time'], 'H:i'); ?>
                                            </small>
                                        </div>
                                        <div class="arrow">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                        <div>
                                            <div class="city"><?php echo e($trip['destination_city']); ?></div>
                                            <small class="text-muted">
                                                <?php echo formatDate($trip['arrival_time'], 'H:i'); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-chair"></i> 
                                            <?php echo $availableSeats; ?> koltuk müsait
                                        </span>
                                        <?php if ($isAlmostFull): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-exclamation-triangle"></i> Son koltuklar!
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 text-center">
                                    <h3 class="text-primary mb-0"><?php echo formatMoney($trip['price']); ?></h3>
                                    <small class="text-muted">Kişi başı</small>
                                </div>
                                
                                <div class="col-md-2 text-center">
                                    <?php if (isLoggedIn() && hasRole(ROLE_USER)): ?>
                                        <?php if ($availableSeats > 0): ?>
                                            <a href="/pages/buy_ticket.php?trip_id=<?php echo $trip['id']; ?>" 
                                               class="btn btn-success">
                                                <i class="fas fa-ticket-alt"></i> Bilet Al
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-times"></i> Dolu
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="/pages/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="/pages/trip_detail.php?trip_id=<?php echo $trip['id']; ?>" 
                                       class="btn btn-outline-secondary btn-sm mt-2">
                                        <i class="fas fa-info-circle"></i> Detaylar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Hoş Geldiniz Mesajı -->
        <div class="col-12">
            <div class="jumbotron bg-light p-5 rounded-3">
                <h1 class="display-4">
                    <i class="fas fa-bus text-primary"></i> 
                    Hoş Geldiniz!
                </h1>
                <p class="lead">Türkiye'nin en güvenilir otobüs bileti satış platformu</p>
                <hr class="my-4">
                <p>Yüzlerce otobüs firması, binlerce sefer seçeneği ile istediğiniz yere kolayca ulaşın.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Güvenli ödeme</li>
                    <li><i class="fas fa-check text-success"></i> Anında bilet</li>
                    <li><i class="fas fa-check text-success"></i> 7/24 destek</li>
                    <li><i class="fas fa-check text-success"></i> Kolay iptal</li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

