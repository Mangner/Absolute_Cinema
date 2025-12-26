<?php


require_once 'Repository.php';
require_once __DIR__."/../models/user.php";


class UserRepository extends Repository
{
    public function getUsers(): ?array
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM users');
        $stmt->execute();

        $users = $stmt->fetchALL(PDO::FETCH_CLASS, User::class);
        if (!$users) { return null; }
        return $users;
    }


    public function getUserByEmail(string $email)
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM users WHERE email =:email');

        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);

        $user = $stmt->fetch();
        if ($user === false ) { return null; }
        return $user;
    }


   public function createUser(User $user): void {
    
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
}

