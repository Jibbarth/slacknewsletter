<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

final class Article
{
    private string $url;

    private string $title;

    private string $content;

    private ?string $imageUrl;

    private ?Contributor $contributor;

    public function __construct(string $url, string $title, string $content, ?string $imageUrl, ?Contributor $contributor = null)
    {
        $this->url = $url;
        $this->title = $title;
        $this->content = $content;
        $this->imageUrl = $imageUrl;
        $this->contributor = $contributor;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function withContributor(Contributor $contributor): self
    {
        $self = clone $this;
        $self->contributor = $contributor;

        return $self;
    }

    public function getContributor(): Contributor
    {
        if (null === $this->contributor) {
            throw new \LogicException(\sprintf('No contributor found for article "%s"', $this->getTitle()));
        }

        return $this->contributor;
    }
}
