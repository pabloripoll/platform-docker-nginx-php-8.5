# Slight Framework PHP

[![Generic badge](https://img.shields.io/badge/version-2.0.1-blue.svg)](https://shields.io/)
[![Open Source? Yes!](https://badgen.net/badge/Open%20Source%20%3F/Yes%21/blue?icon=github)](./)
[![MIT license](https://img.shields.io/badge/License-MIT-blue.svg)](./LICENSE)

## Content

- [Structure](#slight-structure)
- [Router](#slight-router)
- [Database](#slight-database)

## <a href="#slight-structure"></a> Slight Structure

```sh
.
│
├── README.md
├── composer.json
├── composer.lock
├── core
│   ├── DB.php
│   ├── Request.php
│   ├── Response.php
│   └── Route.php
├── database
│   ├── Client
│   │   └── Postgre.php
│   └── Migrations
│       └── 20251101_103325_create_users_table.php
│
├── public
│   ├── files
│   │   ├── css
│   │   │   └── styles.css
│   │   └── js
│   │       └── home.js
│   └── index.php
│
├── resources
│   ├── assets
│   │   ├── css
│   │   │   └── styles.css
│   │   └── js
│   │       └── home.js
│   └── views
│       ├── components
│       │   └── logo.php
│       └── home.php
│
├── routes
│   ├── api.php
│   ├── middleware.php
│   └── web.php
│
├── src
│   ├── Controller
│   │   ├── ApiController.php
│   │   └── WebController.php
│   ├── Middleware
│   │   ├── AuthMiddleware.php
│   │   └── GlobalMiddleware.php
│   ├── Service
│   │   ├── Broker.php
│   │   ├── Mailer.php
│   │   ├── TaskDispatcher.php
│   │   └── TaskQueue.php
│   ├── Support
│   │   ├── Debug.php
│   │   └── Helper.php
│   └── Task
│       └── EmailTask.php
│
├── storage
│   └── logs
│       ├── debug.log
│       └── errors.log
│
└── worker.php
```

## <a href="#slight-router"></a> Slight Router

`./routes/api.php`
```php
use Core\Route;
use App\Controller\ApiController;

Route::post('/api/test/mail', [ApiController::class, 'testMail']);
Route::post('/api/test/queue', [ApiController::class, 'testQueue']);
Route::get('/api/users/{id}', [ApiController::class, 'show'], ['AuthMiddleware']);
Route::post('/api/users/{id}/notes', [ApiController::class, 'addNote'], ['AuthMiddleware']);
```

## <a href="#slight-router"></a> Slight Database

Engines are set in.
```php
namespace Database\Client;

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
```

Thus, databases connections are customizable.
```php
namespace Core;

use Database\Client\Postgre;

class DB
{
    public static function core()
    {
        $host = env('PG_CORE_HOST');
        $port = env('PG_CORE_PORT');
        $name = env('PG_CORE_NAME');
        $user = env('PG_CORE_USER');
        $pass = env('PG_CORE_PASS');

        return new Postgre(
            $host,
            $port,
            $name,
            $user,
            $pass,
        );
    }

    public static function logs()
    {
        $host = env('PG_LOGS_HOST');
        $port = env('PG_LOGS_PORT');
        $name = env('PG_LOGS_NAME');
        $user = env('PG_LOGS_USER');
        $pass = env('PG_LOGS_PASS');

        return new Postgre(
            $host,
            $port,
            $name,
            $user,
            $pass,
        );
    }
}
```

Controller example
```php
namespace App\Controller;

use Core\DB;
use Core\Request;
use Exception;

class ApiTestController
{
    /**
     * GET /api/test/database
     */
    public function database(Request $request)
    {
        $response = [
            'status' => true,
            'message' => 'Database successfully connected.',
        ];

        try {
            DB::core();
        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Database connection error.',
                'error' => $e->getMessage(),
            ];
        }

        return response()->json($response, 200);
    }
}
```