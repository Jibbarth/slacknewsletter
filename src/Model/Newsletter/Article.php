<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

final class Article
{
    private string $url;

    private string $title;

    private string $content;

    private ?string $imageUrl;

    private ?string $sharedBy;

    public function __construct(string $url, string $title, string $content, ?string $imageUrl, ?string $sharedBy = null)
    {
        $this->url = $url;
        $this->title = $title;
        $this->content = $content;
        $this->imageUrl = $imageUrl;
        $this->sharedBy = $sharedBy;
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

    public function withSharedBy(string $sharedBy): self
    {
        $self = clone $this;
        $self->sharedBy = $sharedBy;

        return $self;
    }

    public function getSharedBy(): ?string
    {
        return $this->sharedBy;
    }
}
