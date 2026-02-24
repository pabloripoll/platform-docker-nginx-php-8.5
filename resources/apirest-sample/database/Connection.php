<?php

namespace Database;

use Core\Database\Engine\Postgre;

class Connection
{
    public static function core()
    {
        $host = env('PG_HOST');
        $port = env('PG_PORT');
        $name = env('PG_NAME');
        $user = env('PG_USER');
        $pass = env('PG_PASS');

        return new Postgre(
            $host,
            $port,
            $name,
            $user,
            $pass,
        );
    }
}
