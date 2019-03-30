<?php

namespace App\Builder;

use App\Render\NewsletterRender;
use App\Service\Slack\BrowseService;
use App\Storage\MessageStorage;

class NewsletterBuilder
{
    /**
     * @var NewsletterRender
     */
    private $renderService;
    /**
     * @var array
     */
    private $slackChannels;
    /**
     * @var MessageStorage
     */
    private $storeMessageService;
    /**
     * @var BrowseService
     */
    private $browseService;

    public function __construct(
        NewsletterRender $renderService,
        MessageStorage $storeMessageService,
        BrowseService $browseService,
        array $slackChannels
    ) {
        $this->renderService = $renderService;
        $this->slackChannels = $slackChannels;
        $this->storeMessageService = $storeMessageService;
        $this->browseService = $browseService;
    }

    public function build()
    {
        $messages = $this->getMessagesToDisplay();
        $compresser = \WyriHaximus\HtmlCompress\Factory::construct();

        // TODO : option to disable/enable top contributors
        $messages = $this->addTopContributors($messages);

        if (\count($messages) == 0) {
            throw new \LogicException('No articles to send. Did you launch app:newsletter:browse command ?');
        }

        $newsletter = $this->renderService->render($messages);

        return $compresser->compress($newsletter);
    }

    public function buildAndArchive(): string
    {
        $newsletter = $this->build();

        foreach ($this->slackChannels as $channel) {
            $this->storeMessageService->archiveChannel($channel['name']);
        }

        return $newsletter;
    }

    public function getMessagesToDisplay(): array
    {
        $messages = [];
        foreach ($this->slackChannels as $channel) {
            try {
                $channelMessages = $this->storeMessageService->retrieveMessagesForChannel($channel['name']);

                $channelMessages = $this->removeDuplicationInMessages($channelMessages);

                if (\count($channelMessages) > 0) {
                    $messages[$channel['name']] = [
                        'messages' => $channelMessages,
                        'title' => $channel['name'],
                        'link' => $channel['link'],
                    ];
                    if (isset($channel['description'])) {
                        $messages[$channel['name']]['description'] = $channel['description'];
                    }

                    if (isset($channel['image'])) {
                        $messages[$channel['name']]['image'] = $channel['image'];
                    }
                }
            } catch (\Throwable $throwable) {
            }
        }

        foreach ($messages as $channel => $section) {
            if (!isset($section['messages'])) {
                unset($messages[$channel]);
            }
        }

        return $messages;
    }

    protected function addTopContributors(array $messages): array
    {
        foreach ($messages as $channel => $section) {
            $messages[$channel]['topContributors'] = $this->browseService->getTopContributors($section['messages']);
        }

        return $messages;
    }

    protected function removeDuplicationInMessages(array $messages): array
    {
        // In case browse method retrieve twice same message
        $messages = \array_unique($messages, SORT_REGULAR);

        // TODO : Filter duplicate link to avoid returning twice in the same part.
        // IE some users like reshare same content -_-'
        return $messages;
    }
}
