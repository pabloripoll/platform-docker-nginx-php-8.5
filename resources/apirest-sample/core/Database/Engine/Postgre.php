<?php

namespace Core\Database\Engine;

use PDO;

class Postgre
{
    public $db;

    public function __construct(
        string $host,
        string $port,
        string $name,
        string $user,
        string $pass,
    )
    {
        $db = new PDO("pgsql:host=$host;port=$port;dbname=$name", $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this->db;
    }
}
