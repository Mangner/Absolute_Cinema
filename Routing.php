<?php
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/MovieController.php';
require_once 'src/controllers/BookingController.php';
require_once 'src/controllers/ProfileController.php';
require_once 'src/controllers/AdminController.php';
require_once 'src/middleware/MiddlewareHandler.php';

class Routing {

    private static ?Routing $instance = null;

    private array $routes = [
        'login' => [
            'controller' => "SecurityController",
            'action' => 'login'
        ],
        'logout' => [
            'controller' => "SecurityController",
            'action' => 'logout'
        ],
        'register'=> [
            'controller' => "SecurityController",
            'action' => 'register'
        ],
        'dashboard' => [
            'controller' => "DashboardController",
            'action' => 'index'
        ],
        'profile' => [
            'controller' => "ProfileController",
            'action' => 'index'
        ],
        'search-movies' => [
            'controller' => "DashboardController",
            'action' => 'search'
        ],
        'get-OnScreen-movies' => [
            'controller' => "DashboardController",
            'action' => 'getOnScreenMovies'
        ],
        'get-Upcoming-movies' => [
            'controller' => "DashboardController",
            'action' => 'getUpcomingMovies'
        ],
        'get-snacks' => [
            'controller' => "DashboardController",
            'action' => 'getSnacks'
        ],
        'get-cinemas' => [
            'controller' => "DashboardController",
            'action' => 'getCinemas'
        ],
        'get-showtimes' => [
            'controller' => "MovieController",
            'action' => 'getShowtimes'
        ],
        'set-cinema' => [
            'controller' => "DashboardController",
            'action' => 'setCinema'
        ],
        'movie' => [
            'controller' => "MovieController",
            'action' => 'getDetails'
        ],
        'booking' => [
            'controller' => "BookingController",
            'action' => 'show'
        ],
        'checkout' => [
            'controller' => "BookingController",
            'action' => 'checkout'
        ],
        'payment/process' => [
            'controller' => "BookingController",
            'action' => 'processPayment'
        ],
        // Admin routes - Users
        'admin' => [
            'controller' => "AdminController",
            'action' => 'index'
        ],
        'admin/users' => [
            'controller' => "AdminController",
            'action' => 'users'
        ],
        'admin/user/add' => [
            'controller' => "AdminController",
            'action' => 'addUser'
        ],
        'admin/user/delete' => [
            'controller' => "AdminController",
            'action' => 'deleteUser'
        ],
        // Admin routes - Movies
        'admin/movies' => [
            'controller' => "AdminController",
            'action' => 'movies'
        ],
        'admin/movie/add' => [
            'controller' => "AdminController",
            'action' => 'addMovie'
        ],
        'admin/movie/delete' => [
            'controller' => "AdminController",
            'action' => 'deleteMovie'
        ],
        // Admin routes - Showtimes
        'admin/showtimes' => [
            'controller' => "AdminController",
            'action' => 'showtimes'
        ],
        'admin/showtime/add' => [
            'controller' => "AdminController",
            'action' => 'addShowtime'
        ],
        'admin/showtime/delete' => [
            'controller' => "AdminController",
            'action' => 'deleteShowtime'
        ]
    ];

    
    private function __construct() {}

  
    private function __clone() {}

   
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }

   
    public static function getInstance(): Routing {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function run(string $url): void {
        
        if (array_key_exists($url, $this->routes)) {
            $controller = $this->routes[$url]['controller']; 
            $action = $this->routes[$url]['action'];
            $object = new $controller;

            // Weryfikacja atrybutów przez Middleware
            if (!MiddlewareHandler::handle($object, $action)) {
                return; // Middleware obsłużył błąd (405, 401, etc.)
            }

            $object->$action();

        } else if (preg_match('/^movie\/(\d+)$/', $url, $matches)) {
            
            $controller = $this->routes['movie']['controller'];
            $action = $this->routes['movie']['action'];
            $object = new $controller;

            // Weryfikacja atrybutów przez Middleware
            if (!MiddlewareHandler::handle($object, $action)) {
                return;
            }

            $movieId = (int)$matches[1];
            $object->$action($movieId);

        } else if (preg_match('/^movie\/(\d+)\/(\d+)$/', $url, $matches)) {

            $controller = $this->routes['booking']['controller'];
            $action = $this->routes['booking']['action'];
            $object = new $controller;

            // Weryfikacja atrybutów przez Middleware
            if (!MiddlewareHandler::handle($object, $action)) {
                return;
            }

            $movieId = (int)$matches[1];
            $showtimeId = (int)$matches[2];

            $object->$action($movieId, $showtimeId);

        } else if (preg_match('/^admin\/user\/edit\/(\d+)$/', $url, $matches)) {
            // Edycja użytkownika
            $object = new AdminController;
            $action = 'editUser';

            // Weryfikacja atrybutów przez Middleware
            if (!MiddlewareHandler::handle($object, $action)) {
                return;
            }

            $userId = (int)$matches[1];
            $object->$action($userId);

        } else if (preg_match('/^admin\/movie\/edit\/(\d+)$/', $url, $matches)) {
            // Edycja filmu
            $object = new AdminController;
            $action = 'editMovie';

            // Weryfikacja atrybutów przez Middleware
            if (!MiddlewareHandler::handle($object, $action)) {
                return;
            }

            $movieId = (int)$matches[1];
            $object->$action($movieId);

        } else if (preg_match('/^admin\/showtime\/edit\/(\d+)$/', $url, $matches)) {
            // Edycja seansu
            $object = new AdminController;
            $action = 'editShowtime';

            // Weryfikacja atrybutów przez Middleware
            if (!MiddlewareHandler::handle($object, $action)) {
                return;
            }

            $showtimeId = (int)$matches[1];
            $object->$action($showtimeId);

        } else {
            http_response_code(404);
            include 'public/views/404.html';
        }
    }
}
