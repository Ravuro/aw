<?php
// includes/steam_auth.php - Steam OpenID ile giriş yapma mantığını içerir.

// LightOpenID kütüphanesini dahil ediyoruz.
require_once __DIR__ . '/lightopenid/openid.php';

// Steam'den kullanıcı bilgilerini çekmek için daha güvenilir cURL fonksiyonu
function fetch_steam_user_info($steam_id_64) {
    // API URL'sini oluşturuyoruz.
    $api_url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . STEAM_API_KEY . "&steamids=" . $steam_id_64;

    // cURL session'ı başlat
    $ch = curl_init();

    // cURL seçeneklerini ayarla
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Sonucun string olarak dönmesini sağla
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Zaman aşımı süresi (10 saniye)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL sertifika kontrolünü devre dışı bırak (yerel testler için gerekebilir)

    // İsteği çalıştır ve sonucu al
    $response = curl_exec($ch);

    // cURL session'ı kapat
    curl_close($ch);

    // Gelen JSON verisini PHP dizisine çevir
    $json = json_decode($response, true);

    // Verinin doğru gelip gelmediğini kontrol et
    if (isset($json['response']['players'][0])) {
        return $json['response']['players'][0];
    }

    return null; // Eğer veri alınamazsa null döndür
}


function handle_steam_login() {
    try {
        $openid = new LightOpenID(SITE_URL);

        if (!$openid->mode) {
            // Kullanıcı henüz giriş yapmamış, Steam'e yönlendiriyoruz.
            $openid->identity = 'https://steamcommunity.com/openid';
            header('Location: ' . $openid->authUrl());
            exit;
        } elseif ($openid->mode == 'cancel') {
            // Kullanıcı girişi iptal etti.
            echo 'Kullanıcı girişi iptal etti.';
            exit;
        } else {
            // Kullanıcı Steam'den geri döndü, bilgileri doğruluyoruz.
            if ($openid->validate()) {
                preg_match('/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25})$/', $openid->identity, $matches);
                
                if (!isset($matches[1])) {
                    die("Steam ID alınamadı.");
                }
                
                $steam_id_64 = $matches[1];

                // Yeni cURL fonksiyonumuzla kullanıcı bilgilerini çekiyoruz.
                $player = fetch_steam_user_info($steam_id_64);

                if ($player) {
                    // Gelen bilgileri session'a kaydediyoruz.
                    $_SESSION['steam_loggedin'] = true;
                    $_SESSION['steam_steamid'] = $player['steamid'];
                    $_SESSION['steam_personaname'] = $player['personaname'];
                    $_SESSION['steam_avatar'] = $player['avatarfull'];
                    $_SESSION['steam_profileurl'] = $player['profileurl'];

                    // Kullanıcıyı anasayfaya yönlendir.
                    header('Location: ' . SITE_URL . '/index.php');
                    exit;
                } else {
                    die("Steam API'sinden kullanıcı bilgileri alınamadı. Lütfen API anahtarınızın doğru olduğundan emin olun.");
                }

            } else {
                echo "Giriş başarısız. Lütfen tekrar deneyin.";
                exit;
            }
        }
    } catch (ErrorException $e) {
        die('Gerekli kütüphane bulunamadı. Lütfen "lightopenid" klasörünün "includes" içinde olduğundan emin olun.');
    }
}
