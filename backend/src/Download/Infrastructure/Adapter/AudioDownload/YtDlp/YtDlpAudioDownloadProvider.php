<?php

declare(strict_types=1);

namespace App\Download\Infrastructure\Adapter\AudioDownload\YtDlp;

use App\Download\Domain\Port\AudioDownloadProviderInterface;
use App\Download\Domain\ValueObject\AudioDownloadQuery;
use Symfony\Component\Process\Process;

class YtDlpAudioDownloadProvider implements AudioDownloadProviderInterface
{
    public function __construct(
        private string $ytDlpBin = 'yt-dlp',
        private string $proxy = '',
        private string $denoBin = '',
    ) {}

    public function download(AudioDownloadQuery $query): void
    {
        $cmd = [$this->ytDlpBin];

        if ($this->proxy !== '') {
            $cmd[] = '--proxy';
            $cmd[] = $this->proxy;
        }

        if ($this->denoBin !== '') {
            $cmd[] = '--js-runtimes';
            $cmd[] = 'deno:' . $this->denoBin;
        }

        $process = new Process(array_merge($cmd, [
            '--remote-components', 'ejs:github',
            '-f', 'bestaudio',
            '--extract-audio',
            '--audio-format', 'mp3',
            '-o', '/tmp/' . $query->trackId->toString() . '.%(ext)s',
            $query->url,
        ]));
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('yt-dlp failed: ' . $process->getErrorOutput());
        }
    }
}
