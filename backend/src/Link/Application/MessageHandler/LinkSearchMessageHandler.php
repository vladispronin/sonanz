<?php

declare(strict_types=1);

namespace App\Link\Application\MessageHandler;

use App\Shared\Application\Event\JobFailedEvent;
use App\Shared\Application\Event\LinksFoundEvent;
use App\Shared\Application\Message\LinkSearchMessage;
use App\Link\Domain\Port\AudioSourceProviderInterface;
use App\Link\Domain\ValueObject\AudioSourceLink;
use App\Link\Domain\ValueObject\AudioSearchQuery;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class LinkSearchMessageHandler
{
    public function __construct(
        private AudioSourceProviderInterface $audioSourceProvider,
        private MessageBusInterface $bus,
    ) {}

    public function __invoke(LinkSearchMessage $message): void
    {
        $audioSearchQuery = AudioSearchQuery::fromRawInput(
            $message->author,
            $message->title,
            $message->titleType,
        );

        $audioSourceLinks = $this->audioSourceProvider->search($audioSearchQuery);

        if (!$audioSourceLinks) {
            $this->bus->dispatch(new JobFailedEvent($message->jobId, JobFailedEvent::LINKS_NOT_FOUND));
            return;
        }

        $urls = array_map(fn(AudioSourceLink $link) => $link->url, $audioSourceLinks);

        $this->bus->dispatch(new LinksFoundEvent($message->jobId, $message->titleType, $urls));
    }
}
