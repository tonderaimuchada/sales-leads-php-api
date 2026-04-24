<?php

declare(strict_types=1);

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class JwtMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Missing or invalid Authorization header');
        }

        $token = substr($authHeader, 7);

        try {
            $secret = $_ENV['JWT_SECRET'] ?? 'secret';
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // Attach user info to request
            $request = $request
                ->withAttribute('user_id', $decoded->sub ?? null)
                ->withAttribute('username', $decoded->username ?? null)
                ->withAttribute('role', $decoded->role ?? 'USER');

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorized('Invalid or expired token: ' . $e->getMessage());
        }
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'status'    => 401,
            'error'     => 'Unauthorized',
            'message'   => $message,
            'timestamp' => date('c'),
        ]));

        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
}
