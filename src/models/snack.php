<?php


class Snack {

    public int $id;
    public string $name;
    public string $category;
    public float $price;
    public string $image;

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getCategory() { return $this->category; }
    public function getPrice() { return $this->price; }
    public function getImage() { return $this->image; }

    public function setName(string $name) { $this->name = $name; }
    public function setCategory(string $category) { $this->category = $category; }
    public function setPrice(float $price) { $this->price = $price; }
    public function setImage(string $image) { $this->image = $image; }
}

?>