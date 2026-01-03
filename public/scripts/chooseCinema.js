const chooseCinemaBtn = document.getElementById("cinema-choice-btn");
const cinemasURL = "http://localhost:8080/get-cinemas";

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
            <form id="cinema-form" method="POST" action="">
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
          option.value = cinema.name;
          option.textContent = `${cinema.name} - ${cinema.city}`;
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
    console.log("Formularz wysłany");
  }
});

chooseCinemaBtn.addEventListener("click", createCinemaModal);
