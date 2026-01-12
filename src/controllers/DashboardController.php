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
        header('Content-Type: application/json');
        
        // Manual session check for AJAX - requireLogin() redirects which breaks fetch
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'status' => 'unauthorized',
                'message' => 'Session expired. Please login again.'
            ]);
            return;
        }

        // Check session timeout (same as requireLogin)
        $timeout = 600;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            session_unset();
            session_destroy();
            http_response_code(401);
            echo json_encode([
                'status' => 'unauthorized',
                'message' => 'Session expired. Please login again.'
            ]);
            return;
        }
        $_SESSION['last_activity'] = time();

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
        $cinemaId = (int)($decoded['cinema_id'] ?? 0);
        $cinemaName = $decoded['cinema_name'] ?? null;

        if (!$cinemaId) {
            http_response_code(400);
            echo json_encode(['status' => 'Cinema ID required']);
            return;
        }

        // Store BOTH numeric ID (for queries) and name (for display) in session
        $_SESSION['selected_cinema_id'] = $cinemaId;
        $_SESSION['selected_cinema_name'] = $cinemaName;

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'cinema_id' => $cinemaId
        ]);
    }
}