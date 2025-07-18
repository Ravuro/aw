<?php
// admin/arac_sil.php - Aracı veritabanından ve sunucudan siler

require_once __DIR__ . '/includes/header.php';

// ID parametresi var mı ve sayısal mı kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: magaza.php");
    exit;
}
$vehicle_id = (int)$_GET['id'];

try {
    // Silmeden önce aracın resim yolunu al
    $stmt = $pdo->prepare("SELECT image_url FROM donation_vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();

    if ($vehicle) {
        // Eğer bir resim yolu varsa ve dosya sunucuda mevcutsa, onu sil
        $image_path = $vehicle['image_url'];
        if (!empty($image_path) && file_exists(__DIR__ . '/../' . $image_path)) {
            unlink(__DIR__ . '/../' . $image_path);
        }
    }

    // Aracı veritabanından sil
    $delete_stmt = $pdo->prepare("DELETE FROM donation_vehicles WHERE id = ?");
    $delete_stmt->execute([$vehicle_id]);

    header("Location: magaza.php?status=success_delete");
    exit;

} catch (PDOException $e) {
    die("Silme işlemi sırasında bir hata oluştu: " . $e->getMessage());
}
?>
