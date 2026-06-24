<?php

declare(strict_types=1);

namespace App\Tag\Application\MessageHandler;

use App\Shared\Application\Event\AudioTaggedEvent;
use App\Shared\Application\Message\TagAudioMessage;
use App\Shared\Domain\ValueObject\TrackTagMetadata;
use App\Tag\Domain\Port\CoverArtProviderInterface;
use App\Tag\Domain\Port\FingerprintProviderInterface;
use App\Tag\Domain\Port\ID3TagsProviderInterface;
use App\Tag\Domain\ValueObject\AudioTagQuery;
use App\Tag\Domain\ValueObject\TrackMetadata;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
class TagAudioMessageHandler
{
    private FingerprintProviderInterface $fingerprintProvider;
    private ID3TagsProviderInterface $id3TagsProvider;
    private CoverArtProviderInterface $coverArtProvider;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;

    public function __construct(
        FingerprintProviderInterface $fingerprintProvider,
        ID3TagsProviderInterface $id3TagsProvider,
        CoverArtProviderInterface $coverArtProvider,
        MessageBusInterface $messageBus,
        LoggerInterface $logger,
    ) {
        $this->fingerprintProvider = $fingerprintProvider;
        $this->id3TagsProvider = $id3TagsProvider;
        $this->coverArtProvider = $coverArtProvider;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function __invoke(TagAudioMessage $message): void
    {
        $audioPath = $this->getAudioPath($message->trackId);

        $fingerprint = $this->fingerprintProvider->getFingerprint($audioPath);

        $tagQuery = new AudioTagQuery($fingerprint, $message->authorTitle, $message->trackTitle);

        $ID3Tags = $this->getID3Tags($tagQuery);

        if (is_null($ID3Tags)) {
            $this->logger->warning('ID3-теги не найдены, обработка трека остановлена', [
                'track_id' => $message->trackId->toString(),
            ]);
            return;
        }

        $coverArt = $this->coverArtProvider->getByReleaseGroupId($ID3Tags->releaseGroupId);

        $this->enrichWithTags($audioPath, $ID3Tags, $coverArt);

        $this->messageBus->dispatch(new AudioTaggedEvent(
            $message->trackId,
            new TrackTagMetadata($ID3Tags->title, $ID3Tags->album, $ID3Tags->artist, $ID3Tags->albumArtist),
        ));
    }

    private function enrichWithTags(string $filePath, TrackMetadata $tags, ?string $coverArt): void
    {
        new \getID3();
        $writer = new \getid3_writetags();
        $writer->filename = $filePath;
        $writer->tagformats = ['id3v2.3'];
        $writer->overwrite_tags = true;
        $writer->tag_encoding = 'UTF-8';

        $data = [];

        if ($tags->title)       $data['title']        = [$tags->title];
        if ($tags->artist)      $data['artist']       = [$tags->artist];
        if ($tags->album)       $data['album']        = [$tags->album];
        if ($tags->trackNumber) $data['track_number'] = [(string) $tags->trackNumber];

        if ($coverArt) {
            $data['attached_picture'] = [[
                'data'          => $coverArt,
                'picturetypeid' => 3,
                'description'   => '',
                'mime'          => 'image/jpeg',
            ]];
        }

        $writer->tag_data = $data;
        $writer->WriteTags();
    }

    private function getID3Tags(AudioTagQuery $query): ?TrackMetadata
    {
        return $this->id3TagsProvider->getTags($query);
    }

    private function getAudioPath(Uuid $trackId): string
    {
        return '/tmp/' . $trackId->toString() . '.mp3';
    }
}
