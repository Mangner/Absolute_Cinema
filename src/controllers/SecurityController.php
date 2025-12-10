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

        $user = $this->userRepository->getUserByEmail($email);

        if (!$user) {
            return $this->render("login", ["message" => "User not exists!"]);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->render("login", ['message' => "Wrong password"]);
        }
    
        // TODO create user session/ cookie/ token 

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_firstname'] = $user['name'] ?? null;
        $_SESSION['is_logged_in'] = true;

        return $this->url("dashboard");
    }

    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        session_destroy();

        return $this->url("login");
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