<?php 

require_once 'Repository.php';
require_once __DIR__."/../models/movie.php";

class MovieRepository extends Repository {

    public function getMovies() {

        $smtm = $this->database->connect()->prepare('
            SELECT * FROM movies'
        );

        $smtm->execute();
        return $smtm->fetchAll(PDO::FETCH_ASSOC);
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
}