<?php

class User {
    private $email;
    private $password;
    private $name;
    private $surname;

    public function __construct(
        string $email,
        string $password,
        string $name,
        string $surname
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->surname = $surname;
    }

    public function getEmail(): string { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getName(): string { return $this->name; }
    public function getSurname(): string { return $this->surname; }

    public function setEmail(string $email): void { $this->email = $email; }
    public function setName(string $name): void { $this->name = $name; }
    public function setSurname(string $surname): void { $this->surname = $surname; }

}