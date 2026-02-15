<?php

namespace Core;

use Database\Client\Postgre;

class DB
{
    public static function pg()
    {
        return new Postgre;
    }
}
