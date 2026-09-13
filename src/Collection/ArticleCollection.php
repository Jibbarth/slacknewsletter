<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\Collection;

use Barth\SlackNewsletterBundle\Model\Newsletter\Article;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Article>
 */
final class ArticleCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Article::class;
    }
}
