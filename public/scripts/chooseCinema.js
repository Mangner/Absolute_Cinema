const chooseCinemaBtn = document.getElementById("cinema-choice-btn");
const cinemasURL = "http://localhost:8080/get-cinemas";
const setCinemaURL = "http://localhost:8080/set-cinema";


function createCinemaModal() {
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

  closeBtn.addEventListener("click", closeModal);
  cancelBtn.addEventListener("click", closeModal);

  modal.addEventListener("click", e => {
    if (e.target === modal) {
      closeModal();
    }
  });

  loadCinemas();
}

function closeModal() {
  const modal = document.getElementById("cinema-modal");
  if (modal) {
    modal.style.display = "none";
  }
}

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

document.addEventListener("submit", e => {
  if (e.target.id === "cinema-form") {
    e.preventDefault(); // Prevent page reload and POST data
    
    const select = document.getElementById("cinema-select");
    const cinemaId = select.options[select.selectedIndex].value;
    const cinemaName = select.options[select.selectedIndex].textContent;

    if (!cinemaId) {
      alert("Proszę wybrać kino");
      return;
    }

    // Send cinema selection to PHP backend to store in session
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
      // Check if session expired (401 Unauthorized)
      if (response.status === 401) {
        alert("Twoja sesja wygasła. Zostaniesz przekierowany do strony logowania.");
        window.location.href = "http://localhost:8080/login";
        return;
      }
      return response.json();
    })
    .then(data => {
      if (!data) return; // Session expired, already redirected
      
      if (data.status === 'ok') {
        closeModal();
        // Reload page to show updated cinema in navbar
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

chooseCinemaBtn.addEventListener("click", createCinemaModal);
