<?php
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';

class Routing {

    public static $routes = [
        'login' => [
            'controller' => "SecurityController",
            'action' => 'login'
        ],
        'register'=> [
            'controller' => "SecurityController",
            'action' => 'register'
        ],
        'dashboard' => [
            'controller' => "DashboardController",
            'action' => 'index'
        ],
        'search-cards' => [
            'controller' => "DashboardController",
            'action' => 'search'
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
        } else {
            http_response_code(404);
            include 'public/views/404.html';
        }
    }
}
