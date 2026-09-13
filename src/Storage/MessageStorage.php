<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\Storage;

use Barth\SlackNewsletterBundle\Collection\ArticleCollection;
use Barth\SlackNewsletterBundle\Model\Newsletter\Article;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Serializer\SerializerInterface;

final class MessageStorage
{
    private const CURRENT_FOLDER = 'messages/current/';
    private const ARCHIVE_FOLDER = 'messages/archive/';

    private Filesystem $filesystem;

    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer, string $publicDir)
    {
        // TODO: use flysystem services provider
        $localAdapter = new LocalFilesystemAdapter($publicDir);
        $this->filesystem = new Filesystem($localAdapter);
        $this->serializer = $serializer;
    }

    public function saveChannel(string $channel, ArticleCollection $articleCollection): void
    {
        $channelDirectory = $this->getChannelDirectoryPath($channel);
        $this->filesystem->createDir($channelDirectory);
        $messagesFile = $this->getMessageFilePath($channel);

        if ($this->filesystem->has($messagesFile)) {
            $articleCollection = $articleCollection->merge($this->retrieveMessagesForChannel($channel));
            $this->filesystem->delete($messagesFile);
        }

        $this->filesystem->write($messagesFile, $this->serializer->serialize($articleCollection->toArray(), 'json'));
    }

    public function retrieveMessagesForChannel(string $channel): ArticleCollection
    {
        $messagesFile = $this->getMessageFilePath($channel);
        if (!$this->filesystem->has($messagesFile)) {
            $this->filesystem->write($messagesFile, \Safe\json_encode([]));
        }

        return new ArticleCollection($this->serializer->deserialize(
            $this->filesystem->read($messagesFile),
            Article::class . '[]',
            'json',
        ));
    }

    public function archiveChannel(string $channel): void
    {
        $channelArchive = self::ARCHIVE_FOLDER . $channel;
        $this->filesystem->copy(
            $this->getMessageFilePath($channel),
            $channelArchive . \DIRECTORY_SEPARATOR . \date('Y') . \DIRECTORY_SEPARATOR . \date('Y-m-d_hi') . '.json',
        );
        $this->filesystem->delete($this->getMessageFilePath($channel));
    }

    private function getChannelDirectoryPath(string $channel): string
    {
        return self::CURRENT_FOLDER . $channel;
    }

    private function getMessageFilePath(string $channel): string
    {
        $channelDirectory = $this->getChannelDirectoryPath($channel);

        return $channelDirectory . \DIRECTORY_SEPARATOR . 'messages.json';
    }
}
