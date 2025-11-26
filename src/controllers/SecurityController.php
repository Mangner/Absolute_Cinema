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
        

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        $userRepository = new UserRepository();
        $users = $userRepository->getUsers($email);


        $userRepository = new UserRepository();
        $user = $userRepository->getUserByEmail($email);

        if (!$user) {
            return $this->render("login", ["message" => "User not exists!"]);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->render("login", ['message' => "Wrong password"]);
        }
    
        // TODO create user session/ cookie/ token 
        return $this->render("dashboard");
    }

    

    public function register()
    {
        if (!$this->isPost()) {
            return $this->render('register');
        }

        var_dump($_POST);
        $email = $_POST['email'] ?? "";
        $password = $_POST['password1'] ?? "";
        $password2 = $_POST['password2'] ?? "";
        $firstname = $_POST['firstname'] ?? "";
        $lastname = $_POST['lastname'] ?? "";

        if (empty($email || empty($password) || empty($firstname))) {
            return $this->render('register', ['message' => 'Fill all fields']);
        }

        if ($password !== $password2) {
            return $this->render('register', ['message' => 'Passwords should be the same!']);
        }


        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->userRepository->createUser(
            $email,
            $hashedPassword,
            $firstname,
            $lastname
        );

        return $this->render("login", ['message' => 'Registration completed, please login']);
    }

}