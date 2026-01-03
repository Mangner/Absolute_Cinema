<?php

require_once 'AppController.php';
require_once __DIR__."/../repository/showtimeRepository.php";


class MovieController extends AppController {

    private $showtimeRepository;

    public function __construct() {
        $this->showtimeRepository = new ShowtimeRepository();
    }


    public function getDetails() {
        echo "Dziala";
    }

}


?>