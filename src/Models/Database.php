<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getInstance(): PDO
    {
        if (self::$connection === null) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '5432';
            $name = $_ENV['DB_NAME'] ?? 'leadsdb';
            $user = $_ENV['DB_USER'] ?? 'postgres';
            $pass = $_ENV['DB_PASSWORD'] ?? 'postgres';

            $dsn = "pgsql:host={$host};port={$port};dbname={$name}";

            try {
                self::$connection = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
