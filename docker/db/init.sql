DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS movies CASCADE;
DROP TABLE IF EXISTS food_items CASCADE;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, surname, email, password) 
VALUES ('Jan', 'Kowalski', 'admin@example.com', '$2y$10$wz2g9JrHYcF8bLGBbDkEXuJQAnl4uO9RV6cWJKcf.6uAEkhFZpU0i');
-- Hasło: admin123

/* --- 3. FILMY (REPERTUAR) --- */
CREATE TABLE movies (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    director VARCHAR(100),
    release_date DATE NOT NULL,
    image VARCHAR(1024),        -- Zwiększono limit znaków dla długich URLi
    price DECIMAL(10, 2),
    duration INT
);

INSERT INTO movies (title, description, director, release_date, image, price, duration) VALUES
(
    'Diuna: Część druga', 
    'Książę Paul Atryda przyjmuje przydomek Muad''Dib i rozpoczyna duchowo-fizyczną podróż, by stać się przepowiedzianym wyzwolicielem ludu Diuny.', 
    'Denis Villeneuve', 
    '2024-02-29', 
    'https://upload.wikimedia.org/wikipedia/en/5/52/Dune_Part_Two_poster.jpg', 
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
    'https://upload.wikimedia.org/wikipedia/en/thumb/f/f3/Gladiator_II_poster.jpg/220px-Gladiator_II_poster.jpg', 
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


CREATE TABLE food_items (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(1024) -- Tutaj też długi URL
);

INSERT INTO food_items (name, category, price, image) VALUES
('Popcorn Mały', 'Przekąska', 15.00, 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d2/Popcorn_at_a_fair_or_event.jpg/320px-Popcorn_at_a_fair_or_event.jpg'),
('Coca-Cola 0.5L', 'Napój', 9.00, 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f6/15-09-26-RalfR-WLC-0098.jpg/320px-15-09-26-RalfR-WLC-0098.jpg'),
('Nachos z serem', 'Przekąska', 25.00, 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Nachos_with_cheese.jpg/320px-Nachos_with_cheese.jpg');