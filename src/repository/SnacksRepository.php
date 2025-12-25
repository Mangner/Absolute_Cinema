<?php

require_once 'Repository.php';
require_once __DIR__."/../models/snack.php";

class SnacksRepository extends Repository {

    public function getSnacks() {

        $smtm = $this->database->connect()->prepare('
            SELECT * FROM food_items'
        );

        $smtm->execute();
        $snacks =  $smtm->fetchAll(PDO::FETCH_CLASS, Snack::class);
        if (!$snacks) { return null; }
        return $snacks;
    }
}