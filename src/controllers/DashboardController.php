<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../repository/CardsRepository.php';

class DashboardController extends AppController {

    private $cardsRepository;

    public function __construct() {
        $this->cardsRepository = new CardsRepository();
    }

    public function index() {
        $cards = [
    [
        'id' => 1,
        'title' => 'Ace of Spades',
        'subtitle' => 'Legendary card',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/AS.png',
        'href' => '/cards/ace-of-spades'
    ],
    [
        'id' => 2,
        'title' => 'Queen of Hearts',
        'subtitle' => 'Classic romance',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/QH.png',
        'href' => '/cards/queen-of-hearts'
    ],
    [
        'id' => 3,
        'title' => 'King of Clubs',
        'subtitle' => 'Royal strength',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/KC.png',
        'href' => '/cards/king-of-clubs'
    ],
    [
        'id' => 4,
        'title' => 'Jack of Diamonds',
        'subtitle' => 'Sly and sharp',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/JD.png',
        'href' => '/cards/jack-of-diamonds'
    ],
    [
        'id' => 5,
        'title' => 'Ten of Hearts',
        'subtitle' => 'Lucky draw',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/0H.png',
        'href' => '/cards/ten-of-hearts'
    ],
    ];


        $userRepository = new UserRepository();
        $users = $userRepository->getUsers();

        var_dump($users);

        return $this->render('dashboard');
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

        if (!$contentType != "application/json") {
            http_response_code(415);
            echo json_encode([
                'status' => 'Application/json content type not found'
            ]);
        }

        //TODO wyciągnac odpowiedni content 
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        $searchTag = $decoded['search'];
        

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'cards' => $this->cardsRepository->getCardsByTitle($searchTag)
        ]);
        return;
    }
}