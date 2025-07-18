<?php
// login.php - Kullanıcıyı Steam'e yönlendirir.

// DÜZELTME: Önce sitenin temel ayarlarını ve sabitlerini içeren config.php dosyasını çağırıyoruz.
require_once __DIR__ . '/config.php';

// Şimdi Steam ile giriş mantığını içeren steam_auth.php dosyasını çağırabiliriz.
require_once __DIR__ . '/includes/steam_auth.php';

// Steam giriş fonksiyonunu çalıştır.
// Bu fonksiyon kullanıcıyı Steam'e yönlendirecek veya
// Steam'den dönen kullanıcıyı doğrulayacaktır.
try {
    handle_steam_login();
} catch (ErrorException $e) {
    // LightOpenID kütüphanesi bulunamazsa hata verir.
    die('Gerekli kütüphane bulunamadı. Lütfen "lightopenid" klasörünün "includes" içinde olduğundan emin olun.');
}
?>
