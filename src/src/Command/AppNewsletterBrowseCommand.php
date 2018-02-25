<?php

namespace App\Command;

use App\Service\Slack\BrowseService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AppNewsletterBrowseCommand
 * @package App\Command
 */
class AppNewsletterBrowseCommand extends Command
{
    protected static $defaultName = 'app:newsletter:browse';

    /**
     * @var \App\Service\Slack\BrowseService
     */
    private $browseService;
    /**
     * @var array
     */
    private $slackChannels;

    /**
     * AppNewsletterBuildCommand constructor.
     * @param \App\Service\Slack\BrowseService $browseService
     * @param array $slackChannels
     */
    public function __construct(
        BrowseService $browseService,
        array $slackChannels
    ) {
        parent::__construct();
        $this->browseService = $browseService;
        $this->slackChannels = $slackChannels;
    }

    protected function configure()
    {
        $this
            ->setDescription('Browse all slack channels defined in config/channels.json')
            ->setHelp('This command must be launched at regular interval to avoid Slack history limitation.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleInteract = new SymfonyStyle($input, $output);

        foreach ($this->slackChannels as $channel) {
            $messages[$channel['name']] = $this->browseService->getPublicChannel($channel['link']);
        }

        // TODO : store $messages
        $consoleInteract->success('');
    }
}
