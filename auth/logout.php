<?php
// auth/logout.php

require_once __DIR__ . '/../config.php';

// Session config.php'de zaten başlatıldı
session_destroy();

header('Location: /index.php?message=logged_out');
exit;
?>

