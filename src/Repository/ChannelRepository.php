<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\ChannelCollection;
use App\Model\Channel;
use Symfony\Component\Serializer\SerializerInterface;

final class ChannelRepository
{
    private const FILENAME = 'channels.json';

    private ChannelCollection $collection;

    public function __construct(SerializerInterface $serializer, string $projectDir)
    {
        $filePath = \sprintf('%s/config/%s', $projectDir, self::FILENAME);
        if (!\file_exists($filePath)) {
            throw new \LogicException('Unable to find ' . self::FILENAME);
        }

        $channels = $serializer->deserialize(
            \Safe\file_get_contents($filePath),
            Channel::class . '[]',
            'json'
        );
        $this->collection = new ChannelCollection($channels);
    }

    public function getAll(): ChannelCollection
    {
        return $this->collection;
    }

    public function getByLink(string $link): Channel
    {
        return $this->collection->where('getLink', $link)->first();
    }

    public function getByName(string $name): Channel
    {
        return $this->collection->where('getName', $name)->first();
    }
}
