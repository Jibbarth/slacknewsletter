<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

use App\Collection\ArticleCollection;
use App\Model\Channel;

final class Section
{
    private string $title;

    private string $link;

    private string $description;

    private ?string $image;

    private ArticleCollection $articles;

    /**
     * @var array<array<string, \App\Model\Newsletter\Contributor|int>>
     */
    private array $topContributors = [];

    public function __construct(Channel $channel, ArticleCollection $articles)
    {
        $this->title = $channel->getName();
        $this->link = $channel->getLink();
        $this->description = $channel->getDescription();
        $this->image = $channel->getImage();
        $this->articles = $articles;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getArticles(): ArticleCollection
    {
        return $this->articles;
    }

    /**
     * @deprecated Use GetArticles instead
     */
    public function getMessages(): ArticleCollection
    {
        return $this->getArticles();
    }

    /**
     * @param array<array<string, \App\Model\Newsletter\Contributor|int>> $topContributors
     */
    public function withTopContributors(array $topContributors): self
    {
        $self = clone $this;

        $self->topContributors = $topContributors;

        return $self;
    }

    /**
     * @return array<array<string, \App\Model\Newsletter\Contributor|int>>
     */
    public function getTopContributors(): array
    {
        return $this->topContributors;
    }
}
