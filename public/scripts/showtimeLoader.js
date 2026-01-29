const cinemaId = document.getElementById("");
const showTimesUrl = "http://localhost:8080/get-showtimes";
const DAYS_OF_WEEK = ["Pn.", "Wt.", "Śr.", "Cz.", "Pt.", "Sb.", "Nd."];
const showtimesCache = {};

function getMovieId() {
  const section = document.querySelector(".showtimes-section");
  return section ? section.dataset.movieId : null;
}

function getCinemaId() {
  const section = document.querySelector(".showtimes-section");
  return section ? section.dataset.cinemaId : null;
}

function formatDate(date) {
  return date.toISOString().split("T")[0];
}

function getDayName(dateStr) {
  const date = new Date(dateStr);
  const dayIndex = date.getDay();

  const adjustedIndex = dayIndex === 0 ? 6 : dayIndex - 1;
  return DAYS_OF_WEEK[adjustedIndex];
}

function extractTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function generateCalendarDays(startDate) {
  const days = [];
  for (let i = 0; i < 7; i++) {
    const day = new Date(startDate);
    day.setDate(day.getDate() + i);
    days.push(formatDate(day));
  }

  return days;
}

function getAudioTypeLabel(audioType) {
  const labels = {
    dubbed: "Dubbing",
    subtitled: "Napisy",
    voiceover: "Lektor",
    original: "Oryginał",
  };
  return labels[audioType] || audioType;
}

function generateShowtimesButtons(showtimes, selectedDate = null) {
  if (!showtimes || showtimes.length === 0) {
    const today = formatDate(new Date());
    
    // Sprawdź czy wybrana data to dzisiaj
    if (selectedDate === today) {
      return `
        <div class="no-showtimes-container">
          <i class="fas fa-clock"></i>
          <p class="no-showtimes">Brak dostępnych seansów na dzisiaj.</p>
          <p class="no-showtimes-hint">Wszystkie dzisiejsze seanse już się rozpoczęły. Zapraszamy jutro!</p>
        </div>
      `;
    }
    
    return '<p class="no-showtimes">Brak seansów w wybranym dniu.</p>';
  }

  // Grupowanie po kombinacji: technology + language + audio_type
  const groupedShowtimes = {};
  showtimes.forEach(show => {
    const techName = show.technology || "Standard";
    const lang = show.language || "PL";
    const audioType = show.audio_type || "dubbed";
    const groupKey = `${techName}|${lang}|${audioType}`;

    if (!groupedShowtimes[groupKey]) {
      groupedShowtimes[groupKey] = {
        technology: techName,
        language: lang,
        audioType: audioType,
        shows: [],
      };
    }
    groupedShowtimes[groupKey].shows.push(show);
  });

  const container = document.getElementById("showtimes");
  const movieId = container.dataset.movieId;

  let html = "";
  for (const [key, group] of Object.entries(groupedShowtimes)) {
    group.shows.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));

    const buttonsHtml = group.shows
      .map(show => {
        const time = extractTime(show.start_time);
        return `
        <a href="/movie/${movieId}/${show.showtime_id}" class="showtime-btn">
          <span class="time">${time}</span>
          </a>
      `;
      })
      .join("");

    const audioLabel = getAudioTypeLabel(group.audioType);

    html += `
      <div class="showtime-group">
        <h3 class="showtime-tech">${group.technology}</h3>
        <div class="showtime-buttons-container">
          ${buttonsHtml}
        </div>
        <p class="tech-subinfo">${audioLabel} ${group.language}</p>
      </div>
    `;
  }
  return html;
}

let currentWeekOffset = 0;
const MAX_WEEKS = 4;

function generateDaysButtons() {
  const container = document.querySelector(".showtimes-placeholder");
  renderWeekView(container, currentWeekOffset);
}

function renderWeekView(container, weekOffset) {
  const today = new Date();
  const startDate = new Date(today);
  startDate.setDate(today.getDate() + weekOffset * 7);

  const days = generateCalendarDays(startDate);

  // Generuj przyciski dni
  let buttonsHtml = "";
  days.forEach((day, index) => {
    const isToday = day === formatDate(today);
    const dayLabel = isToday ? "Dziś" : getDayName(day);
    const dayNum = day.split("-")[2];
    const monthNum = day.split("-")[1];

    buttonsHtml += `
      <button class="day-btn" type="button" data-date="${day}">
        <span class="day-name">${dayLabel}</span>
        <span class="day-date">${dayNum}.${monthNum}</span>
      </button>
    `;
  });

  // Określ czy strzałki są aktywne
  const prevDisabled = weekOffset === 0 ? "disabled" : "";
  const nextDisabled = weekOffset >= MAX_WEEKS - 1 ? "disabled" : "";

  // Etykieta tygodnia
  const weekStart = days[0].split("-").slice(1).join(".");
  const weekEnd = days[6].split("-").slice(1).join(".");

  container.innerHTML = `
    <div class="week-navigation">
      <button class="week-arrow prev" ${prevDisabled} title="Poprzedni tydzień">
        <i class="fas fa-chevron-left"></i>
      </button>
      <div class="week-days">
        ${buttonsHtml}
      </div>
      <button class="week-arrow next" ${nextDisabled} title="Następny tydzień">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
    <div class="week-label">${weekStart} - ${weekEnd}</div>
    <div id="showtimes-results">
        <p class="loading-message">Wybierz dzień, aby zobaczyć seanse.</p>
    </div>
  `;

  attachDayButtonListeners();
  attachWeekNavigationListeners(container);

  // Automatycznie załaduj seanse na pierwszy dzień tygodnia
  const firstBtn = container.querySelector(".day-btn");
  if (firstBtn) {
    firstBtn.click();
    firstBtn.classList.add("active");
  }
}

function attachWeekNavigationListeners(container) {
  const prevBtn = container.querySelector(".week-arrow.prev");
  const nextBtn = container.querySelector(".week-arrow.next");

  prevBtn?.addEventListener("click", () => {
    if (currentWeekOffset > 0) {
      currentWeekOffset--;
      renderWeekView(container, currentWeekOffset);
    }
  });

  nextBtn?.addEventListener("click", () => {
    if (currentWeekOffset < MAX_WEEKS - 1) {
      currentWeekOffset++;
      renderWeekView(container, currentWeekOffset);
    }
  });
}

function attachDayButtonListeners() {
  const buttons = document.querySelectorAll(".day-btn");

  buttons.forEach(btn => {
    btn.addEventListener("click", e => {
      buttons.forEach(b => b.classList.remove("active"));
      const targetBtn = e.target.closest("button");
      targetBtn.classList.add("active");
      const selectedDate = targetBtn.dataset.date;
      onDateClick(selectedDate);
    });
  });
}

async function onDateClick(dateStr) {
  const resultsContainer = document.getElementById("showtimes-results");
  resultsContainer.innerHTML =
    '<p class="loading-message"><i class="fas fa-spinner fa-spin"></i> Pobieranie seansów...</p>';
  const data = await fetchShowtimes(dateStr);

  if (!data) {
    resultsContainer.innerHTML =
      '<p class="error-message">Nie udało się pobrać seansów.</p>';
    return;
  }
  resultsContainer.innerHTML = generateShowtimesButtons(data, dateStr);
}

async function fetchShowtimes(dateStr) {
  if (showtimesCache[dateStr]) {
    console.log(
      `Dane dla ${dateStr} pobrane z pamięci podręcznej (bez zapytania do API).`
    );
    return showtimesCache[dateStr];
  }

  const requestBody = {
    movie_id: parseInt(getMovieId()),
    cinema_id: parseInt(getCinemaId()),
    date: dateStr,
  };

  try {
    const response = await fetch(showTimesUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestBody),
    });

    if (response.status === 401) {
      console.warn("Sesja wygasła (401). Przekierowywanie...");
      alert(
        "Twoja sesja wygasła. Zostaniesz przekierowany do strony logowania."
      );
      window.location.href = "http://localhost:8080/login";
      return null;
    }

    if (!response.ok) {
      throw new Error(`Błąd HTTP! Status: ${response.status}`);
    }

    const result = await response.json();
    const showtimesList = result.showtimes || [];
    showtimesCache[dateStr] = showtimesList;
    console.log("Otrzymane seanse (lista):", showtimesList);
    return showtimesList;
  } catch (error) {
    console.error("Wystąpił błąd podczas pobierania:", error);
    return null;
  }
}

function initShowtimesSection() {
  const container = document.querySelector(".showtimes-placeholder");
  const cinemaId = getCinemaId();

  if (!cinemaId || cinemaId.trim() === "") {
    console.log("Brak wybranego kina.");

    container.innerHTML = `
      <div class="alert-box" style="text-align: center; padding: 40px;">
        <i class="fas fa-exclamation-circle" style="font-size: 4rem; color: #d4af37; margin-bottom: 20px;"></i>
        <p style="font-size: 1.2rem; margin-bottom: 25px; color: #ccc;">
            Nie wybrano kina. Wybierz lokalizację, aby zobaczyć repertuar.
        </p>
        <button onclick="createCinemaModal()" class="btn-primary" style="margin: 0 auto; display: flex;">
            Wybierz kino
        </button>
      </div>
    `;
  } else {
    generateDaysButtons();
  }
}

document.addEventListener("DOMContentLoaded", initShowtimesSection);
