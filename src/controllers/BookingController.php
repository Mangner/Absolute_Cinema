<?php

require_once 'AppController.php';
require_once __DIR__."/../repository/BookingRepository.php";


class BookingController extends AppController {

    public function show(int $movie_id, int $showtime_id) {
        $this->render("booking");
    }
}

?>