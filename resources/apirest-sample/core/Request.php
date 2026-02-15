<?php

namespace Core;

class Request
{
    public string $method;
    public string $route;
    public array $get;
    public array $post;
    public ?object $put;
    public ?object $patch;
    public array $files;

    public function __construct()
    {
        $request = $this->composite();

        $this->method = $request->method ?? 'get';
        $this->route  = $request->route ?? '/';
        $this->get    = $request->get ?? [];
        $this->post   = $request->post ?? [];
        $this->put    = $request->put ?? null;
        $this->patch  = $request->patch ?? null;
        $this->files  = $request->files ?? [];
    }

    protected function composite(): object
    {
        // avoid notices if keys aren't defined
        if (isset($_GET['q'])) {
            unset($_GET['q']);
        }

        $request = [
            'method' => strtolower($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            'route'  => preg_replace('~(\.php|\.html)?(\?.*)?$~i', '', $_SERVER['REQUEST_URI'] ?? '/'),
            'get'    => $_GET ?? [],
            'post'   => $_POST ?? [],
            'put'    => null,
            'patch'  => null,
            'files'  => $_FILES ?? [],
        ];

        if ($request['method'] === 'put') {
            $request['put'] = json_decode(file_get_contents('php://input'));
        }

        if ($request['method'] === 'patch') {
            $request['patch'] = json_decode(file_get_contents('php://input'));
        }

        return (object) $request;
    }
}