<?php

namespace App\Storage;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class MessageStorage
{
    private const CURRENT_FOLDER = 'messages/current/';
    private const ARCHIVE_FOLDER = 'messages/archive/';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $publicDir)
    {
        $localAdapter = new Local($publicDir);
        $this->filesystem = new Filesystem($localAdapter);
    }

    public function saveChannel(string $channel, array $messages): void
    {
        $channelDirectory = $this->getChannelDirectoryPath($channel);
        $this->filesystem->createDir($channelDirectory);
        $messagesFile = $this->getMessageFilePath($channel);

        if ($this->filesystem->has($messagesFile)) {
            $messages = \array_merge($messages, $this->retrieveMessagesForChannel($channel));
            $this->filesystem->delete($messagesFile);
        }

        $this->filesystem->write($messagesFile, \json_encode($messages));
    }

    public function retrieveMessagesForChannel(string $channel): array
    {
        $messagesFile = $this->getMessageFilePath($channel);
        if (!$this->filesystem->has($messagesFile)) {
            $this->filesystem->write($messagesFile, \json_encode([]));
        }

        return \json_decode($this->filesystem->read($messagesFile), true);
    }

    public function archiveChannel(string $channel): void
    {
        $channelArchive = static::ARCHIVE_FOLDER . $channel;
        $this->filesystem->copy(
            $this->getMessageFilePath($channel),
            $channelArchive . \DIRECTORY_SEPARATOR . \date('Y') . \DIRECTORY_SEPARATOR . \date('Y-m-d_hi') . '.json'
        );
        $this->filesystem->delete($this->getMessageFilePath($channel));
    }

    private function getChannelDirectoryPath(string $channel): string
    {
        return static::CURRENT_FOLDER . $channel;
    }

    private function getMessageFilePath(string $channel): string
    {
        $channelDirectory = $this->getChannelDirectoryPath($channel);

        return $channelDirectory . \DIRECTORY_SEPARATOR . 'messages.json';
    }
}
