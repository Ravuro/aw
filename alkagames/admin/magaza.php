<?php
// admin/magaza.php - Mağaza ürünlerini yönetme sayfası

require_once __DIR__ . '/includes/header.php';

// Mağazadaki tüm araçları veritabanından çekiyoruz.
try {
    $stmt = $pdo->query("SELECT * FROM donation_vehicles ORDER BY category, price ASC");
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<p class="admin-error">Araçlar listelenirken bir hata oluştu: ' . $e->getMessage() . '</p>';
    $vehicles = [];
}
?>

<div class="admin-page-header">
    <h1 class="admin-page-title">Mağaza Yönetimi</h1>
    <a href="arac_ekle.php" class="btn btn-success"><i class="fas fa-plus"></i> Yeni Araç Ekle</a>
</div>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Görsel</th>
                <th>Araç Adı</th>
                <th>Model Kodu</th>
                <th>Fiyat</th>
                <th>Kategori</th>
                <th>Durum</th>
                <th>Eylemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Henüz mağazaya eklenmiş bir ürün bulunmuyor.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td>
                            <?php // DÜZELTME: Resim yolunun başına tam site adresini (SITE_URL) ekliyoruz. ?>
                            <img src="<?php echo SITE_URL . '/' . htmlspecialchars($vehicle['image_url']); ?>" alt="Araç Görseli" class="table-image" onerror="this.src='https://placehold.co/100x50/2c2c2c/f0f0f0?text=Resim+Yok';">
                        </td>
                        <td><?php echo htmlspecialchars($vehicle['vehicle_label']); ?></td>
                        <td><code><?php echo htmlspecialchars($vehicle['vehicle_model']); ?></code></td>
                        <td>₺<?php echo number_format($vehicle['price'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['category']); ?></td>
                        <td>
                            <?php if ($vehicle['active']): ?>
                                <span class="status status-active">Aktif</span>
                            <?php else: ?>
                                <span class="status status-passive">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="arac_duzenle.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                            <a href="arac_sil.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-delete" onclick="return confirm('Bu aracı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');"><i class="fas fa-trash"></i></a>
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
