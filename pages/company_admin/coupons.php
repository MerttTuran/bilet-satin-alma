<?php
$pageTitle = "Kupon Yönetimi";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_COMPANY_ADMIN);

$currentUser = getCurrentUser();

// Firma kuponlarını getir
$coupons = fetchAll("
    SELECT c.*,
           (SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = c.id) as usage_count
    FROM Coupons c
    WHERE c.company_id = ?
    ORDER BY c.created_at DESC
", [$currentUser['company_id']]);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-gift"></i> Kupon Yönetimi</h2>
            <a href="/pages/company_admin/coupon_form.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Yeni Kupon Oluştur
            </a>
        </div>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (empty($coupons)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Henüz kupon bulunmuyor.
                        <a href="/pages/company_admin/coupon_form.php">Yeni kupon oluşturun</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kod</th>
                                    <th>İndirim</th>
                                    <th>Kullanım Limiti</th>
                                    <th>Kullanım</th>
                                    <th>Son Kullanma</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coupons as $coupon): ?>
                                    <?php 
                                        $isExpired = $coupon['expire_date'] && strtotime($coupon['expire_date']) < time();
                                        $isLimitReached = $coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit'];
                                        $isActive = !$isExpired && !$isLimitReached;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo e($coupon['code']); ?></strong></td>
                                        <td><span class="badge bg-warning">%<?php echo $coupon['discount']; ?></span></td>
                                        <td>
                                            <?php if ($coupon['usage_limit']): ?>
                                                <?php echo $coupon['usage_limit']; ?> kullanım
                                            <?php else: ?>
                                                <span class="text-muted">Sınırsız</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $coupon['usage_count']; ?> kez</td>
                                        <td>
                                            <?php if ($coupon['expire_date']): ?>
                                                <?php echo formatDate($coupon['expire_date'], 'd.m.Y'); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Süresiz</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isExpired): ?>
                                                <span class="badge bg-secondary">Süresi Doldu</span>
                                            <?php elseif ($isLimitReached): ?>
                                                <span class="badge bg-secondary">Limit Doldu</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/pages/company_admin/coupon_form.php?id=<?php echo $coupon['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="/pages/company_admin/coupon_delete.php?id=<?php echo $coupon['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirmDelete('Bu kuponu silmek istediğinize emin misiniz?')" 
                                                   title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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

