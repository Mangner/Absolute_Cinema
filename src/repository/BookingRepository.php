<?php

require_once "Repository.php";
require_once __DIR__."/../models/seat.php";

class BookingRepository extends Repository {
    

    public function getSeats(int $showtime_id) {

        $sql = '
            SELECT s.seat_id, s.row_label, s.seat_number,
            CASE 
                WHEN t.ticket_id IS NOT NULL THEN \'ZAJĘTE\' 
                ELSE \'WOLNE\' 
            END AS status, t.price
            FROM seats s
            JOIN showtimes sh ON s.hall_id = sh.hall_id
            LEFT JOIN tickets t ON s.seat_id = t.seat_id AND t.showtime_id = sh.showtime_id
            WHERE sh.showtime_id = :showtime_id
            ORDER BY s.row_label, s.seat_number;
        ';

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(":showtime_id", $showtime_id, PDO::PARAM_INT);
        $stmt->execute();

        $seats = $stmt->fetchAll(PDO::FETCH_CLASS, Seat::class);
        return $seats;
    }
}