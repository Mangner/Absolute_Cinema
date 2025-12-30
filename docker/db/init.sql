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



CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'employee')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, surname, email, password, role) 
VALUES ('Jan', 'Kowalski', 'admin@example.com', '$2y$10$wz2g9JrHYcF8bLGBbDkEXuJQAnl4uO9RV6cWJKcf.6uAEkhFZpU0i', 'admin'); 
-- Hasło: admin123



/* --- 2. KINA I SALE --- */
CREATE TABLE cinemas (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- np. Absolute Cinema Warszawa
    city VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL
);

CREATE TABLE halls (
    id SERIAL PRIMARY KEY,
    cinema_id INT REFERENCES cinemas(id) ON DELETE CASCADE,
    name VARCHAR(50) NOT NULL, -- np. Sala 1, Sala IMAX
    type VARCHAR(20) DEFAULT 'Standard' -- Standard, IMAX, 4DX
);

/* --- 3. MIEJSCA (Siatka foteli) --- */
CREATE TABLE seats (
    id SERIAL PRIMARY KEY,
    hall_id INT REFERENCES halls(id) ON DELETE CASCADE,
    row_label CHAR(1) NOT NULL, -- A, B, C...
    seat_number INT NOT NULL,   -- 1, 2, 3...
    UNIQUE(hall_id, row_label, seat_number) -- Unikalne miejsce w danej sali
);

/* --- 4. FILMY --- */
CREATE TABLE movies (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    director VARCHAR(100),
    release_date DATE NOT NULL,
    image VARCHAR(1024),
    price DECIMAL(10, 2),
    duration INT NOT NULL -- czas w minutach
);

/* --- 5. SEANSE (Repertuar) --- */
CREATE TABLE showtimes (
    id SERIAL PRIMARY KEY,
    movie_id INT REFERENCES movies(id) ON DELETE CASCADE,
    hall_id INT REFERENCES halls(id) ON DELETE CASCADE,
    start_time TIMESTAMP NOT NULL,
    technology VARCHAR(50) DEFAULT '2D', -- 2D, 3D, IMAX
    base_price DECIMAL(10, 2) NOT NULL   -- Cena bazowa biletu na ten seans
);

/* --- 6. REZERWACJE I PŁATNOŚCI --- */
CREATE TABLE bookings (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    payment_status VARCHAR(20) DEFAULT 'PENDING' CHECK (payment_status IN ('PENDING', 'PAID', 'CANCELLED')),
    payment_method VARCHAR(50) -- np. BLIK, Karta (tylko do celów informacyjnych)
);

CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    booking_id INT REFERENCES bookings(id) ON DELETE CASCADE,
    showtime_id INT REFERENCES showtimes(id) ON DELETE CASCADE,
    seat_id INT REFERENCES seats(id) ON DELETE RESTRICT,
    price DECIMAL(10, 2) NOT NULL, -- Cena konkretnego biletu (może uwzględniać zniżki studenckie itp.)
    ticket_token VARCHAR(64) UNIQUE -- Unikalny kod do kodu QR na bilecie
);

/* --- 7. JEDZENIE --- */
CREATE TABLE food_items (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(1024)
);




-- Kina
INSERT INTO cinemas (name, city, address) VALUES 
('Absolute Cinema Warszawa', 'Warszawa', 'Złota 44'),
('Absolute Cinema Kraków', 'Kraków', 'Pawia 5 (Galeria Krakowska)');


INSERT INTO movies (title, description, director, release_date, image, price, duration) VALUES
(
    'Diuna: Część druga', 
    'Książę Paul Atryda przyjmuje przydomek Muad''Dib i rozpoczyna duchowo-fizyczną podróż, by stać się przepowiedzianym wyzwolicielem ludu Diuny.', 
    'Denis Villeneuve', 
    '2024-02-29', 
    'https://image.tmdb.org/t/p/w600_and_h900_face/xdfO6EB9e59qZpzmHxezTdPfTxZ.jpg', 
    25.00,
    166
),
(
    'Kung Fu Panda 4', 
    'Po wyrusza w nową przygodę w starożytnych Chinach, gdzie jego miłość do kung fu zderza się z nienasyconym apetytem.', 
    'Mike Mitchell', 
    '2024-03-08', 
    'https://upload.wikimedia.org/wikipedia/en/7/7f/Kung_Fu_Panda_4_poster.jpg', 
    20.00,
    94
),
(
    'Oppenheimer', 
    'Historia amerykańskiego naukowca J. Roberta Oppenheimera i jego roli w stworzeniu bomby atomowej.', 
    'Christopher Nolan', 
    '2023-07-21', 
    'https://upload.wikimedia.org/wikipedia/en/4/4a/Oppenheimer_%28film%29.jpg', 
    22.50,
    180
);

INSERT INTO movies (title, description, director, release_date, image, price, duration) VALUES
(
    'Gladiator 2', 
    'Kontynuacja epickiej historii o zdradzie, zemście i walce o honor w starożytnym Rzymie.', 
    'Ridley Scott', 
    '2026-11-22', 
    'https://image.tmdb.org/t/p/w600_and_h900_face/q6mkkb5XU6ERF7xP9nAjnNq9n7V.jpg', 
    28.00,
    140
),
(
    'Shrek 5', 
    'Powrót ulubionego ogra i jego przyjaciół w zupełnie nowej przygodzie.', 
    'Nieznany', 
    '2026-07-01', 
    'https://upload.wikimedia.org/wikipedia/en/thumb/4/4d/Shrek_%28character%29.png/220px-Shrek_%28character%29.png', 
    24.00,
    95
);


INSERT INTO food_items (name, category, price, image) VALUES
('Popcorn Mały', 'Przekąska', 15.00, 'https://images.unsplash.com/photo-1691480213129-106b2c7d1ee8?q=80&w=880&auto=format&fit=crop'),
('Coca-Cola 0.5L', 'Napój', 9.00, 'https://images.unsplash.com/photo-1583683433877-042a75ba47e3?q=80&w=749&auto=format&fit=crop');

