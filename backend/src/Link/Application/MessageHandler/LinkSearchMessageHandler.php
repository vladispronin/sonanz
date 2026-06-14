<?php
declare(strict_types=1);

namespace App\Link\Application\MessageHandler;

use App\Link\Application\Message\LinkSearchMessage;
use App\Link\Domain\Port\AudioSourceProviderInterface;
use App\Link\Domain\ValueObject\LinkSearchQuery;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkSearchMessageHandler
{
    private AudioSourceProviderInterface $audioSourceProvider;

    public function __invoke(LinkSearchMessage $message): void
    {
        $linkSearchQuery = LinkSearchQuery::fromRawInput(
            $message->author,
            $message->title,
            $message->titleType
        );

        $audioSourceLinks = $this->audioSourceProvider->search($linkSearchQuery);

        //TODO send links to queue
    }
}
