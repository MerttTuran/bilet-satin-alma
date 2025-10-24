<?php
$pageTitle = "Bilet Satın Al";
require_once __DIR__ . '/../includes/header.php';

requireRole(ROLE_USER);

$tripId = input('trip_id');

if (!$tripId) {
    redirect('/index.php', 'Geçersiz sefer.', 'error');
}

// Sefer bilgilerini getir
$trip = fetchOne("
    SELECT t.*, bc.name as company_name, bc.logo_path, bc.id as company_id
    FROM Trips t
    INNER JOIN Bus_Company bc ON t.company_id = bc.id
    WHERE t.id = ?
", [$tripId]);

if (!$trip) {
    redirect('/index.php', 'Sefer bulunamadı.', 'error');
}

// Kalkış saati geçmiş mi kontrol et
if (strtotime($trip['departure_time']) < time()) {
    redirect('/index.php', 'Bu seferin kalkış saati geçmiş.', 'error');
}

// Dolu koltukları getir
$bookedSeats = getBookedSeats($tripId);
$availableSeats = $trip['capacity'] - count($bookedSeats);

if ($availableSeats <= 0) {
    redirect('/pages/trip_detail.php?trip_id=' . $tripId, 'Bu sefer dolu.', 'error');
}

$currentUser = getCurrentUser();
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-ticket-alt"></i> Bilet Satın Al</h4>
            </div>
            <div class="card-body">
                <div class="trip-route mb-4">
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
                            <?php echo formatDate($trip['arrival_time'], 'H:i'); ?>
                        </small>
                    </div>
                </div>
                
                <h5 class="mb-3"><i class="fas fa-chair"></i> Koltuk Seçin</h5>
                <p class="text-muted">Lütfen satın almak istediğiniz koltukları seçin</p>
                
                <div class="seat-container">
                    <?php for ($i = 1; $i <= $trip['capacity']; $i++): ?>
                        <div class="seat <?php echo in_array($i, $bookedSeats) ? 'booked' : ''; ?>" 
                             data-seat="<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <div class="seat-legend">
                    <div class="legend-item">
                        <div class="legend-box" style="background: white;"></div>
                        <span>Boş</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background: var(--success-color); border-color: var(--success-color);"></div>
                        <span>Seçili</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background: var(--secondary-color); border-color: var(--secondary-color);"></div>
                        <span>Dolu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h5><i class="fas fa-shopping-cart"></i> Özet</h5>
            </div>
            <div class="card-body">
                <form action="/api/buy_ticket.php" method="POST" id="buyTicketForm">
                    <input type="hidden" name="trip_id" id="tripId" value="<?php echo e($tripId); ?>">
                    <input type="hidden" name="selected_seats" id="selectedSeats" value="">
                    <input type="hidden" name="applied_coupon" id="appliedCoupon" value="">
                    <input type="hidden" name="base_price" id="basePrice" value="<?php echo $trip['price']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Firma</label>
                        <p class="mb-0"><strong><?php echo e($trip['company_name']); ?></strong></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Seçilen Koltuklar</label>
                        <p class="mb-0" id="seatCount">0</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Koltuk Başı Fiyat</label>
                        <p class="mb-0"><?php echo formatMoney($trip['price']); ?></p>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label for="couponCode" class="form-label">
                            <i class="fas fa-gift"></i> İndirim Kuponu
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="couponCode" 
                                   placeholder="Kupon kodunuz">
                            <button type="button" class="btn btn-outline-secondary apply-coupon-btn" 
                                    onclick="applyCoupon()">
                                Uygula
                            </button>
                        </div>
                        <small class="text-muted" id="couponDiscount"></small>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h5>Toplam: <span class="text-primary" id="totalPrice">0,00 ₺</span></h5>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-wallet"></i> 
                            Bakiyeniz: <strong><?php echo formatMoney($currentUser['balance']); ?></strong>
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100" id="submitBtn" disabled>
                        <i class="fas fa-check"></i> Satın Al
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Sayfa yüklendiğinde koltuk seçimini başlat
document.addEventListener('DOMContentLoaded', function() {
    initSeatSelection();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

