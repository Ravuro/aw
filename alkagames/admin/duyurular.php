<?php
// admin/duyurular.php - Duyuruları yönetme sayfası
require_once __DIR__ . '/includes/header.php';

// Duyuruları veritabanından çekiyoruz. Yazar adını alabilmek için 'players' tablosu ile birleştiriyoruz.
try {
    $stmt = $pdo->query("
        SELECT d.*, p.name as author_name 
        FROM duyurular d
        LEFT JOIN players p ON d.author_steamid = p.steam
        ORDER BY d.created_at DESC
    ");
    $announcements = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<p class="admin-error">Duyurular listelenirken bir hata oluştu: ' . $e->getMessage() . '</p>';
    $announcements = [];
}
?>

<div class="admin-page-header">
    <h1 class="admin-page-title">Duyuru Yönetimi</h1>
    <a href="duyuru_islem.php?action=add" class="btn btn-success"><i class="fas fa-plus"></i> Yeni Duyuru Ekle</a>
</div>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Başlık</th>
                <th>Yazar</th>
                <th>Tarih</th>
                <th>Eylemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($announcements)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Henüz bir duyuru yayınlanmamış.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                        <td><?php echo htmlspecialchars($announcement['author_name'] ?? 'Bilinmeyen'); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($announcement['created_at'])); ?></td>
                        <td class="actions">
                            <a href="duyuru_islem.php?action=edit&id=<?php echo $announcement['id']; ?>" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                            <a href="duyuru_islem.php?action=delete&id=<?php echo $announcement['id']; ?>" class="btn btn-delete" onclick="return confirm('Bu duyuruyu silmek istediğinizden emin misiniz?');"><i class="fas fa-trash"></i></a>
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
