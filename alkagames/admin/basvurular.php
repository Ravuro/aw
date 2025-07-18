<?php
// admin/basvurular.php - Gelen başvuruları yönetme sayfası
require_once __DIR__ . '/includes/header.php';

// Başvuruları veritabanından çekiyoruz.
try {
    $stmt = $pdo->query("
        SELECT b.*, p.name as player_name 
        FROM basvurular b
        LEFT JOIN players p ON b.steam_id = p.steam_id
        ORDER BY b.created_at DESC
    ");
    $applications = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<p class="admin-error">Başvurular listelenirken bir hata oluştu: ' . $e->getMessage() . '</p>';
    $applications = [];
}
?>

<div class="admin-page-header">
    <h1 class="admin-page-title">Başvuru Yönetimi</h1>
</div>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Oyuncu Adı</th>
                <th>Başvuru Tarihi</th>
                <th>Durum</th>
                <th>Eylemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($applications)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">İncelenecek başvuru bulunmuyor.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($app['player_name'] ?? $app['citizenid']); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($app['created_at'])); ?></td>
                        <td>
                            <span class="status status-<?php echo strtolower($app['status']); ?>"><?php echo $app['status']; ?></span>
                        </td>
                        <td class="actions">
                            <a href="basvuru_detay.php?id=<?php echo $app['id']; ?>" class="btn btn-edit"><i class="fas fa-eye"></i> Görüntüle</a>
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
