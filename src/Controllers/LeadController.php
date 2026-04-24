<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\LeadService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LeadController
{
    public function __construct(private readonly LeadService $leadService) {}

    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $filters = [
            'start_date' => $params['start_date'] ?? null,
            'end_date'   => $params['end_date'] ?? null,
            'status'     => $params['status'] ?? null,
        ];

        $page = max(0, (int)($params['page'] ?? 0));
        $size = min(100, max(1, (int)($params['size'] ?? 10)));

        try {
            $result = $this->leadService->getAll($filters, $page, $size);
            return $this->json($response, $result);
        } catch (\RuntimeException $e) {
            return $this->error($response, $e);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $lead = $this->leadService->getById((int)$args['id']);
            return $this->json($response, $lead);
        } catch (\RuntimeException $e) {
            return $this->error($response, $e);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        $body   = (array)$request->getParsedBody();
        $userId = (int)$request->getAttribute('sub', 1);

        try {
            $lead = $this->leadService->create($body, $userId);
            return $this->json($response, $lead, 201);
        } catch (\RuntimeException $e) {
            return $this->error($response, $e);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $body = (array)$request->getParsedBody();

        try {
            $lead = $this->leadService->update((int)$args['id'], $body);
            return $this->json($response, $lead);
        } catch (\RuntimeException $e) {
            return $this->error($response, $e);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $this->leadService->delete((int)$args['id']);
            return $response->withStatus(204);
        } catch (\RuntimeException $e) {
            return $this->error($response, $e);
        }
    }

    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    private function error(Response $response, \RuntimeException $e): Response
    {
        $code = $e->getCode() ?: 500;
        $errorMap = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
        ];

        return $this->json($response, [
            'status'    => $code,
            'error'     => $errorMap[$code] ?? 'Error',
            'message'   => $e->getMessage(),
            'timestamp' => date('c'),
        ], $code);
    }
}
