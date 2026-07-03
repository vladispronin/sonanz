<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use App\Api\Infrastructure\Attribute\PublicRoute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Firebase\JWT\JWT;
use Symfony\Component\Uid\Uuid;

class TokenController
{
    private const string JWT_ISS = 'sonanz';

    #[PublicRoute]
    #[Route(
        path: '/api/v1/token',
        name: 'api_token_create',
        methods: ['POST']
    )]
    public function create(): JsonResponse
    {
        $userId = Uuid::v7();

        $payload = [
            'iss' => self::JWT_ISS,
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => $userId->toString(),
        ];

        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

        return new JsonResponse([
            'token' => $token,
        ]);
    }
}
