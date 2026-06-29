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
        private string $denoBin = '',
        private string $cookiesFile = '',
    ) {}

    public function download(AudioDownloadQuery $query): void
    {
        $process = $this->buildProcess($query, withCookies: false);
        $process->run();

        if (!$process->isSuccessful()) {
            $process = $this->buildProcess($query, withCookies: true);
            $process->run();
        }

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('yt-dlp failed: ' . $process->getErrorOutput());
        }
    }

    private function buildProcess(AudioDownloadQuery $query, bool $withCookies): Process
    {
        $cmd = [$this->ytDlpBin];

        $proxy = $_SERVER['YT_DLP_PROXY'] ?? '';
        if ($proxy !== '') {
            $cmd[] = '--proxy';
            $cmd[] = $proxy;
        }

        if ($this->denoBin !== '') {
            $cmd[] = '--js-runtimes';
            $cmd[] = 'deno:' . $this->denoBin;
        }

        if ($withCookies && $this->cookiesFile !== '') {
            $cmd[] = '--cookies';
            $cmd[] = $this->cookiesFile;
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

        return $process;
    }
}
