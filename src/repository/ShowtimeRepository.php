<?php

require_once "Repository.php";
require_once __DIR__."/../models/showtime.php";
require_once __DIR__."/../DTOs/MovieTechnologyDTO.php";



class ShowtimeRepository extends Repository {

    public function getShowtimesByMovieAndCinemaIdAndDate(int $movie_id, int $cinema_id, string $date) {

        $today = date('Y-m-d');
        
        // Data w przeszłości - nie zwracamy żadnych seansów
        if ($date < $today) {
            return [];
        }

        // Budujemy zapytanie SQL z warunkiem czasowym
        // Jeśli data = dzisiaj, filtrujemy tylko przyszłe seanse
        // Jeśli data > dzisiaj, zwracamy wszystkie seanse na ten dzień
        $sql = "
                SELECT s.showtime_id, s.movie_id, s.hall_id, s.start_time, s.technology, s.language, s.audio_type, s.base_price
                FROM showtimes s
                INNER JOIN movies m ON s.movie_id = m.movie_id
                INNER JOIN halls h ON s.hall_id = h.hall_id
                WHERE s.movie_id = :movie_id
                AND h.cinema_id = :cinema_id
                AND DATE(s.start_time) = :date
        ";

        // Dla dzisiejszej daty - tylko seanse, które jeszcze się nie zaczęły
        if ($date === $today) {
            $sql .= " AND s.start_time > NOW() ";
        }

        $sql .= " ORDER BY s.start_time ASC";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->bindParam(':cinema_id', $cinema_id, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        $showtimes = $stmt->fetchAll(PDO::FETCH_CLASS, Showtime::class);
        
        return $showtimes ?: [];
    }


    public function getFormatsByMovieIdAndCinemaId(int $movie_id, int $cinema_id): array {

        $sql = "
            SELECT DISTINCT s.technology
            FROM showtimes s
            INNER JOIN movies m ON m.movie_id = s.movie_id
            INNER JOIN halls h ON s.hall_id = h.hall_id
            WHERE s.movie_id = :movie_id
            AND h.cinema_id = :cinema_id
            ORDER BY s.technology ASC 
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->bindParam(':cinema_id', $cinema_id, PDO::PARAM_INT);

        $stmt->execute();

        $technologies = $stmt->fetchAll(PDO::FETCH_CLASS, TechnologyDTO::class);
        
        return $technologies ?: [];
    }

    /**
     * Pobiera wszystkie seanse z dodatkowymi informacjami (dla panelu admina)
     */
    public function getAllShowtimesAdmin(): array
    {
        $sql = "
            SELECT 
                s.showtime_id,
                s.movie_id,
                s.hall_id,
                s.start_time,
                s.technology,
                s.language,
                s.audio_type,
                s.base_price,
                m.title AS movie_title,
                m.image AS movie_image,
                h.name AS hall_name,
                c.name AS cinema_name,
                c.city AS cinema_city,
                c.cinema_id
            FROM showtimes s
            INNER JOIN movies m ON s.movie_id = m.movie_id
            INNER JOIN halls h ON s.hall_id = h.hall_id
            INNER JOIN cinemas c ON h.cinema_id = c.cinema_id
            ORDER BY s.start_time DESC
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Pobiera seans po ID (z dodatkowymi informacjami)
     */
    public function getShowtimeById(int $showtimeId): ?array
    {
        $sql = "
            SELECT 
                s.*,
                m.title AS movie_title,
                h.name AS hall_name,
                c.name AS cinema_name,
                c.cinema_id
            FROM showtimes s
            INNER JOIN movies m ON s.movie_id = m.movie_id
            INNER JOIN halls h ON s.hall_id = h.hall_id
            INNER JOIN cinemas c ON h.cinema_id = c.cinema_id
            WHERE s.showtime_id = :id
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':id', $showtimeId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Dodaje nowy seans
     */
    public function addShowtime(
        int $movieId,
        int $hallId,
        string $startTime,
        string $technology,
        string $language,
        string $audioType,
        float $basePrice
    ): int {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO showtimes (movie_id, hall_id, start_time, technology, language, audio_type, base_price)
            VALUES (:movie_id, :hall_id, :start_time, :technology, :language, :audio_type, :base_price)
            RETURNING showtime_id
        ');

        $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->bindParam(':hall_id', $hallId, PDO::PARAM_INT);
        $stmt->bindParam(':start_time', $startTime);
        $stmt->bindParam(':technology', $technology);
        $stmt->bindParam(':language', $language);
        $stmt->bindParam(':audio_type', $audioType);
        $stmt->bindParam(':base_price', $basePrice);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Aktualizuje seans
     */
    public function updateShowtime(
        int $showtimeId,
        int $movieId,
        int $hallId,
        string $startTime,
        string $technology,
        string $language,
        string $audioType,
        float $basePrice
    ): void {
        $stmt = $this->database->connect()->prepare('
            UPDATE showtimes SET 
                movie_id = :movie_id,
                hall_id = :hall_id,
                start_time = :start_time,
                technology = :technology,
                language = :language,
                audio_type = :audio_type,
                base_price = :base_price
            WHERE showtime_id = :showtime_id
        ');

        $stmt->bindParam(':showtime_id', $showtimeId, PDO::PARAM_INT);
        $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->bindParam(':hall_id', $hallId, PDO::PARAM_INT);
        $stmt->bindParam(':start_time', $startTime);
        $stmt->bindParam(':technology', $technology);
        $stmt->bindParam(':language', $language);
        $stmt->bindParam(':audio_type', $audioType);
        $stmt->bindParam(':base_price', $basePrice);
        $stmt->execute();
    }

    /**
     * Usuwa seans
     */
    public function deleteShowtime(int $showtimeId): void
    {
        $stmt = $this->database->connect()->prepare('DELETE FROM showtimes WHERE showtime_id = :id');
        $stmt->bindParam(':id', $showtimeId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Zlicza seanse
     */
    public function countShowtimes(): int
    {
        $stmt = $this->database->connect()->prepare('SELECT COUNT(*) FROM showtimes');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Zlicza nadchodzące seanse
     */
    public function countUpcomingShowtimes(): int
    {
        $stmt = $this->database->connect()->prepare('SELECT COUNT(*) FROM showtimes WHERE start_time > NOW()');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}

?>