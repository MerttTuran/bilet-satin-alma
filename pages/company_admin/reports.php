<?php
$pageTitle = "Raporlar";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_COMPANY_ADMIN);

$currentUser = getCurrentUser();

// İstatistikler
$stats = [
    'total_revenue' => fetchOne("
        SELECT COALESCE(SUM(t.total_price), 0) as total 
        FROM Tickets t 
        INNER JOIN Trips tr ON t.trip_id = tr.id 
        WHERE tr.company_id = ? AND t.status = 'active'
    ", [$currentUser['company_id']])['total'],
    
    'monthly_revenue' => fetchOne("
        SELECT COALESCE(SUM(t.total_price), 0) as total 
        FROM Tickets t 
        INNER JOIN Trips tr ON t.trip_id = tr.id 
        WHERE tr.company_id = ? 
        AND t.status = 'active' 
        AND t.created_at >= date('now', 'start of month')
    ", [$currentUser['company_id']])['total'],
    
    'total_tickets' => fetchOne("
        SELECT COUNT(*) as count 
        FROM Tickets t 
        INNER JOIN Trips tr ON t.trip_id = tr.id 
        WHERE tr.company_id = ? AND t.status = 'active'
    ", [$currentUser['company_id']])['count'],
    
    'cancelled_tickets' => fetchOne("
        SELECT COUNT(*) as count 
        FROM Tickets t 
        INNER JOIN Trips tr ON t.trip_id = tr.id 
        WHERE tr.company_id = ? AND t.status = 'cancelled'
    ", [$currentUser['company_id']])['count'],
];

// En çok satan güzergahlar
$topRoutes = fetchAll("
    SELECT 
        tr.departure_city,
        tr.destination_city,
        COUNT(t.id) as ticket_count,
        SUM(t.total_price) as total_revenue
    FROM Trips tr
    LEFT JOIN Tickets t ON tr.id = t.trip_id AND t.status = 'active'
    WHERE tr.company_id = ?
    GROUP BY tr.departure_city, tr.destination_city
    ORDER BY ticket_count DESC
    LIMIT 5
", [$currentUser['company_id']]);
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-chart-line"></i> Raporlar ve İstatistikler</h2>
        <hr>
    </div>
</div>

<!-- Genel İstatistikler -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="icon"><i class="fas fa-lira-sign"></i></div>
            <h3><?php echo formatMoney($stats['total_revenue']); ?></h3>
            <p>Toplam Gelir</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card success">
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            <h3><?php echo formatMoney($stats['monthly_revenue']); ?></h3>
            <p>Bu Ay Gelir</p>
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
        <div class="dashboard-card danger">
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <h3><?php echo $stats['cancelled_tickets']; ?></h3>
            <p>İptal Edilen</p>
        </div>
    </div>
</div>

<!-- En Popüler Güzergahlar -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-trophy"></i> En Popüler Güzergahlar</h5>
            </div>
            <div class="card-body">
                <?php if (empty($topRoutes)): ?>
                    <p class="text-muted">Henüz veri bulunmuyor.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sıra</th>
                                    <th>Güzergah</th>
                                    <th>Satılan Bilet</th>
                                    <th>Gelir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; ?>
                                <?php foreach ($topRoutes as $route): ?>
                                    <tr>
                                        <td>
                                            <?php if ($rank == 1): ?>
                                                <i class="fas fa-trophy text-warning"></i>
                                            <?php elseif ($rank == 2): ?>
                                                <i class="fas fa-medal" style="color: silver;"></i>
                                            <?php elseif ($rank == 3): ?>
                                                <i class="fas fa-medal" style="color: #CD7F32;"></i>
                                            <?php else: ?>
                                                <?php echo $rank; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e($route['departure_city']); ?></strong>
                                            <i class="fas fa-arrow-right mx-2"></i>
                                            <strong><?php echo e($route['destination_city']); ?></strong>
                                        </td>
                                        <td><?php echo $route['ticket_count'] ?: 0; ?> bilet</td>
                                        <td><?php echo formatMoney($route['total_revenue'] ?: 0); ?></td>
                                    </tr>
                                    <?php $rank++; ?>
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

