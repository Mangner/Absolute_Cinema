<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/CinemaRepository.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../repository/MovieRepository.php';
require_once __DIR__.'/../repository/SnacksRepository.php';
require_once __DIR__.'/../middleware/Attribute/AllowedMethods.php';
require_once __DIR__.'/../middleware/Attribute/IsLoggedIn.php';

use Middleware\Attribute\AllowedMethods;
use Middleware\Attribute\IsLoggedIn;


class DashboardController extends AppController {

    private $cinemaRepository;
    private $movieRepository;
    private $snacksRepository;

    public function __construct() {
        $this->cinemaRepository = new CinemaRepository();
        $this->movieRepository = new MovieRepository();
        $this->snacksRepository = new SnacksRepository();
    }

    #[AllowedMethods(['GET'])]
    #[IsLoggedIn]
    public function index() {
        $this->render('dashboard');
    }


    #[AllowedMethods(['POST'])]
    #[IsLoggedIn(redirectOnFail: false)]
    public function search() {

        header('Content-Type: application/json');

        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

        if ($contentType !== "application/json") {
            http_response_code(415);
            echo json_encode([
                'status' => 'Application/json content type not found'
            ]);
            return;
        }

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


    #[AllowedMethods(['GET'])]
    public function getOnScreenMovies() {
        header('Content-Type: application/json');

        $movies = $this->movieRepository->getMoviesOnScreen(); 

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'movies' => $movies,
        ]);
    }


    #[AllowedMethods(['GET'])]
    public function getUpcomingMovies() {
        header('Content-Type: application/json');

        $movies = $this->movieRepository->getUpcomingMovies(); 

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'movies' => $movies
        ]);
    }


    #[AllowedMethods(['GET'])]
    public function getSnacks() {
        header('Content-Type: application/json');

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'snacks' => $this->snacksRepository->getSnacks()
        ]);
    }


    #[AllowedMethods(['GET'])]
    public function getCinemas() {
        header('Content-Type: application/json');

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'cinemas' => $this->cinemaRepository->getCinemas()
        ]);
    }


    #[AllowedMethods(['POST'])]
    #[IsLoggedIn(redirectOnFail: false)]
    public function setCinema() {
        header('Content-Type: application/json');

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


    #[AllowedMethods(['GET', 'POST'])]
    public function contact() {
        $messageSent = false;
        $error = null;

        // Obsługa formularza (atrapa - nie wysyła maili)
        if ($this->isPost()) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (empty($name) || empty($email) || empty($message)) {
                $error = 'Proszę wypełnić wszystkie wymagane pola.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Podaj prawidłowy adres e-mail.';
            } else {
                // TODO: Implementacja wysyłki maili
                $messageSent = true;
            }
        }

        $this->render('contact', [
            'messageSent' => $messageSent,
            'error' => $error
        ]);
    }
}