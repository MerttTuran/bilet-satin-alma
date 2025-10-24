<?php
$pageTitle = "Sefer Yönetimi";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_COMPANY_ADMIN);

$currentUser = getCurrentUser();

// Filtreler
$filter = input('filter', 'upcoming'); // upcoming, past, all

$sql = "
    SELECT t.*,
           (SELECT COUNT(*) FROM Booked_Seats bs
            INNER JOIN Tickets ti ON bs.ticket_id = ti.id
            WHERE ti.trip_id = t.id AND ti.status = 'active') as booked_count
    FROM Trips t
    WHERE t.company_id = ?
";

if ($filter === 'upcoming') {
    $sql .= " AND t.departure_time > datetime('now')";
} elseif ($filter === 'past') {
    $sql .= " AND t.departure_time <= datetime('now')";
}

$sql .= " ORDER BY t.departure_time DESC";

$trips = fetchAll($sql, [$currentUser['company_id']]);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-bus"></i> Sefer Yönetimi</h2>
            <a href="/pages/company_admin/trip_form.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Yeni Sefer Ekle
            </a>
        </div>
        <hr>
    </div>
</div>

<!-- Filtre Butonları -->
<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group" role="group">
            <a href="?filter=upcoming" class="btn btn-outline-primary <?php echo $filter === 'upcoming' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Yaklaşan Seferler
            </a>
            <a href="?filter=past" class="btn btn-outline-secondary <?php echo $filter === 'past' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> Geçmiş Seferler
            </a>
            <a href="?filter=all" class="btn btn-outline-info <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Tüm Seferler
            </a>
        </div>
    </div>
</div>

<!-- Sefer Listesi -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (empty($trips)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Sefer bulunamadı.
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
                                    <th>Kapasite</th>
                                    <th>Doluluk</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trips as $trip): ?>
                                    <?php 
                                        $availableSeats = $trip['capacity'] - $trip['booked_count'];
                                        $occupancyRate = ($trip['booked_count'] / $trip['capacity']) * 100;
                                        $isPast = strtotime($trip['departure_time']) < time();
                                    ?>
                                    <tr class="<?php echo $isPast ? 'table-secondary' : ''; ?>">
                                        <td>
                                            <strong><?php echo e($trip['departure_city']); ?></strong>
                                            <i class="fas fa-arrow-right text-muted mx-2"></i>
                                            <strong><?php echo e($trip['destination_city']); ?></strong>
                                        </td>
                                        <td><?php echo formatDate($trip['departure_time'], 'd.m.Y H:i'); ?></td>
                                        <td><?php echo formatDate($trip['arrival_time'], 'H:i'); ?></td>
                                        <td><?php echo formatMoney($trip['price']); ?></td>
                                        <td><?php echo $trip['capacity']; ?> koltuk</td>
                                        <td>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar <?php echo $occupancyRate > 80 ? 'bg-success' : ($occupancyRate > 50 ? 'bg-warning' : 'bg-info'); ?>" 
                                                     style="width: <?php echo $occupancyRate; ?>%">
                                                    <?php echo $trip['booked_count']; ?>/<?php echo $trip['capacity']; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($isPast): ?>
                                                <span class="badge bg-secondary">Tamamlandı</span>
                                            <?php elseif ($availableSeats == 0): ?>
                                                <span class="badge bg-danger">Dolu</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/pages/company_admin/trip_form.php?id=<?php echo $trip['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($trip['booked_count'] == 0): ?>
                                                    <a href="/pages/company_admin/trip_delete.php?id=<?php echo $trip['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirmDelete('Bu seferi silmek istediğinize emin misiniz?')" 
                                                       title="Sil">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Satılan bilet var, silinemez">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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

