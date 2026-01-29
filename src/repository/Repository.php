<?php

require_once __DIR__.'/../../Database.php';

class Repository {
    protected Database $database;

    public function __construct()
    {
        // Używamy Singletona - jedna instancja Database dla całej aplikacji
        $this->database = Database::getInstance();
    }
}