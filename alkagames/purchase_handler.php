<?php
// purchase_handler.php - Mağazadan gelen satın alma isteklerini işler.

require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

// --- Güvenlik Kontrolleri ---
if (!isset($_SESSION['steam_loggedin']) || $_SESSION['steam_loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Bu işlemi yapmak için önce Steam ile giriş yapmalısınız.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu.']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['vehicle_id']) || !is_numeric($data['vehicle_id'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz araç kimliği.']);
    exit;
}
$vehicle_id = (int)$data['vehicle_id'];

// --- Veritabanı İşlemleri ---
try {
    // 1. Satın alınmak istenen aracın bilgilerini doğrula.
    $stmt = $pdo->prepare("SELECT vehicle_model, vehicle_label, price FROM donation_vehicles WHERE id = ? AND active = 1");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        echo json_encode(['success' => false, 'message' => 'Bu ürün artık mevcut değil veya geçersiz.']);
        exit;
    }

    // 2. Session'dan sayısal Steam64 ID'yi al.
    $steam_id_64 = $_SESSION['steam_steamid'];
    
    // 3. Oyuncunun gerçek CitizenID'sini bul.
    $hex_id = 'steam:' . dechex($steam_id_64);
    
    // DÜZELTME: Veritabanındaki 'steam' sütununu TRIM() fonksiyonu ile olası boşluklardan temizliyoruz.
    // Bu, gizli boşluk karakterleri nedeniyle oluşabilecek eşleşme hatalarını önler.
    $player_stmt = $pdo->prepare("SELECT citizenid FROM players WHERE TRIM(steam) = ?");
    $player_stmt->execute([$hex_id]);
    $player = $player_stmt->fetch();

    // Eğer oyuncu veritabanında bulunamazsa işlemi durdur.
    if (!$player || empty($player['citizenid'])) {
        echo json_encode(['success' => false, 'message' => 'Karakteriniz sunucuda bulunamadı. Lütfen en az bir kere oyuna giriş yapıp karakter oluşturduktan sonra tekrar deneyin.']);
        exit;
    }
    
    $citizenid = $player['citizenid'];

    // 4. 'donations' tablosuna doğru ID formatlarıyla yeni kaydı ekle.
    $insert_stmt = $pdo->prepare(
        "INSERT INTO donations (citizenid, steam_id, vehicle_model, vehicle_label, price, delivered) VALUES (?, ?, ?, ?, ?, 0)"
    );
    
    $success = $insert_stmt->execute([
        $citizenid,
        $steam_id_64,
        $vehicle['vehicle_model'],
        $vehicle['vehicle_label'],
        $vehicle['price']
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => $vehicle['vehicle_label'] . ' başarıyla satın alındı! Oyuna girdiğinizde teslim edilecektir.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Satın alma işlemi sırasında bir veritabanı hatası oluştu.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
}
?>
