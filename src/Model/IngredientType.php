<?php

declare(strict_types=1);

namespace App\Model;

class IngredientType
{
    public $id;
    public $title;
    public $code;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->code = $data['code'];
    }
}