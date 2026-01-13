<?php

require_once "Repository.php";
require_once __DIR__."/../models/showtime.php";
require_once __DIR__."/../DTOs/MovieTechnologyDTO.php";



class ShowtimeRepository extends Repository {

    public function getShowtimesByMovieAndCinemaIdAndDate(int $movie_id, int $cinema_id, string $date) {

        $sql = "
                SELECT s.showtime_id, s.movie_id, s.hall_id, s.start_time, s.technology, s.language, s.audio_type, s.base_price
                FROM showtimes s
                INNER JOIN movies m ON s.movie_id = m.movie_id
                INNER JOIN halls h ON s.hall_id = h.hall_id
                WHERE s.movie_id = :movie_id
                AND h.cinema_id = :cinema_id
                AND DATE(s.start_time) = :date
                ORDER BY s.start_time ASC
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->bindParam(':cinema_id', $cinema_id, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        $showtimes = $stmt->fetchAll(PDO::FETCH_CLASS, Showtime::class);
        
        if (!$showtimes) { return null; }
        return $showtimes;
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
}

?>