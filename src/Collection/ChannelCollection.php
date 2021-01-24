<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\Channel;
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
