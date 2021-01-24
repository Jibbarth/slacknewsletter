<?php

declare(strict_types=1);

namespace App\Parser;

use App\Model\Newsletter\Article;
use Embed\Embed;

final class SlackMessageParser
{
    private UriParser $uriParser;

    /**
     * @var array<string>
     */
    private array $blocklistUrls;

    private Embed $embedParser;

    /**
     * @param array<string> $blocklistUrls
     */
    public function __construct(
        UriParser $uriParser,
        array $blocklistUrls
    ) {
        $this->uriParser = $uriParser;
        $this->blocklistUrls = $blocklistUrls;
        $this->embedParser = new Embed();
    }

    /**
     * @param array<mixed> $message
     */
    public function getArticle(array $message): Article
    {
        // Get attachment info directly
        if (\array_key_exists('attachments', $message)) {
            return $this->getAttachmentDetail($message);
        }

        // Otherwise, regard in text if there is an url and try to retrieve data from it
        if (!\array_key_exists('text', $message)) {
            throw new \LogicException('No text found');
        }

        if ($this->uriParser->hasUriInText($message['text'])) {
            return $this->getMessageContent($message);
        }

        throw new \LogicException(\sprintf('Unprocessable message "%s"', $message['text']));
    }

    /**
     * @param array<mixed> $message
     */
    private function getAttachmentDetail(array $message): Article
    {
        $attachment = $message['attachments'][0];

        if ($this->isJustMediaAttachment($attachment)) {
            throw new \LogicException(\sprintf('Not a valuable attachment in "%s" (only media)', $message['text']));
        }

        if (
            (!\array_key_exists('title_link', $attachment) || !\array_key_exists('title', $attachment))
            && !$this->isLinkBlocklisted($attachment['original_url'])
        ) {
            return $this->parseContentFromUrl($attachment['original_url']);
        }

        if ($this->isLinkBlocklisted($attachment['title_link'])) {
            throw new \LogicException('Unauthorized url');
        }

        return new Article(
            $attachment['title_link'],
            $attachment['title'],
            $attachment['text'] ?? '',
            $attachment['thumb_url'] ?? $attachment['image_url']
        );
    }

    /**
     * @param array<mixed> $message
     */
    private function getMessageContent(array $message): Article
    {
        $url = $this->uriParser->getUriFromText($message['text']);

        if ($this->isLinkBlocklisted($url)) {
            throw new \LogicException('Unauthorized url');
        }

        return $this->parseContentFromUrl($url);
    }

    private function parseContentFromUrl(string $url): Article
    {
        $info = $this->embedParser->get($url);

        return new Article(
            (string) $info->__get('url'),
            $info->__get('title'),
            $info->__get('description'),
            (string) $info->__get('image')
        );
    }

    private function isLinkBlocklisted(string $link): bool
    {
        // TODO : change by blockList
        foreach ($this->blocklistUrls as $blocklistUrl) {
            if (\mb_strpos($link, $blocklistUrl) > -1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<mixed> $attachment
     */
    private function isJustMediaAttachment(array $attachment): bool
    {
        if (!\array_key_exists('title_link', $attachment) || !\array_key_exists('title', $attachment)) {
            if (!\array_key_exists('text', $attachment)) {
                return true;
            }
            if (\array_key_exists('is_animated', $attachment) && true === $attachment['is_animated']) {
                return true;
            }
        }

        return false;
    }
}
