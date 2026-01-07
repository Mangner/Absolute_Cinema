<?php

class Cast_Member {
    public ?int $cast_id;
    public string $name;
    public string $role;
    public string $biography;
    public string $image;

    public function getId() : int { return $this->cast_id; }
    public function getName() : string { return $this->name; }
    public function getRole() : string { return $this-> role; }
    public function getBiography() : string { return $this->biography; }
    public function getImageUrl() : string { return $this->image; }
    
    public function setName(string $name) : void { $this->name = $name; }
    public function setRole(string $role) : void { $this->role = $role; }
    public function setBiography(string $biography) : void { $this->biography = $biography; }
    public function setImageUrl(string $image) : void { $this->image = $image; }
    
}
?>