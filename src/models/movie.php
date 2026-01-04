<?php


class Movie {

    public ?int $movie_id;
    public string $title;
    public string $description;
    public string $director;
    public string $release_date;
    public string $image;
    public float $price;
    public float $duration;

    public function getId() : ?int { return $this->movie_id; }
    public function getTitle() : string { return $this->title; }
    public function getDescription() : string { return $this->description; }
    public function getDirector() : string { return $this->director; }
    public function getReleaseDate() : string {return $this->release_date; } 
    public function getImageUrl() : string { return $this->image; }
    public function getPrice() : float { return $this->price; }
    public function getDuration() : float { return $this->duration; }

    public function setTitle(string $title) : void { $this->title = $title; }
    public function setDescription(string $description) : void { $this->description = $description; }
    public function setDirector(string $director) : void { $this->director = $director; }
    public function setReleaseDate(string $release_date) : void { $this->release_date = $release_date; }
    public function setImageURL(string $image) : void { $this->image = $image; }
    public function setPrice(float $price) : void { $this->price = $price; }
    public function setDuration(float $duration) : void { $this->duration = $duration; }

}

?>