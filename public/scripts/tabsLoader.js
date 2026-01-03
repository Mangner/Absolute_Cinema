function initTab(config) {
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
    tabButton.addEventListener("change", handleTabAction);
    handleTabAction();
  }
}

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
