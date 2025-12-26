<?php

class Name {
    private string $value;

    public function __construct(string $name) {
        $name = trim($name);
        if (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ \'-]+$/', $name)) {
            throw new InvalidArgumentException("Imię zawiera niedozwolone znaki!");
        }
        $this->value = $name;
    }

    public function __toString(): string {
        return $this->value;
    }
}

?>