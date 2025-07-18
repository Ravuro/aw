<?php
// duyurular.php - Yayınlanan duyuruları listeler.
require_once __DIR__ . '/includes/header.php';

// Duyuruları veritabanından çekiyoruz. Yazar adını alabilmek için 'players' tablosu ile birleştiriyoruz.
try {
    $stmt = $pdo->query("
        SELECT 
            d.title, 
            d.content, 
            d.created_at, 
            p.name as author_name 
        FROM duyurular d
        LEFT JOIN players p ON d.author_steamid = p.steam
        ORDER BY d.created_at DESC
    ");
    $announcements = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<p class="container" style="color:red;">Duyurular yüklenirken bir hata oluştu.</p>';
    $announcements = [];
}
?>

<div class="container">
    <h1 class="page-title">Duyurular</h1>

    <div class="announcements-container">
        <?php if (empty($announcements)): ?>
            <p style="text-align: center; color: var(--text-muted);">Şu anda yayınlanmış bir duyuru bulunmamaktadır.</p>
        <?php else: ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-card">
                    <h2 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h2>
                    <div class="announcement-meta">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($announcement['author_name'] ?? 'Yönetim'); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('d F Y, H:i', strtotime($announcement['created_at'])); ?></span>
                    </div>
                    <div class="announcement-content">
                        <?php 
                            // nl2br fonksiyonu, metindeki alt satırları (<br>) etiketine çevirir.
                            echo nl2br(htmlspecialchars($announcement['content'])); 
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
