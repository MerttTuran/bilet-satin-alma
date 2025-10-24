<?php
$pageTitle = "Firma Ekle/Düzenle";
require_once __DIR__ . '/../../includes/header.php';

requireRole(ROLE_ADMIN);

$companyId = input('id');
$isEdit = !empty($companyId);

// Düzenleme modunda firmayı getir
$company = null;
if ($isEdit) {
    $company = fetchOne("SELECT * FROM Bus_Company WHERE id = ?", [$companyId]);
    if (!$company) {
        redirect('/pages/admin/companies.php', 'Firma bulunamadı.', 'error');
    }
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(input('name'));
    
    // Validasyon
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Firma adı boş olamaz';
    }
    
    // Firma adı kontrolü (düzenleme dışında)
    if (!$isEdit || ($isEdit && $company['name'] !== $name)) {
        $existingCompany = fetchOne("SELECT id FROM Bus_Company WHERE name = ?", [$name]);
        if ($existingCompany) {
            $errors[] = 'Bu firma adı zaten kullanılıyor';
        }
    }
    
    // Logo yükleme
    $logoPath = $company['logo_path'] ?? null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['logo'], LOGO_PATH, ['jpg', 'jpeg', 'png', 'gif', 'svg']);
        if ($uploadResult['success']) {
            // Eski logoyu sil
            if ($logoPath && file_exists(LOGO_PATH . $logoPath)) {
                unlink(LOGO_PATH . $logoPath);
            }
            $logoPath = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    if (empty($errors)) {
        try {
            if ($isEdit) {
                // Güncelle
                execute("UPDATE Bus_Company SET name = ?, logo_path = ? WHERE id = ?", 
                       [$name, $logoPath, $companyId]);
                redirect('/pages/admin/companies.php', 'Firma başarıyla güncellendi.');
            } else {
                // Yeni ekle
                $newCompanyId = generateUUID();
                insert("INSERT INTO Bus_Company (id, name, logo_path) VALUES (?, ?, ?)", 
                      [$newCompanyId, $name, $logoPath]);
                redirect('/pages/admin/companies.php', 'Firma başarıyla eklendi.');
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
            <?php echo $isEdit ? 'Firma Düzenle' : 'Yeni Firma Ekle'; ?>
        </h2>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data" data-validate id="companyForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Firma Adı *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $company ? e($company['name']) : ''; ?>" 
                               required placeholder="Örn: Metro Turizm">
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Firma Logosu</label>
                        <?php if ($company && $company['logo_path']): ?>
                            <div class="mb-2">
                                <img src="/uploads/logos/<?php echo e($company['logo_path']); ?>" 
                                     alt="Mevcut logo" style="max-height: 100px;" class="img-thumbnail">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <div class="form-text">JPG, PNG, GIF veya SVG formatında yükleyebilirsiniz</div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/pages/admin/companies.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Geri
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $isEdit ? 'Güncelle' : 'Kaydet'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

