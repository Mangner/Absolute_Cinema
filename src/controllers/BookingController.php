<?php

require_once 'AppController.php';
require_once __DIR__."/../repository/BookingRepository.php";


class BookingController extends AppController {

    private $bookingRepository;

    public function __construct() {
        $this->bookingRepository = new BookingRepository();
    }

    public function show(int $movie_id, int $showtime_id) {

        $seats = $this->bookingRepository->getSeats($showtime_id);
        $this->render("booking", ['seats' => $seats]);
    }
}

?>