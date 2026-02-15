<?php

namespace Core;

use Exception;

class Response
{
    /**
     * Return JSON string and set headers (does not exit).
     */
    public function json(array|object $data, ?int $httpCode = 200): string
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Create a view template from resources/views
     */
    public function view(string $script, array|object|null $data = null): string
    {
        return $this->resource('view', $script, $data);
    }

    /**
     * Includes a view template from resources/views
     */
    public function includes(string $script, array|object|null $data = null): string
    {
        return $this->resource('includes', $script, $data);
    }

    /**
     * Requires a view template from resources/views
     */
    public function requires(string $script, array|object|null $data = null): string
    {
        return $this->resource('requires', $script, $data);
    }

    /**
     * Render a resource view. Returns the rendered content as string.
     *
     * - $action: 'view'|'include'|'require'
     * - $script: dot.notation.to.path (e.g. "home" => resources/views/home.php)
     * - $data: array|object|null, extracted into view scope (EXTR_SKIP)
     */
    public function resource(string $action, string $script, array|object|null $data = null): string
    {
        // convert dot notation to path and build absolute path
        $scriptPath = str_replace('.', '/', $script) . '.php';

        $fullPath = dirname(__DIR__, 1) . '/resources/views/' . $scriptPath;

        if (!is_file($fullPath)) {
            http_response_code(500);
            return "View not found: {$script} (looked for {$fullPath})";
        }

        // prepare $data for extraction
        $vars = [];
        if ($data !== null) {
            if (is_object($data)) {
                $vars = (array) $data;
            } elseif (is_array($data)) {
                $vars = $data;
            }
        }

        // extract variables into local scope for the view
        if (!empty($vars)) {
            extract($vars, EXTR_SKIP);
        }

        try {
            ob_start();

            // include/require according to action
            switch ($action) {
                case 'includes':
                case 'view':
                    include $fullPath;
                    break;
                case 'requires':
                    require $fullPath;
                    break;
                default:
                    include $fullPath;
                    break;
            }

            $content = (string) ob_get_clean();

            return $content;

        } catch (Exception $e) {
            // ensure we return readable error instead of blank page
            return 'Caught exception: ' . $e->getMessage();
        }
    }
}
