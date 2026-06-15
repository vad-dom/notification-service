<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API микросервиса уведомлений',
    title: 'Notification Service API'
)]
#[OA\Server(
    url: 'http://localhost:8080/api',
    description: 'Local API server'
)]
#[OA\SecurityScheme(
    securityScheme: 'ApiToken',
    type: 'http',
    description: 'Use token: test-token',
    bearerFormat: 'Token',
    scheme: 'bearer'
)]
#[OA\SecurityScheme(
    securityScheme: 'ProviderToken',
    type: 'apiKey',
    description: 'Use token: super-secret-token',
    name: 'X-Provider-Token',
    in: 'header'
)]
class OpenApi {}
