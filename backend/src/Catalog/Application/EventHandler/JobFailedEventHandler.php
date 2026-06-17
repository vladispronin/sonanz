<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Link\Application\Event\JobFailedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class JobFailedEventHandler
{
    public function __invoke(JobFailedEvent $event): void
    {
        // TODO обновить статус Job на failed
    }
}
