<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use App\Shared\Application\Message\CreateJobMessage;
use App\Api\Infrastructure\Request\CreateJobRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class JobController
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    #[Route(
        path: '/api/v1/job',
        name: 'api_job_create',
        methods: ['POST']
    )]
    public function create(#[MapRequestPayload] CreateJobRequest $request): JsonResponse
    {
        $jobId = Uuid::v7();

        $this->messageBus->dispatch(new CreateJobMessage(
            $jobId,
            $request->author,
            $request->title,
            $request->titleType
        ));

        return new JsonResponse(
            data: ['id' => $jobId->toString()],
            status: 202
        );
    }
}
