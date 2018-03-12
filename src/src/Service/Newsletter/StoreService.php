<?php

namespace App\Service\Newsletter;

use Carbon\Carbon;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class StoreService
 * @package App\Service\Newsletter
 */
class StoreService
{
    const CURRENT_FOLDER = 'news/current/';
    const ARCHIVE_FOLDER = 'news/archive/';

    /**
     * @var string
     */
    private $publicDirectory;
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        string $publicDir
    ) {
        $localAdapter = new Local($publicDir);
        $this->filesystem = new Filesystem($localAdapter);
        $this->publicDirectory = $publicDir;
    }

    /**
     * @param string $content
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function saveNews(string $content)
    {
        $newsPath = $this->getNewsPath();

        if ($this->filesystem->has($newsPath)) {
            $this->filesystem->delete($newsPath);
        }

        $this->filesystem->write($newsPath, $content);
    }

    /**
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function archiveNews()
    {
        $now = Carbon::now();
        $newsName = $now->format('Y-W') . '.html';

        if (!$this->filesystem->has($this->getNewsPath())) {
            throw new \LogicException('No newsletter to archive');
        }

        $this->filesystem->copy($this->getNewsPath(), static::ARCHIVE_FOLDER . $newsName);
        $this->filesystem->delete($this->getNewsPath());
    }

    /**
     * @return string
     */
    private function getNewsPath()
    {
        return static::CURRENT_FOLDER . 'current.html';
    }
}
