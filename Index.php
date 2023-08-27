<?php
namespace App;

use App\Builder\DishBuilder;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();



$builder = new DishBuilder('dddcciiii');
$dishes = $builder->buildDishes();

// или через ввод
//$line = trim(fgets(STDIN));
//$builder = new DishBuilder($line);

print_r($dishes);

