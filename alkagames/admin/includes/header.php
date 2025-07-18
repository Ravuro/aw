<?php
// admin/includes/header.php - Admin paneli için özel header

// Ana config dosyasını çağırarak veritabanı ve session'ı başlatıyoruz.
require_once __DIR__ . '/../../config.php';

// Güvenlik Kontrolü: Kullanıcının admin olup olmadığını kontrol et.
$is_admin = false;
if (isset($_SESSION['steam_loggedin']) && $_SESSION['steam_loggedin'] === true) {
    try {
        // 'steam_id' yerine doğru sütun adı olan 'steam' kullanılıyor.
        $stmt = $pdo->prepare("SELECT role FROM players WHERE steam = ?");
        // Session'daki sayısal ID'yi, veritabanındaki format olan hex'e çeviriyoruz.
        $stmt->execute(['steam:' . dechex($_SESSION['steam_steamid'])]);
        $user = $stmt->fetch();
        if ($user && $user['role'] === 'admin') {
            $is_admin = true;
        }
    } catch (PDOException $e) {
        // Hata durumunda admin yetkisi verme.
        $is_admin = false;
    }
}

// Eğer kullanıcı admin değilse, anasayfaya yönlendir ve işlemi durdur.
if (!$is_admin) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Alka Games</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>/admin/index.php">ALKA <span>GAMES | ADMİN</span></a>
            </div>
            <nav class="admin-nav">
                <a href="<?php echo SITE_URL; ?>/admin/index.php">Ana Panel</a>
                <a href="<?php echo SITE_URL; ?>/admin/magaza.php">Mağaza Yönetimi</a>
                <a href="<?php echo SITE_URL; ?>/admin/bagis_yonetimi.php">Bağış Yönetimi</a>
                <a href="<?php echo SITE_URL; ?>/admin/duyurular.php">Duyurular</a>
                <a href="<?php echo SITE_URL; ?>" target="_blank">Siteyi Görüntüle <i class="fas fa-external-link-alt"></i></a>
            </nav>
        </div>
    </header>
    <div class="admin-main-content">
        <div class="container">
