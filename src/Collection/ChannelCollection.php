<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\Collection;

use Barth\SlackNewsletterBundle\Model\Channel;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Channel>
 */
final class ChannelCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Channel::class;
    }
}
