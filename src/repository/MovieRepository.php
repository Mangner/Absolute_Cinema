<?php 

require_once 'Repository.php';


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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingMovies(): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM movies 
            WHERE release_date > CURRENT_DATE
            ORDER BY release_date ASC
        ');

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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