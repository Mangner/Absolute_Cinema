<?php

require_once "Repository.php";
require_once __DIR__."/../models/seat.php";

class BookingRepository extends Repository {
    

    public function getSeats(int $showtime_id) {

        $sql = "DELETE FROM bookings WHERE payment_status = 'PENDING' AND booking_date < NOW() - INTERVAL '15 minutes'";
        $this->database->connect()->exec($sql);

        $sql = '
            SELECT 
                s.seat_id, 
                s.row_label, 
                s.seat_number,
                s.grid_row,
                s.grid_col,
                s.extra_charge,
                (sh.base_price + s.extra_charge) AS price, 
                CASE 
                    WHEN t.ticket_id IS NOT NULL THEN \'ZAJĘTE\' 
                    ELSE \'WOLNE\' 
                END AS status
            FROM seats s
            JOIN showtimes sh ON s.hall_id = sh.hall_id
            LEFT JOIN tickets t ON s.seat_id = t.seat_id AND t.showtime_id = sh.showtime_id
            WHERE sh.showtime_id = :showtime_id
            ORDER BY s.grid_row, s.grid_col; 
        ';

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(":showtime_id", $showtime_id, PDO::PARAM_INT);
        $stmt->execute();

        $seats = $stmt->fetchAll(PDO::FETCH_CLASS, Seat::class);
        return $seats;
    }


    public function createBooking(int $userId, int $showtimeId, array $seatIds): array {
        $pdo = $this->database->connect();
        
        try {
            $pdo->beginTransaction();
            $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
            
            $checkSql = "SELECT seat_id FROM tickets 
                        WHERE showtime_id = ? AND seat_id IN ($placeholders)
                        FOR UPDATE"; 
                        
            $stmt = $pdo->prepare($checkSql);
            $stmt->execute(array_merge([$showtimeId], $seatIds));
            
            if ($stmt->rowCount() > 0) {
                throw new Exception("Jedno z wybranych miejsc zostało właśnie zajęte!");
            }

            $stmt = $pdo->prepare("SELECT base_price FROM showtimes WHERE showtime_id = ?");
            $stmt->execute([$showtimeId]);
            $basePrice = $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT SUM(extra_charge) FROM seats WHERE seat_id IN ($placeholders)");
            $stmt->execute($seatIds);
            $extraCharges = $stmt->fetchColumn();

            $totalPrice = ($basePrice * count($seatIds)) + $extraCharges;

            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, total_price, payment_status, payment_method) 
                VALUES (?, ?, 'PENDING', NULL) RETURNING booking_id
            ");
            $stmt->execute([$userId, $totalPrice]);
            $bookingId = $stmt->fetchColumn();

            $insertTicketSql = "INSERT INTO tickets (booking_id, showtime_id, seat_id, price, ticket_token) VALUES (?, ?, ?, ?, ?)";
            $ticketStmt = $pdo->prepare($insertTicketSql);

            foreach ($seatIds as $seatId) {
                $token = bin2hex(random_bytes(16));
                $thisSeatPrice = $basePrice; 
                $ticketStmt->execute([$bookingId, $showtimeId, $seatId, $totalPrice / count($seatIds), $token]);
            }
            $pdo->commit();
            return ['booking_id' => $bookingId, 'total_price' => $totalPrice];

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function confirmBooking(int $bookingId) {
        $stmt = $this->database->connect()->prepare('
            UPDATE bookings 
            SET payment_status = \'PAID\' 
            WHERE booking_id = :id
        ');
        $stmt->bindParam(':id', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
    }
}