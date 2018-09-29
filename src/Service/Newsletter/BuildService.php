<?php

namespace App\Service\Newsletter;

use App\Service\Slack\BrowseService;
use App\Service\StoreMessageService;

/**
 * Class BuildService
 *
 * @package App\Service\Newsletter
 */
class BuildService
{
    /**
     * @var RenderService
     */
    private $renderService;
    /**
     * @var array
     */
    private $slackChannels;
    /**
     * @var StoreMessageService
     */
    private $storeMessageService;
    /**
     * @var BrowseService
     */
    private $browseService;

    public function __construct(
        RenderService $renderService,
        StoreMessageService $storeMessageService,
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

        // TODO : option to disable/enable top contributors
        $messages = $this->addTopContributors($messages);

        if (\count($messages) == 0) {
            throw new \LogicException('No articles to send. Did you launch app:newsletter:browse command ?');
        }

        $newsletter = $this->renderService->render($messages);

        return $newsletter;
    }

    /**
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return string
     */
    public function buildAndArchive()
    {
        $newsletter = $this->build();

        foreach ($this->slackChannels as $channel) {
            $this->storeMessageService->archiveChannel($channel['name']);
        }

        return $newsletter;
    }

    /**
     * @return array
     */
    public function getMessagesToDisplay()
    {
        $messages = [];
        foreach ($this->slackChannels as $channel) {
            try {
                $channelMessages = $this->storeMessageService->retrieveMessagesForChannel($channel['name']);

                if (\count($channelMessages) > 0) {
                    $messages[$channel['name']] = [
                        'messages' => $channelMessages,
                        'title' => $channel['name'],
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

    /**
     * @param array $messages
     *
     * @return array
     */
    protected function addTopContributors(array $messages)
    {
        foreach ($messages as $channel => $section) {
            $messages[$channel]['topContributors'] = $this->browseService->getTopContributors($section['messages']);
        }

        return $messages;
    }
}
