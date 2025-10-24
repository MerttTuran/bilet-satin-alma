<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Session config.php'de zaten başlatıldı
$currentUser = getCurrentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle ?? APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/index.php">
                <i class="fas fa-bus"></i> <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php"><i class="fas fa-home"></i> Ana Sayfa</a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole(ROLE_ADMIN)): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/pages/admin/dashboard.php">
                                    <i class="fas fa-shield-alt"></i> Admin Panel
                                </a>
                            </li>
                        <?php elseif (hasRole(ROLE_COMPANY_ADMIN)): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/pages/company_admin/dashboard.php">
                                    <i class="fas fa-briefcase"></i> Firma Panel
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/pages/my_tickets.php">
                                    <i class="fas fa-ticket-alt"></i> Biletlerim
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo e($currentUser['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <span class="dropdown-item-text">
                                        <small>Bakiye: <strong><?php echo formatMoney($currentUser['balance']); ?></strong></small>
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/pages/profile.php"><i class="fas fa-user-circle"></i> Profilim</a></li>
                                <li><a class="dropdown-item" href="/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/login.php"><i class="fas fa-sign-in-alt"></i> Giriş Yap</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/register.php"><i class="fas fa-user-plus"></i> Kayıt Ol</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo e($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <main class="container my-4">

