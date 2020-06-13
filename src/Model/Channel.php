<?php

declare(strict_types=1);

namespace App\Model;

final class Channel
{
    private string $name;
    private string $link;
    private string $description;
    private ?string $image;

    public function __construct(string $name, string $link, string $description, ?string $image)
    {
        $this->name = $name;
        $this->link = $link;
        $this->description = $description;
        $this->image = $image;
    }

    public function getName(): string
    {
        return $this->name;
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
}
