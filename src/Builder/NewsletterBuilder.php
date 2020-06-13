<?php

declare(strict_types=1);

namespace App\Builder;

use App\Collection\SectionCollection;
use App\Model\Newsletter\Section;
use App\Render\NewsletterRender;
use App\Repository\ChannelRepository;
use App\Service\Slack\BrowseService;
use App\Storage\MessageStorage;

final class NewsletterBuilder
{
    private NewsletterRender $renderService;

    private ChannelRepository $channelRepository;

    private MessageStorage $storeMessageService;

    private BrowseService $browseService;

    public function __construct(
        NewsletterRender $renderService,
        MessageStorage $storeMessageService,
        BrowseService $browseService,
        ChannelRepository $channelRepository
    ) {
        $this->renderService = $renderService;
        $this->channelRepository = $channelRepository;
        $this->storeMessageService = $storeMessageService;
        $this->browseService = $browseService;
    }

    public function build(): string
    {
        $messages = $this->getMessagesToDisplay();
        $compresser = \WyriHaximus\HtmlCompress\Factory::construct();

        // TODO : option to disable/enable top contributors
        $messages = $this->addTopContributors($messages);

        if (0 == \count($messages)) {
            throw new \LogicException('No articles to send. Did you launch app:newsletter:browse command ?');
        }

        $newsletter = $this->renderService->render($messages);

        return $compresser->compress($newsletter);
    }

    public function buildAndArchive(): string
    {
        $newsletter = $this->build();

        /** @var \App\Model\Channel $channel */
        foreach ($this->channelRepository->getAll() as $channel) {
            $this->storeMessageService->archiveChannel($channel->getName());
        }

        return $newsletter;
    }

    public function getMessagesToDisplay(): SectionCollection
    {
        $messages = [];
        /** @var \App\Model\Channel $channel */
        foreach ($this->channelRepository->getAll() as $channel) {
            try {
                $channelMessages = $this->storeMessageService->retrieveMessagesForChannel($channel->getName());

                // TODO : remove duplication
                //$channelMessages = $this->removeDuplicationInMessages($channelMessages);

                if (\count($channelMessages) > 0) {
                    $messages[$channel->getName()] = new Section($channel, $channelMessages);
                }
            } catch (\Throwable $throwable) {
                continue;
            }
        }

        return new SectionCollection($messages);
    }

    private function addTopContributors(SectionCollection $messages): SectionCollection
    {
        /** @var Section $section */
        foreach ($messages as &$section) {
            $section = $section->withTopContributors($this->browseService->getTopContributors($section->getMessages()));
        }

        return $messages;
    }

    /*private function removeDuplicationInMessages(array $messages): array
    {
        // In case browse method retrieve twice same message
        return \array_unique($messages, SORT_REGULAR);

        // TODO : Filter duplicate link to avoid returning twice in the same part.
        // IE some users like reshare same content -_-'
    }*/
}
