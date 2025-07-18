<?php
// magaza.php - Ürünlerin listelendiği ve satın alındığı sayfa.

require_once __DIR__ . '/includes/header.php';

// Veritabanından satıştaki araçları çekiyoruz.
try {
    $stmt = $pdo->prepare("SELECT id, vehicle_model, vehicle_label, price, category, image_url FROM donation_vehicles WHERE active = 1 ORDER BY category, price");
    $stmt->execute();
    $vehicles = $stmt->fetchAll();

    // Araçları kategoriye göre grupla
    $categorized_vehicles = [];
    foreach ($vehicles as $vehicle) {
        $categorized_vehicles[$vehicle['category']][] = $vehicle;
    }

} catch (PDOException $e) {
    // Hata olursa ekrana yazdır.
    echo '<div class="container"><p style="color:red;">Mağaza yüklenirken bir hata oluştu: ' . $e->getMessage() . '</p></div>';
    $categorized_vehicles = [];
}
?>

<div class="container">
    <h1 class="page-title">Mağaza</h1>
    <p style="text-align: center; color: var(--text-muted); margin-bottom: 3rem;">
        Sunucumuza destek olmak için aşağıdaki paketleri satın alabilirsiniz.
    </p>

    <?php if (empty($categorized_vehicles)): ?>
        <p style="text-align: center;">Şu anda satışta olan bir ürün bulunmamaktadır.</p>
    <?php else: ?>
        <?php foreach ($categorized_vehicles as $category => $items): ?>
            <div class="category-section">
                <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
                <div class="store-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="vehicle-card">
                            <div class="vehicle-image-container">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['vehicle_label']); ?>" class="vehicle-image" onerror="this.src='https://placehold.co/400x225/1a1a1a/f0f0f0?text=Resim+Yok';">
                            </div>
                            <div class="vehicle-details">
                                <h3 class="vehicle-name"><?php echo htmlspecialchars($item['vehicle_label']); ?></h3>
                                <p class="vehicle-price">₺<?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                                <button class="btn btn-buy" data-id="<?php echo $item['id']; ?>">Satın Al</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
