<?php
// basvuru.php - Whitelist başvuru formu

require_once __DIR__ . '/includes/header.php';

// Kullanıcı giriş yapmamışsa, bu sayfayı göremez.
if (!isset($_SESSION['steam_loggedin']) || $_SESSION['steam_loggedin'] !== true) {
    echo '<div class="container"><p class="form-message error">Başvuru yapabilmek için lütfen önce Steam ile giriş yapın.</p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$steam_id = $_SESSION['steam_steamid'];
$application_status = null;
$application_message = '';

// Kullanıcının mevcut başvurusunu kontrol et
try {
    $stmt = $pdo->prepare("SELECT status FROM basvurular WHERE steam_id = ?");
    $stmt->execute([$steam_id]);
    $application = $stmt->fetch();
    if ($application) {
        $application_status = $application['status'];
    }
} catch (PDOException $e) {
    $error_message = "Başvuru durumu kontrol edilirken bir hata oluştu.";
}

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$application_status) {
    $answers = [
        'rp_tecrubesi' => $_POST['rp_tecrubesi'] ?? '',
        'neden_biz' => $_POST['neden_biz'] ?? '',
        'karakter_hikayesi' => $_POST['karakter_hikayesi'] ?? ''
    ];

    // Oyuncunun citizenid'sini al
    $hex_id = 'steam:' . dechex($steam_id);
    $player_stmt = $pdo->prepare("SELECT citizenid FROM players WHERE steam = ?");
    $player_stmt->execute([$hex_id]);
    $player = $player_stmt->fetch();
    $citizenid = $player['citizenid'] ?? 'YOK';

    if (!empty($answers['rp_tecrubesi']) && !empty($answers['neden_biz']) && !empty($answers['karakter_hikayesi'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO basvurular (steam_id, citizenid, answers, status) VALUES (?, ?, ?, 'Bekliyor')");
            $stmt->execute([$steam_id, $citizenid, json_encode($answers)]);
            $application_status = 'Bekliyor'; // Durumu anında güncelle
            $application_message = '<p class="form-message success">Başvurunuz başarıyla gönderildi! En kısa sürede incelenecektir.</p>';
        } catch (PDOException $e) {
            $error_message = "Başvuru gönderilirken bir hata oluştu: " . $e->getMessage();
        }
    } else {
        $error_message = "Lütfen tüm soruları cevaplayın.";
    }
}

// Başvuru durumuna göre mesaj oluştur
if ($application_status) {
    switch ($application_status) {
        case 'Bekliyor':
            $application_message = '<p class="form-message pending">Zaten beklemede olan bir başvurunuz var. Lütfen sonucunu bekleyin.</p>';
            break;
        case 'Onaylandı':
            $application_message = '<p class="form-message success">Tebrikler! Başvurunuz onaylandı. Sunucuya giriş yapabilirsiniz.</p>';
            break;
        case 'Reddedildi':
            $application_message = '<p class="form-message error">Üzgünüz, başvurunuz reddedildi. Belirli bir süre sonra tekrar deneyebilirsiniz.</p>';
            break;
    }
}
?>

<div class="container">
    <h1 class="page-title">Whitelist Başvurusu</h1>

    <?php if (isset($error_message)) echo "<p class='form-message error'>$error_message</p>"; ?>
    <?php echo $application_message; ?>

    <?php if (!$application_status): // Eğer kullanıcının mevcut bir başvurusu yoksa formu göster ?>
    <form action="basvuru.php" method="POST" class="application-form">
        <div class="form-group">
            <label for="rp_tecrubesi">Roleplay tecrübeniz nedir? (Önceki sunucular, karakterleriniz vb.)</label>
            <textarea name="rp_tecrubesi" id="rp_tecrubesi" rows="6" required></textarea>
        </div>
        <div class="form-group">
            <label for="neden_biz">Neden Alaka Games sunucusunda oynamak istiyorsunuz?</label>
            <textarea name="neden_biz" id="neden_biz" rows="6" required></textarea>
        </div>
        <div class="form-group">
            <label for="karakter_hikayesi">Oluşturmayı düşündüğünüz karakterin kısa bir hikayesini anlatır mısınız?</label>
            <textarea name="karakter_hikayesi" id="karakter_hikayesi" rows="10" required></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-submit">Başvuruyu Gönder</button>
        </div>
    </form>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
