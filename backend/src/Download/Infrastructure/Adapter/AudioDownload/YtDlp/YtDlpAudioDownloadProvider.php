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
            // yt-dlp при выходе перезаписывает файл cookies, а исходный смонтирован read-only,
            // поэтому работаем с временной копией
            $cookiesCopy = $this->copyCookies($query);
            try {
                $process = $this->buildProcess($query, withCookies: true, cookiesFile: $cookiesCopy);
                $process->run();
            } finally {
                if ($cookiesCopy !== null) {
                    @unlink($cookiesCopy);
                }
            }
        }

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('yt-dlp failed: ' . $process->getErrorOutput());
        }
    }

    private function copyCookies(AudioDownloadQuery $query): ?string
    {
        if ($this->cookiesFile === '' || !is_readable($this->cookiesFile)) {
            return null;
        }

        $copy = sys_get_temp_dir() . '/yt-cookies-' . $query->trackId->toString() . '.txt';
        if (!@copy($this->cookiesFile, $copy)) {
            return null;
        }

        return $copy;
    }

    private function buildProcess(AudioDownloadQuery $query, bool $withCookies, ?string $cookiesFile = null): Process
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

        if ($withCookies && $cookiesFile !== null) {
            $cmd[] = '--cookies';
            $cmd[] = $cookiesFile;
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
