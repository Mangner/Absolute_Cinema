<?php

require_once __DIR__.'/../repository/UserRepository.php';
require_once 'AppController.php';

class SecurityController extends AppController {


    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    
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

        // Odbieramy nowe pola z formularza
        $email = $_POST['email'] ?? "";
        $password = $_POST['password'] ?? "";
        $confirmedPassword = $_POST['confirmedPassword'] ?? "";
        $name = $_POST['name'] ?? "";       // <--- Tutaj zmiana
        $surname = $_POST['surname'] ?? ""; // <--- Tutaj zmiana

        if ($password !== $confirmedPassword) {
            return $this->render('register', ['message' => 'Passwords match error!']);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $name, 
            $surname, 
            $email, 
            $hashedPassword
        );

        return $this->render("login", ['message' => 'Registration completed!']);
    }

}