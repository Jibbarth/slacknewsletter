<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

use App\Model\Channel;

final class Section
{
    private string $title;

    private string $link;

    private string $description;

    private ?string $image;

    private array $messages;

    private array $topContributors;

    public function __construct(Channel $channel, array $messages)
    {
        $this->title = $channel->getName();
        $this->link = $channel->getLink();
        $this->description = $channel->getDescription();
        $this->image = $channel->getImage();
        $this->messages = $messages;
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

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function withTopContributors(array $topContributors): self
    {
        $self = clone $this;

        $self->topContributors = $topContributors;

        return $self;
    }

    public function getTopContributors(): array
    {
        return $this->topContributors;
    }
}
