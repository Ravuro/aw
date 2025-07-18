<?php
// admin/arac_ekle.php - Yeni araç ekleme formu ve dosya yükleme mantığı

require_once __DIR__ . '/includes/header.php';

$error_message = '';
$success_message = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $label = $_POST['vehicle_label'] ?? '';
    $model = $_POST['vehicle_model'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category = $_POST['category'] ?? '';
    $active = isset($_POST['active']) ? 1 : 0;
    $image_path = ''; // Varsayılan olarak resim yolu boş

    // --- Görsel Yükleme Mantığı ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $upload_dir = __DIR__ . '/../uploads/vehicles/';
        
        // Güvenlik: Dosya uzantısını ve MIME türünü kontrol et
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $error_message = 'Geçersiz dosya türü. Lütfen sadece JPG, PNG, WEBP veya GIF formatında bir görsel yükleyin.';
        } else {
            // Benzersiz bir dosya adı oluştur (model_zamanstempeli.uzantı)
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = strtolower($model) . '_' . time() . '.' . $extension;
            $target_path = $upload_dir . $new_filename;

            // Dosyayı belirtilen klasöre taşı
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Veritabanına kaydedilecek yolu oluştur
                $image_path = 'uploads/vehicles/' . $new_filename;
            } else {
                $error_message = 'Dosya yüklenirken bir hata oluştu.';
            }
        }
    }

    // Eğer bir hata oluşmadıysa veritabanına kaydet
    if (empty($error_message)) {
        if (!empty($label) && !empty($model) && is_numeric($price) && !empty($category)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO donation_vehicles (vehicle_label, vehicle_model, price, category, image_url, active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$label, $model, $price, $category, $image_path, $active]);
                
                header("Location: magaza.php?status=success_add");
                exit;
            } catch (PDOException $e) {
                $error_message = "Veritabanı hatası: " . $e->getMessage();
            }
        } else {
            $error_message = "Lütfen tüm zorunlu alanları doğru bir şekilde doldurun.";
        }
    }
}
?>

<div class="admin-page-header">
    <h1 class="admin-page-title">Yeni Araç Ekle</h1>
    <a href="magaza.php" class="btn btn-cancel"><i class="fas fa-arrow-left"></i> Geri Dön</a>
</div>

<?php if (!empty($error_message)): ?>
    <p class="admin-error"><?php echo $error_message; ?></p>
<?php endif; ?>

<!-- DİKKAT: Dosya yüklemek için form etiketine enctype="multipart/form-data" ekledik -->
<form action="arac_ekle.php" method="POST" class="admin-form" enctype="multipart/form-data">
    <div class="form-group">
        <label for="vehicle_label">Araç Adı (Örn: Pegassi Zentorno)</label>
        <input type="text" id="vehicle_label" name="vehicle_label" required>
    </div>
    <div class="form-group">
        <label for="vehicle_model">Model Kodu (Spawn Kodu, Örn: zentorno)</label>
        <input type="text" id="vehicle_model" name="vehicle_model" required>
    </div>
    <div class="form-group">
        <label for="price">Fiyat (₺)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" required>
    </div>
    <div class="form-group">
        <label for="category">Kategori (Örn: Super, Sports, Motosiklet)</label>
        <input type="text" id="category" name="category" required>
    </div>
    <div class="form-group">
        <!-- DİKKAT: Input tipini 'text' yerine 'file' olarak değiştirdik -->
        <label for="image">Görsel Dosyası (Önerilen: 400x225 piksel)</label>
        <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/webp, image/gif">
    </div>
    <div class="form-group form-group-checkbox">
        <input type="checkbox" id="active" name="active" value="1" checked>
        <label for="active">Bu ürün satışta aktif olsun mu?</label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-success">Aracı Ekle</button>
    </div>
</form>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
