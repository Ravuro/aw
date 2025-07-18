<?php
// index.php - Sitenin ana karşılama sayfası

// Her sayfanın başında olması gereken header'ı çağırıyoruz.
// Bu dosya, menüyü, oturum bilgilerini ve sayfa başlığını içerir.
require_once __DIR__ . '/includes/header.php';
?>

<!-- Sayfaya özel içerik burada başlıyor -->
<div class="container">
    <h1 class="page-title">Alaka Games Roleplay'e Hoş Geldiniz</h1>
    <p style="text-align: center; font-size: 1.2rem; color: var(--text-muted);">
        Sunucumuzda en iyi roleplay deneyimini yaşamak için doğru yerdesiniz.
    </p>
    
    <div style="margin-top: 3rem; text-align: center;">
        <p>Mağazamızı ziyaret ederek sunucumuza destek olabilir ve özel araçlara sahip olabilirsiniz.</p>
        <a href="magaza.php" class="btn" style="background-color: var(--orange); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 1rem; display: inline-block;">
            Mağazaya Git
        </a>
    </div>
</div>
<!-- Sayfaya özel içerik burada bitiyor -->

<?php
// Her sayfanın sonunda olması gereken footer'ı çağırıyoruz.
require_once __DIR__ . '/includes/footer.php';
?>
