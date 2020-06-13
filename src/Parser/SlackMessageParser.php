<?php

declare(strict_types=1);

namespace App\Parser;

use Embed\Embed;

final class SlackMessageParser
{
    /**
     * @var \App\Parser\UriParser
     */
    private $uriParser;
    /**
     * @var array
     */
    private $blacklistUrls;

    public function __construct(
        UriParser $uriParser,
        array $blacklistUrls
    ) {
        $this->uriParser = $uriParser;
        $this->blacklistUrls = $blacklistUrls;
    }

    public function getParsedMessage(array $message): ?array
    {
        // Get attachment info directly
        if (isset($message['attachments'])) {
            return $this->getAttachmentDetail($message);
        }

        // Otherwise, regard in text if there is an url and try to retrieve data from it
        if (!isset($message['text'])) {
            throw new \LogicException('No text found');
        }

        if ($this->uriParser->hasUriInText($message['text'])) {
            return $this->getMessageContent($message);
        }

        throw new \LogicException(\sprintf('Unprocessable message "%s"', $message['text']));
    }

    private function getAttachmentDetail(array $message): array
    {
        $attachment = $message['attachments'][0];

        if ($this->isJustMediaAttachment($attachment)) {
            throw  new \LogicException(\sprintf('Not a valuable attachment in "%s" (only media)', $message['text']));
        }

        if (
            !isset($attachment['title_link'], $attachment['title'])
            && !$this->isLinkBlackListed($attachment['original_url'])
        ) {
            return  $this->parseContentFromUrl($attachment['original_url']);
        }

        if ($this->isLinkBlackListed($attachment['title_link'])) {
            throw new \LogicException('Unauthorized url');
        }

        return $attachment;
    }

    private function getMessageContent(array $message): array
    {
        $url = $this->uriParser->getUriFromText($message['text']);

        if ($this->isLinkBlackListed($url)) {
            throw new \LogicException('Unauthorized url');
        }

        return $this->parseContentFromUrl($url);
    }

    private function parseContentFromUrl(string $url): array
    {
        $info = Embed::create($url);

        return [
            'title' => $info->getTitle(),
            'title_link' => $info->getUrl(),
            'text' => $info->getDescription(),
            'thumb_url' => $info->getImage(),
        ];
    }

    private function isLinkBlackListed(string $link): bool
    {
        foreach ($this->blacklistUrls as $blacklistUrl) {
            if (\mb_strpos($link, $blacklistUrl) > -1) {
                return true;
            }
        }

        return false;
    }

    private function isJustMediaAttachment(array $attachment): bool
    {
        if (!isset($attachment['title_link'], $attachment['title'])) {
            if (!isset($attachment['text'])) {
                return true;
            }
            if (isset($attachment['is_animated']) && true === $attachment['is_animated']) {
                return true;
            }
        }

        return false;
    }
}
