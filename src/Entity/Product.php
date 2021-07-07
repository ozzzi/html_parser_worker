<?php

namespace App\Entity;

class Product
{
    public $name;

    public $price;

    public $image;

    public function __construct(
        string $name,
        float $price,
        string $image
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->image = $image;
    }
}