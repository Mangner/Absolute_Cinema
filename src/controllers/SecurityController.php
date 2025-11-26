<?php

require_once __DIR__.'/../repository/UserRepository.php';
require_once 'AppController.php';

class SecurityController extends AppController {


    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }
    // public function login() {
        

    //     if (!$this->isPost()) {
    //         return $this->render("login");
    //     }       
        
    //     var_dump($_POST);
        
    //     // TODO pobieramy z formularza email, haslo
    //     // TODO sprawdzamy czy takie user istnieje w db
    //     // jesli nie istnieje to zwaracamy, odpowiednie komunikaty
    //     // jesli istnieje, to przekierowujemy go do dashboard

    //     return $this->render("dashboard");
    // }

    // public function register() {
        
    //     if (!$this->isPost()) {
    //         return $this->render("register");
    //     }  

    //     var_dump($_POST);

    //     $email = $_POST['email'] ?? '';
    //     $password = $_POST['password'] ?? '';

    //     if ($email === '') {
    //         return $this->render("login", ['message'=> 'Podaj email']);
    //     }
    //     var_dump($email, $password);

    //     return $this->render("login");

    // }


      // ======= LOKALNA "BAZA" UŻYTKOWNIKÓW =======
    private static array $users = [
        [
            'email' => 'anna@example.com',
            'password' => '$2y$10$wz2g9JrHYcF8bLGBbDkEXuJQAnl4uO9RV6cWJKcf.6uAEkhFZpU0i', // test123
            'first_name' => 'Anna'
        ],
        [
            'email' => 'bartek@example.com',
            'password' => '$2y$10$fK9rLobZK2C6rJq6B/9I6u6Udaez9CaRu7eC/0zT3pGq5piVDsElW', // haslo456
            'first_name' => 'Bartek'
        ],
        [
            'email' => 'celina@example.com',
            'password' => '$2y$10$Cq1J6YMGzRKR6XzTb3fDF.6sC6CShm8kFgEv7jJdtyWkhC1GuazJa', // qwerty
            'first_name' => 'Celina'
        ],
    ];


    public function login()
    {



        if (!$this->isPost()) {
            return $this->render('login');
        }
        
        
        
        $userRepository = new UserRepository();
        $users = $userRepository->getUsers($email);

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        if (empty($email) || empty($password)) {
            return $this->render('login', ['messages' => 'Fill all fields']);
        }

       //TODO replace with search from database
        $userRow = null;
        foreach (self::$users as $u) {
            if (strcasecmp($u['email'], $email) === 0) {
                $userRow = $u;
                break;
            }
        }

        if (!$userRow) {
            return $this->render('login', ['messages' => 'User not found']);
        }

        if (!password_verify($password, $userRow['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        // TODO możemy przechowywać sesje użytkowika lub token
        // setcookie("username", $userRow['email'], time() + 3600, '/');

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/dashboard");
    }

    public function register()
    {
        if (!$this->isPost()) {
            return $this->render('register');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm-password'] ?? '';

        if (empty($email) || empty($password) || empty($confirm_password)) {
            return $this->render('register', ['messages' => 'Fill all fields']);
        }

	// TODO this will be checked in database
        foreach (self::$users as $u) {
            if (strcasecmp($u['email'], $email) === 0) {
                return $this->render('register', ['messages' => 'Email is taken']);
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        self::$users[] = [
            'email' => $email,
            'password' => $hashedPassword,
            'first_name' => $confirm_password
        ];

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
    }

}