<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\Collection;

use Barth\SlackNewsletterBundle\Model\Newsletter\Section;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Section>
 */
final class SectionCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Section::class;
    }
}
