<?php

declare(strict_types=1);

namespace App\Repository;



use App\DB\DB;
use App\Model\Ingredient;
use App\Model\IngredientType;

final class IngredientsRepository
{
    private $db;

    public function __construct()
    {
        $this->db = new DB();
    }

    public function getIngredientTypes()
    {
        $sql = "SELECT * FROM ingredient_type";
        $result = mysqli_query($this->db->connect(), $sql);
        $types = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $type = new IngredientType($row);
            $types[] = $type;
        }
        return $types;
    }

    public function getIngredients()
    {
        $sql = "SELECT * FROM ingredient";
        $result = mysqli_query($this->db->connect(), $sql);
        $ingredients = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ingredient = new Ingredient($row);
            $ingredients[] = $ingredient;
        }
        return $ingredients;
    }

}