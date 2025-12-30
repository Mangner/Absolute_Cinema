<?php


class Movie {

    public $id;
    public $title;
    public $description;
    public $director;
    public $release_date;
    public $image;
    public $price;
    public $duration;

    
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getDirector() { return $this->director; }
    public function getReleaseDate()  {return $this->release_date; } 
    public function getImageUrl() { return $this->image_url; }
    public function getPrice() { return $this->price; }
    public function getDuration() { return $this->duration; }

    public function setTitle(string $title) { $this->title = $title; }
    public function setDescription(string $description) { $this->description = $description; }
    public function setDirector(string $director) { $this->director = $director; }
    public function setReleaseDate(string $release_date) { $this->release_date = $release_date; }
    public function setImageURL(string $image) { $this->image = $image; }
    public function setPrice(float $price) { $this->price = $price; }
    public function setDuration(float $duration) { $this->duration = $duration; }

}

?>