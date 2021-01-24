<?php

declare(strict_types=1);

namespace App\Parser;

use App\Model\Newsletter\Article;
use Embed\Embed;
use JoliCode\Slack\Api\Model\ObjsMessage;
use JoliCode\Slack\Api\Model\ObjsMessageAttachmentsItem;

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

    public function getArticle(ObjsMessage $message): Article
    {
        // Get attachment info directly
        if (null !== $message->getAttachments() && [] !== $message->getAttachments()) {
            return $this->getAttachmentDetail($message->getAttachments()[0], $message);
        }

        if (null !== $message->getBlocks() && [] !== $message->getBlocks()) {
            $linksBlock = \array_values(\array_filter(
                $message->getBlocks()[0]->offsetGet('elements')[0]['elements'],
                static fn (array $block) => 'link' === $block['type']
            ));
            if (\count($linksBlock) > 0) {
                return $this->parseContentFromUrl($linksBlock[0]['url']);
            }
        }

        // Otherwise, regard in text if there is an url and try to retrieve data from it
        if (null === $message->getText()) {
            throw new \LogicException('No text found');
        }

        if ($this->uriParser->hasUriInText($message->getText())) {
            return $this->getMessageContent($message);
        }

        throw new \LogicException(\sprintf('Unprocessable message "%s"', $message->getText()));
    }

    private function getAttachmentDetail(ObjsMessageAttachmentsItem $attachment, ObjsMessage $message): Article
    {
        if ($this->isJustMediaAttachment($attachment)) {
            throw new \LogicException(\sprintf('Not a valuable attachment in "%s" (only media)', $message->getText()));
        }

        if (
            (null === $attachment->getTitleLink() || null === $attachment->getTitle())
            && !$this->isLinkBlocklisted($attachment->offsetGet('original_url'))
        ) {
            return $this->parseContentFromUrl($attachment->offsetGet('original_url'));
        }

        if ($this->isLinkBlocklisted($attachment->getTitleLink() ?? '')) {
            throw new \LogicException('Unauthorized url');
        }

        return new Article(
            $attachment->getTitleLink() ?? '',
            $attachment->getTitle() ?? '',
            $attachment->getText() ?? $attachment->getFallback() ?? '',
            $attachment->getThumbUrl() ?? $attachment->getImageUrl()
        );
    }

    private function getMessageContent(ObjsMessage $message): Article
    {
        $url = $this->uriParser->getUriFromText($message->getText() ?? '');

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
            $info->__get('description') ?? '',
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

    private function isJustMediaAttachment(ObjsMessageAttachmentsItem $attachment): bool
    {
        if (null === $attachment->getTitleLink() || null === $attachment->getTitle()) {
            if (null === $attachment->getText()) {
                return true;
            }
            // TODO: check if is_animated is still used
            /*if (\array_key_exists('is_animated', $attachment) && true === $attachment['is_animated']) {
                return true;
            }*/
        }

        return false;
    }
}
