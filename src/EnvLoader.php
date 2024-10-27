<?php

namespace EnvLoader;

class EnvLoader
{
    protected $path;

    public function __construct($path)
    {
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
    }

    public function load()
    {
        $envFile = $this->path . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($envFile)) {
            throw new \Exception(".env file not found at: {$envFile}");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = $this->sanitizeValue($value);

            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }

            if (!array_key_exists($name, $_SERVER)) {
                $_SERVER[$name] = $value;
            }

            putenv("$name=$value");
        }
    }

    protected function sanitizeValue($value)
    {
        $value = trim($value);

        // Remove surrounding quotes for PHP 7 compatibility
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")
        ) {
            $value = substr($value, 1, -1);
        }

        // Handle boolean and null-like values
        if (strcasecmp($value, 'true') === 0) {
            return true;
        } elseif (strcasecmp($value, 'false') === 0) {
            return false;
        } elseif (strcasecmp($value, 'null') === 0) {
            return null;
        }

        // Attempt to cast numeric values
        if (is_numeric($value)) {
            return $value + 0; // Converts to int or float as appropriate
        }

        return $value;
    }

    public function get($key, $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}
