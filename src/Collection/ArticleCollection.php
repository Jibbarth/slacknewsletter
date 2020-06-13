<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\Newsletter\Article;
use Ramsey\Collection\AbstractCollection;

final class ArticleCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Article::class;
    }
}
