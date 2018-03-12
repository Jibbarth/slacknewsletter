<?php

namespace App\Command;

use App\Service\Newsletter\RenderService;
use App\Service\Newsletter\StoreService;
use App\Service\Slack\BrowseService;
use App\Service\StoreMessageService;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AppNewsletterBrowseCommand
 *
 * @package App\Command
 */
class AppNewsletterBuildCommand extends Command
{
    protected static $defaultName = 'app:newsletter:build';

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
    /**
     * @var RenderService
     */
    private $newsletterRenderService;
    /**
     * @var StoreService
     */
    private $newsletterStoreService;

    /**
     * AppNewsletterBuildCommand constructor.
     * @param StoreMessageService $storeMessageService
     * @param BrowseService $browseService
     * @param RenderService $newsRenderService
     * @param StoreService $newsStoreService
     * @param array $slackChannels
     */
    public function __construct(
        StoreMessageService $storeMessageService,
        BrowseService $browseService,
        RenderService $newsRenderService,
        StoreService $newsStoreService,
        array $slackChannels
    ) {
        $this->slackChannels = $slackChannels;
        $this->storeMessageService = $storeMessageService;
        $this->browseService = $browseService;
        $this->newsletterRenderService = $newsRenderService;
        $this->newsletterStoreService = $newsStoreService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Build the newsletter on parsed channels located in public/current')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleInteract = new SymfonyStyle($input, $output);

        $messages = $this->getMessagesToSend();
        foreach ($messages as $channel => &$section) {
            $section['topContributors'] = $this->browseService->getTopContributors($section['messages']);
        }

        if (\count($messages) == 0) {
            throw new \LogicException('No articles to send');
        }

        $newsletter = $this->newsletterRenderService->render($messages);
        try {
            $this->newsletterStoreService->saveNews($newsletter);
            $consoleInteract->success('News correctly saved');
        } catch (\Throwable $throwable) {
            $consoleInteract->error([
                'Unable to save news',
                $throwable->getMessage()
            ]);
        }
    }

    /**
     * @return array
     */
    private function getMessagesToSend()
    {
        $messages = [];
        foreach ($this->slackChannels as $channel) {
            try {
                $channelMessages = $this->storeMessageService->retrieveMessagesForChannel($channel['name']);
                $this->storeMessageService->archiveChannel($channel['name']);

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
}
