<?php

declare(strict_types=1);

namespace App\Storage;

use Carbon\Carbon;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

final class NewsletterStorage
{
    private const CURRENT_FOLDER = 'news/current/';
    private const ARCHIVE_FOLDER = 'news/archive/';

    private Filesystem $filesystem;

    public function __construct(string $publicDir)
    {
        $localAdapter = new Local($publicDir);
        $this->filesystem = new Filesystem($localAdapter);
    }

    public function saveNews(string $content): void
    {
        $newsPath = $this->getNewsPath();

        if ($this->hasNewsReady()) {
            $this->filesystem->delete($newsPath);
        }

        $this->filesystem->write($newsPath, $content);
    }

    public function hasNewsReady(): bool
    {
        $newsPath = $this->getNewsPath();

        return $this->filesystem->has($newsPath);
    }

    public function getNewsContent(): string
    {
        $newsPath = $this->getNewsPath();

        $content = $this->filesystem->read($newsPath);
        if (false === $content) {
            throw new \LogicException('Unable to get News Content');
        }

        return $content;
    }

    public function archiveNews(): void
    {
        $now = Carbon::now();
        $newsName = $now->format('Y-m-d') . '.html';

        if (!$this->hasNewsReady()) {
            throw new \LogicException('No newsletter to archive');
        }

        $this->filesystem->copy(
            $this->getNewsPath(),
            \sprintf('%s/%s/%s', self::ARCHIVE_FOLDER, $now->year, $newsName)
        );
        $this->filesystem->delete($this->getNewsPath());
    }

    private function getNewsPath(): string
    {
        return self::CURRENT_FOLDER . 'current.html';
    }
}
