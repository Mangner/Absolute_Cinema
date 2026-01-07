<?php

require_once 'AppController.php';
require_once __DIR__."/../repository/MovieRepository.php";
require_once __DIR__."/../repository/ShowtimeRepository.php";


class MovieController extends AppController {

    private $movieRepository;
    private $showtimeRepository;

    public function __construct() {
        $this->movieRepository = new MovieRepository();
        $this->showtimeRepository = new ShowtimeRepository();
    }


    public function getDetails($movieId, $cinemaId = null) {
        
        $movie = $this->movieRepository->getMovieById($movieId);
        $genres = $this->movieRepository->getGenresByMovieId($movieId);
        $cast = $this->movieRepository->getCastByMovieId($movieId);

        $this->render('movieDetails', ["movie" => $movie, "genres" => $genres, "cast" => $cast]);
    }


    public function getShowtimes($movie_id, $cinema_id, $date) {

        header('Content-Type: application/json');

        if (!$this->isGet()) {
            http_response_code(405);
            echo json_encode([
                'status' => 'Method not allowed'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'cinemas' => $this->showtimeRepository->getShowtimesByMovieAndCinemaIdAndDate($movie_id, $cinema_id, $date)
        ]);

    }
}


?>