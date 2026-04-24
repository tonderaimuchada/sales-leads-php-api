<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use PDO;

class AuthService
{
    public function __construct(private readonly PDO $db) {}

    public function login(string $username, string $password): array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new \RuntimeException('Invalid credentials', 401);
        }

        $secret     = $_ENV['JWT_SECRET'] ?? 'secret';
        $expiration = (int)($_ENV['JWT_EXPIRATION'] ?? 86400);
        $issuedAt   = time();
        $expiresAt  = $issuedAt + $expiration;

        $payload = [
            'sub'      => (string)$user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
            'iat'      => $issuedAt,
            'exp'      => $expiresAt,
        ];

        $token = JWT::encode($payload, $secret, 'HS256');

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expiration * 1000,
            'username'   => $user['username'],
            'role'       => $user['role'],
        ];
    }
}
