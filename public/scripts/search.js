/**
 * Live Search - Wyszukiwanie filmów w pasku nawigacji
 * Absolute Cinema
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-bar');
    if (!searchInput) return;

    // Tworzenie kontenera wyników
    const searchContainer = searchInput.closest('.search-container');
    let resultsContainer = document.createElement('div');
    resultsContainer.className = 'search-results';
    resultsContainer.style.display = 'none';
    searchContainer.appendChild(resultsContainer);

    let debounceTimer;

    // Nasłuchiwanie na wpisywanie tekstu
    searchInput.addEventListener('keyup', function(e) {
        const query = this.value.trim();

        // Czyszczenie poprzedniego timera
        clearTimeout(debounceTimer);

        // Jeśli query jest zbyt krótkie, ukryj wyniki
        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            resultsContainer.innerHTML = '';
            return;
        }

        // Debounce - czekamy 300ms po ostatnim naciśnięciu klawisza
        debounceTimer = setTimeout(() => {
            fetchSearchResults(query);
        }, 300);
    });

    // Ukrywanie wyników po kliknięciu poza
    document.addEventListener('click', function(e) {
        if (!searchContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });

    // Pokazywanie wyników przy ponownym focusie
    searchInput.addEventListener('focus', function() {
        if (resultsContainer.innerHTML !== '' && this.value.trim().length >= 2) {
            resultsContainer.style.display = 'block';
        }
    });

    /**
     * Pobieranie wyników wyszukiwania z API
     */
    async function fetchSearchResults(query) {
        try {
            const response = await fetch('/search?q=' + encodeURIComponent(query));
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const movies = await response.json();
            displayResults(movies);

        } catch (error) {
            console.error('Błąd wyszukiwania:', error);
            resultsContainer.innerHTML = '<div class="search-result-item search-error">Błąd wyszukiwania</div>';
            resultsContainer.style.display = 'block';
        }
    }

    /**
     * Wyświetlanie wyników wyszukiwania
     */
    function displayResults(movies) {
        resultsContainer.innerHTML = '';

        if (movies.length === 0) {
            resultsContainer.innerHTML = '<div class="search-result-item search-no-results">Nie znaleziono filmów</div>';
            resultsContainer.style.display = 'block';
            return;
        }

        // Ograniczenie do 8 wyników
        const limitedMovies = movies.slice(0, 8);

        limitedMovies.forEach(movie => {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.dataset.movieId = movie.id;

            // Formatowanie daty
            let yearText = '';
            if (movie.release_date) {
                const year = new Date(movie.release_date).getFullYear();
                yearText = `<span class="search-result-year">(${year})</span>`;
            }

            // Poster lub placeholder
            const posterHtml = movie.image 
                ? `<img src="${movie.image}" alt="${movie.title}" class="search-result-poster">`
                : `<div class="search-result-poster-placeholder"><i class="fas fa-film"></i></div>`;

            item.innerHTML = `
                ${posterHtml}
                <div class="search-result-info">
                    <span class="search-result-title">${escapeHtml(movie.title)}</span>
                    ${yearText}
                </div>
            `;

            // Kliknięcie przekierowuje do szczegółów filmu
            item.addEventListener('click', function() {
                window.location.href = '/movie/' + movie.id;
            });

            resultsContainer.appendChild(item);
        });

        // Jeśli jest więcej wyników
        if (movies.length > 8) {
            const moreItem = document.createElement('div');
            moreItem.className = 'search-result-item search-more';
            moreItem.innerHTML = `<i class="fas fa-ellipsis-h"></i> Jeszcze ${movies.length - 8} wyników...`;
            resultsContainer.appendChild(moreItem);
        }

        resultsContainer.style.display = 'block';
    }

    /**
     * Escape HTML dla bezpieczeństwa
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
