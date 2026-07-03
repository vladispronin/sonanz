<?php

declare(strict_types=1);

namespace App\Api\Infrastructure\Controller;

use App\Shared\Application\Message\CreateJobMessage;
use App\Api\Infrastructure\Request\CreateJobRequest;
use App\Shared\Application\Message\DownloadFileMessage;
use App\Shared\Application\Message\GetJobsMessage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
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
    public function create(
        #[MapRequestPayload] CreateJobRequest $request,
        Request $httpRequest,
    ): JsonResponse
    {
        $userId = $httpRequest->attributes->get('clientId');

        $jobId = Uuid::v7();

        $this->messageBus->dispatch(new CreateJobMessage(
            $jobId,
            $userId,
            $request->author,
            $request->title,
            $request->titleType,
            $request->withMetadata,
        ));

        return new JsonResponse(
            data: ['id' => $jobId->toString()],
            status: 202
        );
    }

    #[Route(
        path: '/api/v1/job',
        name: 'api_job_get',
        methods: ['GET']
    )]
    public function get(
        Request $httpRequest,
    ): JsonResponse
    {
        $userId = $httpRequest->attributes->get('clientId');

        $envelope = $this->messageBus->dispatch(new GetJobsMessage(
            $userId,
        ));

        $jobs = $envelope->last(HandledStamp::class)->getResult();

        return new JsonResponse(
            data: $jobs,
            status: 200
        );
    }

    #[Route(
        path: '/api/v1/job/{jobId}/download',
        name: 'api_file_download',
        methods: ['GET']
    )]
    public function download(string $jobId): BinaryFileResponse
    {
        $envelope = $this->messageBus->dispatch(
            new DownloadFileMessage(
                Uuid::fromString($jobId)
            )
        );
        $filePath = $envelope->last(HandledStamp::class)->getResult();

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($filePath)
        );

        return $response;
    }
}
