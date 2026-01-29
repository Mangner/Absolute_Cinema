/**
 * QR Code Modal Handler for Absolute Cinema
 * Obsługa wyświetlania kodów QR biletów w oknie modalnym
 */

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('qr-modal');
    const qrcodeContainer = document.getElementById('qrcode');
    const modalTitle = document.getElementById('qr-modal-title');
    const modalMovieInfo = document.getElementById('qr-modal-movie');
    const modalSeatInfo = document.getElementById('qr-modal-seat');
    const closeBtn = document.getElementById('qr-modal-close');
    const overlay = document.querySelector('.qr-modal-overlay');
    
    let qrCodeInstance = null;

    // Funkcja pokazująca modal z kodem QR
    window.showQRCode = function(token, movieTitle, seatInfo, dateInfo) {
        if (!modal || !qrcodeContainer) return;

        // Wyczyść poprzedni kod QR
        qrcodeContainer.innerHTML = '';

        // Ustaw informacje w modalu
        if (modalTitle) modalTitle.textContent = 'Twój Bilet';
        if (modalMovieInfo) modalMovieInfo.textContent = movieTitle || 'Film';
        if (modalSeatInfo) modalSeatInfo.textContent = seatInfo || '';

        // Wygeneruj nowy kod QR
        try {
            qrCodeInstance = new QRCode(qrcodeContainer, {
                text: token,
                width: 200,
                height: 200,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (e) {
            console.error('Błąd generowania kodu QR:', e);
            qrcodeContainer.innerHTML = '<p style="color: #e74c3c;">Błąd generowania kodu QR</p>';
        }

        // Pokaż modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Zablokuj scroll
    };

    // Funkcja zamykająca modal
    function closeModal() {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = ''; // Przywróć scroll
        }
    }

    // Zamknij po kliknięciu X
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // Zamknij po kliknięciu w overlay (tło)
    if (overlay) {
        overlay.addEventListener('click', closeModal);
    }

    // Zamknij po naciśnięciu Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // Zapobiegaj zamknięciu po kliknięciu w zawartość modala
    const modalContent = document.querySelector('.qr-modal-content');
    if (modalContent) {
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
