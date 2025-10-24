<?php
$pageTitle = "Giriş Yap";
require_once __DIR__ . '/../includes/header.php';

// Zaten giriş yapmışsa yönlendir
if (isLoggedIn()) {
    redirect('/index.php');
}

$redirect = input('redirect', '/index.php');
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
            <div class="card-header text-center">
                <h3><i class="fas fa-sign-in-alt"></i> Giriş Yap</h3>
            </div>
            <div class="card-body p-4">
                <form action="/auth/login_process.php" method="POST" data-validate id="loginForm">
                    <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               placeholder="ornek@email.com" autocomplete="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Şifreniz" autocomplete="current-password">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </button>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Hesabınız yok mu? 
                            <a href="/pages/register.php">Kayıt Olun</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Demo Hesaplar Bilgisi -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle"></i> Demo Hesaplar</h6>
                <small class="text-muted">
                    <strong>Admin:</strong> admin@busticket.com / admin123<br>
                    <strong>Kullanıcı:</strong> user@test.com / user123<br>
                    <strong>Firma Admin:</strong> admin1@metroturizm.com / admin123
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

