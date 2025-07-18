<?php
// includes/header.php
require_once __DIR__ . '/../config.php';

// Kullanıcının admin olup olmadığını kontrol et.
$is_admin = false;
if (isset($_SESSION['steam_loggedin']) && $_SESSION['steam_loggedin'] === true) {
    try {
        // players tablosunda steam ID'sini arıyoruz.
        $stmt = $pdo->prepare("SELECT role FROM players WHERE steam = ?");
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alka Games Roleplay</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Ikonları -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Kendi CSS Dosyamız -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <nav class="container">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                ALKA <span>GAMES</span>
            </a>
            <ul class="nav-links">
                <li><a href="<?php echo SITE_URL; ?>/index.php">Anasayfa</a></li>
                <li><a href="#">Başvuru</a></li>
                <li><a href="<?php echo SITE_URL; ?>/duyurular.php">Duyurular</a></li>
                <li><a href="<?php echo SITE_URL; ?>/magaza.php">Mağaza</a></li>
            </ul>
            <div class="profile-area">
                <?php if (isset($_SESSION['steam_loggedin']) && $_SESSION['steam_loggedin'] === true): ?>
                    <!-- Kullanıcı giriş yapmışsa -->
                    <div class="profile-dropdown">
                        <a href="<?php echo SITE_URL; ?>/profil.php" class="profile-info">
                            <?php // Eğer avatar boş gelirse varsayılan bir resim kullan (?? operatörü ile) ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['steam_avatar'] ?? 'https://placehold.co/40x40/ff6600/FFFFFF?text=A'); ?>" alt="Avatar">
                            
                            <?php // Eğer kullanıcı adı boş gelirse 'Kullanıcı' yaz (?? operatörü ile) ?>
                            <span><?php echo htmlspecialchars($_SESSION['steam_personaname'] ?? 'Kullanıcı'); ?></span>
                        </a>
                        <div class="dropdown-content">
                            <a href="<?php echo SITE_URL; ?>/profil.php">Profilim</a>
                            <?php if ($is_admin): // Eğer kullanıcı admin ise bu linki göster ?>
                                <a href="<?php echo SITE_URL; ?>/admin/index.php">Admin Paneli</a>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL; ?>/logout.php">Çıkış Yap</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Kullanıcı giriş yapmamışsa -->
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-steam">
                        <i class="fab fa-steam"></i> Steam ile Giriş Yap
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>
