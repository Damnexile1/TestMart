<?php

// Класс для работы с БД
namespace App\DB;

use mysqli;

final class DB
{
    public function connect(): mysqli
    {
        return new mysqli($_ENV["HOST"], $_ENV["USER"], $_ENV["PASSWORD"], $_ENV["DB_NAME"]);
    }
}