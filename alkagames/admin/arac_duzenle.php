<?php
// admin/arac_duzenle.php - Mevcut aracı düzenleme formu

require_once __DIR__ . '/includes/header.php';

// ID parametresi var mı ve sayısal mı kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: magaza.php");
    exit;
}
$vehicle_id = (int)$_GET['id'];

// Düzenlenecek aracı veritabanından çek
try {
    $stmt = $pdo->prepare("SELECT * FROM donation_vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();
    if (!$vehicle) {
        // Araç bulunamazsa ana sayfaya yönlendir
        header("Location: magaza.php");
        exit;
    }
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

$error_message = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $label = $_POST['vehicle_label'] ?? '';
    $model = $_POST['vehicle_model'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category = $_POST['category'] ?? '';
    $active = isset($_POST['active']) ? 1 : 0;
    $image_path = $vehicle['image_url']; // Varsayılan olarak mevcut resmi koru

    // Yeni bir görsel yüklendi mi?
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $upload_dir = __DIR__ . '/../uploads/vehicles/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (in_array($file_type, $allowed_types)) {
            // Eğer varsa, eski resmi sunucudan sil
            if (!empty($image_path) && file_exists(__DIR__ . '/../' . $image_path)) {
                unlink(__DIR__ . '/../' . $image_path);
            }
            // Yeni resmi kaydet
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = strtolower($model) . '_' . time() . '.' . $extension;
            $target_path = $upload_dir . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $image_path = 'uploads/vehicles/' . $new_filename;
            }
        } else {
            $error_message = 'Geçersiz dosya türü. Lütfen sadece JPG, PNG, WEBP veya GIF formatında bir görsel yükleyin.';
        }
    }

    // Hata yoksa veritabanını güncelle
    if (empty($error_message)) {
        try {
            $update_stmt = $pdo->prepare("UPDATE donation_vehicles SET vehicle_label = ?, vehicle_model = ?, price = ?, category = ?, image_url = ?, active = ? WHERE id = ?");
            $update_stmt->execute([$label, $model, $price, $category, $image_path, $active, $vehicle_id]);
            header("Location: magaza.php?status=success_edit");
            exit;
        } catch (PDOException $e) {
            $error_message = "Güncelleme sırasında hata: " . $e->getMessage();
        }
    }
}
?>

<div class="admin-page-header">
    <h1 class="admin-page-title">Aracı Düzenle</h1>
    <a href="magaza.php" class="btn btn-cancel"><i class="fas fa-arrow-left"></i> Geri Dön</a>
</div>

<?php if (!empty($error_message)): ?>
    <p class="admin-error"><?php echo $error_message; ?></p>
<?php endif; ?>

<form action="arac_duzenle.php?id=<?php echo $vehicle_id; ?>" method="POST" class="admin-form" enctype="multipart/form-data">
    <div class="form-group">
        <label for="vehicle_label">Araç Adı</label>
        <input type="text" id="vehicle_label" name="vehicle_label" value="<?php echo htmlspecialchars($vehicle['vehicle_label']); ?>" required>
    </div>
    <div class="form-group">
        <label for="vehicle_model">Model Kodu</label>
        <input type="text" id="vehicle_model" name="vehicle_model" value="<?php echo htmlspecialchars($vehicle['vehicle_model']); ?>" required>
    </div>
    <div class="form-group">
        <label for="price">Fiyat (₺)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($vehicle['price']); ?>" required>
    </div>
    <div class="form-group">
        <label for="category">Kategori</label>
        <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($vehicle['category']); ?>" required>
    </div>
    <div class="form-group">
        <label>Mevcut Görsel</label>
        <div>
            <img src="<?php echo SITE_URL . '/' . htmlspecialchars($vehicle['image_url']); ?>" alt="Mevcut Görsel" class="table-image" onerror="this.src='https://placehold.co/100x50/2c2c2c/f0f0f0?text=Resim+Yok'; this.style.border='1px solid #444';">
        </div>
    </div>
    <div class="form-group">
        <label for="image">Görseli Değiştir (Yeni dosya seçilmezse mevcut görsel korunur)</label>
        <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/webp, image/gif">
    </div>
    <div class="form-group form-group-checkbox">
        <input type="checkbox" id="active" name="active" value="1" <?php echo $vehicle['active'] ? 'checked' : ''; ?>>
        <label for="active">Bu ürün satışta aktif olsun mu?</label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-success">Değişiklikleri Kaydet</button>
    </div>
</form>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
