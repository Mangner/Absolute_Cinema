<?php

class RegisterUserDTO {
    public function __construct(
        public readonly string $name,
        public readonly string $surname,
        public readonly string $email,
        public readonly string $password,
        public readonly string $confirmedPassword
    )
    {}
}


?>