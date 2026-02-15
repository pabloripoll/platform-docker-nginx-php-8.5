<?php

namespace Database\Client;

use PDO;

class Postgre
{
    public $db;

    public function __construct()
    {
        $host = env('PG_HOST');
        $port = env('PG_PORT');
        $name = env('PG_NAME');
        $user = env('PG_USER');
        $pass = env('PG_PASS');

        $db = new PDO("pgsql:host=$host;port=$port;dbname=$name", $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this->db;
    }
}
