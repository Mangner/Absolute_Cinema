/* init.sql */

/* --- CZYSZCZENIE STAREJ STRUKTURY --- */
DROP TABLE IF EXISTS movie_cast CASCADE;
DROP TABLE IF EXISTS movie_genres CASCADE;
DROP TABLE IF EXISTS tickets CASCADE;
DROP TABLE IF EXISTS booking_snacks CASCADE;
DROP TABLE IF EXISTS bookings CASCADE;
DROP TABLE IF EXISTS showtimes CASCADE;
DROP TABLE IF EXISTS seats CASCADE;
DROP TABLE IF EXISTS halls CASCADE;
DROP TABLE IF EXISTS cinemas CASCADE;
DROP TABLE IF EXISTS food_items CASCADE;
DROP TABLE IF EXISTS movies CASCADE;
DROP TABLE IF EXISTS genres CASCADE;
DROP TABLE IF EXISTS cast_members CASCADE;
DROP TABLE IF EXISTS users CASCADE;

/* --- 1. UŻYTKOWNICY I ROLE --- */
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'employee')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* --- 2. GATUNKI FILMÓW --- */
CREATE TABLE genres (
    genre_id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

/* --- 3. OBSADA FILMÓW --- */
CREATE TABLE cast_members (
    cast_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50), -- np. 'Aktor', 'Reżyser', 'Producent'
    biography TEXT,
    image VARCHAR(1024)
);

/* --- 4. KINA I SALE --- */
CREATE TABLE cinemas (
    cinema_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL
);

CREATE TABLE halls (
    hall_id SERIAL PRIMARY KEY, 
    cinema_id INT REFERENCES cinemas(cinema_id) ON DELETE CASCADE, 
    name VARCHAR(50) NOT NULL,
    type VARCHAR(20) DEFAULT 'Standard'
);

/* --- 5. MIEJSCA (Zaktualizowane) --- */
CREATE TABLE seats (
    seat_id SERIAL PRIMARY KEY, 
    hall_id INT REFERENCES halls(hall_id) ON DELETE CASCADE,
    row_label CHAR(1) NOT NULL,
    seat_number INT NOT NULL,
    grid_row INT NOT NULL,  -- Współrzędna Y
    grid_col INT NOT NULL,  -- Współrzędna X
    extra_charge DECIMAL(10, 2) DEFAULT 0.00, -- Dopłata za miejsce (np. VIP)
    UNIQUE(hall_id, row_label, seat_number)
);


/* --- 6. FILMY --- */
CREATE TABLE movies (
    movie_id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    original_title VARCHAR(255),
    description TEXT,
    director VARCHAR(100),
    release_date DATE NOT NULL,
    image VARCHAR(1024),
    trailer_url VARCHAR(512),
    price DECIMAL(10, 2), 
    duration INT NOT NULL,
    production_country VARCHAR(100),
    original_language VARCHAR(10),
    age_rating VARCHAR(10),
    imdb_rating DECIMAL(3, 1),
    rotten_tomatoes_rating DECIMAL(3, 1),
    metacritic_rating DECIMAL(3, 1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* --- 7. POWIĄZANIE FILMÓW Z GATUNKAMI (Relacja jeden do wielu) --- */
CREATE TABLE movie_genres (
    movie_genre_id SERIAL PRIMARY KEY,
    movie_id INT REFERENCES movies(movie_id) ON DELETE CASCADE,
    genre_id INT REFERENCES genres(genre_id) ON DELETE CASCADE,
    UNIQUE(movie_id, genre_id)
);

/* --- 8. POWIĄZANIE FILMÓW Z OBSADĄ (Relacja jeden do wielu) --- */
CREATE TABLE movie_cast (
    movie_cast_id SERIAL PRIMARY KEY,
    movie_id INT REFERENCES movies(movie_id) ON DELETE CASCADE,
    cast_id INT REFERENCES cast_members(cast_id) ON DELETE CASCADE,
    character_name VARCHAR(100),
    position INT DEFAULT 0, -- Pozycja w obsadzie (1 = główna rola)
    UNIQUE(movie_id, cast_id)
);

/* --- 9. SEANSE --- */
CREATE TABLE showtimes (
    showtime_id SERIAL PRIMARY KEY, 
    movie_id INT REFERENCES movies(movie_id) ON DELETE CASCADE,
    hall_id INT REFERENCES halls(hall_id) ON DELETE CASCADE,
    start_time TIMESTAMP NOT NULL,
    technology VARCHAR(50) DEFAULT '2D',
    language VARCHAR(10) DEFAULT 'PL',
    audio_type VARCHAR(20) DEFAULT 'dubbed' CHECK (audio_type IN ('dubbed', 'subtitled', 'voiceover', 'original')),
    base_price DECIMAL(10, 2) NOT NULL
);

/* --- 10. JEDZENIE --- */
CREATE TABLE food_items (
    food_item_id SERIAL PRIMARY KEY, 
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(1024)
);

/* --- 11. REZERWACJE I PŁATNOŚCI --- */
CREATE TABLE bookings (
    booking_id SERIAL PRIMARY KEY, 
    user_id INT REFERENCES users(user_id) ON DELETE SET NULL, 
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    payment_status VARCHAR(20) DEFAULT 'PENDING' CHECK (payment_status IN ('PENDING', 'PAID', 'CANCELLED')),
    payment_method VARCHAR(50)
);

CREATE TABLE tickets (
    ticket_id SERIAL PRIMARY KEY, 
    booking_id INT REFERENCES bookings(booking_id) ON DELETE CASCADE,
    showtime_id INT REFERENCES showtimes(showtime_id) ON DELETE CASCADE,
    seat_id INT REFERENCES seats(seat_id) ON DELETE RESTRICT,
    price DECIMAL(10, 2) NOT NULL,
    ticket_token VARCHAR(64) UNIQUE
);

/* --- DODANIE ADMINA (ID: 1) --- */
INSERT INTO users (name, surname, email, password, role) VALUES 
('Admin', 'Główny', 'admin@example.com', '$2y$10$vI8aWBnW3fBr4ffg5PMDlO.pOeYbeDL.xeO/IyceOArj8k8sAGvFC', 'admin');

/* --- DODANIE GATUNKÓW --- */
INSERT INTO genres (name, description) VALUES
('Przygodowy', 'Filmy pełne akcji i niespodziewanych zwrotów'),
('Sci-Fi', 'Science fiction pełny futurystycznych wizji'),
('Fantasy', 'Światy magii i mitycznych stworzeń'),
('Drama', 'Emocjonalne opowieści człowieka'),
('Komedia', 'Filmy do śmiechu'),
('Horror', 'Filmy przerażające'),
('Thriller', 'Napięte filmy kryminalne'),
('Animacja', 'Filmy animowane');

/* --- DODANIE OBSADY --- */
INSERT INTO cast_members (name, role, biography, image) VALUES
('Sam Worthington', 'Aktor', 'Australijski aktor znany z filmów Avatar i Clash of the Titans.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=500'),
('Zoe Saldaña', 'Aktorka', 'Amerykańska aktorka znana z Avatar i Guardians of the Galaxy.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=500'),
('Sigourney Weaver', 'Aktorka', 'Legendarny aktorka z serii Obca.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=500'),
('Stephen Lang', 'Aktor', 'Americański aktor znany z Avatar i Hard Target.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=500'),
('Kate Winslet', 'Aktorka', 'Brytyjska aktorka znana z Tytanika i The Reader.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=500'),
('Denis Villeneuve', 'Reżyser', 'Kanadyjski reżyser znany z filmów Diuna, Blade Runner 2049.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=500'),
('Jack Champion', 'Aktor', 'Młody aktor ze spinoffu Avatara.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=500'),
('Jack Black', 'Aktor', 'Komediowy aktor znany z Kung Fu Panda.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=500'),
('Angelina Jolie', 'Aktorka', 'Znana aktorka i reżyserka.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=500'),
('Cillian Murphy', 'Aktor', 'Brytyjski aktor znany z Oppenheimera.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=500');

/* --- KINA --- */
INSERT INTO cinemas (name, city, address) VALUES 
('Absolute Cinema Warszawa', 'Warszawa', 'Złota 44'),
('Absolute Cinema Kraków', 'Kraków', 'Pawia 5 (Galeria Krakowska)');

/* --- SALE --- */
INSERT INTO halls (cinema_id, name, type) VALUES 
(1, 'Sala 1', 'Standard'),
(1, 'Sala 2', 'IMAX'),
(2, 'Sala 1', 'Standard'),
(2, 'Sala 2', 'Standard'),
(2, 'Sala 3', 'IMAX 3D');


/* --- GENEROWANIE MIEJSC Z UWZGLĘDNIENIEM SIATKI I CEN VIP --- */

-- SALA 1 (Warszawa - Standard)
-- 8 rzędów (A-H), 10 miejsc w rzędzie.
-- Rzędy G i H (7 i 8) to VIP (+5.00 zł).
INSERT INTO seats (hall_id, row_label, seat_number, grid_row, grid_col, extra_charge)
SELECT 
    1,                  -- hall_id
    chr(r),             -- row_label (np. 'A')
    s,                  -- seat_number (np. 1)
    (r - 64),           -- grid_row (65-64 = 1, czyli Rząd 1)
    s,                  -- grid_col (Kolumna tożsama z numerem miejsca)
    CASE 
        WHEN (r - 64) >= 7 THEN 5.00 -- Dopłata dla rzędów 7 i 8
        ELSE 0.00 
    END                 -- extra_charge
FROM generate_series(65, 72) AS r, -- A do H
     generate_series(1, 10) AS s;


-- SALA 2 (Warszawa - IMAX)
-- 11 rzędów (A-K), 15 miejsc.
-- KORYTARZ PIONOWY: Po miejscu nr 5 jest przerwa.
-- Miejsca 1-5 są w kolumnach 1-5. Miejsca 6-15 są w kolumnach 7-16.
-- Ostatnie 3 rzędy (I, J, K) to VIP (+8.00 zł).
INSERT INTO seats (hall_id, row_label, seat_number, grid_row, grid_col, extra_charge)
SELECT 
    2,
    chr(r),
    s,
    (r - 64),
    CASE 
        WHEN s > 5 THEN s + 1 -- Przesuwamy o 1 w prawo, tworząc korytarz na kolumnie 6
        ELSE s 
    END, 
    CASE 
        WHEN (r - 64) >= 9 THEN 8.00 
        ELSE 0.00 
    END
FROM generate_series(65, 75) AS r, -- A do K
     generate_series(1, 15) AS s;


-- SALA 3 (Kraków - Standard)
-- 7 rzędów (A-G), 12 miejsc.
-- Ostatni rząd (G) to VIP (+4.00 zł).
INSERT INTO seats (hall_id, row_label, seat_number, grid_row, grid_col, extra_charge)
SELECT 
    3,
    chr(r),
    s,
    (r - 64),
    s,
    CASE 
        WHEN (r - 64) = 7 THEN 4.00 
        ELSE 0.00 
    END
FROM generate_series(65, 71) AS r, -- A do G
     generate_series(1, 12) AS s;


-- SALA 4 (Kraków - Standard)
-- Identyczna jak Sala 3
INSERT INTO seats (hall_id, row_label, seat_number, grid_row, grid_col, extra_charge)
SELECT 
    4,
    chr(r),
    s,
    (r - 64),
    s,
    CASE 
        WHEN (r - 64) = 7 THEN 4.00 
        ELSE 0.00 
    END
FROM generate_series(65, 71) AS r, 
     generate_series(1, 12) AS s;


-- SALA 5 (Kraków - IMAX 3D)
-- 13 rzędów (A-M), 20 miejsc.
-- Duża sala, rzędy L i M (12, 13) to Super VIP (+10.00 zł).
-- Rzędy J, K (10, 11) to VIP (+6.00 zł).
INSERT INTO seats (hall_id, row_label, seat_number, grid_row, grid_col, extra_charge)
SELECT 
    5,
    chr(r),
    s,
    (r - 64),
    s,
    CASE 
        WHEN (r - 64) >= 12 THEN 10.00
        WHEN (r - 64) >= 10 THEN 6.00
        ELSE 0.00 
    END
FROM generate_series(65, 77) AS r, -- A do M
     generate_series(1, 20) AS s;

/* --- FILMY --- */
INSERT INTO movies (title, original_title, description, director, release_date, image, trailer_url, price, duration, production_country, original_language, age_rating, imdb_rating, rotten_tomatoes_rating, metacritic_rating) VALUES
(
    'Diuna: Część druga', 
    'Dune: Part Two',
    'Książę Paul Atryda przyjmuje przydomek Muad''Dib i rozpoczyna duchowo-fizyczną podróż.',
    'Denis Villeneuve', 
    '2024-02-29', 
    'https://image.tmdb.org/t/p/w600_and_h900_face/xdfO6EB9e59qZpzmHxezTdPfTxZ.jpg',
    'https://www.youtube.com/embed/Way9Dexny3w',
    25.00, 166,
    'USA',
    'EN',
    'PG-13',
    8.5,
    82,
    79
),
(
    'Kung Fu Panda 4', 
    'Kung Fu Panda 4',
    'Po wyrusza w nową przygodę w starożytnych Chinach.',
    'Mike Mitchell', 
    '2024-03-08', 
    'https://upload.wikimedia.org/wikipedia/en/7/7f/Kung_Fu_Panda_4_poster.jpg',
    'https://www.youtube.com/embed/_inKs4eeHiI',
    20.00, 94,
    'USA',
    'EN',
    'PG',
    7.2,
    56,
    58
),
(
    'Oppenheimer', 
    'Oppenheimer',
    'Historia amerykańskiego naukowca J. Roberta Oppenheimera i jego roli w projekcie Manhattan.',
    'Christopher Nolan', 
    '2023-07-21', 
    'https://upload.wikimedia.org/wikipedia/en/4/4a/Oppenheimer_%28film%29.jpg',
    'https://www.youtube.com/embed/uYPbbksJxIg',
    22.50, 180,
    'USA',
    'EN',
    'PG-13',
    8.5,
    92,
    90
),
(
    'Gladiator 2', 
    'Gladiator II',
    'Kontynuacja epickiej historii o zdradzie, honorze i walce o wolność.',
    'Ridley Scott', 
    '2026-11-22', 
    'https://image.tmdb.org/t/p/w600_and_h900_face/q6mkkb5XU6ERF7xP9nAjnNq9n7V.jpg',
    'https://www.youtube.com/embed/4rgYUipGJNo',
    28.00, 140,
    'USA',
    'EN',
    '15',
    7.8,
    75,
    72
),
(
    'Shrek 5', 
    'Shrek 5',
    'Powrót ulubionego ogra i jego nowych przygód w magicznym świecie.',
    'Andrew Adamson', 
    '2026-07-01', 
    'https://upload.wikimedia.org/wikipedia/en/thumb/4/4d/Shrek_%28character%29.png/220px-Shrek_%28character%29.png',
    'https://www.youtube.com/embed/W37DlG1i61s',
    24.00, 95,
    'USA',
    'EN',
    'PG',
    8.0,
    78,
    76
);

/* --- POWIĄZANIE FILMÓW Z GATUNKAMI --- */
INSERT INTO movie_genres (movie_id, genre_id) VALUES
(1, 1), (1, 2), (1, 3), -- Diuna: Przygodowy, Sci-Fi, Fantasy
(2, 1), (2, 5), (2, 8), -- Kung Fu Panda: Przygodowy, Komedia, Animacja
(3, 4), (3, 2), -- Oppenheimer: Drama, Sci-Fi
(4, 1), (4, 4), -- Gladiator: Przygodowy, Drama
(5, 5), (5, 8); -- Shrek: Komedia, Animacja

/* --- POWIĄZANIE FILMÓW Z OBSADĄ --- */
INSERT INTO movie_cast (movie_id, cast_id, character_name, position) VALUES
(1, 1, 'Jake Sully', 1),
(1, 2, 'Neytiri', 2),
(1, 3, 'Moat', 3),
(1, 4, 'Miles Quaritch', 4),
(1, 5, 'Ronal', 5),
(2, 8, 'Po (głos)', 1),
(3, 10, 'J. Robert Oppenheimer', 1),
(4, 9, 'Lucilla', 1),
(5, 8, 'Shrek (głos)', 1);

/* --- SEANSE --- */
INSERT INTO showtimes (movie_id, hall_id, start_time, technology, language, audio_type, base_price) VALUES
-- Diuna (movie_id=1) - Warszawa
(1, 1, '2026-01-17 10:00:00', '2D', 'PL', 'dubbed', 25.00),
(1, 1, '2026-01-18 14:00:00', '2D', 'PL', 'subtitled', 25.00),
(1, 2, '2026-01-19 18:30:00', 'IMAX 3D', 'EN', 'original', 35.00),
(1, 2, '2026-01-19 21:00:00', 'IMAX 3D', 'PL', 'dubbed', 35.00),
(1, 1, '2026-01-19 10:00:00', '2D', 'PL', 'dubbed', 25.00),
(1, 2, '2026-01-19 16:00:00', 'IMAX 3D', 'EN', 'subtitled', 35.00),
(1, 1, '2026-01-19 14:30:00', '2D', 'PL', 'dubbed', 25.00),
(1, 2, '2026-01-19 19:00:00', 'IMAX 3D', 'EN', 'original', 35.00),
(1, 1, '2026-01-19 10:00:00', '2D', 'PL', 'voiceover', 25.00),
(1, 2, '2026-01-19 20:30:00', 'IMAX 3D', 'PL', 'dubbed', 35.00),
(1, 1, '2026-01-20 15:00:00', '2D', 'PL', 'dubbed', 25.00),
(1, 2, '2026-01-20 18:00:00', 'IMAX 3D', 'EN', 'subtitled', 35.00),
(1, 1, '2026-01-20 11:00:00', '2D', 'PL', 'dubbed', 25.00),
(1, 2, '2026-01-20 19:30:00', 'IMAX 3D', 'EN', 'original', 35.00),
(1, 1, '2026-01-21 13:00:00', '2D', 'PL', 'subtitled', 25.00),
(1, 2, '2026-01-21 20:00:00', 'IMAX 3D', 'PL', 'dubbed', 35.00),

-- Kung Fu Panda (movie_id=2) - Warszawa
(2, 1, '2026-01-21 12:00:00', '2D', 'PL', 'dubbed', 20.00),
(2, 1, '2026-01-21 16:00:00', '2D', 'PL', 'dubbed', 20.00),
(2, 1, '2026-01-22 12:30:00', '2D', 'PL', 'dubbed', 20.00),
(2, 1, '2026-01-22 18:00:00', '2D', 'EN', 'subtitled', 20.00),
(2, 1, '2026-01-23 11:00:00', '2D', 'PL', 'dubbed', 20.00),
(2, 1, '2026-01-23 16:30:00', '2D', 'PL', 'dubbed', 20.00),
(2, 1, '2026-01-24 12:00:00', '2D', 'PL', 'dubbed', 20.00),
(2, 1, '2026-01-24 17:00:00', '2D', 'EN', 'original', 20.00),

-- Oppenheimer (movie_id=3) - Warszawa
(3, 2, '2026-01-28 15:00:00', 'IMAX 3D', 'EN', 'subtitled', 32.00),
(3, 2, '2026-01-29 19:30:00', 'IMAX 3D', 'EN', 'original', 32.00),
(3, 2, '2026-01-29 14:00:00', 'IMAX 3D', 'PL', 'voiceover', 32.00),
(3, 2, '2026-01-29 20:00:00', 'IMAX 3D', 'EN', 'subtitled', 32.00),

-- Diuna (movie_id=1) - Kraków
(1, 3, '2026-01-28 11:00:00', '2D', 'PL', 'dubbed', 23.00),
(1, 3, '2026-01-28 15:30:00', '2D', 'PL', 'subtitled', 23.00),
(1, 5, '2026-01-28 19:00:00', 'IMAX 3D', 'EN', 'original', 33.00),
(1, 5, '2026-01-28 21:30:00', 'IMAX 3D', 'PL', 'dubbed', 33.00),
(1, 3, '2026-01-29 10:30:00', '2D', 'PL', 'dubbed', 23.00),
(1, 5, '2026-01-29 17:00:00', 'IMAX 3D', 'EN', 'subtitled', 33.00),
(1, 3, '2026-01-29 13:00:00', '2D', 'PL', 'voiceover', 23.00),
(1, 5, '2026-01-29 18:30:00', 'IMAX 3D', 'EN', 'original', 33.00),
(1, 3, '2026-01-30 11:00:00', '2D', 'PL', 'dubbed', 23.00),
(1, 5, '2026-01-30 20:00:00', 'IMAX 3D', 'PL', 'dubbed', 33.00),

-- Kung Fu Panda (movie_id=2) - Kraków
(2, 4, '2026-01-28 13:00:00', '2D', 'PL', 'dubbed', 18.00),
(2, 4, '2026-01-28 17:00:00', '2D', 'PL', 'dubbed', 18.00),
(2, 4, '2026-01-28 12:00:00', '2D', 'PL', 'dubbed', 18.00),
(2, 4, '2026-01-29 16:30:00', '2D', 'EN', 'subtitled', 18.00),

-- Oppenheimer (movie_id=3) - Kraków
(3, 5, '2026-01-29 14:00:00', 'IMAX 3D', 'EN', 'subtitled', 30.00),
(3, 5, '2026-01-29 20:00:00', 'IMAX 3D', 'EN', 'original', 30.00),
(3, 5, '2026-01-29 15:00:00', 'IMAX 3D', 'PL', 'voiceover', 30.00),
(3, 5, '2026-01-29 19:30:00', 'IMAX 3D', 'EN', 'subtitled', 30.00);

/* --- JEDZENIE --- */
INSERT INTO food_items (name, category, price, image) VALUES
('Popcorn Mały', 'Przekąska', 15.00, 'https://images.unsplash.com/photo-1691480213129-106b2c7d1ee8?q=80&w=880&auto=format&fit=crop'),
('Coca-Cola 0.5L', 'Napój', 9.00, 'https://images.unsplash.com/photo-1583683433877-042a75ba47e3?q=80&w=749&auto=format&fit=crop');

/* --- REZERWACJA --- */
INSERT INTO bookings (user_id, total_price, payment_status, payment_method) VALUES
(1, 35.00, 'PAID', 'BLIK');

/* --- BILETY --- */
-- Pobieramy dynamicznie ID miejsca dla Sali 1, Rząd F, Miejsce 5 (zamiast wpisywać '7' na sztywno)
INSERT INTO tickets (booking_id, showtime_id, seat_id, price, ticket_token)
VALUES (
    1, 
    1, 
    (SELECT seat_id FROM seats WHERE hall_id = 1 AND row_label = 'F' AND seat_number = 5 LIMIT 1), 
    35.00, 
    'AC-84JFG61'
);