<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/CinemaRepository.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../repository/MovieRepository.php';
require_once __DIR__.'/../repository/SnacksRepository.php';


class DashboardController extends AppController {

    private $cinemaRepository;
    private $movieRepository;
    private $snacksRepository;

    public function __construct() {
        $this->cinemaRepository = new CinemaRepository();
        $this->movieRepository = new MovieRepository();
        $this->snacksRepository = new SnacksRepository();
    }

    public function index() {
        $this->requireLogin();
        $this->render('dashboard');
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


    public function getOnScreenMovies() {
        header('Content-Type: application/json');

        if (!$this->isGet()) {
            http_response_code(405);
            echo json_encode([
                'status' => 'Method not allowed'
            ]);
            return;
        }

        $movies = $this->movieRepository->getMoviesOnScreen(); 

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'movies' => $movies,
        ]);
    }


    public function getUpcomingMovies() {
        header('Content-Type: application/json');

        if (!$this->isGet()) {
            http_response_code(405);
            echo json_encode([
                'status' => 'Method not allowed'
            ]);
            return;
        }

        $movies = $this->movieRepository->getUpcomingMovies(); 

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'movies' => $movies
        ]);
    }


    public function getSnacks() {
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
            'snacks' => $this->snacksRepository->getSnacks()
        ]);
    }


    public function getCinemas() {
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
            'cinemas' => $this->cinemaRepository->getCinemas()
        ]);
    }


    public function setCinema() {
        $this->requireLogin(); // Security: only logged-in users
        header('Content-Type: application/json');

        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['status' => 'Method not allowed']);
            return;
        }

        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        
        if ($contentType !== "application/json") {
            http_response_code(415);
            echo json_encode(['status' => 'Invalid content type']);
            return;
        }

        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        $cinemaId = $decoded['cinema_id'] ?? null;

        if (!$cinemaId) {
            http_response_code(400);
            echo json_encode(['status' => 'Cinema ID required']);
            return;
        }

        // Store in THIS user's session only
        $_SESSION['selected_cinema_id'] = $cinemaId;

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'cinema_id' => $cinemaId
        ]);
    }
}