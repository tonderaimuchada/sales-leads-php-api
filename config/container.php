<?php

declare(strict_types=1);

use App\Models\Database;
use App\Services\AuthService;
use App\Services\LeadService;
use Psr\Container\ContainerInterface;

return [
    'db' => function () {
        return Database::getInstance();
    },

    AuthService::class => function (ContainerInterface $c) {
        return new AuthService($c->get('db'));
    },

    LeadService::class => function (ContainerInterface $c) {
        return new LeadService($c->get('db'));
    },
];
