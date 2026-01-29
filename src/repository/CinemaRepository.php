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

    /**
     * Pobiera wszystkie sale kinowe z informacją o kinie
     */
    public function getAllHalls(): array
    {
        $sql = "
            SELECT 
                h.hall_id,
                h.cinema_id,
                h.name AS hall_name,
                h.type,
                c.name AS cinema_name,
                c.city AS cinema_city
            FROM halls h
            INNER JOIN cinemas c ON h.cinema_id = c.cinema_id
            ORDER BY c.city, c.name, h.name
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Pobiera sale dla konkretnego kina
     */
    public function getHallsByCinemaId(int $cinemaId): array
    {
        $sql = "
            SELECT hall_id, name, type 
            FROM halls 
            WHERE cinema_id = :cinema_id
            ORDER BY name
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

}


?>