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
        ]
    ];


    public static function run(string $path) {
    switch($path) {
        case 'dashboard':
            // co jesli user przekaze w url np. dashboard/1456
            // ? jak przekazac zmienna do akcji z kontrolera
            // Podpowiedź użyć REGEX-a
            $controller = new Routing::$routes[$path]['controller'];
            $action = Routing::$routes[$path]['action'];
            $controller->$action();
            break;
        case 'login':

            #tutaj zmienić architekture żeby zrobić SINGLETON
            $controller = new Routing::$routes[$path]['controller'];
            $action = Routing::$routes[$path]['action'];
            $controller->$action();
            break;

        case 'register':
            #tutaj zmienić architekture żeby zrobić SINGLETON
            $controller = new Routing::$routes[$path]['controller'];
            $action = Routing::$routes[$path]['action'];
            $controller->$action();
            break;

        default:
            include 'public/views/404.html';
            break;
        };
        
    }
}