<?php

class Seat {

    public ?int $seat_id;
    public int $seat_number;
    public string $row_label;
    public string $status;
    public ?float $price;


    public function getId() : ?int { return $this->seat_id; }
    public function getSeatNumber() : int { return $this->seat_number; }
    public function getSeatRow() : string { return $this->row_label; }
    public function getStatus() : string { return $this->status; }
    public function getPrice() : ?float { return $this->price; }

    public function setSeatNumber(int $seat_number) : void { $this->seat_number = $seat_number; }
    public function setRow(string $row) : void { $this->row_label = $row; }
    public function setStatus(string $status) : void { $this->status = $status; }
    public function setPrice(float $price) : void { $this->price = $price; }

}

?>