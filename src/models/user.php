<?php

require_once __DIR__."/../valueObjects/Email.php";
require_once __DIR__."/../valueObjects/Password.php";

class User {

    public ?int $user_id;
    public string $name;
    public string $surname;
    public string $email;
    public string $password;
    public ?string $role;
    public string $created_at;

    public function __construct() {
    }

    
    public static function create(string $name, string $surname, Email $email, string $passwordHash): self 
    {
        $user = new self();
        $user->name = $name;
        $user->surname = $surname;
        $user->email = $email; // Wyciągamy stringa z Value Object!
        $user->password = $passwordHash;
        return $user;
    }

    public function getId(): int { return $this->user_id; }
    public function getName(): string { return $this->name; }
    public function getSurname(): string { return $this->surname; }
    public function getEmail(): string { return $this->email; }
    public function getPassword() { return $this->password; }

    public function setName(string $name): void { $this->name = $name; }
    public function setSurname(string $surname): void { $this->surname = $surname; }
    public function setEmail(string $email): void { $this->email = $email; }

}