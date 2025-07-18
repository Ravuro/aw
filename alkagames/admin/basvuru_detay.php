<?php
// admin/basvuru_detay.php - Tek bir başvuruyu inceleme ve işlem yapma
require_once __DIR__ . '/includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: basvurular.php");
    exit;
}
$app_id = (int)$_GET['id'];

// Durum güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    if (in_array($status, ['Onaylandı', 'Reddedildi'])) {
        $stmt = $pdo->prepare("UPDATE basvurular SET status = ? WHERE id = ?");
        $stmt->execute([$status, $app_id]);
        header("Location: basvurular.php?status=updated");
        exit;
    }
}

// Başvuru detaylarını çek
try {
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as player_name 
        FROM basvurular b
        LEFT JOIN players p ON b.steam_id = p.steam_id
        WHERE b.id = ?
    ");
    $stmt->execute([$app_id]);
    $app = $stmt->fetch();
    if (!$app) {
        header("Location: basvurular.php");
        exit;
    }
    $answers = json_decode($app['answers'], true);
} catch (PDOException $e) {
    die("Başvuru yüklenirken hata: " . $e->getMessage());
}
?>

<div class="admin-page-header">
    <h1 class="admin-page-title">Başvuru Detayı: <?php echo htmlspecialchars($app['player_name'] ?? $app['citizenid']); ?></h1>
    <a href="basvurular.php" class="btn btn-cancel"><i class="fas fa-arrow-left"></i> Geri Dön</a>
</div>

<div class="application-details">
    <div class="question-block">
        <h3>Roleplay tecrübesi nedir?</h3>
        <p><?php echo nl2br(htmlspecialchars($answers['rp_tecrubesi'])); ?></p>
    </div>
    <div class="question-block">
        <h3>Neden Alaka Games?</h3>
        <p><?php echo nl2br(htmlspecialchars($answers['neden_biz'])); ?></p>
    </div>
    <div class="question-block">
        <h3>Karakter hikayesi</h3>
        <p><?php echo nl2br(htmlspecialchars($answers['karakter_hikayesi'])); ?></p>
    </div>

    <?php if ($app['status'] === 'Bekliyor'): ?>
    <div class="application-actions">
        <form action="basvuru_detay.php?id=<?php echo $app_id; ?>" method="POST" style="display: inline;">
            <input type="hidden" name="status" value="Onaylandı">
            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Onayla</button>
        </form>
        <form action="basvuru_detay.php?id=<?php echo $app_id; ?>" method="POST" style="display: inline;">
            <input type="hidden" name="status" value="Reddedildi">
            <button type="submit" class="btn btn-delete"><i class="fas fa-times"></i> Reddet</button>
        </form>
    </div>
    <?php else: ?>
        <p class="form-message info">Bu başvuru zaten işlem görmüş. Durum: <strong><?php echo $app['status']; ?></strong></p>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
