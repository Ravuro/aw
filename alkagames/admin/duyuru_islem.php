<?php
// admin/duyuru_islem.php - Duyuru ekleme, düzenleme ve silme işlemlerini yönetir.
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'add';
$id = $_GET['id'] ?? null;
$title = '';
$content = '';
$page_title = 'Yeni Duyuru Ekle';

// Düzenleme için mevcut veriyi çek
if ($action === 'edit' && $id) {
    $page_title = 'Duyuruyu Düzenle';
    $stmt = $pdo->prepare("SELECT * FROM duyurular WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch();
    if ($announcement) {
        $title = $announcement['title'];
        $content = $announcement['content'];
    }
}

// Silme işlemi
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM duyurular WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: duyurular.php?status=deleted");
    exit;
}

// Form gönderildi mi? (Ekleme veya Düzenleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $author_steam_hex = 'steam:' . dechex($_SESSION['steam_steamid']);

    if (!empty($title) && !empty($content)) {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO duyurular (title, content, author_steamid) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $author_steam_hex]);
        } elseif ($action === 'edit' && $id) {
            $stmt = $pdo->prepare("UPDATE duyurular SET title = ?, content = ? WHERE id = ?");
            $stmt->execute([$title, $content, $id]);
        }
        header("Location: duyurular.php?status=success");
        exit;
    } else {
        $error_message = "Başlık ve içerik alanları boş bırakılamaz.";
    }
}
?>

<div class="admin-page-header">
    <h1 class="admin-page-title"><?php echo $page_title; ?></h1>
    <a href="duyurular.php" class="btn btn-cancel"><i class="fas fa-arrow-left"></i> Geri Dön</a>
</div>

<?php if (isset($error_message)): ?>
    <p class="admin-error"><?php echo $error_message; ?></p>
<?php endif; ?>

<form action="duyuru_islem.php?action=<?php echo $action; ?><?php echo $id ? '&id='.$id : ''; ?>" method="POST" class="admin-form">
    <div class="form-group">
        <label for="title">Duyuru Başlığı</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
    </div>
    <div class="form-group">
        <label for="content">Duyuru İçeriği</label>
        <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-success">Kaydet</button>
    </div>
</form>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
