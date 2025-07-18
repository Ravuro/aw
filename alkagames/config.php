<?php
// config.php - Sitenin temel ayarlarını ve hassas bilgileri burada saklıyoruz.

// PHP Session'ı başlatıyoruz. Bu, kullanıcı giriş bilgilerini saklamak için gerekli.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata Raporlama (Sadece geliştirme aşamasında 'E_ALL' olmalı)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- VERİTABANI BAĞLANTI BİLGİLERİ ---
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'emygdatabase');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- STEAM API AYARLARI ---
define('STEAM_API_KEY', '28217FC4B1D32281E3FA02A800E8C937');

// --- Site URL Ayarı ---
define('SITE_URL', 'http://25.45.80.131/alkagames/'); // KENDİ IP ADRESİNLE DEĞİŞTİR

// --- YENİ: FIVEM SUNUCU AYARLARI ---
define('SERVER_IP', '25.45.80.131'); // Sunucunun IP adresi
define('SERVER_PORT', '30120');      // Sunucunun OYUN portu

// Veritabanı Bağlantısı (PDO)
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı kurulamadı: " . $e->getMessage());
}
?>
