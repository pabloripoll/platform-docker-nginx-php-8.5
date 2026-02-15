<?php

namespace App\Controller;

use Core\DB;
use Core\Request;
use Exception;

class WebController
{
    public function home(Request $request)
    {
        $dbstatus_message = '<span>POSTGRE database successfully connected</span>';

        try {
            DB::pg();
        } catch (Exception $e) {
            $dbstatus_message = "<b style=\"color:red\">POSTGRE connection error:</b> " . $e->getMessage();
        }

        return view('home', [
            'php_version' => phpversion(),
            'dbstatus_message' => $dbstatus_message,
        ]);
    }
}

