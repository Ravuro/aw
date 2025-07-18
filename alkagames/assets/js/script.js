// assets/js/script.js

// Sayfanın tüm HTML içeriği yüklendikten sonra bu kodları çalıştır.
document.addEventListener('DOMContentLoaded', function() {

    // Mağaza sayfasındaki tüm "Satın Al" butonlarını bul.
    const buyButtons = document.querySelectorAll('.btn-buy');

    // Eğer sayfada "Satın Al" butonu varsa...
    if (buyButtons.length > 0) {
        buyButtons.forEach(button => {
            // Her bir butona tıklama olayı ekle.
            button.addEventListener('click', function() {
                // Kullanıcının giriş yapıp yapmadığını kontrol et.
                // En basit yol, profil menüsünün var olup olmadığına bakmaktır.
                const profileDropdown = document.querySelector('.profile-dropdown');
                if (!profileDropdown) {
                    // Giriş yapmamışsa, uyar ve giriş sayfasına yönlendir.
                    showAlert('Lütfen satın alma işlemi yapmadan önce Steam ile giriş yapın.', 'error');
                    setTimeout(() => { window.location.href = 'login.php'; }, 2000);
                    return;
                }

                // Tıklanan butonun ait olduğu ürünün bilgilerini al.
                const vehicleCard = this.closest('.vehicle-card');
                const vehicleName = vehicleCard.querySelector('.vehicle-name').textContent;
                const vehiclePrice = vehicleCard.querySelector('.vehicle-price').textContent;
                const vehicleId = this.dataset.id;

                // Kullanıcıya onay penceresini (modal) göster.
                showConfirmationModal(vehicleName, vehiclePrice, vehicleId);
            });
        });
    }

    // Onay penceresi (modal) oluşturma ve yönetme fonksiyonları
    function createModal() {
        // Modal'ın HTML yapısını oluştur.
        const modalHTML = `
            <div class="modal-overlay" id="purchaseModal">
                <div class="modal-content">
                    <h2 class="modal-title">Satın Almayı Onayla</h2>
                    <div class="modal-item-info">
                        <strong id="modalItemName"></strong>
                        <span id="modalItemPrice"></span>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-cancel" id="cancelButton">İptal</button>
                        <button class="btn btn-confirm" id="confirmButton">Onayla ve Satın Al</button>
                    </div>
                    <div id="modal-spinner" class="spinner" style="display: none;"></div>
                </div>
            </div>
        `;
        // Oluşturulan HTML'i body'nin sonuna ekle.
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // İptal butonlarına ve dışarı tıklamaya olay ekle.
        document.getElementById('cancelButton').addEventListener('click', hideModal);
        document.querySelector('.modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) { // Eğer overlay'in kendisine tıklandıysa
                hideModal();
            }
        });
    }

    // Modal'ı gösterme fonksiyonu
    function showConfirmationModal(name, price, id) {
        document.getElementById('modalItemName').textContent = name;
        document.getElementById('modalItemPrice').textContent = price;
        document.getElementById('purchaseModal').style.display = 'flex';

        // Onay butonuna tıklandığında çalışacak fonksiyonu ayarla.
        const confirmBtn = document.getElementById('confirmButton');
        // Olası çift tıklama sorunlarını önlemek için butonu klonlayıp olay dinleyicisini yeniden ekliyoruz.
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', () => handlePurchase(id));
    }

    // Modal'ı gizleme fonksiyonu
    function hideModal() {
        document.getElementById('purchaseModal').style.display = 'none';
        // Butonları ve yüklenme animasyonunu sıfırla.
        document.getElementById('modal-spinner').style.display = 'none';
        document.getElementById('confirmButton').style.display = 'inline-block';
        document.getElementById('cancelButton').style.display = 'inline-block';
    }

    // Satın alma işlemini sunucuya gönderme fonksiyonu
    async function handlePurchase(vehicleId) {
        const spinner = document.getElementById('modal-spinner');
        const confirmBtn = document.getElementById('confirmButton');
        const cancelBtn = document.getElementById('cancelButton');

        // Butonları gizle ve yüklenme animasyonunu göster.
        spinner.style.display = 'block';
        confirmBtn.style.display = 'none';
        cancelBtn.style.display = 'none';

        try {
            // `fetch` ile `purchase_handler.php` dosyasına POST isteği gönder.
            const response = await fetch('purchase_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ vehicle_id: vehicleId })
            });

            const result = await response.json(); // Gelen JSON yanıtını işle.

            hideModal(); // İşlem bitince modal'ı kapat.

            // Sunucudan gelen yanıta göre bildirim göster.
            if (result.success) {
                showAlert(result.message, 'success');
            } else {
                showAlert(result.message || 'Bilinmeyen bir hata oluştu.', 'error');
            }

        } catch (error) {
            hideModal();
            showAlert('Bir ağ hatası oluştu. Lütfen tekrar deneyin.', 'error');
            console.error('Satın alma hatası:', error);
        }
    }
    
    // Ekranda şık bildirimler gösterme fonksiyonu
    function showAlert(message, type = 'success') {
        const alertBox = document.createElement('div');
        alertBox.className = `custom-alert alert-${type}`;
        alertBox.textContent = message;
        document.body.appendChild(alertBox);
        
        // Bildirimi ekranın sağına kaydırarak göster.
        setTimeout(() => {
            alertBox.classList.add('show');
        }, 10);
        
        // 5 saniye sonra bildirimi kaldır.
        setTimeout(() => {
            alertBox.classList.remove('show');
            setTimeout(() => {
                alertBox.remove();
            }, 500);
        }, 5000);
    }

    // Sayfa ilk yüklendiğinde modal'ı oluştur (ama gizli tut).
    createModal();
});
