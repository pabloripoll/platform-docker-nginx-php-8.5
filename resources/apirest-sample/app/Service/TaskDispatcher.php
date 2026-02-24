<?php
namespace App\Service;

class TaskDispatcher
{
    /**
     * Task register
     *
     * Map task-name => handler.
     * Handler can be:
     *  - a callable (closure, [object, method], [ClassName, 'method'])
     *  - an array of [ClassName::class, 'method'] (dispatcher will instantiate class)
     *  - a string "ClassName@method"
     *
     * @return array<string, callable|string|array>
     */
    public function tasks(): array
    {
        return [
            'installation-test-email' => [\App\Task\EmailTask::class, 'installationTestEmail'],
        ];
    }

    /**
     * Dispatch task to the appropriate handler.
     *
     * @param string $task Task key as defined in tasks()
     * @param array $payload Job-specific payload
     * @param array $envelope Message metadata (message_id, attempts, headers, etc.)
     * @return bool true on success, false on transient/fatal failure
     */
    public function dispatch(string $task, array $payload = [], array $envelope = []): bool
    {
        $map = $this->tasks();

        if (! array_key_exists($task, $map)) {
            error_log("[dispatcher] No handler registered for task '{$task}'");
            return false;
        }

        $handler = $map[$task];

        try {
            // If the handler is a direct callable, just call it.
            if (is_callable($handler)) {
                $result = call_user_func($handler, $payload, $envelope);

                return $this->taskDispatched($result, $task);
            }

            // If handler is a "Class@method" string, convert to [class, method]
            if (is_string($handler) && strpos($handler, '@') !== false) {
                [$class, $method] = explode('@', $handler, 2);
                $handler = [$class, $method];
            }

            // If handler is an array [classOrObj, method]
            if (is_array($handler) && count($handler) === 2) {
                [$classOrObject, $method] = $handler;

                // If first element is an object instance, call method on it
                if (is_object($classOrObject)) {
                    if (! method_exists($classOrObject, $method)) {
                        error_log("[dispatcher] Method {$method} not found on object for task '{$task}'");

                        return false;
                    }
                    $result = $classOrObject->{$method}($payload, $envelope);

                    return $this->taskDispatched($result, $task);
                }

                // If first element is a class name string
                if (is_string($classOrObject) && class_exists($classOrObject)) {
                    // If static method is callable, call it statically
                    if (is_callable([$classOrObject, $method])) {
                        $result = $classOrObject::{$method}($payload, $envelope);

                        return $this->taskDispatched($result, $task);
                    }

                    // Otherwise, instantiate the class and call the method on the instance
                    $instance = new $classOrObject();
                    if (! method_exists($instance, $method)) {
                        error_log("[dispatcher] Method {$method} not found on instance of {$classOrObject} for task '{$task}'");

                        return false;
                    }

                    $result = $instance->{$method}($payload, $envelope);

                    return $this->taskDispatched($result, $task);
                }

                error_log("[dispatcher] Invalid handler configuration for task '{$task}'");

                return false;
            }

            error_log("[dispatcher] Unsupported handler type for task '{$task}'");

            return false;

        } catch (\Throwable $e) {
            error_log("[dispatcher] Exception from handler for task '{$task}': " . $e->getMessage());

            return false;
        }
    }

    /**
     * Normalize handler result to boolean and log non-boolean returns.
     */
    protected function taskDispatched($result, string $task): bool
    {
        if (is_bool($result)) {
            return $result;
        }

        // Some handlers may return null or other types: treat truthy as success, falsy as failure,
        // but log to make handler behavior explicit.
        error_log("[dispatcher] Handler for task '{$task}' returned non-boolean result (" . gettype($result) . "). Coercing to boolean.");

        return (bool)$result;
    }
}
