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

        //Не смог сделать гибким код, получилась жоская привязка к тому, что мы знаем какие у нас точно ингридиенты :((((((
        //TODO можем обсудить как это решить)
        $ingredientTypes = array(
            'd' => array_filter($ingredients, function($ingredient) { return $ingredient['type_id'] == '1'; }),
            'c' => array_filter($ingredients, function($ingredient) { return $ingredient['type_id'] == '2'; }),
            'i' => array_filter($ingredients, function($ingredient) { return $ingredient['type_id'] == '3'; }),
        );

        $combinations = $this->generateCombinations($this->template, $ingredientTypes);

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
