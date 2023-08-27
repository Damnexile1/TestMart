<?php

declare(strict_types=1);

namespace App\Model;

class Dish
{
    public $ingredients = [];

    public function addIngredient($ingredient)
    {
        $this->ingredients[] = $ingredient;
    }

}