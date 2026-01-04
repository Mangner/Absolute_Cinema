<?php 

require_once 'Repository.php';
require_once __DIR__."/../models/movie.php";

class MovieRepository extends Repository {

    public function getMovies() {

        $smtm = $this->database->connect()->prepare('
            SELECT * FROM movies'
        );
        $smtm->execute();

        $movies = $smtm->fetchAll(PDO::FETCH_CLASS, Movie::class);
        if (!$movies) { return null; }
        return $movies;
    }

    public function getMoviesOnScreen(): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM movies 
            WHERE release_date <= CURRENT_DATE
            ORDER BY release_date DESC
        ');
        $stmt->execute();

        $movies =  $stmt->fetchAll(PDO::FETCH_CLASS, Movie::class);
        if (!$movies) { return null; }
        return $movies;
    }

    public function getUpcomingMovies(): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM movies 
            WHERE release_date > CURRENT_DATE
            ORDER BY release_date ASC
        ');
        $stmt->execute();

        $movies = $stmt->fetchAll(PDO::FETCH_CLASS, Movie::class);
        if (!$movies) { return null; }
        return $movies;
    }

    public function getMoviesByTitle(string $searchString)
    {
        $searchString = '%' . strtolower($searchString) . '%';

        $stmt = $this->database->connect()->prepare('
            SELECT * FROM movies
            WHERE LOWER(title) LIKE :search OR LOWER(description) LIKE :search
        ');
        $stmt->bindParam(':search', $searchString, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMovieById(int $movie_id) {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM movies 
            WHERE movie_id = :given_id
        ');
        $stmt->bindParam(':given_id', $movie_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $stmt->setFetchMode(PDO::FETCH_CLASS, Movie::class);
        $movie = $stmt->fetch();
        if ($movie === false) {
            return null;
        }
        return $movie;
    }

    public function getMoviesByCinemaId(int $cinemaId): array {
        $sql = "
            SELECT DISTINCT * FROM MOVIES
            NATURAL JOIN showtimes
            NATURAL JOIN halls
            WHERE cinema_id = :cinemaId
            AND release_date <= NOW()
            AND s.start_time > NOW()
            ORDER BY release_date DESC;
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':cinemaId', $cinemaId, PDO::PARAM_INT);
        $stmt->execute();

        $movies = $stmt->fetchAll(PDO::FETCH_CLASS, Movie::class);
        if (!$movies) { return null; }
        return $movies;
    }

    public function getUpcomingMoviesByCinemaId(int $cinemaId): array {
        $sql = "
            SELECT DISTINCT m.* FROM movies m
            INNER JOIN showtimes s ON m.id = s.movie_id
            INNER JOIN halls h ON s.hall_id = h.id
            WHERE h.cinema_id = :cinemaId
            AND s.start_time > NOW()
            AND m.release_date > CURRENT_DATE 
            ORDER BY m.release_date DESC
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':cinemaId', $cinemaId, PDO::PARAM_INT);
        $stmt->execute();

        $movies = $stmt->fetchAll(PDO::FETCH_CLASS, Movie::class);
        if (!$movies) { return null; }
        return $movies;
    }
}