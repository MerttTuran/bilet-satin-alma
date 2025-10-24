<?php
$pageTitle = "Firma Yönetimi";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_ADMIN);

// Firmaları getir
$companies = fetchAll("
    SELECT bc.*,
           (SELECT COUNT(*) FROM Trips WHERE company_id = bc.id) as trip_count,
           (SELECT COUNT(*) FROM User WHERE company_id = bc.id AND role = ?) as admin_count
    FROM Bus_Company bc
    ORDER BY bc.created_at DESC
", [ROLE_COMPANY_ADMIN]);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-building"></i> Firma Yönetimi</h2>
            <a href="/pages/admin/company_form.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Yeni Firma Ekle
            </a>
        </div>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (empty($companies)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Henüz firma bulunmuyor.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Logo</th>
                                    <th>Firma Adı</th>
                                    <th>Sefer Sayısı</th>
                                    <th>Admin Sayısı</th>
                                    <th>Eklenme Tarihi</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($companies as $company): ?>
                                    <tr>
                                        <td>
                                            <?php if ($company['logo_path']): ?>
                                                <img src="/uploads/logos/<?php echo e($company['logo_path']); ?>" 
                                                     alt="<?php echo e($company['name']); ?>" 
                                                     style="max-height: 40px;">
                                            <?php else: ?>
                                                <i class="fas fa-building fa-2x text-muted"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo e($company['name']); ?></strong></td>
                                        <td><?php echo $company['trip_count']; ?> sefer</td>
                                        <td><?php echo $company['admin_count']; ?> admin</td>
                                        <td><?php echo formatDate($company['created_at'], 'd.m.Y'); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/pages/admin/company_form.php?id=<?php echo $company['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($company['trip_count'] == 0 && $company['admin_count'] == 0): ?>
                                                    <a href="/pages/admin/company_delete.php?id=<?php echo $company['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirmDelete('Bu firmayı silmek istediğinize emin misiniz?')" 
                                                       title="Sil">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled 
                                                            title="Sefer veya admin var, silinemez">
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

