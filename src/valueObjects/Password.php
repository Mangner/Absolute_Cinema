<?php

class Password {

    private string $value;


    public function __construct(string $password, string $confirmedPassword) {
        if ($password !== $confirmedPassword) {
            throw new InvalidArgumentException("Hasła nie są identyczne");
        }

        if (strlen($password) < 6) {
            throw new InvalidArgumentException("Hasło musi mieć mininum 6 znaków!");
        }

        $this->value = $password;
    }

    public function getHash(): string {
        return password_hash($this->value, PASSWORD_BCRYPT);
    }
}


?>