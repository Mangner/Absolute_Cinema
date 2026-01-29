<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__."/../valueObjects/Email.php";
require_once __DIR__."/../valueObjects/Password.php";
require_once __DIR__."/../valueObjects/Name.php";
require_once __DIR__."/../DTOs/RegisterUserDTO.php";
require_once __DIR__."/../models/user.php";
require_once __DIR__.'/../middleware/Attribute/AllowedMethods.php';
require_once __DIR__.'/../middleware/Attribute/IsLoggedIn.php';

use Middleware\Attribute\AllowedMethods;
use Middleware\Attribute\IsLoggedIn;


class SecurityController extends AppController {


    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }


    #[AllowedMethods(['GET', 'POST'])]
    public function login()
    {
        if (!$this->isPost()) {
            return $this->render('login');
        }
        
        try {
            $emailVo = new Email($_POST["email"] ?? ''); 
        } catch (InvalidArgumentException $e) {
            return $this->render("login", ["message" => "Podaj poprawny adres email!"]);
        }

        $user = $this->userRepository->getUserByEmail((string)$emailVo);

        if (!$user) {
            return $this->render("login", ["message" => "Użytkownik nie istnieje!"]);
        }

        if (!password_verify($_POST["password"], $user->getPassword())) {
            return $this->render("login", ['message' => "Błędne hasło!"]);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId(); 
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_firstname'] = $user->getName() ?? null;
        $_SESSION['is_logged_in'] = true;

        return $this->url("dashboard");
    }

    
    #[AllowedMethods(['GET'])]
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


    #[AllowedMethods(['GET', 'POST'])]
    public function register() {

        if (!$this->isPost()) {
            return $this->render('register');
        }

        $registerDTO = new RegisterUserDTO(
            $_POST['name'] ?? '',
            $_POST['surname'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? '',
            $_POST['confirmedPassword'] ?? ''  
        );

        try {
            $nameVo = new Name($registerDTO->name);
            $surnameVo = new Name($registerDTO->surname);
            $emailVo = new Email($registerDTO->email);
            $passwordVo = new Password($registerDTO->password, $registerDTO->confirmedPassword);

            $userEntity = User::create(
                $nameVo,
                $surnameVo,
                $emailVo,
                $passwordVo->getHash()
            );

            $this->userRepository->createUser($userEntity);
            return $this->render("login", ['message' => 'Registration completed!']);

        } catch (InvalidArgumentException $e) {
            return $this->render('register', ['message' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->render('register', ['message' => 'Wystąpił błąd serwera.']);
        }
    }

}