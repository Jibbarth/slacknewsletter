<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\Newsletter\Section;
use Ramsey\Collection\AbstractCollection;

final class SectionCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Section::class;
    }
}
