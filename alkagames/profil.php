<?php
// profil.php - Giriş yapmış kullanıcının bilgilerini gösterir.

require_once __DIR__ . '/includes/header.php';

// Güvenlik: Eğer kullanıcı giriş yapmamışsa, onu anasayfaya yönlendir.
if (!isset($_SESSION['steam_loggedin']) || $_SESSION['steam_loggedin'] !== true) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Session'dan kullanıcı bilgilerini alırken ?? operatörü ile boş gelme ihtimaline karşı varsayılan değerler atıyoruz.
$steam_id = $_SESSION['steam_steamid'] ?? '0';
$username = $_SESSION['steam_personaname'] ?? 'Bilinmeyen Kullanıcı';
$avatar = $_SESSION['steam_avatar'] ?? 'https://placehold.co/120x120/ff6600/FFFFFF?text=A';
$profile_url = $_SESSION['steam_profileurl'] ?? '#';

// SteamID'yi Hex formatına çevirme (FiveM için gerekli olabilir)
function steamid_to_hex($steam_id) {
    if (!is_numeric($steam_id)) {
        return '0';
    }
    return dechex((int)$steam_id);
}
$steam_hex = 'steam:' . steamid_to_hex($steam_id);


// YENİ: Kullanıcının satın alım geçmişini veritabanından çekiyoruz.
$purchase_history = [];
try {
    $stmt = $pdo->prepare("SELECT vehicle_label, price, purchased_at, delivered FROM donations WHERE steam_id = ? ORDER BY purchased_at DESC");
    $stmt->execute([$steam_id]);
    $purchase_history = $stmt->fetchAll();
} catch (PDOException $e) {
    // Hata olursa, boş bir dizi olarak bırak ve hatayı logla (opsiyonel)
    // error_log("Satın alım geçmişi çekilemedi: " . $e->getMessage());
}

?>

<div class="container">
    <h1 class="page-title"><?php echo htmlspecialchars($username); ?> Adlı Kullanıcının Profili</h1>
    
    <div class="profile-container">
        <div class="profile-card">
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profil Fotoğrafı" class="profile-avatar">
            <h2><?php echo htmlspecialchars($username); ?></h2>
            <p><strong>Steam ID64:</strong> <?php echo htmlspecialchars($steam_id); ?></p>
            <p><strong>FiveM Hex ID:</strong> <?php echo htmlspecialchars($steam_hex); ?></p>
            <a href="<?php echo htmlspecialchars($profile_url); ?>" target="_blank" class="btn btn-steam" style="margin-top: 1rem; display: inline-block;">
                Steam Profilini Görüntüle
            </a>
        </div>
        <div class="profile-content">
            <h3>Satın Alım Geçmişi</h3>
            
            <?php if (empty($purchase_history)): ?>
                <p>Daha önce hiç satın alım yapmamışsınız.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="purchase-history-table">
                        <thead>
                            <tr>
                                <th>Ürün Adı</th>
                                <th>Tutar</th>
                                <th>Tarih</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchase_history as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['vehicle_label']); ?></td>
                                    <td>₺<?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($item['purchased_at'])); ?></td>
                                    <td>
                                        <?php if ($item['delivered']): ?>
                                            <span class="status status-delivered"><i class="fas fa-check-circle"></i> Teslim Edildi</span>
                                        <?php else: ?>
                                            <span class="status status-pending"><i class="fas fa-clock"></i> Bekliyor</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
