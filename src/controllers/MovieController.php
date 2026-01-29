<?php

require_once 'AppController.php';
require_once __DIR__."/../repository/MovieRepository.php";
require_once __DIR__."/../repository/ShowtimeRepository.php";
require_once __DIR__.'/../middleware/Attribute/AllowedMethods.php';
require_once __DIR__.'/../middleware/Attribute/IsLoggedIn.php';

use Middleware\Attribute\AllowedMethods;
use Middleware\Attribute\IsLoggedIn;


class MovieController extends AppController {

    private $movieRepository;
    private $showtimeRepository;

    public function __construct() {
        $this->movieRepository = new MovieRepository();
        $this->showtimeRepository = new ShowtimeRepository();
    }


    #[AllowedMethods(['GET'])]
    #[IsLoggedIn]
    public function getDetails($movieId) {
        
        $cinemaId = isset($_SESSION['selected_cinema_id']) ? (int)$_SESSION['selected_cinema_id'] : null;

        $movie = $this->movieRepository->getMovieById($movieId);
        $genres = $this->movieRepository->getGenresByMovieId($movieId);
        $cast = $this->movieRepository->getCastByMovieId($movieId);

        $technologies = [];
        if ($cinemaId !== null) {
            $technologies = $this->showtimeRepository->getFormatsByMovieIdAndCinemaId($movieId, $cinemaId);
        }

        $this->render('movieDetails', ["movie" => $movie, "genres" => $genres, "cast" => $cast, "technologies" => $technologies]);
    }


    #[AllowedMethods(['POST'])]
    #[IsLoggedIn(redirectOnFail: false)]
    public function getShowtimes() {
        header('Content-Type: application/json');

        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType !== "application/json") {
            http_response_code(415);
            echo json_encode(['status' => 'Invalid content type']);
            return;
        }

        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        // Get parameters (cinema_id should be numeric)
        $movie_id = (int)($decoded['movie_id'] ?? 0);
        $cinema_id = (int)($decoded['cinema_id'] ?? 0);
        $date = $decoded['date'] ?? date('Y-m-d');

        // Validate required parameters
        if (!$movie_id || !$cinema_id) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'movie_id and cinema_id are required',
                'received' => ['movie_id' => $movie_id, 'cinema_id' => $cinema_id]
            ]);
            return;
        }

        // Fetch showtimes from repository
        $showtimes = $this->showtimeRepository->getShowtimesByMovieAndCinemaIdAndDate($movie_id, $cinema_id, $date);

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'showtimes' => $showtimes,
            'date' => $date,
            'debug' => [
                'query_params' => ['movie_id' => $movie_id, 'cinema_id' => $cinema_id, 'date' => $date],
                'result_count' => $showtimes ? count($showtimes) : 0
            ]
        ]);
    }
}