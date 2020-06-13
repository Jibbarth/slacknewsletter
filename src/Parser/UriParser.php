<?php

declare(strict_types=1);

namespace App\Parser;

final class UriParser
{
    private const URI_PATTERN = '#\bhttps?://[^\s()<>]+(?:\([\w]+\)|([^[:punct:]\s]|/))#';

    public function getUriFromText(string $text): string
    {
        if (!\preg_match(static::URI_PATTERN, $text, $url)) {
            throw new \LogicException('No URI found in "' . $text . '"');
        }

        return $url[0];
    }

    public function hasUriInText(string $text): bool
    {
        return (bool) \preg_match(static::URI_PATTERN, $text);
    }
}
