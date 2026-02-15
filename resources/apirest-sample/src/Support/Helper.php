<?php

/**
 * Helper functions
 */

if (! function_exists('env')) {
    /**
     * Get an environment value.
     * - Loads .env once (uses vlucas/phpdotenv if installed, otherwise falls back to getenv()/$_ENV).
     * - Returns parsed values (handles "true","false","null","empty" and quoted strings).
     *
     * @param string|null $key     If null, returns null (or you can extend to return all envs)
     * @param mixed       $default Default value if env not set
     */
    function env(?string $key = null, $default = null): mixed
    {
        static $loaded = false;

        // Load .env once if vlucas/phpdotenv is available and a .env file exists.
        if (! $loaded) {
            $projectRoot = dirname(__DIR__, 2); // assuming src/Support -> projectRoot/src/Support
            $envPath = $projectRoot . '/.env';

            if (file_exists($envPath) && class_exists(\Dotenv\Dotenv::class)) {
                // createImmutable will not overwrite existing env vars by default
                try {
                    Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
                } catch (\Throwable $e) {
                    // ignore loading errors; fall back to getenv/$_ENV
                }
            }

            $loaded = true;
        }

        if ($key === null) {
            return $default;
        }

        // Prefer $_ENV / $_SERVER ?? $_SERVER[$key] / then getenv() ?? getenv($key)
        $value = $_ENV[$key] ?? null;

        if ($value === false || $value === null) {
            return $default;
        }

        // Normalize strings like "true", "false", "null", "empty"
        $lower = strtolower($value);

        if ($lower === 'true' || $lower === '(true)') {
            return true;
        }

        if ($lower === 'false' || $lower === '(false)') {
            return false;
        }

        if ($lower === 'null' || $lower === '(null)') {
            return null;
        }

        if ($lower === 'empty' || $lower === '(empty)') {
            return '';
        }

        // Strip surrounding quotes
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (! function_exists('response')) {
    function response()
    {
        return new Core\Response;
    }
}

if (! function_exists('view')) {
    function view(string $script, array|object|null $data = null)
    {
        return (new Core\Response)->resource('view', $script, $data);
    }
}

if (! function_exists('includes')) {
    function includes(string $script, array|object|null $data = null)
    {
        return (new Core\Response)->resource('includes', $script, $data);
    }
}

if (! function_exists('assets')) {
    function assets(string $path, string $prefix = 'assets'): string
    {
        // derive scheme+host from current request (works for most setups)
        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
        // If request run from CLI or host missing, default to empty base -> relative URLs
        $base = $host ? $scheme . '://' . $host : '';

        $prefix = trim($prefix, '/');
        $path = ltrim($path, '/');

        $url = ($base ? $base : '') . '/' . ($prefix ? $prefix . '/' : '') . $path;

        // If base is empty, return relative path without leading double slash
        if ($base === '') {
            $url = '/' . ($prefix ? $prefix . '/' : '') . $path;
        }

        return $url;
    }
}

// Small helper examples — guard each function to avoid collisions
if (! function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $v) {
            var_dump($v);
        }
        exit(1);
    }
}

if (! function_exists('debug')) {
    function debug(mixed $values = null): void
    {
        App\Support\Debug::log($values);
    }
}

if (! function_exists('asset')) {
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}
