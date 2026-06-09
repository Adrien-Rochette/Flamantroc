<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        require_once __DIR__ . '/../config/database.php';

        self::$connection = getDatabaseConnection();

        return self::$connection;
    }
}
