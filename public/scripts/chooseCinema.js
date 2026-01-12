const chooseCinemaBtn = document.getElementById("cinema-choice-btn");
const cinemasURL = "http://localhost:8080/get-cinemas";
const setCinemaURL = "http://localhost:8080/set-cinema";

// ZMIANA 1: Dodajemy "window." - teraz funkcja jest globalna
window.createCinemaModal = function() {
  let existingModal = document.getElementById("cinema-modal");
  if (existingModal) {
    existingModal.style.display = "flex";
    return;
  }

  const modal = document.createElement("div");
  modal.id = "cinema-modal";
  modal.className = "cinema-modal";

  modal.innerHTML = `
        <div class="modal-content"> 
            <div class="modal-header">
                <h2>Wybierz kino</h2>
                <button class="close-btn" id="close-modal">&times;</button>
            </div>
            <form id="cinema-form">
                <div class="form-group">
                    <label for="cinema-select">Lokalizacja kina:</label>
                    <select id="cinema-select" name="cinema_id" required>
                        <option value="">Ładowanie...</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Wybierz</button>
                    <button type="button" class="cancel-btn" id="cancel-btn">Anuluj</button>
                </div>
            </form>
        </div>
    `;

  document.body.appendChild(modal);

  const closeBtn = document.getElementById("close-modal");
  const cancelBtn = document.getElementById("cancel-btn");

  // Funkcja zamykania musi być wewnątrz, żeby widzieć zmienną 'modal'
  function closeModal() {
      modal.style.display = "none";
  }

  closeBtn.addEventListener("click", closeModal);
  cancelBtn.addEventListener("click", closeModal);

  modal.addEventListener("click", e => {
    if (e.target === modal) {
      closeModal();
    }
  });

  loadCinemas();
}; // koniec funkcji window.createCinemaModal

// Tę funkcję też musisz mieć dostępną (lub przenieść ją do środka createCinemaModal, ale może zostać tutaj)
function loadCinemas() {
  const select = document.getElementById("cinema-select");

  fetch(cinemasURL)
    .then(response => response.json())
    .then(data => {
      const cinemas = data.cinemas || data;

      if (Array.isArray(cinemas)) {
        select.innerHTML = '<option value="">-- Wybierz kino --</option>';

        cinemas.forEach(cinema => {
          const option = document.createElement("option");
          option.value = cinema.cinema_id;
          option.textContent = `${cinema.name}`;
          select.appendChild(option);
        });
      } else {
        console.error("Błąd: Oczekiwano tablicy dla", cinemasURL);
        select.innerHTML = '<option value="">Błąd ładowania kin</option>';
      }
    })
    .catch(err => {
      console.error("Błąd pobierania:", err);
      select.innerHTML = '<option value="">Błąd ładowania kin</option>';
      alert("Nie udało się załadować listy kin. Spróbuj ponownie później.");
    });
}

// Globalny nasłuchiwacz formularza (działa dla każdego modala)
document.addEventListener("submit", e => {
  if (e.target.id === "cinema-form") {
    e.preventDefault(); 
    
    const select = document.getElementById("cinema-select");
    const cinemaId = select.options[select.selectedIndex].value;
    const cinemaName = select.options[select.selectedIndex].textContent;

    if (!cinemaId) {
      alert("Proszę wybrać kino");
      return;
    }

    fetch(setCinemaURL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ 
        cinema_id: cinemaId,
        cinema_name: cinemaName
       })
    })
    .then(response => {
      if (response.status === 401) {
        alert("Twoja sesja wygasła. Zostaniesz przekierowany do strony logowania.");
        window.location.href = "http://localhost:8080/login";
        return;
      }
      return response.json();
    })
    .then(data => {
      if (!data) return; 
      
      if (data.status === 'ok') {
        // Zamykamy modal ręcznie
        const modal = document.getElementById("cinema-modal");
        if (modal) modal.style.display = "none";
        
        window.location.reload();
      } else if (data.status === 'unauthorized') {
        alert(data.message || "Twoja sesja wygasła.");
        window.location.href = "http://localhost:8080/login";
      } else {
        alert("Błąd przy zapisywaniu wyboru kina");
      }
    })
    .catch(err => {
      console.error("Błąd:", err);
      alert("Błąd przy zapisywaniu wyboru kina");
    });
  }
});

// ZMIANA 2: Podpinamy przycisk z nawigacji używając window.createCinemaModal
if (chooseCinemaBtn) {
    chooseCinemaBtn.addEventListener("click", window.createCinemaModal);
}