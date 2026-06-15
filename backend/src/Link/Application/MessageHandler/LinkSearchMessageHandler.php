<?php

declare(strict_types=1);

namespace App\Link\Application\MessageHandler;

use App\Link\Application\Message\LinkSearchMessage;
use App\Link\Domain\Port\AudioSourceProviderInterface;
use App\Link\Domain\ValueObject\AudioSourceLink;
use App\Link\Domain\ValueObject\AudioSearchQuery;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkSearchMessageHandler
{
    private AudioSourceProviderInterface $audioSourceProvider;

    public function __construct(AudioSourceProviderInterface $audioSourceProvider)
    {
        $this->audioSourceProvider = $audioSourceProvider;
    }

    public function __invoke(LinkSearchMessage $message): void
    {
        $audioSearchQuery = AudioSearchQuery::fromRawInput(
            $message->author,
            $message->title,
            $message->titleType
        );

        $audioSourceLinks = $this->audioSourceProvider->search($audioSearchQuery);

        //TODO send links to queue
    }

    /**
     * @type AudioSourceLink[] $link
     */
    private function isAlbum(array $link): bool
    {
        return count($link) > 1;
    }
}
