<?php
$pageTitle = "Firma Admin Panel";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_COMPANY_ADMIN);

$currentUser = getCurrentUser();

// Firma bilgilerini getir
$company = fetchOne("SELECT * FROM Bus_Company WHERE id = ?", [$currentUser['company_id']]);

if (!$company) {
    redirect('/index.php', 'Firma bulunamadı.', 'error');
}

// İstatistikler
$stats = [
    'total_trips' => fetchOne("SELECT COUNT(*) as count FROM Trips WHERE company_id = ?", [$currentUser['company_id']])['count'],
    'active_trips' => fetchOne("SELECT COUNT(*) as count FROM Trips WHERE company_id = ? AND departure_time > datetime('now')", [$currentUser['company_id']])['count'],
    'total_tickets' => fetchOne("SELECT COUNT(*) as count FROM Tickets t INNER JOIN Trips tr ON t.trip_id = tr.id WHERE tr.company_id = ? AND t.status = 'active'", [$currentUser['company_id']])['count'],
    'total_revenue' => fetchOne("SELECT COALESCE(SUM(t.total_price), 0) as total FROM Tickets t INNER JOIN Trips tr ON t.trip_id = tr.id WHERE tr.company_id = ? AND t.status = 'active'", [$currentUser['company_id']])['total'],
];

// Yaklaşan seferler
$upcomingTrips = fetchAll("
    SELECT t.*,
           (SELECT COUNT(*) FROM Booked_Seats bs
            INNER JOIN Tickets ti ON bs.ticket_id = ti.id
            WHERE ti.trip_id = t.id AND ti.status = 'active') as booked_count
    FROM Trips t
    WHERE t.company_id = ? AND t.departure_time > datetime('now')
    ORDER BY t.departure_time ASC
    LIMIT 5
", [$currentUser['company_id']]);
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-briefcase"></i> Firma Yönetim Paneli</h2>
        <p class="text-muted">Hoş geldiniz, <strong><?php echo e($company['name']); ?></strong></p>
        <hr>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="icon"><i class="fas fa-bus"></i></div>
            <h3><?php echo $stats['total_trips']; ?></h3>
            <p>Toplam Sefer</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card success">
            <div class="icon"><i class="fas fa-clock"></i></div>
            <h3><?php echo $stats['active_trips']; ?></h3>
            <p>Aktif Sefer</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card warning">
            <div class="icon"><i class="fas fa-ticket-alt"></i></div>
            <h3><?php echo $stats['total_tickets']; ?></h3>
            <p>Satılan Bilet</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #198754, #146c43);">
            <div class="icon"><i class="fas fa-lira-sign"></i></div>
            <h3><?php echo formatMoney($stats['total_revenue']); ?></h3>
            <p>Toplam Gelir</p>
        </div>
    </div>
</div>

<!-- Hızlı İşlemler -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-bolt"></i> Hızlı İşlemler</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="/pages/company_admin/trips.php" class="btn btn-primary w-100">
                            <i class="fas fa-bus"></i> Sefer Yönetimi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/pages/company_admin/trip_form.php" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Yeni Sefer Ekle
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/pages/company_admin/coupons.php" class="btn btn-warning w-100">
                            <i class="fas fa-gift"></i> Kupon Yönetimi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/pages/company_admin/reports.php" class="btn btn-info w-100">
                            <i class="fas fa-chart-line"></i> Raporlar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Yaklaşan Seferler -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Yaklaşan Seferler</h5>
                <a href="/pages/company_admin/trips.php" class="btn btn-sm btn-outline-primary">
                    Tümünü Gör
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingTrips)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Henüz aktif sefer bulunmuyor.
                        <a href="/pages/company_admin/trip_form.php">Yeni sefer ekleyin</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Güzergah</th>
                                    <th>Kalkış</th>
                                    <th>Varış</th>
                                    <th>Fiyat</th>
                                    <th>Doluluk</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingTrips as $trip): ?>
                                    <?php 
                                        $availableSeats = $trip['capacity'] - $trip['booked_count'];
                                        $occupancyRate = ($trip['booked_count'] / $trip['capacity']) * 100;
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($trip['departure_city']); ?></strong>
                                            <i class="fas fa-arrow-right text-muted"></i>
                                            <strong><?php echo e($trip['destination_city']); ?></strong>
                                        </td>
                                        <td><?php echo formatDate($trip['departure_time'], 'd.m.Y H:i'); ?></td>
                                        <td><?php echo formatDate($trip['arrival_time'], 'H:i'); ?></td>
                                        <td><?php echo formatMoney($trip['price']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar <?php echo $occupancyRate > 80 ? 'bg-success' : ($occupancyRate > 50 ? 'bg-warning' : 'bg-info'); ?>" 
                                                     style="width: <?php echo $occupancyRate; ?>%">
                                                    <?php echo $trip['booked_count']; ?>/<?php echo $trip['capacity']; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="/pages/company_admin/trip_form.php?id=<?php echo $trip['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

