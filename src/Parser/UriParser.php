<?php

namespace App\Parser;

/**
 * Class UriParser
 *
 * @package App\Parser
 *
 * @author Jibé Barth <barth.jib@gmail.com>
 */
class UriParser
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
