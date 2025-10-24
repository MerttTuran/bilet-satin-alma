<?php
// config.php - Genel Yapılandırma Dosyası

// Session'ı en başta başlat (output'tan önce)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Output buffering başlat
ob_start();

// Hata raporlamayı aç (geliştirme aşamasında)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zaman dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

// Veritabanı yapılandırması
define('DB_PATH', __DIR__ . '/database/bus_tickets.db');

// Session yapılandırması
define('SESSION_LIFETIME', 3600 * 24); // 24 saat

// Uygulama ayarları
define('APP_NAME', 'Otobüs Bilet Satış Platformu');
define('BASE_URL', 'http://localhost:8080');

// Upload klasörü ayarları
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('LOGO_PATH', UPLOAD_PATH . 'logos/');

// Kullanıcı rolleri
define('ROLE_USER', 'user');
define('ROLE_COMPANY_ADMIN', 'company.admin');
define('ROLE_ADMIN', 'admin');

// Varsayılan kredi miktarı (yeni kullanıcılar için)
define('DEFAULT_BALANCE', 1000);

// Bilet iptal süresi (dakika olarak)
define('CANCEL_TIME_LIMIT', 60); // 60 dakika = 1 saat

// PDF ayarları
define('PDF_TEMP_PATH', __DIR__ . '/temp/');

// Upload klasörlerini oluştur
if (!file_exists(LOGO_PATH)) {
    mkdir(LOGO_PATH, 0777, true);
}

if (!file_exists(PDF_TEMP_PATH)) {
    mkdir(PDF_TEMP_PATH, 0777, true);
}

// Database klasörünü oluştur
if (!file_exists(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0777, true);
}
?>

