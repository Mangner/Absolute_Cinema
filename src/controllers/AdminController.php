<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../middleware/Attribute/AllowedMethods.php';
require_once __DIR__.'/../middleware/Attribute/IsLoggedIn.php';

use Middleware\Attribute\AllowedMethods;
use Middleware\Attribute\IsLoggedIn;

/**
 * AdminController - Panel Administratora
 * 
 * Wszystkie metody wymagają zalogowania oraz roli 'admin'.
 */
class AdminController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * Sprawdza, czy zalogowany użytkownik ma rolę admina.
     * Jeśli nie - przekierowuje na dashboard.
     */
    private function requireAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->url('dashboard');
            exit();
        }

        return true;
    }

    /**
     * Główny widok panelu admina (alias dla users)
     */
    #[AllowedMethods(['GET'])]
    #[IsLoggedIn]
    public function index(): void
    {
        $this->requireAdmin();
        $this->users();
    }

    /**
     * Lista wszystkich użytkowników
     */
    #[AllowedMethods(['GET'])]
    #[IsLoggedIn]
    public function users(): void
    {
        $this->requireAdmin();

        $users = $this->userRepository->getUsers();
        $message = $_GET['message'] ?? null;
        $error = $_GET['error'] ?? null;

        $this->render('admin_users', [
            'users' => $users ?? [],
            'message' => $message,
            'error' => $error
        ]);
    }

    /**
     * Formularz i logika dodawania nowego użytkownika
     */
    #[AllowedMethods(['GET', 'POST'])]
    #[IsLoggedIn]
    public function addUser(): void
    {
        $this->requireAdmin();

        if ($this->isGet()) {
            $this->render('admin_user_form', [
                'mode' => 'add',
                'user' => null
            ]);
            return;
        }

        // POST - tworzenie użytkownika
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        // Walidacja
        if (empty($name) || empty($surname) || empty($email) || empty($password)) {
            $this->render('admin_user_form', [
                'mode' => 'add',
                'user' => null,
                'error' => 'Wszystkie pola są wymagane!'
            ]);
            return;
        }

        // Sprawdź, czy email już istnieje
        $existingUser = $this->userRepository->getUserByEmail($email);
        if ($existingUser) {
            $this->render('admin_user_form', [
                'mode' => 'add',
                'user' => null,
                'error' => 'Użytkownik z tym adresem email już istnieje!'
            ]);
            return;
        }

        try {
            $this->userRepository->createUserWithRole($name, $surname, $email, $password, $role);
            header('Location: /admin/users?message=Użytkownik został dodany pomyślnie');
            exit();
        } catch (Exception $e) {
            $this->render('admin_user_form', [
                'mode' => 'add',
                'user' => null,
                'error' => 'Wystąpił błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Formularz i logika edycji użytkownika
     */
    #[AllowedMethods(['GET', 'POST'])]
    #[IsLoggedIn]
    public function editUser(int $id): void
    {
        $this->requireAdmin();

        $user = $this->userRepository->getUserById($id);
        
        if (!$user) {
            header('Location: /admin/users?error=Użytkownik nie został znaleziony');
            exit();
        }

        if ($this->isGet()) {
            $this->render('admin_user_form', [
                'mode' => 'edit',
                'user' => $user
            ]);
            return;
        }

        // POST - aktualizacja użytkownika
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $newPassword = $_POST['password'] ?? '';

        // Walidacja
        if (empty($name) || empty($surname) || empty($email)) {
            $this->render('admin_user_form', [
                'mode' => 'edit',
                'user' => $user,
                'error' => 'Imię, nazwisko i email są wymagane!'
            ]);
            return;
        }

        // Sprawdź, czy email nie jest zajęty przez innego użytkownika
        $existingUser = $this->userRepository->getUserByEmail($email);
        if ($existingUser && $existingUser->getId() !== $id) {
            $this->render('admin_user_form', [
                'mode' => 'edit',
                'user' => $user,
                'error' => 'Ten adres email jest już używany przez innego użytkownika!'
            ]);
            return;
        }

        try {
            $this->userRepository->updateUser($id, $name, $surname, $email, $role, $newPassword);
            header('Location: /admin/users?message=Dane użytkownika zostały zaktualizowane');
            exit();
        } catch (Exception $e) {
            $this->render('admin_user_form', [
                'mode' => 'edit',
                'user' => $user,
                'error' => 'Wystąpił błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Usuwanie użytkownika
     */
    #[AllowedMethods(['POST'])]
    #[IsLoggedIn]
    public function deleteUser(): void
    {
        $this->requireAdmin();

        $userId = (int)($_POST['user_id'] ?? 0);

        if (!$userId) {
            header('Location: /admin/users?error=Nieprawidłowy ID użytkownika');
            exit();
        }

        // Nie pozwól adminowi usunąć samego siebie
        if ($userId === (int)$_SESSION['user_id']) {
            header('Location: /admin/users?error=Nie możesz usunąć swojego własnego konta!');
            exit();
        }

        try {
            $this->userRepository->deleteUser($userId);
            header('Location: /admin/users?message=Użytkownik został usunięty');
            exit();
        } catch (Exception $e) {
            header('Location: /admin/users?error=Błąd podczas usuwania użytkownika');
            exit();
        }
    }
}
