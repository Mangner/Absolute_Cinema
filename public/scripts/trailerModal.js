document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('trailer-modal');
    const iframeContainer = document.querySelector('.trailer-iframe-container');
    const closeBtn = document.getElementById('trailer-close');
    const triggers = document.querySelectorAll('.trailer-trigger');
    
    // Pobierz URL zwiastuna z data attribute
    const trailerUrl = modal?.dataset.trailerUrl;
    
    if (!modal || !iframeContainer || !trailerUrl) {
        console.warn('Trailer modal: brak wymaganych elementów lub URL zwiastuna');
        return;
    }
    
    // Usuń statyczny iframe (jeśli istnieje)
    const existingIframe = document.getElementById('trailer-iframe');
    if (existingIframe) existingIframe.remove();
    
    // Funkcja otwierająca modal
    function openTrailer() {
        // Twórz iframe dynamicznie
        const iframe = document.createElement('iframe');
        iframe.id = 'trailer-iframe';
        iframe.src = trailerUrl + '?autoplay=1&rel=0';
        iframe.frameBorder = '0';
        iframe.allowFullscreen = true;
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        
        iframeContainer.appendChild(iframe);
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Funkcja zamykająca modal
    function closeTrailer() {
        if (!modal.classList.contains('active')) return;
        
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Całkowicie usuń iframe - to zapobiega problemom z historią
        const iframe = document.getElementById('trailer-iframe');
        if (iframe) iframe.remove();
    }
    
    // Obsługa kliknięć na triggery (plakat, przycisk)
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            openTrailer();
        });
    });
    
    // Zamknij po kliknięciu X
    closeBtn?.addEventListener('click', closeTrailer);
    
    // Zamknij po kliknięciu w tło
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeTrailer();
        }
    });
    
    // Zamknij po naciśnięciu Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeTrailer();
        }
    });
});
