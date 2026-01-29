<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../repository/MovieRepository.php';
require_once __DIR__.'/../repository/ShowtimeRepository.php';
require_once __DIR__.'/../repository/CinemaRepository.php';
require_once __DIR__.'/../middleware/Attribute/AllowedMethods.php';
require_once __DIR__.'/../middleware/Attribute/IsLoggedIn.php';

use Middleware\Attribute\AllowedMethods;
use Middleware\Attribute\IsLoggedIn;

/**
 * AdminController - Panel Administratora
 * 
 * Wszystkie metody wymagają zalogowania oraz roli 'admin'.
 */
class AdminController extends AppController
{
    private UserRepository $userRepository;
    private MovieRepository $movieRepository;
    private ShowtimeRepository $showtimeRepository;
    private CinemaRepository $cinemaRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->movieRepository = new MovieRepository();
        $this->showtimeRepository = new ShowtimeRepository();
        $this->cinemaRepository = new CinemaRepository();
    }

    /**
     * Sprawdza, czy zalogowany użytkownik ma rolę admina.
     * Jeśli nie - przekierowuje na dashboard.
     */
    private function requireAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->url('dashboard');
            exit();
        }

        return true;
    }

    /**
     * Główny widok panelu admina (alias dla users)
     */
    #[AllowedMethods(['GET'])]
    #[IsLoggedIn]
    public function index(): void
    {
        $this->requireAdmin();
        $this->users();
    }

    /**
     * Lista wszystkich użytkowników
     */
    #[AllowedMethods(['GET'])]
    #[IsLoggedIn]
    public function users(): void
    {
        $this->requireAdmin();

        $users = $this->userRepository->getUsers();
        $message = $_GET['message'] ?? null;
        $error = $_GET['error'] ?? null;

        $this->render('admin_users', [
            'users' => $users ?? [],
            'message' => $message,
            'error' => $error
        ]);
    }

    /**
     * Formularz i logika dodawania nowego użytkownika
     */
    #[AllowedMethods(['GET', 'POST'])]
    #[IsLoggedIn]
    public function addUser(): void
    {
        $this->requireAdmin();

        if ($this->isGet()) {
            $this->render('admin_user_form', [
                'mode' => 'add',
                'user' => null
            ]);
            return;
        }

        // POST - tworzenie użytkownika
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        // Walidacja
        if (empty($name) || empty($surname) || empty($email) || empty($password)) {
            $this->render('admin_user_form', [
                'mode' => 'add',
                'user' => null,
                'error' => 'Wszystkie pola są wymagane!'
            ]);
            return;
        }

        // Sprawdź, czy email już istnieje
        $existingUser = $this->userRepository->getUserByEmail($email);
        if ($existingUser) {
            $this->render('admin_user_form', [
                'mode' => 'add',
                'user' => null,
                'error' => 'Użytkownik z tym adresem email już istnieje!'
            ]);
            return;
        }

        try {
            $this->userRepository->createUserWithRole($name, $surname, $email, $password, $role);
            header('Location: /admin/users?message=Użytkownik został dodany pomyślnie');
            exit();
        } catch (Exception $e) {
            $this->render('admin_user_form', [
                'mode' => 'add',
                'user' => null,
                'error' => 'Wystąpił błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Formularz i logika edycji użytkownika
     */
    #[AllowedMethods(['GET', 'POST'])]
    #[IsLoggedIn]
    public function editUser(int $id): void
    {
        $this->requireAdmin();

        $user = $this->userRepository->getUserById($id);
        
        if (!$user) {
            header('Location: /admin/users?error=Użytkownik nie został znaleziony');
            exit();
        }

        if ($this->isGet()) {
            $this->render('admin_user_form', [
                'mode' => 'edit',
                'user' => $user
            ]);
            return;
        }

        // POST - aktualizacja użytkownika
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $newPassword = $_POST['password'] ?? '';

        // Walidacja
        if (empty($name) || empty($surname) || empty($email)) {
            $this->render('admin_user_form', [
                'mode' => 'edit',
                'user' => $user,
                'error' => 'Imię, nazwisko i email są wymagane!'
            ]);
            return;
        }

        // Sprawdź, czy email nie jest zajęty przez innego użytkownika
        $existingUser = $this->userRepository->getUserByEmail($email);
        if ($existingUser && $existingUser->getId() !== $id) {
            $this->render('admin_user_form', [
                'mode' => 'edit',
                'user' => $user,
                'error' => 'Ten adres email jest już używany przez innego użytkownika!'
            ]);
            return;
        }

        try {
            $this->userRepository->updateUser($id, $name, $surname, $email, $role, $newPassword);
            header('Location: /admin/users?message=Dane użytkownika zostały zaktualizowane');
            exit();
        } catch (Exception $e) {
            $this->render('admin_user_form', [
                'mode' => 'edit',
                'user' => $user,
                'error' => 'Wystąpił błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Usuwanie użytkownika
     */
    #[AllowedMethods(['POST'])]
    #[IsLoggedIn]
    public function deleteUser(): void
    {
        $this->requireAdmin();

        $userId = (int)($_POST['user_id'] ?? 0);

        if (!$userId) {
            header('Location: /admin/users?error=Nieprawidłowy ID użytkownika');
            exit();
        }

        // Nie pozwól adminowi usunąć samego siebie
        if ($userId === (int)$_SESSION['user_id']) {
            header('Location: /admin/users?error=Nie możesz usunąć swojego własnego konta!');
            exit();
        }

        try {
            $this->userRepository->deleteUser($userId);
            header('Location: /admin/users?message=Użytkownik został usunięty');
            exit();
        } catch (Exception $e) {
            header('Location: /admin/users?error=Błąd podczas usuwania użytkownika');
            exit();
        }
    }

    // ========================================
    // SEKCJA: ZARZĄDZANIE FILMAMI
    // ========================================

    /**
     * Lista wszystkich filmów
     */
    #[AllowedMethods(['GET'])]
    #[IsLoggedIn]
    public function movies(): void
    {
        $this->requireAdmin();

        $movies = $this->movieRepository->getMovies();
        $message = $_GET['message'] ?? null;
        $error = $_GET['error'] ?? null;

        $this->render('admin_movies', [
            'movies' => $movies ?? [],
            'message' => $message,
            'error' => $error
        ]);
    }

    /**
     * Formularz i logika dodawania nowego filmu
     */
    #[AllowedMethods(['GET', 'POST'])]
    #[IsLoggedIn]
    public function addMovie(): void
    {
        $this->requireAdmin();

        $genres = $this->movieRepository->getAllGenres();

        if ($this->isGet()) {
            $this->render('admin_movie_form', [
                'movie' => null,
                'genres' => $genres,
                'selectedGenres' => []
            ]);
            return;
        }

        // POST - tworzenie filmu
        $title = trim($_POST['title'] ?? '');
        $originalTitle = trim($_POST['original_title'] ?? $title);
        $description = trim($_POST['description'] ?? '');
        $director = trim($_POST['director'] ?? '');
        $releaseDate = $_POST['release_date'] ?? '';
        $image = trim($_POST['image'] ?? '');
        $trailerUrl = trim($_POST['trailer_url'] ?? '') ?: null;
        $price = (float)($_POST['price'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 0);
        $productionCountry = trim($_POST['production_country'] ?? '');
        $originalLanguage = trim($_POST['original_language'] ?? 'PL');
        $ageRating = trim($_POST['age_rating'] ?? '');
        $imdbRating = !empty($_POST['imdb_rating']) ? (float)$_POST['imdb_rating'] : null;
        $rottenTomatoesRating = !empty($_POST['rotten_tomatoes_rating']) ? (float)$_POST['rotten_tomatoes_rating'] : null;
        $metacriticRating = !empty($_POST['metacritic_rating']) ? (float)$_POST['metacritic_rating'] : null;
        $selectedGenreIds = $_POST['genres'] ?? [];

        // Walidacja
        if (empty($title) || empty($releaseDate) || empty($duration)) {
            $this->render('admin_movie_form', [
                'movie' => null,
                'genres' => $genres,
                'selectedGenres' => $selectedGenreIds,
                'error' => 'Tytuł, data premiery i czas trwania są wymagane!'
            ]);
            return;
        }

        try {
            $movieId = $this->movieRepository->addMovie(
                $title, $originalTitle, $description, $director, $releaseDate,
                $image, $trailerUrl, $price, $duration, $productionCountry,
                $originalLanguage, $ageRating, $imdbRating, $rottenTomatoesRating, $metacriticRating
            );

            // Przypisz gatunki
            if (!empty($selectedGenreIds)) {
                $this->movieRepository->setMovieGenres($movieId, $selectedGenreIds);
            }

            header('Location: /admin/movies?message=Film został dodany pomyślnie');
            exit();
        } catch (Exception $e) {
            $this->render('admin_movie_form', [
                'movie' => null,
                'genres' => $genres,
                'selectedGenres' => $selectedGenreIds,
                'error' => 'Wystąpił błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Formularz i logika edycji filmu
     */
    #[AllowedMethods(['GET', 'POST'])]
    #[IsLoggedIn]
    public function editMovie(int $id): void
    {
        $this->requireAdmin();

        $movie = $this->movieRepository->getMovieById($id);
        $genres = $this->movieRepository->getAllGenres();
        $movieGenres = $this->movieRepository->getGenresByMovieId($id);
        $selectedGenreIds = $movieGenres ? array_map(fn($g) => $g->getId(), $movieGenres) : [];

        if (!$movie) {
            header('Location: /admin/movies?error=Film nie został znaleziony');
            exit();
        }

        if ($this->isGet()) {
            $this->render('admin_movie_form', [
                'movie' => $movie,
                'genres' => $genres,
                'selectedGenres' => $selectedGenreIds
            ]);
            return;
        }

        // POST - aktualizacja filmu
        $title = trim($_POST['title'] ?? '');
        $originalTitle = trim($_POST['original_title'] ?? $title);
        $description = trim($_POST['description'] ?? '');
        $director = trim($_POST['director'] ?? '');
        $releaseDate = $_POST['release_date'] ?? '';
        $image = trim($_POST['image'] ?? '');
        $trailerUrl = trim($_POST['trailer_url'] ?? '') ?: null;
        $price = (float)($_POST['price'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 0);
        $productionCountry = trim($_POST['production_country'] ?? '');
        $originalLanguage = trim($_POST['original_language'] ?? 'PL');
        $ageRating = trim($_POST['age_rating'] ?? '');
        $imdbRating = !empty($_POST['imdb_rating']) ? (float)$_POST['imdb_rating'] : null;
        $rottenTomatoesRating = !empty($_POST['rotten_tomatoes_rating']) ? (float)$_POST['rotten_tomatoes_rating'] : null;
        $metacriticRating = !empty($_POST['metacritic_rating']) ? (float)$_POST['metacritic_rating'] : null;
        $selectedGenreIds = $_POST['genres'] ?? [];

        // Walidacja
        if (empty($title) || empty($releaseDate) || empty($duration)) {
            $this->render('admin_movie_form', [
                'movie' => $movie,
                'genres' => $genres,
                'selectedGenres' => $selectedGenreIds,
                'error' => 'Tytuł, data premiery i czas trwania są wymagane!'
            ]);
            return;
        }

        try {
            $this->movieRepository->updateMovie(
                $id, $title, $originalTitle, $description, $director, $releaseDate,
                $image, $trailerUrl, $price, $duration, $productionCountry,
                $originalLanguage, $ageRating, $imdbRating, $rottenTomatoesRating, $metacriticRating
            );

            // Aktualizuj gatunki
            $this->movieRepository->setMovieGenres($id, $selectedGenreIds);

            header('Location: /admin/movies?message=Film został zaktualizowany');
            exit();
        } catch (Exception $e) {
            $this->render('admin_movie_form', [
                'movie' => $movie,
                'genres' => $genres,
                'selectedGenres' => $selectedGenreIds,
                'error' => 'Wystąpił błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Usuwanie filmu
     */
    #[AllowedMethods(['POST'])]
    #[IsLoggedIn]
    public function deleteMovie(): void
    {
        $this->requireAdmin();

        $movieId = (int)($_POST['movie_id'] ?? 0);

        if (!$movieId) {
            header('Location: /admin/movies?error=Nieprawidłowy ID filmu');
            exit();
        }

        try {
            $this->movieRepository->deleteMovie($movieId);
            header('Location: /admin/movies?message=Film został usunięty');
            exit();
        } catch (Exception $e) {
            header('Location: /admin/movies?error=Błąd podczas usuwania filmu (może mieć przypisane seanse)');
            exit();
        }
    }

    // ========================================
    // SEKCJA: ZARZĄDZANIE SEANSAMI
    // ========================================

    /**
     * Lista wszystkich seansów
     */
    #[AllowedMethods(['GET'])]
    #[IsLoggedIn]
    public function showtimes(): void
    {
        $this->requireAdmin();

        $showtimes = $this->showtimeRepository->getAllShowtimesAdmin();
        $message = $_GET['message'] ?? null;
        $error = $_GET['error'] ?? null;

        $this->render('admin_showtimes', [
            'showtimes' => $showtimes,
            'message' => $message,
            'error' => $error
        ]);
    }

    /**
     * Formularz i logika dodawania nowego seansu
     */
    #[AllowedMethods(['GET', 'POST'])]
    #[IsLoggedIn]
    public function addShowtime(): void
    {
        $this->requireAdmin();

        $movies = $this->movieRepository->getMovies();
        $halls = $this->cinemaRepository->getAllHalls();

        if ($this->isGet()) {
            $this->render('admin_showtime_form', [
                'showtime' => null,
                'movies' => $movies ?? [],
                'halls' => $halls
            ]);
            return;
        }

        // POST - tworzenie seansu
        $movieId = (int)($_POST['movie_id'] ?? 0);
        $hallId = (int)($_POST['hall_id'] ?? 0);
        $startDate = $_POST['start_date'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $technology = $_POST['technology'] ?? '2D';
        $language = $_POST['language'] ?? 'PL';
        $audioType = $_POST['audio_type'] ?? 'dubbed';
        $basePrice = (float)($_POST['base_price'] ?? 0);

        $startDateTime = $startDate . ' ' . $startTime . ':00';

        // Walidacja
        if (!$movieId || !$hallId || empty($startDate) || empty($startTime) || $basePrice <= 0) {
            $this->render('admin_showtime_form', [
                'showtime' => null,
                'movies' => $movies ?? [],
                'halls' => $halls,
                'error' => 'Wszystkie pola są wymagane!'
            ]);
            return;
        }

        try {
            $this->showtimeRepository->addShowtime(
                $movieId, $hallId, $startDateTime, $technology, $language, $audioType, $basePrice
            );

            header('Location: /admin/showtimes?message=Seans został dodany pomyślnie');
            exit();
        } catch (Exception $e) {
            $this->render('admin_showtime_form', [
                'showtime' => null,
                'movies' => $movies ?? [],
                'halls' => $halls,
                'error' => 'Wystąpił błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Formularz i logika edycji seansu
     */
    #[AllowedMethods(['GET', 'POST'])]
    #[IsLoggedIn]
    public function editShowtime(int $id): void
    {
        $this->requireAdmin();

        $showtime = $this->showtimeRepository->getShowtimeById($id);
        $movies = $this->movieRepository->getMovies();
        $halls = $this->cinemaRepository->getAllHalls();

        if (!$showtime) {
            header('Location: /admin/showtimes?error=Seans nie został znaleziony');
            exit();
        }

        if ($this->isGet()) {
            $this->render('admin_showtime_form', [
                'showtime' => $showtime,
                'movies' => $movies ?? [],
                'halls' => $halls
            ]);
            return;
        }

        // POST - aktualizacja seansu
        $movieId = (int)($_POST['movie_id'] ?? 0);
        $hallId = (int)($_POST['hall_id'] ?? 0);
        $startDate = $_POST['start_date'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $technology = $_POST['technology'] ?? '2D';
        $language = $_POST['language'] ?? 'PL';
        $audioType = $_POST['audio_type'] ?? 'dubbed';
        $basePrice = (float)($_POST['base_price'] ?? 0);

        $startDateTime = $startDate . ' ' . $startTime . ':00';

        // Walidacja
        if (!$movieId || !$hallId || empty($startDate) || empty($startTime) || $basePrice <= 0) {
            $this->render('admin_showtime_form', [
                'showtime' => $showtime,
                'movies' => $movies ?? [],
                'halls' => $halls,
                'error' => 'Wszystkie pola są wymagane!'
            ]);
            return;
        }

        try {
            $this->showtimeRepository->updateShowtime(
                $id, $movieId, $hallId, $startDateTime, $technology, $language, $audioType, $basePrice
            );

            header('Location: /admin/showtimes?message=Seans został zaktualizowany');
            exit();
        } catch (Exception $e) {
            $this->render('admin_showtime_form', [
                'showtime' => $showtime,
                'movies' => $movies ?? [],
                'halls' => $halls,
                'error' => 'Wystąpił błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Usuwanie seansu
     */
    #[AllowedMethods(['POST'])]
    #[IsLoggedIn]
    public function deleteShowtime(): void
    {
        $this->requireAdmin();

        $showtimeId = (int)($_POST['showtime_id'] ?? 0);

        if (!$showtimeId) {
            header('Location: /admin/showtimes?error=Nieprawidłowy ID seansu');
            exit();
        }

        try {
            $this->showtimeRepository->deleteShowtime($showtimeId);
            header('Location: /admin/showtimes?message=Seans został usunięty');
            exit();
        } catch (Exception $e) {
            header('Location: /admin/showtimes?error=Błąd podczas usuwania seansu');
            exit();
        }
    }
}