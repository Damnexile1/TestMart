<?php

declare(strict_types=1);

namespace App\Builder;


use App\Repository\IngredientsRepository;

final class DishBuilder
{
    private IngredientsRepository $repo;
    private string $template;

    public function __construct($template)
    {
        $this->repo = new IngredientsRepository();
        $this->template = $template;
    }

    /**
     * @return false|string
     */
    public function buildDishes()
    {
        $ingredients = $this->getIngridients();
        $ingredientsTypes = $this->getIngredientsTypes();
        $types = $this->getTypes($ingredientsTypes, $ingredients);
        $combinations = $this->generateCombinations($this->template, $types);

        $json = json_encode($combinations);
        $uniqueCombinations = json_decode($json, true);

        $uniqueCombinations = array_map("unserialize", array_unique(array_map("serialize", $uniqueCombinations)));

        $uniqueCombinations = array_values($uniqueCombinations);

        return json_encode($uniqueCombinations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }


    /**
     * @param string $input
     * @param array $ingredientTypes
     * @param array $usedIngredients
     * @return array|array[]
     */
    public function generateCombinations(string $input, array $ingredientTypes, array $usedIngredients = array()): array
    {
        if (empty($input)) {
            return array(array('products' => array(), 'price' => 0));
        }

        $combinations = array();
        $ingredientType = $ingredientTypes[$input[0]];
        $remainingInput = substr($input, 1);

        foreach ($ingredientType as $ingredient) {
            if (in_array($ingredient['title'], $usedIngredients)) {
                continue;
            }

            $subCombinations = $this->generateCombinations($remainingInput, $ingredientTypes, array_merge($usedIngredients, array($ingredient['title'])));

            foreach ($subCombinations as $subCombination) {
                $products = $subCombination['products'];
                $price = $subCombination['price'];

                $products[] = array('type' => $ingredient['type_id'], 'value' => $ingredient['title']);
                $price += $ingredient['price'];

                usort($products, function ($a, $b) {
                    if ($a['type'] == $b['type']) {
                        return strcmp($a['value'], $b['value']);
                    }
                    return $a['type'] <=> $b['type'];
                });

                $combinations[] = array('products' => $products, 'price' => $price);
            }
        }

        return $combinations;
    }

    /**
     * @return array
     */
    public function getIngredientsTypes(): array
    {
        $iingridientsType = $this->repo->getIngredientTypes();
        $ingredientsTypes = array();

        foreach ($iingridientsType as $item) {
            $ingredientsTypes[] = [
                'code' => $item->code, 'type_id' => $item->id,
            ];
        }

        return $ingredientsTypes;
    }

    /**
     * @param array $ingredientsTypes
     * @param array $ingredients
     * @return array
     */
    public function getTypes(array $ingredientsTypes, array $ingredients): array
    {
        $types = [];

        foreach ($ingredientsTypes as $ingredientType) {
            $types[$ingredientType["code"]] =
                array_filter($ingredients, function($ingredient) use ($ingredientType) {
                    return $ingredient['type_id'] == $ingredientType["type_id"];
                });
        }

        return array_map(function($v) {
            return $v;
        }, $types);
    }

    /**
     * @return array
     */
    public function getIngridients(): array
    {
        $iingridients = $this->repo->getIngredients();
        $result = array();

        foreach ($iingridients as $iingridient){
            $result[] = array(
                'title' => $iingridient->title, 'type_id' => $iingridient->type_id, 'price' => $iingridient->price
            );
        }

        return $result;
    }


//.
//⣿⣿⣿⣿⣿⣿⣿⣿⡿⠿⠛⠛⠛⠋⠉⠈⠉⠉⠉⠉⠛⠻⢿⣿⣿⣿⣿⣿⣿⣿
//⣿⣿⣿⣿⣿⡿⠋⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠉⠛⢿⣿⣿⣿⣿
//⣿⣿⣿⣿⡏⣀⠀⠀⠀⠀⠀⠀⠀⣀⣤⣤⣤⣄⡀⠀⠀⠀⠀⠀⠀⠀⠙⢿⣿⣿
//⣿⣿⣿⢏⣴⣿⣷⠀⠀⠀⠀⠀⢾⣿⣿⣿⣿⣿⣿⡆⠀⠀⠀⠀⠀⠀⠀⠈⣿⣿
//⣿⣿⣟⣾⣿⡟⠁⠀⠀⠀⠀⠀⢀⣾⣿⣿⣿⣿⣿⣷⢢⠀⠀⠀⠀⠀⠀⠀⢸⣿
//⣿⣿⣿⣿⣟⠀⡴⠄⠀⠀⠀⠀⠀⠀⠙⠻⣿⣿⣿⣿⣷⣄⠀⠀⠀⠀⠀⠀⠀⣿
//⣿⣿⣿⠟⠻⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠶⢴⣿⣿⣿⣿⣿⣧⠀⠀⠀⠀⠀⠀⣿
//⣿⣁⡀⠀⠀⢰⢠⣦⠀⠀⠀⠀⠀⠀⠀⠀⢀⣼⣿⣿⣿⣿⣿⡄⠀⣴⣶⣿⡄⣿
//⣿⡋⠀⠀⠀⠎⢸⣿⡆⠀⠀⠀⠀⠀⠀⣴⣿⣿⣿⣿⣿⣿⣿⠗⢘⣿⣟⠛⠿⣼
//⣿⣿⠋⢀⡌⢰⣿⡿⢿⡀⠀⠀⠀⠀⠀⠙⠿⣿⣿⣿⣿⣿⡇⠀⢸⣿⣿⣧⢀⣼
//⣿⣿⣷⢻⠄⠘⠛⠋⠛⠃⠀⠀⠀⠀⠀⢿⣧⠈⠉⠙⠛⠋⠀⠀⠀⣿⣿⣿⣿⣿
//⣿⣿⣧⠀⠈⢸⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠟⠀⠀⠀⠀⢀⢃⠀⠀⢸⣿⣿⣿⣿
//⣿⣿⡿⠀⠴⢗⣠⣤⣴⡶⠶⠖⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⡸⠀⣿⣿⣿⣿
//⣿⣿⣿⡀⢠⣾⣿⠏⠀⠠⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠛⠉⠀⣿⣿⣿⣿
//⣿⣿⣿⣧⠈⢹⡇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣰⣿⣿⣿⣿
//⣿⣿⣿⣿⡄⠈⠃⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣠⣴⣾⣿⣿⣿⣿⣿
//⣿⣿⣿⣿⣧⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣠⣾⣿⣿⣿⣿⣿⣿⣿⣿⣿
//⣿⣿⣿⣿⣷⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣴⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿
//⣿⣿⣿⣿⣿⣦⣄⣀⣀⣀⣀⠀⠀⠀⠀⠘⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿
//⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣷⡄⠀⠀⠀⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿

}
