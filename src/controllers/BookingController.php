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
        $this->render("booking", [
        'seats' => $seats, 
        'showtime_id' => $showtime_id,
        'movie_id' => $movie_id 
        ]);
    }


    public function checkout() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            return $this->render('booking', ['messages' => ['Błąd przesyłania danych']]);
        }

        $movieId = $_POST['movie_id'] ?? null;
        $showtimeId = $_POST['showtime_id'] ?? null;
        $seatIdsString = $_POST['selected_seats'] ?? ''; 
    
        if (!$movieId || !$showtimeId) {
            header("Location: /dashboard");
            return;
        }

        // Pobierz user_id z sesji (NIE zahardkodowane!)
        $userId = (int) $_SESSION['user_id'];

        $seatIds = explode(',', $seatIdsString);
        try {
            $bookingResult = $this->bookingRepository->createBooking($userId, $showtimeId, $seatIds); 
            return $this->render('payment', [
                'booking_id' => $bookingResult['booking_id'],
                'price' => $bookingResult['total_price']
            ]);

        } catch (Exception $e) {
            echo "Problem: " . $e->getMessage();
        }
    }


    public function processPayment() {
        if (!$this->isPost()) {
            header("Location: /dashboard");
            return;
        }

        $bookingId = $_POST['booking_id'] ?? null;
        if (!$bookingId) {
            header("Location: /dashboard");
            return;
        }
        $paymentSuccess = true; 
        if ($paymentSuccess) {
            try {
                $this->bookingRepository->confirmBooking($bookingId);
                
                $this->render('payment_status', [
                    'status' => 'success',
                    'booking_id' => $bookingId
                ]);

            } catch (Exception $e) {
                $this->render('error_view', ['message' => 'Błąd systemu podczas potwierdzania płatności.']);
            }
        } else {
            $this->render('payment_status', [
                'status' => 'error',
                'booking_id' => $bookingId
            ]);
        }
    }

}

?>