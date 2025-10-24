<?php
$pageTitle = "Kullanıcı Listesi";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_ADMIN);

// Kullanıcıları getir
$users = fetchAll("
    SELECT u.*,
           (SELECT COUNT(*) FROM Tickets WHERE user_id = u.id AND status = 'active') as ticket_count
    FROM User u
    WHERE u.role = ?
    ORDER BY u.created_at DESC
", [ROLE_USER]);
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-users"></i> Kullanıcı Listesi</h2>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Henüz kullanıcı bulunmuyor.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>E-posta</th>
                                    <th>Bakiye</th>
                                    <th>Aktif Bilet</th>
                                    <th>Kayıt Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo e($user['full_name']); ?></td>
                                        <td><?php echo e($user['email']); ?></td>
                                        <td><?php echo formatMoney($user['balance']); ?></td>
                                        <td>
                                            <?php if ($user['ticket_count'] > 0): ?>
                                                <span class="badge bg-success"><?php echo $user['ticket_count']; ?> bilet</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($user['created_at'], 'd.m.Y H:i'); ?></td>
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

