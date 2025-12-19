<?php

require_once 'Repository.php';


class SnacksRepository extends Repository {

    public function getSnacks() {

        $smtm = $this->database->connect()->prepare('
            SELECT * FROM food_items'
        );

        $smtm->execute();
        return $smtm->fetchAll(PDO::FETCH_ASSOC);
    }
}