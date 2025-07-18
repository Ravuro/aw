<?php
// admin/bagis_yonetimi.php - Tüm bağışları listeleme ve yönetme sayfası

require_once __DIR__ . '/includes/header.php';

// Arama ve filtreleme için başlangıç değerleri
$search_term = $_GET['search'] ?? '';
$where_clause = '';
$params = [];

// Eğer bir arama terimi varsa, SQL sorgusunu ona göre hazırla
if (!empty($search_term)) {
    // players tablosundan name veya donations tablosundan citizenid/steam_id ile arama yap
    $where_clause = "WHERE p.name LIKE ? OR d.citizenid LIKE ? OR d.steam_id LIKE ?";
    $params = ["%$search_term%", "%$search_term%", "%$search_term%"];
}

// Tüm bağışları veritabanından çekiyoruz. Oyuncu adını alabilmek için 'players' tablosu ile birleştiriyoruz (JOIN).
try {
    // DÜZELTME: SQL JOIN sorgusu, doğru sütunları ve ID formatlarını eşleştirecek şekilde güncellendi.
    // 'donations' tablosundaki sayısal steam_id'yi HEX formatına çevirip 'players' tablosundaki 'steam' sütunu ile karşılaştırıyoruz.
    $sql = "SELECT d.*, p.name as player_name 
            FROM donations d
            LEFT JOIN players p ON p.steam = CONCAT('steam:', LOWER(HEX(d.steam_id)))
            $where_clause
            ORDER BY d.purchased_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $donations = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<p class="admin-error">Bağışlar listelenirken bir hata oluştu: ' . $e->getMessage() . '</p>';
    $donations = [];
}
?>

<div class="admin-page-header">
    <h1 class="admin-page-title">Bağış Yönetimi</h1>
    <!-- Manuel bağış ekleme butonu ileride buraya eklenebilir -->
</div>

<!-- Arama Formu -->
<div class="admin-filters">
    <form action="bagis_yonetimi.php" method="GET">
        <input type="text" name="search" placeholder="Oyuncu Adı, CitizenID veya SteamID ile Ara..." value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit" class="btn btn-edit"><i class="fas fa-search"></i> Ara</button>
    </form>
</div>


<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Oyuncu Adı</th>
                <th>Ürün Adı</th>
                <th>Tutar</th>
                <th>Satın Alma Tarihi</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($donations)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Gösterilecek bağış kaydı bulunamadı.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($donations as $donation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($donation['player_name'] ?? $donation['citizenid']); ?></td>
                        <td><?php echo htmlspecialchars($donation['vehicle_label']); ?></td>
                        <td>₺<?php echo number_format($donation['price'], 2, ',', '.'); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($donation['purchased_at'])); ?></td>
                        <td>
                            <?php if ($donation['delivered']): ?>
                                <span class="status status-delivered"><i class="fas fa-check-circle"></i> Teslim Edildi</span>
                            <?php else: ?>
                                <span class="status status-pending"><i class="fas fa-clock"></i> Bekliyor</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
