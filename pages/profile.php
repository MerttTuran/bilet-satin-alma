<?php
$pageTitle = "Profilim";
require_once __DIR__ . '/../includes/header.php';

requireLogin();

$currentUser = getCurrentUser();

// İstatistikler
$stats = [
    'total_tickets' => fetchOne("SELECT COUNT(*) as count FROM Tickets WHERE user_id = ?", [$currentUser['id']])['count'],
    'active_tickets' => fetchOne("SELECT COUNT(*) as count FROM Tickets WHERE user_id = ? AND status = 'active'", [$currentUser['id']])['count'],
    'total_spent' => fetchOne("SELECT COALESCE(SUM(total_price), 0) as total FROM Tickets WHERE user_id = ? AND status = 'active'", [$currentUser['id']])['total'],
];
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle" style="font-size: 80px; color: #0d6efd;"></i>
                </div>
                <h4><?php echo e($currentUser['full_name']); ?></h4>
                <p class="text-muted mb-0"><?php echo e($currentUser['email']); ?></p>
                <span class="badge bg-primary mt-2">
                    <?php 
                        $roleNames = [
                            ROLE_USER => 'Kullanıcı',
                            ROLE_COMPANY_ADMIN => 'Firma Yetkilisi',
                            ROLE_ADMIN => 'Yönetici'
                        ];
                        echo $roleNames[$currentUser['role']];
                    ?>
                </span>
                
                <hr>
                
                <div class="text-start">
                    <p class="mb-2">
                        <i class="fas fa-calendar"></i> 
                        <strong>Üyelik Tarihi:</strong><br>
                        <small><?php echo formatDate($currentUser['created_at'], 'd F Y'); ?></small>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-wallet"></i> 
                        <strong>Bakiye:</strong><br>
                        <span class="text-success" style="font-size: 1.5rem;">
                            <?php echo formatMoney($currentUser['balance']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> İstatistikler</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="dashboard-card">
                            <div class="icon"><i class="fas fa-ticket-alt"></i></div>
                            <h3><?php echo $stats['total_tickets']; ?></h3>
                            <p>Toplam Bilet</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="dashboard-card success">
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                            <h3><?php echo $stats['active_tickets']; ?></h3>
                            <p>Aktif Bilet</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="dashboard-card warning">
                            <div class="icon"><i class="fas fa-lira-sign"></i></div>
                            <h3><?php echo formatMoney($stats['total_spent']); ?></h3>
                            <p>Toplam Harcama</p>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="mb-3">Hızlı İşlemler</h6>
                <div class="d-grid gap-2">
                    <a href="/pages/my_tickets.php" class="btn btn-outline-primary">
                        <i class="fas fa-ticket-alt"></i> Biletlerimi Görüntüle
                    </a>
                    <a href="/index.php" class="btn btn-outline-success">
                        <i class="fas fa-search"></i> Yeni Sefer Ara
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

