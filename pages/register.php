<?php
$pageTitle = "Kayıt Ol";
require_once __DIR__ . '/../includes/header.php';

// Zaten giriş yapmışsa yönlendir
if (isLoggedIn()) {
    redirect('/index.php');
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
            <div class="card-header text-center">
                <h3><i class="fas fa-user-plus"></i> Kayıt Ol</h3>
            </div>
            <div class="card-body p-4">
                <form action="/auth/register_process.php" method="POST" data-validate id="registerForm">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required 
                               placeholder="Ad Soyad" autocomplete="name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               placeholder="ornek@email.com" autocomplete="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="En az 6 karakter" autocomplete="new-password" minlength="6">
                        <div class="form-text">Şifre en az 6 karakter olmalıdır.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Şifre Tekrar</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required 
                               placeholder="Şifrenizi tekrar girin" autocomplete="new-password" minlength="6">
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-gift"></i> 
                            Kayıt olurken <strong><?php echo formatMoney(DEFAULT_BALANCE); ?></strong> hoşgeldin kredisi kazanın!
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Kayıt Ol
                        </button>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Zaten hesabınız var mı? 
                            <a href="/pages/login.php">Giriş Yapın</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Şifre eşleşme kontrolü
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (password !== passwordConfirm) {
        e.preventDefault();
        alert('Şifreler eşleşmiyor!');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

