document.addEventListener('DOMContentLoaded', () => {
    
    // Pobieramy wszystkie kontenery karuzeli (bo mamy 3 sekcje)
    const carousels = document.querySelectorAll('.carousel-container');

    carousels.forEach(container => {
        const track = container.querySelector('.carousel-track');
        const btnPrev = container.querySelector('.btn-prev');
        const btnNext = container.querySelector('.btn-next');

        // Ile przesuwać przy kliknięciu (szerokość karty + odstęp)
        const scrollAmount = 300; 

        btnNext.addEventListener('click', () => {
            track.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });

        btnPrev.addEventListener('click', () => {
            track.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });
    });
});