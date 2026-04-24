<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\LeadController;
use App\Middleware\JwtMiddleware;

// Auth routes (public)
$app->post('/api/auth/login', [AuthController::class, 'login']);

// Leads routes (protected)
$app->group('/api/leads', function ($group) {
    $group->get('', [LeadController::class, 'index']);
    $group->post('', [LeadController::class, 'store']);
    $group->get('/{id}', [LeadController::class, 'show']);
    $group->put('/{id}', [LeadController::class, 'update']);
    $group->delete('/{id}', [LeadController::class, 'destroy']);
})->add(JwtMiddleware::class);
