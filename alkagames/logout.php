<?php
// logout.php - Kullanıcı oturumunu sonlandırır.

// config.php'yi çağırarak session_start() fonksiyonunun çalışmasını sağlıyoruz.
require_once __DIR__ . '/config.php';

// Tüm session verilerini temizle.
$_SESSION = array();

// Session'ı yok et.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Kullanıcıyı anasayfaya yönlendir.
header('Location: ' . SITE_URL . '/index.php');
exit;
?>
