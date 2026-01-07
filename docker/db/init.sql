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

/* --- 5. MIEJSCA --- */
CREATE TABLE seats (
    seat_id SERIAL PRIMARY KEY, 
    hall_id INT REFERENCES halls(hall_id) ON DELETE CASCADE,
    row_label CHAR(1) NOT NULL,
    seat_number INT NOT NULL,
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
(1, 'Sala 2', 'IMAX');

/* --- MIEJSCA (Przykładowe dla Sali 1) --- */
INSERT INTO seats (hall_id, row_label, seat_number) VALUES
(1, 'F', 1), (1, 'F', 2), (1, 'F', 3), (1, 'F', 4), (1, 'F', 5), (1, 'F', 6),
(1, 'F', 7), (1, 'F', 8), (1, 'F', 9), (1, 'F', 10), (1, 'F', 11), (1, 'F', 12);

/* --- FILMY --- */
INSERT INTO movies (title, original_title, description, director, release_date, image, price, duration, production_country, original_language, age_rating, imdb_rating, rotten_tomatoes_rating, metacritic_rating) VALUES
(
    'Diuna: Część druga', 
    'Dune: Part Two',
    'Książę Paul Atryda przyjmuje przydomek Muad''Dib i rozpoczyna duchowo-fizyczną podróż.',
    'Denis Villeneuve', 
    '2024-02-29', 
    'https://image.tmdb.org/t/p/w600_and_h900_face/xdfO6EB9e59qZpzmHxezTdPfTxZ.jpg', 
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
INSERT INTO showtimes (movie_id, hall_id, start_time, technology, base_price) VALUES
(1, 1, '2025-10-27 20:30:00', 'IMAX 3D', 35.00), 
(2, 1, '2025-10-28 18:00:00', '2D', 25.00);

/* --- JEDZENIE --- */
INSERT INTO food_items (name, category, price, image) VALUES
('Popcorn Mały', 'Przekąska', 15.00, 'https://images.unsplash.com/photo-1691480213129-106b2c7d1ee8?q=80&w=880&auto=format&fit=crop'),
('Coca-Cola 0.5L', 'Napój', 9.00, 'https://images.unsplash.com/photo-1583683433877-042a75ba47e3?q=80&w=749&auto=format&fit=crop');

/* --- REZERWACJA --- */
INSERT INTO bookings (user_id, total_price, payment_status, payment_method) VALUES
(1, 35.00, 'PAID', 'BLIK');

/* --- BILETY --- */
INSERT INTO tickets (booking_id, showtime_id, seat_id, price, ticket_token) VALUES
(1, 1, 7, 35.00, 'AC-84JFG61');