<?php
$pageTitle = "Kupon Ekle/Düzenle";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_COMPANY_ADMIN);

$currentUser = getCurrentUser();
$couponId = input('id');
$isEdit = !empty($couponId);

// Düzenleme modunda kuponu getir
$coupon = null;
if ($isEdit) {
    $coupon = fetchOne("SELECT * FROM Coupons WHERE id = ? AND company_id = ?", [$couponId, $currentUser['company_id']]);
    if (!$coupon) {
        redirect('/pages/company_admin/coupons.php', 'Kupon bulunamadı veya size ait değil.', 'error');
    }
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim(input('code')));
    $discount = floatval(input('discount'));
    $usageLimit = input('usage_limit') ? intval(input('usage_limit')) : null;
    $expireDate = input('expire_date') ? input('expire_date') . ' 23:59:59' : null;
    
    // Validasyon
    $errors = [];
    
    if (empty($code)) $errors[] = 'Kupon kodu boş olamaz';
    if (strlen($code) < 3) $errors[] = 'Kupon kodu en az 3 karakter olmalıdır';
    if ($discount <= 0 || $discount > 100) $errors[] = 'İndirim oranı 1-100 arasında olmalıdır';
    
    // Kod kontrolü (düzenleme dışında)
    if (!$isEdit) {
        $existingCoupon = fetchOne("SELECT id FROM Coupons WHERE code = ?", [$code]);
        if ($existingCoupon) {
            $errors[] = 'Bu kupon kodu zaten kullanılıyor';
        }
    }
    
    if (empty($errors)) {
        try {
            if ($isEdit) {
                // Güncelle
                execute("
                    UPDATE Coupons SET 
                        code = ?,
                        discount = ?,
                        usage_limit = ?,
                        expire_date = ?
                    WHERE id = ? AND company_id = ?
                ", [$code, $discount, $usageLimit, $expireDate, $couponId, $currentUser['company_id']]);
                
                redirect('/pages/company_admin/coupons.php', 'Kupon başarıyla güncellendi.');
            } else {
                // Yeni ekle
                $newCouponId = generateUUID();
                insert("
                    INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, company_id)
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [$newCouponId, $code, $discount, $usageLimit, $expireDate, $currentUser['company_id']]);
                
                redirect('/pages/company_admin/coupons.php', 'Kupon başarıyla oluşturuldu.');
            }
        } catch (Exception $e) {
            $errors[] = 'Bir hata oluştu: ' . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            $_SESSION['flash_message'] = $error;
            $_SESSION['flash_type'] = 'error';
            break;
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <h2>
            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus'; ?>"></i> 
            <?php echo $isEdit ? 'Kupon Düzenle' : 'Yeni Kupon Oluştur'; ?>
        </h2>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-body">
                <form action="" method="POST" data-validate id="couponForm">
                    <div class="mb-3">
                        <label for="code" class="form-label">Kupon Kodu *</label>
                        <input type="text" class="form-control text-uppercase" id="code" name="code" 
                               value="<?php echo $coupon ? e($coupon['code']) : ''; ?>" 
                               placeholder="ORNEK2024" required 
                               <?php echo $isEdit ? 'readonly' : ''; ?>>
                        <div class="form-text">Büyük harfler ve rakamlar kullanılabilir</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="discount" class="form-label">İndirim Oranı (%) *</label>
                        <input type="number" class="form-control" id="discount" name="discount" 
                               value="<?php echo $coupon ? $coupon['discount'] : ''; ?>" 
                               min="1" max="100" step="0.01" required>
                        <div class="form-text">1 ile 100 arasında bir değer girin</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="usage_limit" class="form-label">Kullanım Limiti</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" 
                               value="<?php echo $coupon ? $coupon['usage_limit'] : ''; ?>" 
                               min="1" placeholder="Boş bırakırsanız sınırsız olur">
                        <div class="form-text">Boş bırakırsanız kullanım sayısı sınırsız olacaktır</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expire_date" class="form-label">Son Kullanma Tarihi</label>
                        <input type="date" class="form-control" id="expire_date" name="expire_date" 
                               value="<?php echo $coupon && $coupon['expire_date'] ? date('Y-m-d', strtotime($coupon['expire_date'])) : ''; ?>">
                        <div class="form-text">Boş bırakırsanız süresiz olacaktır</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Bu kupon sadece kendi firmanızın seferleri için geçerli olacaktır.
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/pages/company_admin/coupons.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Geri
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $isEdit ? 'Güncelle' : 'Oluştur'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

