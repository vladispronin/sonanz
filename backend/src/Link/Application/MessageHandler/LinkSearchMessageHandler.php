<?php

declare(strict_types=1);

namespace App\Link\Application\MessageHandler;

use App\Catalog\Application\Message\CreateAlbumCommand;
use App\Catalog\Application\Message\CreateTrackCommand;
use App\Link\Application\Message\LinkSearchMessage;
use App\Link\Domain\Enum\TitleTypeEnum;
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
            return;
        }

        if ($message->titleType === TitleTypeEnum::Track) {
            $this->bus->dispatch(new CreateTrackCommand($message->jobId, $audioSourceLinks[0]->url));
        }

        if ($message->titleType === TitleTypeEnum::Album) {
            $urls = array_map(fn(AudioSourceLink $link) => $link->url, $audioSourceLinks);
            $this->bus->dispatch(new CreateAlbumCommand($message->jobId, $urls));
        }
    }
}
