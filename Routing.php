<?php
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/MovieController.php';
require_once 'src/controllers/BookingController.php';

class Routing {

    public static $routes = [
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
        ]
    ];

    // REGEX NA ROUTINGU ZEBY POBRAC ID
    // DI - SIGNGLETON
    // Sesja Uzytkownika
    // Security Bingo

    public static function run($url) {
        
        if (array_key_exists($url, self::$routes)) {
            $controller = self::$routes[$url]['controller']; 
            $action = self::$routes[$url]['action'];
            $object = new $controller;
            $object->$action();

        } else if (preg_match('/^movie\/(\d+)$/', $url, $matches)) {
            
            $controller = self::$routes['movie']['controller'];
            $action = self::$routes['movie']['action'];
            $object = new $controller;

            $movieId = (int)$matches[1];

            $object->$action($movieId);

        } else if (preg_match('/^movie\/(\d+)\/(\d+)$/', $url, $matches)) {

            $controller = self::$routes['booking']['controller'];
            $action = self::$routes['booking']['action'];
            $object = new $controller;

            $movieId = (int)$matches[1];
            $showtimeId = (int)$matches[2];

            $object->$action($movieId, $showtimeId);

        } else {
            http_response_code(404);
            include 'public/views/404.html';
        }
    }
}
