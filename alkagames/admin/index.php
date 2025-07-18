<?php
// admin/index.php - Admin panelinin ana sayfası (Dashboard)

require_once __DIR__ . '/includes/header.php';

// --- FONKSİYON: Aktif oyuncu sayısını çekme ---
function get_active_players($ip, $port) {
    // @ işareti, sunucu kapalıysa veya yanıt vermiyorsa oluşacak PHP uyarılarını bastırır.
    $json_url = "http://{$ip}:{$port}/players.json";
    // Sunucuya bağlanmak için 2 saniyelik bir zaman aşımı süresi belirliyoruz.
    $context = stream_context_create(['http' => ['timeout' => 2]]);
    $json_data = @file_get_contents($json_url, false, $context);

    // Eğer veri alınamazsa (sunucu kapalıysa veya yanıt vermiyorsa)
    if ($json_data === FALSE) {
        return '<span style="font-size: 1.2rem; color: #e74c3c;">Çevrimdışı</span>';
    }

    $players = json_decode($json_data, true);

    // Gelen veri bir dizi (array) ise, eleman sayısını (oyuncu sayısını) döndür.
    if (is_array($players)) {
        return count($players);
    }

    // Eğer JSON verisi bozuksa veya okunamadıysa
    return '<span style="font-size: 1.2rem; color: #f39c12;">Hata</span>';
}


// --- İstatistikleri Veritabanından ve Sunucudan Çekme ---
try {
    // Toplam oyuncu sayısı
    $total_players = $pdo->query("SELECT COUNT(*) FROM players")->fetchColumn();

    // Toplam bağış geliri
    $total_revenue = $pdo->query("SELECT SUM(price) FROM donations")->fetchColumn();

    // Toplam satış adedi
    $total_sales = $pdo->query("SELECT COUNT(*) FROM donations")->fetchColumn();

    // Son 5 bağış
    // DÜZELTME: Sorgu, artık 'donations' ve 'players' tablolarını 'citizenid' üzerinden birleştiriyor.
    $recent_donations_stmt = $pdo->query("
        SELECT 
            d.vehicle_label, 
            d.price, 
            CONCAT(
                JSON_UNQUOTE(JSON_EXTRACT(p.charinfo, '$.firstname')), 
                ' ', 
                JSON_UNQUOTE(JSON_EXTRACT(p.charinfo, '$.lastname'))
            ) as player_name
        FROM donations d
        LEFT JOIN players p ON d.citizenid = p.citizenid
        ORDER BY d.purchased_at DESC
        LIMIT 5
    ");
    $recent_donations = $recent_donations_stmt->fetchAll();

    // Aktif oyuncu sayısını al
    $active_players = get_active_players(SERVER_IP, SERVER_PORT);

} catch (PDOException $e) {
    // Hata durumunda varsayılan değerler ata
    $total_players = 0;
    $total_revenue = 0;
    $total_sales = 0;
    $active_players = 'N/A';
    $recent_donations = [];
    echo '<p class="admin-error">İstatistikler yüklenirken bir hata oluştu: ' . $e->getMessage() . '</p>';
}

?>

<h1 class="admin-page-title">Yönetim Paneli</h1>

<!-- İstatistik Kartları -->
<div class="dashboard-stats">
    <!-- Aktif Oyuncu Kartı -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(155, 89, 182, 0.2); color: #9b59b6;">
            <i class="fas fa-gamepad"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Aktif Oyuncu</span>
            <span class="stat-value"><?php echo $active_players; ?></span>
        </div>
    </div>
    <!-- Toplam Oyuncu Kartı -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(52, 152, 219, 0.2); color: #3498db;">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Toplam Oyuncu</span>
            <span class="stat-value"><?php echo number_format($total_players); ?></span>
        </div>
    </div>
    <!-- Toplam Gelir Kartı -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(46, 204, 113, 0.2); color: #2ecc71;">
            <i class="fas fa-lira-sign"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Toplam Gelir</span>
            <span class="stat-value">₺<?php echo number_format($total_revenue ?? 0, 2, ',', '.'); ?></span>
        </div>
    </div>
    <!-- Toplam Satış Kartı -->
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(243, 156, 18, 0.2); color: #f39c12;">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Toplam Satış</span>
            <span class="stat-value"><?php echo number_format($total_sales); ?></span>
        </div>
    </div>
</div>

<!-- Son Aktiviteler -->
<div class="recent-activity">
    <h2 class="section-title">Son Bağışlar</h2>
    <?php if (empty($recent_donations)): ?>
        <p>Henüz bir bağış yapılmamış.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($recent_donations as $donation): ?>
                <li>
                    <span class="activity-user"><?php echo htmlspecialchars($donation['player_name'] && trim($donation['player_name']) !== '' ? $donation['player_name'] : 'Bilinmeyen Oyuncu'); ?></span>
                    <span class="activity-action">adlı kullanıcı</span>
                    <span class="activity-item"><?php echo htmlspecialchars($donation['vehicle_label']); ?></span>
                    <span class="activity-action">paketini</span>
                    <span class="activity-price">₺<?php echo number_format($donation['price'], 2, ',', '.'); ?></span>
                    <span class="activity-action">karşılığında satın aldı.</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>
