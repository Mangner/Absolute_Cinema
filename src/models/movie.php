<?php


class Movie {

    public ?int $movie_id;
    public string $title;
    public string $original_title;
    public string $description;
    public string $director;
    public string $release_date;
    public string $image;
    public ?string $trailer_url;
    public float $price;
    public float $duration;
    public string $production_country;
    public string $original_language;
    public string $age_rating;
    public float $imdb_rating;
    public int $rotten_tomatoes_rating;
    public int $metacritic_rating;
    public string $created_at;

    public function getId() : ?int { return $this->movie_id; }
    public function getTitle() : string { return $this->title; }
    public function getOriginalTitle() : string { return $this->original_title; }
    public function getDescription() : string { return $this->description; }
    public function getDirector() : string { return $this->director; }
    public function getReleaseDate() : string {return $this->release_date; } 
    public function getImageUrl() : string { return $this->image; }
    public function getTrailerUrl() : ?string { return $this->trailer_url; }
    public function getPrice() : float { return $this->price; }
    public function getDuration() : float { return $this->duration; }
    public function getProductionCountry() : string { return $this->production_country; }
    public function getOriginalLanguage() : string { return $this->original_language; }
    public function getAgeRating() : string { return $this->age_rating; }
    public function getImdbRating() : float { return $this->imdb_rating; }
    public function getRottenTomatoesRating() : int { return $this->rotten_tomatoes_rating; }
    public function getMetacriticRating() : int { return $this->metacritic_rating; }

    public function setTitle(string $title) : void { $this->title = $title; }
    public function setDescription(string $description) : void { $this->description = $description; }
    public function setDirector(string $director) : void { $this->director = $director; }
    public function setReleaseDate(string $release_date) : void { $this->release_date = $release_date; }
    public function setImageURL(string $image) : void { $this->image = $image; }
    public function setPrice(float $price) : void { $this->price = $price; }
    public function setDuration(float $duration) : void { $this->duration = $duration; }
    public function setProductionCountry(string $production_country) : void { $this->production_country = $production_country; }
    public function setOriginalLanguage(string $original_language) : void { $this->original_language = $original_language; }
    public function setAgeRating(string $age_rating) : void { $this->age_rating = $age_rating; }
    public function seteImdbRating(float $imdb_rating) : void { $this->imdb_rating = $imdb_rating; }
    public function setRottenTomatoesRating(int $rotten_tomatoes_rating) : void { $this->rotten_tomatoes_rating = $rotten_tomatoes_rating; }
    public function setMetacriticRating(int $metacritic_rating) : void { $this->metacritic_rating = $metacritic_rating; }
}

?>