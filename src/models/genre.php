<?php

class Genre {
    public ?int $genre_id;
    public string $name;
    public string $description;
    
    public function getId() { return $this->genre_id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }

    public function setName() { $this->name = $name; }
    public function setDescription() { $this->description = $description; }
}


?>