<?php

namespace App\Service;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class StoreMessageService
 * @package App\Service
 */
class StoreMessageService
{
    const CURRENT_FOLDER = 'current/';
    const ARCHIVE_FOLDER = 'archive/';

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
     * @param string $channel
     * @param array $messages
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function saveChannel(string $channel, array $messages)
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

    /**
     * @param string $channel
     * @return array
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function retrieveMessagesForChannel(string $channel) : array
    {
        $messagesFile = $this->getMessageFilePath($channel);
        if (!$this->filesystem->has($messagesFile)) {
            $this->filesystem->write($messagesFile, \json_encode([]));
        }

        return \json_decode($this->filesystem->read($messagesFile));
    }

    /**
     * @param string $channel
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function archiveChannel(string $channel)
    {
        $channelArchive = static::ARCHIVE_FOLDER . $channel;
        $this->filesystem->copy(
            $this->getMessageFilePath($channel),
            $channelArchive . \DIRECTORY_SEPARATOR . date('Y') . \DIRECTORY_SEPARATOR . date('Y-m-d_hi') . '.json'
        );
    }

    /**
     * @param $channel
     * @return string
     */
    private function getChannelDirectoryPath($channel) : string
    {
        return $this->publicDirectory . static::CURRENT_FOLDER . $channel;
    }

    /**
     * @param string $channel
     * @return string
     */
    private function getMessageFilePath(string $channel) : string
    {
        $channelDirectory = $this->getChannelDirectoryPath($channel);

        return $channelDirectory . \DIRECTORY_SEPARATOR . 'messages.json';
    }
}
