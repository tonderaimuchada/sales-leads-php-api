<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(Request $request, Response $response): Response
    {
        $body = (array)$request->getParsedBody();

        $username = trim($body['username'] ?? '');
        $password = trim($body['password'] ?? '');

        if (empty($username) || empty($password)) {
            return $this->json($response, [
                'status'  => 400,
                'error'   => 'Bad Request',
                'message' => 'Username and password are required',
            ], 400);
        }

        try {
            $result = $this->authService->login($username, $password);
            return $this->json($response, $result, 200);
        } catch (\RuntimeException $e) {
            return $this->json($response, [
                'status'  => $e->getCode() ?: 401,
                'error'   => 'Unauthorized',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 401);
        }
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
