<?php
namespace config;

use Dotenv\Dotenv;
use PDO;

class Config {
    private static $instance = null;
    private $db;
    private $rootPath;
    private $uploadPath;
    private $uploadUrl;
    private $basePath;

        private function __construct() {

            // Set the root path dynamically. __DIR__ is the 'src/config' directory.

            // dirname(__DIR__, 2) will give us the project root.

            $this->rootPath = dirname(__DIR__, 2);

            $this->uploadPath = $this->rootPath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

            

            // BASE_URL from .env (fallback localhost:8080)

            $baseUrl = getenv('BASE_URL') ?: 'http://localhost:8080';

            $this->uploadUrl = rtrim($baseUrl, '/') . '/uploads/';

    

            // Load .env from the project root

            if (file_exists($this->rootPath . DIRECTORY_SEPARATOR . '.env')) {

                $dotenv = Dotenv::createImmutable($this->rootPath);

                $dotenv->load();

            }

        $host = getenv('POSTGRES_HOST') ?: 'localhost';
        $port = getenv('POSTGRES_PORT') ?: '5432';
        $dbName = getenv('POSTGRES_DB') ?: 'tienda';
        $user = getenv('POSTGRES_USER') ?: 'admin';
        $pass = getenv('POSTGRES_PASSWORD') ?: 'adminPassword123';

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
        $this->db = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function getInstance(): Config {
        if (!self::$instance) self::$instance = new Config();
        return self::$instance;
    }

    public function __get($name) { return $this->$name ?? null; }
    public function __set($name, $value) { $this->$name = $value; }
}
