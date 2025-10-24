<?php
$pageTitle = "Sistem Kupon Yönetimi";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_ADMIN);

// Sistem kuponlarını getir (tüm firmalar için geçerli olanlar)
$coupons = fetchAll("
    SELECT c.*,
           (SELECT COUNT(*) FROM User_Coupons WHERE coupon_id = c.id) as usage_count
    FROM Coupons c
    WHERE c.company_id IS NULL
    ORDER BY c.created_at DESC
");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-gift"></i> Sistem Kupon Yönetimi</h2>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                <i class="fas fa-plus"></i> Yeni Sistem Kuponu Oluştur
            </button>
        </div>
        <p class="text-muted">Sistem kuponları tüm firmalarda geçerlidir.</p>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (empty($coupons)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Henüz sistem kuponu bulunmuyor.
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
                                            <a href="/pages/admin/coupon_delete.php?id=<?php echo $coupon['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirmDelete('Bu kuponu silmek istediğinize emin misiniz?')">
                                                <i class="fas fa-trash"></i>
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

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-gift"></i> Yeni Sistem Kuponu Oluştur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/pages/admin/coupon_create.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="code" class="form-label">Kupon Kodu *</label>
                        <input type="text" class="form-control text-uppercase" id="code" name="code" 
                               placeholder="ORNEK2024" required>
                        <div class="form-text">Büyük harfler ve rakamlar kullanılabilir</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="discount" class="form-label">İndirim Oranı (%) *</label>
                        <input type="number" class="form-control" id="discount" name="discount" 
                               min="1" max="100" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="usage_limit" class="form-label">Kullanım Limiti</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" 
                               min="1" placeholder="Boş bırakırsanız sınırsız olur">
                    </div>
                    
                    <div class="mb-3">
                        <label for="expire_date" class="form-label">Son Kullanma Tarihi</label>
                        <input type="date" class="form-control" id="expire_date" name="expire_date">
                        <div class="form-text">Boş bırakırsanız süresiz olacaktır</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Bu kupon tüm firmalar için geçerli olacaktır.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Oluştur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

