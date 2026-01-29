<?php

require_once __DIR__.'/Repository.php';

class ProfileRepository extends Repository
{
    /**
     * Pobiera bilety użytkownika wraz ze szczegółami filmu, seansu i miejsca
     * 
     * @param int $userId ID użytkownika
     * @return array Lista biletów z pełnymi informacjami
     */
    public function getUserTickets(int $userId): array
    {
        $connection = $this->database->connect();
        
        $stmt = $connection->prepare("
            SELECT 
                t.ticket_id,
                t.ticket_token,
                t.price AS ticket_price,
                m.title AS movie_title,
                m.image AS movie_image,
                m.duration AS movie_duration,
                sh.start_time,
                sh.technology,
                sh.language,
                h.name AS hall_name,
                h.type AS hall_type,
                c.name AS cinema_name,
                c.city AS cinema_city,
                s.row_label,
                s.seat_number,
                b.booking_id,
                b.booking_date,
                b.payment_status,
                b.total_price AS booking_total
            FROM tickets t
            INNER JOIN bookings b ON t.booking_id = b.booking_id
            INNER JOIN showtimes sh ON t.showtime_id = sh.showtime_id
            INNER JOIN movies m ON sh.movie_id = m.movie_id
            INNER JOIN halls h ON sh.hall_id = h.hall_id
            INNER JOIN cinemas c ON h.cinema_id = c.cinema_id
            INNER JOIN seats s ON t.seat_id = s.seat_id
            WHERE b.user_id = :user_id
            ORDER BY sh.start_time DESC
        ");

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Pobiera dane użytkownika
     * 
     * @param int $userId ID użytkownika
     * @return array|null Dane użytkownika lub null
     */
    public function getUserById(int $userId): ?array
    {
        $stmt = $this->database->connect()->prepare("
            SELECT user_id, name, surname, email, role, created_at
            FROM users
            WHERE user_id = :user_id
        ");

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Pobiera statystyki użytkownika
     * 
     * @param int $userId ID użytkownika
     * @return array Statystyki (liczba biletów, wydana kwota)
     */
    public function getUserStats(int $userId): array
    {
        $connection = $this->database->connect();
        
        $stmt = $connection->prepare("
            SELECT 
                COUNT(t.ticket_id) AS total_tickets,
                COALESCE(SUM(t.price), 0) AS total_spent
            FROM tickets t
            INNER JOIN bookings b ON t.booking_id = b.booking_id
            WHERE b.user_id = :user_id
        ");

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_tickets' => 0, 'total_spent' => 0];
    }
}
