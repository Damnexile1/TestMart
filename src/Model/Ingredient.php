<?php

declare(strict_types=1);

namespace App\Model;

class Ingredient
{

    public $id;
    public $title;
    public $type_id;
    public $price;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->type_id = $data['type_id'];
        $this->price = $data['price'];
    }

}