<?php

class Seat {

    public ?int $seat_id;
    public int $seat_number;
    public string $row_label;
    public int $grid_row;
    public int $grid_col;
    public string $status;
    public ?float $price;
    public ?float $extra_charge;

    public function getId() : ?int { return $this->seat_id; }
    public function getSeatNumber() : int { return $this->seat_number; }
    public function getSeatRow() : string { return $this->row_label; }
    public function getGridRow() : int { return $this->grid_row; }
    public function getGridCol() : int { return $this->grid_col; }
    public function getStatus() : string { return $this->status; }
    public function getPrice() : ?float { return $this->price; }


    public function setSeatNumber(int $seat_number) : void { $this->seat_number = $seat_number; }
    public function setRow(string $row) : void { $this->row_label = $row; }
    public function setGridRow(int $grid_row) : void { $this->grid_row = $grid_row; }
    public function setGridCol(int $grid_col) : void { $this->grid_col = $grid_col; }
    public function setStatus(string $status) : void { $this->status = $status; }
    public function setPrice(float $price) : void { $this->price = $price; }

}

?>