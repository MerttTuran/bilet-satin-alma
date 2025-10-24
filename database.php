<?php
// database.php - Veritabanı Bağlantısı ve Yardımcı Fonksiyonlar

require_once 'config.php';

/**
 * Veritabanı bağlantısı al
 */
function getDB() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    } catch (PDOException $e) {
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
}

/**
 * Veritabanı sorgusunu çalıştır
 */
function query($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Tek bir kayıt getir
 */
function fetchOne($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetch();
}

/**
 * Tüm kayıtları getir
 */
function fetchAll($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * INSERT işlemi yap ve son eklenen ID'yi döndür
 */
function insert($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $db->lastInsertId();
}

/**
 * UPDATE/DELETE işlemi yap ve etkilenen satır sayısını döndür
 */
function execute($sql, $params = []) {
    $stmt = query($sql, $params);
    return $stmt->rowCount();
}

/**
 * UUID oluştur
 */
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Transaction başlat
 */
function beginTransaction() {
    $db = getDB();
    $db->beginTransaction();
    return $db;
}
?>

