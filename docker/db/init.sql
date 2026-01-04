/* init.sql */

/* --- CZYSZCZENIE STAREJ STRUKTURY --- */
DROP TABLE IF EXISTS tickets CASCADE;
DROP TABLE IF EXISTS booking_snacks CASCADE;
DROP TABLE IF EXISTS bookings CASCADE;
DROP TABLE IF EXISTS showtimes CASCADE;
DROP TABLE IF EXISTS seats CASCADE;
DROP TABLE IF EXISTS halls CASCADE;
DROP TABLE IF EXISTS cinemas CASCADE;
DROP TABLE IF EXISTS food_items CASCADE;
DROP TABLE IF EXISTS movies CASCADE;
DROP TABLE IF EXISTS users CASCADE;

/* --- 1. UŻYTKOWNICY I ROLE --- */
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY, -- Zmiana z id na user_id
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'employee')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* --- 2. KINA I SALE --- */
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

/* --- 3. MIEJSCA --- */
CREATE TABLE seats (
    seat_id SERIAL PRIMARY KEY, 
    hall_id INT REFERENCES halls(hall_id) ON DELETE CASCADE,
    row_label CHAR(1) NOT NULL,
    seat_number INT NOT NULL,
    UNIQUE(hall_id, row_label, seat_number)
);

/* --- 4. FILMY --- */
CREATE TABLE movies (
    movie_id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    director VARCHAR(100),
    release_date DATE NOT NULL,
    image VARCHAR(1024),
    price DECIMAL(10, 2), 
    duration INT NOT NULL
);

/* --- 5. SEANSE --- */
CREATE TABLE showtimes (
    showtime_id SERIAL PRIMARY KEY, 
    movie_id INT REFERENCES movies(movie_id) ON DELETE CASCADE,
    hall_id INT REFERENCES halls(hall_id) ON DELETE CASCADE,
    start_time TIMESTAMP NOT NULL,
    technology VARCHAR(50) DEFAULT '2D',
    base_price DECIMAL(10, 2) NOT NULL
);

/* --- 6. JEDZENIE --- */
CREATE TABLE food_items (
    food_item_id SERIAL PRIMARY KEY, 
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(1024)
);

/* --- 7. REZERWACJE I PŁATNOŚCI --- */
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
-- Hasło w postaci jawnej: admin123
-- Hash bcrypt (cost 10): $2y$10$vI8aWBnW3fBr4ffg5PMDlO.pOeYbeDL.xeO/IyceOArj8k8sAGvFC
INSERT INTO users (name, surname, email, password, role) VALUES 
('Admin', 'Główny', 'admin@example.com', '$2y$10$vI8aWBnW3fBr4ffg5PMDlO.pOeYbeDL.xeO/IyceOArj8k8sAGvFC', 'admin');


-- Kina
INSERT INTO cinemas (name, city, address) VALUES 
('Absolute Cinema Warszawa', 'Warszawa', 'Złota 44'),
('Absolute Cinema Kraków', 'Kraków', 'Pawia 5 (Galeria Krakowska)');

-- Sale (Dla Warszawy - ID kina zakladamy że 1, bo SERIAL startuje od 1)
INSERT INTO halls (cinema_id, name, type) VALUES 
(1, 'Sala 1', 'Standard'),
(1, 'Sala 2', 'IMAX');

-- Miejsca (Przykładowe dla Sali 1)
INSERT INTO seats (hall_id, row_label, seat_number) VALUES
(1, 'F', 1), (1, 'F', 2), (1, 'F', 3), (1, 'F', 4), (1, 'F', 5), (1, 'F', 6),
(1, 'F', 7), (1, 'F', 8), (1, 'F', 9), (1, 'F', 10), (1, 'F', 11), (1, 'F', 12);

-- Filmy
INSERT INTO movies (title, description, director, release_date, image, price, duration) VALUES
(
    'Diuna: Część druga', 
    'Książę Paul Atryda przyjmuje przydomek Muad''Dib i rozpoczyna duchowo-fizyczną podróż.', 
    'Denis Villeneuve', 
    '2024-02-29', 
    'https://image.tmdb.org/t/p/w600_and_h900_face/xdfO6EB9e59qZpzmHxezTdPfTxZ.jpg', 
    25.00, 166
),
(
    'Kung Fu Panda 4', 
    'Po wyrusza w nową przygodę w starożytnych Chinach.', 
    'Mike Mitchell', 
    '2024-03-08', 
    'https://upload.wikimedia.org/wikipedia/en/7/7f/Kung_Fu_Panda_4_poster.jpg', 
    20.00, 94
),
(
    'Oppenheimer', 
    'Historia amerykańskiego naukowca J. Roberta Oppenheimera.', 
    'Christopher Nolan', 
    '2023-07-21', 
    'https://upload.wikimedia.org/wikipedia/en/4/4a/Oppenheimer_%28film%29.jpg', 
    22.50, 180
),
(
    'Gladiator 2', 
    'Kontynuacja epickiej historii o zdradzie.', 
    'Ridley Scott', 
    '2026-11-22', 
    'https://image.tmdb.org/t/p/w600_and_h900_face/q6mkkb5XU6ERF7xP9nAjnNq9n7V.jpg', 
    28.00, 140
),
(
    'Shrek 5', 
    'Powrót ulubionego ogra.', 
    'Nieznany', 
    '2026-07-01', 
    'https://upload.wikimedia.org/wikipedia/en/thumb/4/4d/Shrek_%28character%29.png/220px-Shrek_%28character%29.png', 
    24.00, 95
);

-- Seanse (Powiązanie filmu z salą i czasem)
INSERT INTO showtimes (movie_id, hall_id, start_time, technology, base_price) VALUES
(1, 1, '2025-10-27 20:30:00', 'IMAX 3D', 35.00), 
(2, 1, '2025-10-28 18:00:00', '2D', 25.00);

-- Jedzenie
INSERT INTO food_items (name, category, price, image) VALUES
('Popcorn Mały', 'Przekąska', 15.00, 'https://images.unsplash.com/photo-1691480213129-106b2c7d1ee8?q=80&w=880&auto=format&fit=crop'),
('Coca-Cola 0.5L', 'Napój', 9.00, 'https://images.unsplash.com/photo-1583683433877-042a75ba47e3?q=80&w=749&auto=format&fit=crop');

-- Przykładowa rezerwacja (żeby przetestować joiny)
INSERT INTO bookings (user_id, total_price, payment_status, payment_method) VALUES
(1, 35.00, 'PAID', 'BLIK');

-- Bilet do rezerwacji
INSERT INTO tickets (booking_id, showtime_id, seat_id, price, ticket_token) VALUES
(1, 1, 7, 35.00, 'AC-84JFG61'); -- Bilet dla usera 1, seans 1, miejsce 7 (F7)