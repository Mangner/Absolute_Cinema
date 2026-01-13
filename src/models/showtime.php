<?php


class Showtime {

    public ?int $showtime_id;
    public int $movie_id;
    public int $hall_id;
    public string $start_time;
    public ?string $technology;
    public string $language;
    public string $audio_type;
    public float $base_price;
    
    
    public function getId() : int { return $this->showtime_id; }
    public function getMovieId() : int { return $this->moive_id; }
    public function getHallId() : int { return $this->hall_id; }
    public function getStartTime() : string { return $this->start_time; }
    public function getTechnology() : string { return $this->technology; }
    public function getLanguage() : string { return $this->language; }
    public function getAudioType() : string { return $this->audio_type; }
    public function getAudioTypeLabel() : string {
        return match($this->audio_type) {
            'dubbed' => 'Dubbing',
            'subtitled' => 'Napisy',
            'voiceover' => 'Lektor',
            'original' => 'Oryginał',
            default => $this->audio_type
        };
    }
    public function getPrice() : float { return $this->base_price; }

    public function setMovieId(int $movie_id) : void { $this->movie_id = $movie_id; }
    public function setHallId(int $hall_id) : void { $this->hall_id = $hall_id; }
    public function setStartTime(string $start_time) : void { $this->start_time = $start_time; }
    public function setTechnology(string $technology) : void { $this->technology = $technology; }
    public function setPrice(float $price) : void { $this->base_price = $price; }
}


?>