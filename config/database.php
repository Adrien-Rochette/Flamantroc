<?php
declare(strict_types=1);

function loadDatabaseEnvironment(): void
{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    if (!class_exists(Env::class)) {
        require_once __DIR__ . '/../core/Env.php';
    }

    Env::load(__DIR__ . '/.env');
    $loaded = true;
}

function getRequiredEnv(string $key): string
{
    loadDatabaseEnvironment();

    $value = Env::get($key);
    if ($value === null || trim($value) === '') {
        throw new RuntimeException("Variable d'environnement '$key' manquante dans config/.env.");
    }

    return $value;
}

function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getRequiredEnv('DB_HOST');
    $port = getRequiredEnv('DB_PORT');
    $database = getRequiredEnv('DB_NAME');
    $username = getRequiredEnv('DB_USER');
    $password = getRequiredEnv('DB_PASSWORD');

    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        throw new RuntimeException(
            "Erreur de connexion a la base de donnees '$database' sur '$host'.",
            0,
            $exception
        );
    }

    return $pdo;
}
