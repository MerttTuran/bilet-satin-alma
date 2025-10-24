<?php
$pageTitle = "Firma Admin Yönetimi";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_ADMIN);

// Firma adminlerini getir
$admins = fetchAll("
    SELECT u.*, bc.name as company_name
    FROM User u
    LEFT JOIN Bus_Company bc ON u.company_id = bc.id
    WHERE u.role = ?
    ORDER BY u.created_at DESC
", [ROLE_COMPANY_ADMIN]);

// Firmaları getir (form için)
$companies = fetchAll("SELECT id, name FROM Bus_Company ORDER BY name ASC");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-user-tie"></i> Firma Admin Yönetimi</h2>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="fas fa-plus"></i> Yeni Firma Admin Ekle
            </button>
        </div>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (empty($admins)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Henüz firma admin bulunmuyor.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>E-posta</th>
                                    <th>Firma</th>
                                    <th>Bakiye</th>
                                    <th>Eklenme Tarihi</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?php echo e($admin['full_name']); ?></td>
                                        <td><?php echo e($admin['email']); ?></td>
                                        <td><?php echo e($admin['company_name'] ?? '-'); ?></td>
                                        <td><?php echo formatMoney($admin['balance']); ?></td>
                                        <td><?php echo formatDate($admin['created_at'], 'd.m.Y'); ?></td>
                                        <td>
                                            <a href="/pages/admin/admin_delete.php?id=<?php echo $admin['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirmDelete('Bu admin\'i silmek istediğinize emin misiniz?')">
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

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Yeni Firma Admin Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/pages/admin/admin_create.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Ad Soyad *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre *</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="form-text">En az 6 karakter</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_id" class="form-label">Firma *</label>
                        <select class="form-select" id="company_id" name="company_id" required>
                            <option value="">Firma seçin</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>">
                                    <?php echo e($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

