const cinemaId = document.getElementById('')
const showTimesUrl = "http://localhost:8080/get-showtimes";
const DAYS_OF_WEEK = ["Pn.", "Wt.", "Śr.", "Cz.", "Pt.", "Sb.", "Nd."];
const showtimesCache = {};

function getMovieId() {
  const section = document.querySelector('.showtimes-section');
  return section ? section.dataset.movieId : null;
}


function getCinemaId() {
  const section = document.querySelector('.showtimes-section');
  return section ? section.dataset.cinemaId : null;
}


function formatDate(date) {
  return date.toISOString().split('T')[0];
}


function getDayName(dateStr) {
  const date = new Date(dateStr);
  const dayIndex = date.getDay();

  const adjustedIndex = (dayIndex === 0) ? 6 : dayIndex - 1;
  return DAYS_OF_WEEK[adjustedIndex];
}


function extractTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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
    'dubbed': 'Dubbing',
    'subtitled': 'Napisy',
    'voiceover': 'Lektor',
    'original': 'Oryginał'
  };
  return labels[audioType] || audioType;
}

function generateShowtimesButtons(showtimes) {
  if (!showtimes || showtimes.length === 0) {
    return '<p class="no-showtimes">Brak seansów w wybranym dniu.</p>';
  }

  // Grupowanie po kombinacji: technology + language + audio_type
  const groupedShowtimes = {};
  showtimes.forEach(show => {
    const techName = show.technology || 'Standard';
    const lang = show.language || 'PL';
    const audioType = show.audio_type || 'dubbed';
    const groupKey = `${techName}|${lang}|${audioType}`;
    
    if (!groupedShowtimes[groupKey]) {
      groupedShowtimes[groupKey] = {
        technology: techName,
        language: lang,
        audioType: audioType,
        shows: []
      };
    }
    groupedShowtimes[groupKey].shows.push(show);
  });

  let html = '';
  for (const [key, group] of Object.entries(groupedShowtimes)) {
    group.shows.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
    
    const buttonsHtml = group.shows.map(show => {
      const time = extractTime(show.start_time);
      return `
        <a href="/rezerwacja/${show.showtime_id}" class="showtime-btn">
          <span class="time">${time}</span>
          </a>
      `;
    }).join('');

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


function generateDaysButtons() {
  const container = document.querySelector(".showtimes-placeholder");
  const startDate = new Date();
  const days = generateCalendarDays(startDate);

  let buttonsHtml = '';
  days.forEach(day => {
    buttonsHtml += `
      <button class="day-btn" type="button" data-date="${day}">
        <span class="day-name">${getDayName(day)}</span>
        <span class="day-date">${day}</span>
      </button>
    `;
  });

  container.innerHTML = `
    <div class="calendar-days">
      ${buttonsHtml}
    </div>
    <div id="showtimes-results">
        <p class="loading-message">Wybierz dzień, aby zobaczyć seanse.</p>
    </div>
  `;
  
  attachDayButtonListeners();

  const firstBtn = container.querySelector('.day-btn');
  if (firstBtn) {
      firstBtn.click(); 
      firstBtn.classList.add('active'); 
  }
}

function attachDayButtonListeners() {
  const buttons = document.querySelectorAll('.day-btn');
  buttons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      buttons.forEach(b => b.classList.remove('active'));
      const targetBtn = e.target.closest('button');
      targetBtn.classList.add('active');
      const selectedDate = targetBtn.dataset.date;
      onDateClick(selectedDate);
    });
  });
}

async function onDateClick(dateStr) {
  const resultsContainer = document.getElementById('showtimes-results');
  resultsContainer.innerHTML = '<p class="loading-message"><i class="fas fa-spinner fa-spin"></i> Pobieranie seansów...</p>';
  const data = await fetchShowtimes(dateStr);

  if (!data) {
     resultsContainer.innerHTML = '<p class="error-message">Nie udało się pobrać seansów.</p>';
     return;
  }
  resultsContainer.innerHTML = generateShowtimesButtons(data);
}



async function fetchShowtimes(dateStr) {

  if (showtimesCache[dateStr]) {
    console.log(`Dane dla ${dateStr} pobrane z pamięci podręcznej (bez zapytania do API).`);
    return showtimesCache[dateStr]; 
  }

  const requestBody = {
    movie_id: parseInt(getMovieId()),
    cinema_id: parseInt(getCinemaId()),
    date: dateStr
  }

  try {
    const response = await fetch(showTimesUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(requestBody)
    })

    if (response.status === 401) {
        console.warn("Sesja wygasła (401). Przekierowywanie...");
        alert("Twoja sesja wygasła. Zostaniesz przekierowany do strony logowania.");
        window.location.href = "http://localhost:8080/login";
        return null;
    }
    
    if (!response.ok) {
      throw new Error(`Błąd HTTP! Status: ${response.status}`);
    }

    const result = await response.json()
    const showtimesList = result.showtimes || [];
    showtimesCache[dateStr] = showtimesList;    
    console.log('Otrzymane seanse (lista):', showtimesList);
    return showtimesList; 

  } catch (error) {
    console.error('Wystąpił błąd podczas pobierania:', error);
    return null;
  }
}


function initShowtimesSection() {
  const container = document.querySelector(".showtimes-placeholder");
  const cinemaId = getCinemaId();

  if (!cinemaId || cinemaId.trim() === '') {
    
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


document.addEventListener('DOMContentLoaded', initShowtimesSection);