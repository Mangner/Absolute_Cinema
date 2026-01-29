<?php


require_once 'Repository.php';
require_once __DIR__."/../models/user.php";


class UserRepository extends Repository
{
    /**
     * Pobiera wszystkich użytkowników
     */
    public function getUsers(): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT user_id, name, surname, email, role, created_at 
            FROM users 
            ORDER BY user_id ASC
        ');
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_CLASS, User::class);
        if (!$users) { return null; }
        return $users;
    }

    /**
     * Pobiera użytkownika po ID
     */
    public function getUserById(int $id): ?User
    {
        $stmt = $this->database->connect()->prepare('
            SELECT user_id, name, surname, email, role, created_at 
            FROM users 
            WHERE user_id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
        $user = $stmt->fetch();
        
        if ($user === false) { return null; }
        return $user;
    }

    /**
     * Pobiera użytkownika po adresie email
     */
    public function getUserByEmail(string $email): ?User
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM users WHERE email = :email');

        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);

        $user = $stmt->fetch();
        if ($user === false) { return null; }
        return $user;
    }


    /**
     * Tworzy nowego użytkownika (używane przy rejestracji)
     */
    public function createUser(User $user): void 
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO users (name, surname, email, password)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([
            $user->getName(),
            $user->getSurname(),
            $user->getEmail(),
            $user->getPassword()
        ]);
    }

    /**
     * Tworzy nowego użytkownika z rolą (używane przez admina)
     */
    public function createUserWithRole(
        string $name, 
        string $surname, 
        string $email, 
        string $password, 
        string $role = 'user'
    ): void 
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->database->connect()->prepare('
            INSERT INTO users (name, surname, email, password, role)
            VALUES (:name, :surname, :email, :password, :role)
        ');

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
    }

    /**
     * Aktualizuje dane użytkownika
     */
    public function updateUser(
        int $id, 
        string $name, 
        string $surname, 
        string $email, 
        string $role,
        string $newPassword = ''
    ): void 
    {
        $conn = $this->database->connect();

        if (!empty($newPassword)) {
            // Aktualizacja z nowym hasłem
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare('
                UPDATE users 
                SET name = :name, surname = :surname, email = :email, role = :role, password = :password
                WHERE user_id = :id
            ');
            $stmt->bindParam(':password', $hashedPassword);
        } else {
            // Aktualizacja bez zmiany hasła
            $stmt = $conn->prepare('
                UPDATE users 
                SET name = :name, surname = :surname, email = :email, role = :role
                WHERE user_id = :id
            ');
        }

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
    }

    /**
     * Usuwa użytkownika
     */
    public function deleteUser(int $id): void
    {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM users WHERE user_id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Zlicza wszystkich użytkowników
     */
    public function countUsers(): int
    {
        $stmt = $this->database->connect()->prepare('SELECT COUNT(*) FROM users');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Zlicza użytkowników według roli
     */
    public function countUsersByRole(string $role): int
    {
        $stmt = $this->database->connect()->prepare('
            SELECT COUNT(*) FROM users WHERE role = :role
        ');
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}

