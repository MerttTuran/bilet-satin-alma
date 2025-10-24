<?php
$pageTitle = "Admin Panel";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_ADMIN);

// Sistem geneli istatistikler
$stats = [
    'total_companies' => fetchOne("SELECT COUNT(*) as count FROM Bus_Company")['count'],
    'total_users' => fetchOne("SELECT COUNT(*) as count FROM User WHERE role = ?", [ROLE_USER])['count'],
    'total_trips' => fetchOne("SELECT COUNT(*) as count FROM Trips")['count'],
    'total_tickets' => fetchOne("SELECT COUNT(*) as count FROM Tickets WHERE status = 'active'")['count'],
    'total_revenue' => fetchOne("SELECT COALESCE(SUM(total_price), 0) as total FROM Tickets WHERE status = 'active'")['total'],
    'company_admins' => fetchOne("SELECT COUNT(*) as count FROM User WHERE role = ?", [ROLE_COMPANY_ADMIN])['count'],
];

// Son eklenen firmalar
$recentCompanies = fetchAll("
    SELECT * FROM Bus_Company 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Son kayıt olan kullanıcılar
$recentUsers = fetchAll("
    SELECT * FROM User 
    WHERE role = ? 
    ORDER BY created_at DESC 
    LIMIT 5
", [ROLE_USER]);
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-shield-alt"></i> Yönetim Paneli</h2>
        <p class="text-muted">Sistem geneli yönetim ve istatistikler</p>
        <hr>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="dashboard-card">
            <div class="icon"><i class="fas fa-building"></i></div>
            <h3><?php echo $stats['total_companies']; ?></h3>
            <p>Firma</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-card success">
            <div class="icon"><i class="fas fa-users"></i></div>
            <h3><?php echo $stats['total_users']; ?></h3>
            <p>Kullanıcı</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-card warning">
            <div class="icon"><i class="fas fa-user-tie"></i></div>
            <h3><?php echo $stats['company_admins']; ?></h3>
            <p>Firma Admin</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #0dcaf0, #0aa2c0);">
            <div class="icon"><i class="fas fa-bus"></i></div>
            <h3><?php echo $stats['total_trips']; ?></h3>
            <p>Sefer</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #fd7e14, #d86b11);">
            <div class="icon"><i class="fas fa-ticket-alt"></i></div>
            <h3><?php echo $stats['total_tickets']; ?></h3>
            <p>Bilet</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #198754, #146c43);">
            <div class="icon"><i class="fas fa-lira-sign"></i></div>
            <h3><?php echo number_format($stats['total_revenue'], 0); ?> ₺</h3>
            <p>Gelir</p>
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
                        <a href="/pages/admin/companies.php" class="btn btn-primary w-100">
                            <i class="fas fa-building"></i> Firma Yönetimi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/pages/admin/company_admins.php" class="btn btn-warning w-100">
                            <i class="fas fa-user-tie"></i> Firma Admin Yönetimi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/pages/admin/coupons.php" class="btn btn-success w-100">
                            <i class="fas fa-gift"></i> Kupon Yönetimi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/pages/admin/users.php" class="btn btn-info w-100">
                            <i class="fas fa-users"></i> Kullanıcı Listesi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Son Eklenen Firmalar -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-building"></i> Son Eklenen Firmalar</h5>
                <a href="/pages/admin/companies.php" class="btn btn-sm btn-outline-primary">Tümü</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentCompanies)): ?>
                    <p class="text-muted">Henüz firma eklenmemiş.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($recentCompanies as $company): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?php echo e($company['name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo formatDate($company['created_at'], 'd.m.Y'); ?>
                                        </small>
                                    </div>
                                    <a href="/pages/admin/company_form.php?id=<?php echo $company['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Son Kayıt Olan Kullanıcılar -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users"></i> Son Kayıt Olanlar</h5>
                <a href="/pages/admin/users.php" class="btn btn-sm btn-outline-primary">Tümü</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentUsers)): ?>
                    <p class="text-muted">Henüz kullanıcı yok.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($recentUsers as $user): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?php echo e($user['full_name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo e($user['email']); ?> - 
                                            <?php echo formatDate($user['created_at'], 'd.m.Y'); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-info"><?php echo formatMoney($user['balance']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

