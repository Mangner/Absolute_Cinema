<?php 

require_once 'Repository.php';
require_once __DIR__."/../models/movie.php";
require_once __DIR__."/../models/genre.php";
require_once __DIR__."/../models/cast_member.php";

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

    public function getGenresByMovieId(int $movie_id) {

        $sql = "
            SELECT g.genre_id, g.name, g.description 
            FROM genres g
            INNER JOIN movie_genres mg on g.genre_id = mg.genre_id
            WHERE mg.movie_id = :movie_id
            ORDER BY g.name ASC
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->execute();

        $genres = $stmt->fetchAll(PDO::FETCH_CLASS, Genre::class);
        if (!$genres) { return null; }
        return $genres;
    }

    public function getCastByMovieId(int $movie_id) {

        $sql = "
            SELECT c.cast_id, c.name, c.role, c.biography, c.image 
            FROM cast_members c
            INNER JOIN movie_cast mc on c.cast_id = mc.cast_id
            WHERE mc.movie_id = :movie_id
            ORDER BY c.name ASC
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $movie_cast = $stmt->fetchAll(PDO::FETCH_CLASS, Cast_Member::class);
        if (!$movie_cast) { return null; }
        return $movie_cast;
    }

   
    public function getAllGenres(): array
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM genres ORDER BY name ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, Genre::class) ?: [];
    }

    
    public function addMovie(
        string $title,
        string $originalTitle,
        string $description,
        string $director,
        string $releaseDate,
        string $image,
        ?string $trailerUrl,
        float $price,
        int $duration,
        string $productionCountry,
        string $originalLanguage,
        string $ageRating,
        ?float $imdbRating = null,
        ?float $rottenTomatoesRating = null,
        ?float $metacriticRating = null
    ): int {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO movies (title, original_title, description, director, release_date, image, trailer_url, price, duration, production_country, original_language, age_rating, imdb_rating, rotten_tomatoes_rating, metacritic_rating)
            VALUES (:title, :original_title, :description, :director, :release_date, :image, :trailer_url, :price, :duration, :production_country, :original_language, :age_rating, :imdb_rating, :rotten_tomatoes_rating, :metacritic_rating)
            RETURNING movie_id
        ');

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':original_title', $originalTitle);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':director', $director);
        $stmt->bindParam(':release_date', $releaseDate);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':trailer_url', $trailerUrl);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
        $stmt->bindParam(':production_country', $productionCountry);
        $stmt->bindParam(':original_language', $originalLanguage);
        $stmt->bindParam(':age_rating', $ageRating);
        $stmt->bindParam(':imdb_rating', $imdbRating);
        $stmt->bindParam(':rotten_tomatoes_rating', $rottenTomatoesRating);
        $stmt->bindParam(':metacritic_rating', $metacriticRating);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    
    public function updateMovie(
        int $movieId,
        string $title,
        string $originalTitle,
        string $description,
        string $director,
        string $releaseDate,
        string $image,
        ?string $trailerUrl,
        float $price,
        int $duration,
        string $productionCountry,
        string $originalLanguage,
        string $ageRating,
        ?float $imdbRating = null,
        ?float $rottenTomatoesRating = null,
        ?float $metacriticRating = null
    ): void {
        $stmt = $this->database->connect()->prepare('
            UPDATE movies SET 
                title = :title,
                original_title = :original_title,
                description = :description,
                director = :director,
                release_date = :release_date,
                image = :image,
                trailer_url = :trailer_url,
                price = :price,
                duration = :duration,
                production_country = :production_country,
                original_language = :original_language,
                age_rating = :age_rating,
                imdb_rating = :imdb_rating,
                rotten_tomatoes_rating = :rotten_tomatoes_rating,
                metacritic_rating = :metacritic_rating
            WHERE movie_id = :movie_id
        ');

        $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':original_title', $originalTitle);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':director', $director);
        $stmt->bindParam(':release_date', $releaseDate);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':trailer_url', $trailerUrl);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
        $stmt->bindParam(':production_country', $productionCountry);
        $stmt->bindParam(':original_language', $originalLanguage);
        $stmt->bindParam(':age_rating', $ageRating);
        $stmt->bindParam(':imdb_rating', $imdbRating);
        $stmt->bindParam(':rotten_tomatoes_rating', $rottenTomatoesRating);
        $stmt->bindParam(':metacritic_rating', $metacriticRating);
        $stmt->execute();
    }

    
    public function deleteMovie(int $movieId): void
    {
        $stmt = $this->database->connect()->prepare('DELETE FROM movies WHERE movie_id = :id');
        $stmt->bindParam(':id', $movieId, PDO::PARAM_INT);
        $stmt->execute();
    }

    
    public function setMovieGenres(int $movieId, array $genreIds): void
    {
        $conn = $this->database->connect();
        
        $stmt = $conn->prepare('DELETE FROM movie_genres WHERE movie_id = :movie_id');
        $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare('INSERT INTO movie_genres (movie_id, genre_id) VALUES (:movie_id, :genre_id)');
        foreach ($genreIds as $genreId) {
            $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
            $stmt->bindParam(':genre_id', $genreId, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    
    public function countMovies(): int
    {
        $stmt = $this->database->connect()->prepare('SELECT COUNT(*) FROM movies');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
    
}