<?php


class Cinema {

    public ?int $cinema_id;
    public string $name;
    public string $city;
    public string $address;


    public function getId() : int { return $this->cinema_id; }
    public function getName() : string { return $this->name; }
    public function getCity() : string { return $this->city; }
    public function getAddress() : string { return $this->address; }

    public function setName(string $name) : void { $this->name = $name; }
    public function setCity(string $city) : void { $this->city = $city; }
    public function setAddress(string $address) : void { $this->address = $address; }
}


?>