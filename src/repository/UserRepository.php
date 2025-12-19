<?php


require_once 'Repository.php';


class UserRepository extends Repository
{
    public function getUsers(): ?array
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM users');
        $stmt->execute();

        $users = $stmt->fetchALL(PDO::FETCH_ASSOC);

        return $users;
    }


    public function getUserByEmail(string $email)
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM users WHERE email =:email');

        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }


   public function createUser(string $name, string $surname, string $email, string $password) {
    
        $stmt = $this->database->connect()->prepare('
            INSERT INTO users (name, surname, email, password)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([
            $name,
            $surname,
            $email,
            $password
        ]);
    }
}

