<?php

namespace App\Support;

/**
 * Support class to be implemented instead of stopping execution with var_dump() or dd()
 * It can be added at any time on any process writting out every type of output
 */
class Debug
{
    /**
     * Custom - needs correct permissions
     * $ sudo chmod 775 storage/logs
     * $ sudo chown $USER:www-data storage/logs/debug.log
     * $ sudo chmod 664 storage/logs/debug.log
     * */
    public static function log(mixed $values = null, string|null $file = 'debug', bool $rewrite = false): void
    {
        $path = dirname(__DIR__, 2).'/storage/logs';

        $file = $path . '/' . $file . '.log';

        $empty = 'null or empty ¯\_(ツ)_/¯';

        if (is_array($values) && isset($values['config'])) {
            $path = isset($values['path']) ? $values['path'] : $path;
            $file = isset($values['file']) ? $values['file'] : $file;
            $rewrite = isset($values['rewrite']) ? $values['rewrite'] : $rewrite;
            $values = isset($values['output']) ? $values['output'] : 'with debug config settings, key "output" must be set!';
        }

        $values = ! empty($values) ? $values : $empty;

        if ($rewrite) {
            file_put_contents($file, '');
        }

        file_put_contents($file, "\n[".date('Y.m.d H:i:s')."]↴\n".print_r($values, true)."\n", FILE_APPEND | LOCK_EX);
    }
}
