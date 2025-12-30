<?php

class Email {

    private string $value;

    public function __construct(string $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Niepoprawny format adresu email :) !");
        }
        $this->value = $email;
    }


    public function __toString(): string {
        return $this->value;
    }

}

?>