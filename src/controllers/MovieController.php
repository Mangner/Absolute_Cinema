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
        $this->render('movieDetails', ["movie" => $movie]);
    }

}


?>