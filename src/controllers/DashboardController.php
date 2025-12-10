<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../repository/CardsRepository.php';
require_once __DIR__.'/../repository/MovieRepository.php';
require_once __DIR__.'/../repository/SnacksRepository.php';


class DashboardController extends AppController {

    private $movieRepository;
    private $snacksRepository;

    public function __construct() {
        $this->movieRepository = new MovieRepository();
        $this->snacksRepository = new SnacksRepository();
    }

    public function index() {
        $this->requireLogin();

        $moviesOnScreen = $this->movieRepository->getMoviesOnScreen();
        $moviesUpcoming = $this->movieRepository->getUpcomingMovies();
        
        $snacks = $this->snacksRepository->getSnacks(); 

        $this->render('dashboard', [
            'moviesOnScreen' => $moviesOnScreen, 
            'moviesUpcoming' => $moviesUpcoming,
            'snacks' => $snacks
        ]);
    }


    public function search() {

        header('Content-Type: application/json');

        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode([
                'status' => 'Method not allowed'
            ]);
            return;
        }

        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

        if ($contentType !== "application/json") {
            http_response_code(415);
            echo json_encode([
                'status' => 'Application/json content type not found'
            ]);
            return;
        }

        //TODO wyciągnac odpowiedni content 
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        $searchTag = $decoded['search'] ?? '';
        

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'movies' => $this->movieRepository->getMoviesByTitle($searchTag)
        ]);
        return;
    }
}