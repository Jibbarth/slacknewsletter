<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\Repository;

use Barth\SlackNewsletterBundle\Collection\ChannelCollection;
use Barth\SlackNewsletterBundle\Model\Channel;

final class ChannelRepository
{
    private ChannelCollection $collection;

    public function __construct(array $channelsData)
    {
        $channels = \array_map(
            static fn (array $data): Channel => new Channel(
                $data['name'],
                $data['link'],
                $data['description'],
                $data['image'] ?? null,
            ),
            $channelsData,
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
