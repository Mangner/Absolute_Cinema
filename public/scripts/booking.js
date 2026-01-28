document.addEventListener('DOMContentLoaded', () => {
    // Pobieramy elementy DOM
    const hallContainer = document.getElementById('cinema-hall');
    const seatsDisplay = document.getElementById('selected-seats-display');
    const priceDisplay = document.getElementById('total-price-display');
    const selectedSeatsInput = document.getElementById('input-selected-seats');
    const checkoutBtn = document.getElementById('checkout-btn');

    // Stan aplikacji (koszyk)
    let selectedSeats = []; // Tablica obiektów: { id, label, price }
    let totalPrice = 0.0;

    // --- 1. Generowanie siatki miejsc ---
    function renderHall() {
        // Sprawdzenie czy mamy dane (zmienna seatsData zdefiniowana w HTML)
        if (typeof seatsData === 'undefined' || !seatsData.length) {
            hallContainer.innerHTML = '<p style="color:white; text-align:center; width:100%;">Brak dostępnych miejsc dla tego seansu.</p>';
            return;
        }

        hallContainer.innerHTML = '';

        seatsData.forEach(seat => {
            // Tworzymy przycisk dla każdego miejsca
            const seatBtn = document.createElement('button');
            seatBtn.type = 'button'; // Ważne: żeby nie resetował formularza
            seatBtn.className = 'seat';
            
            // --- POZYCJONOWANIE GRID (Kluczowe!) ---
            // Używamy danych z bazy (grid_row, grid_col)
            seatBtn.style.gridRow = seat.grid_row;
            seatBtn.style.gridColumn = seat.grid_col;

            // --- Metadane ---
            seatBtn.innerText = seat.seat_number;
            seatBtn.dataset.id = seat.seat_id;
            seatBtn.dataset.price = seat.price; // Cena (baza + dopłata)
            // Tworzymy etykietę np. "A5"
            const label = `${seat.row_label}${seat.seat_number}`;
            seatBtn.dataset.label = label;
            
            // Konwersja statusu z bazy na logiczny
            // Zakładam, że w bazie/modelu status to 'ZAJĘTE' lub 'WOLNE'
            const isTaken = (seat.status === 'ZAJĘTE');
            const extraCharge = parseFloat(seat.extra_charge || 0);

            // --- Klasy CSS ---
            if (isTaken) {
                seatBtn.classList.add('taken');
                seatBtn.disabled = true;
                seatBtn.title = `Miejsce ${label} - Zajęte`;
            } else {
                seatBtn.classList.add('available');
                
                // Jeśli jest dopłata, dodajemy klasę VIP
                if (extraCharge > 0) {
                    seatBtn.classList.add('vip');
                    seatBtn.title = `Miejsce ${label} - VIP (+${extraCharge} PLN)`;
                } else {
                    seatBtn.title = `Miejsce ${label}`;
                }

                // Dodajemy nasłuchiwanie kliknięcia tylko dla wolnych miejsc
                seatBtn.addEventListener('click', () => toggleSeat(seatBtn));
            }

            hallContainer.appendChild(seatBtn);
        });
    }

    // --- 2. Obsługa kliknięcia (Logika biznesowa) ---
    function toggleSeat(seatBtn) {
        const seatId = seatBtn.dataset.id;
        const price = parseFloat(seatBtn.dataset.price);
        const label = seatBtn.dataset.label;

        // Sprawdzamy czy miejsce jest już wybrane
        const index = selectedSeats.findIndex(s => s.id === seatId);

        if (index !== -1) {
            // ODZNACZANIE
            seatBtn.classList.remove('selected');
            selectedSeats.splice(index, 1); // Usuń z tablicy
            totalPrice -= price;
        } else {
            // ZAZNACZANIE
            seatBtn.classList.add('selected');
            selectedSeats.push({ id: seatId, label: label, price: price });
            totalPrice += price;
        }

        // Zabezpieczenie przed ujemną ceną przy błędach zaokrągleń
        if (totalPrice < 0) totalPrice = 0;

        updateSummary();
    }

    // --- 3. Aktualizacja UI ---
    function updateSummary() {
        // 1. Wyświetl listę miejsc (np. "F7, F8")
        if (selectedSeats.length > 0) {
            // Sortujemy po etykiecie żeby było ładnie (A1, A2 zamiast A2, A1)
            selectedSeats.sort((a, b) => a.label.localeCompare(b.label));
            const labels = selectedSeats.map(s => s.label).join(', ');
            seatsDisplay.innerText = labels;
        } else {
            seatsDisplay.innerText = 'Brak';
        }

        // 2. Wyświetl cenę łączną
        priceDisplay.innerText = totalPrice.toFixed(2);

        // 3. Zaktualizuj ukryty input (do wysłania formularza)
        // Wysyłamy string ID oddzielonych przecinkami, np. "15,16,20"
        const ids = selectedSeats.map(s => s.id).join(',');
        selectedSeatsInput.value = ids;

        // 4. Zarządzanie przyciskiem "Przejdź do płatności"
        if (selectedSeats.length > 0) {
            checkoutBtn.disabled = false;
        } else {
            checkoutBtn.disabled = true;
        }
    }

    // Uruchomienie generowania przy starcie
    renderHall();
});