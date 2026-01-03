<?php

require_once "Repository.php";
require_once __DIR__."/../models/cinema.php";


class CinemaRepository extends Repository {

    public function getCinemas() {
        
        $stmt = $this->database->connect()->prepare('
        SELECT * FROM cinemas'
        );
        $stmt->execute();

        $cinemas = $stmt->fetchAll(PDO::FETCH_CLASS, Cinema::class);
        if (!$cinemas) { return null; }
        return $cinemas;
    }

}


?>