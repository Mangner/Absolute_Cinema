const ACTIVE_TAB_KEY = 'dashboard_active_tab';
const tabConfigs = [];

function initTab(config) {
  tabConfigs.push(config);
}

function setupTabs() {
  // Najpierw przywróć zapisaną zakładkę (przed inicjalizacją)
  const savedTab = sessionStorage.getItem(ACTIVE_TAB_KEY);
  if (savedTab) {
    const tabButton = document.getElementById(savedTab);
    if (tabButton) {
      tabButton.checked = true;
    }
  }

  // Teraz inicjalizuj wszystkie zakładki
  tabConfigs.forEach(config => {
    const tabButton = document.getElementById(config.buttonId);
    const track = document.querySelector(config.trackSelector);
    let isLoaded = false;

    function handleTabAction() {
      if (tabButton && tabButton.checked && !isLoaded) {
        fetchData();
      }
    }

    function fetchData() {
      fetch(config.apiUrl)
        .then(response => response.json())
        .then(data => {
          const items = data[config.dataKey] || data;

          if (Array.isArray(items)) {
            items.forEach(item => {
              const cardHTML = config.renderCard(item);
              const div = document.createElement("div");
              div.classList.add("card");
              div.innerHTML = cardHTML;
              track.appendChild(div);
            });
            isLoaded = true;
          } else {
            console.error("Błąd: Oczekiwano tablicy dla", config.apiUrl);
          }
        })
        .catch(err => console.error("Błąd pobierania:", err));
    }

    if (tabButton) {
      tabButton.addEventListener("change", () => {
        sessionStorage.setItem(ACTIVE_TAB_KEY, config.buttonId);
        handleTabAction();
      });
      // Załaduj dane tylko dla aktualnie zaznaczonej zakładki
      handleTabAction();
    }
  });
}

document.addEventListener('DOMContentLoaded', setupTabs);

initTab({
  buttonId: "tab-screen",
  trackSelector: "#content-screen .carousel-track",
  apiUrl: "http://localhost:8080/get-OnScreen-movies",
  dataKey: "movies",
  renderCard: movie => `
        <div class="card-image">
            <img src="${movie.image}" alt="${movie.title}">
        </div>
        <div class="card-content">
            <h3>${movie.title}</h3>
            <a href="http://localhost:8080/movie/${movie.movie_id}" class="btn-gold">Zarezerwuj Bilety</a>
        </div>
    `,
});

initTab({
  buttonId: "tab-soon",
  trackSelector: "#content-soon .carousel-track",
  apiUrl: "http://localhost:8080/get-Upcoming-movies",
  dataKey: "movies",
  renderCard: movie => `
    <div class="card-image">
        <img src="${movie.image}" alt="${movie.title}">
        <div class="release-badge">Premiera: ${movie.release_date}</div>
    </div>
    <div class="card-content">
        <h3>${movie.title}</h3>
        <a href="http://localhost:8080/movie/${movie.movie_id}" class="btn-gold">Zobacz Zwiastun</a>
    </div>
    `,
});

initTab({
  buttonId: "tab-gastro",
  trackSelector: "#content-gastro .carousel-track",
  apiUrl: "http://localhost:8080/get-snacks",
  dataKey: "snacks",
  renderCard: snack => `
        <div class="card-image">
            <img src="${snack.image}" alt="${snack.name}">
        </div>
        <div class="card-content">
            <h3>${snack.name}</h3>
            <p class="price">${snack.price} PLN</p>
            <button class="btn-gold">Dodaj do koszyka</button>
        </div>
    `,
});
