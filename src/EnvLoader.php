<?php
namespace EnvLoader;

class EnvLoader
{
    protected $path;

    public function __construct(string $path)
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
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }

            if (!array_key_exists($name, $_SERVER)) {
                $_SERVER[$name] = $value;
            }

            putenv("$name=$value");
        }
    }

    public function get($key, $default = null)
    {
        return isset($_ENV[$key]) ? $_ENV[$key] : (isset($_SERVER[$key]) ? $_SERVER[$key] : $default);
    }

}
