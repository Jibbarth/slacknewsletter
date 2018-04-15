<?php

namespace App\Command;

use App\Service\Slack\BrowseService;
use App\Service\StoreMessageService;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AppNewsletterBrowseCommand
 *
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
     * @var int
     */
    private $daysToBrowse;
    /**
     * @var StoreMessageService
     */
    private $storeMessageService;

    /**
     * AppNewsletterBuildCommand constructor.
     * @param \App\Service\Slack\BrowseService $browseService
     * @param StoreMessageService $storeMessageService
     * @param array $slackChannels
     * @param int $daysToBrowse
     */
    public function __construct(
        BrowseService $browseService,
        StoreMessageService $storeMessageService,
        array $slackChannels,
        int $daysToBrowse
    ) {
        $this->browseService = $browseService;
        $this->slackChannels = $slackChannels;
        $this->daysToBrowse = $daysToBrowse;
        $this->storeMessageService = $storeMessageService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Browse all slack channels defined in config/channels.json')
            ->setHelp('This command must be launched at regular interval to avoid Slack history limitation.')
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'days to browse', $this->daysToBrowse)
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
        $daysToBrowse = (int) $input->getOption('days');
        $timestamp = Carbon::now()->subDays($daysToBrowse)->getTimestamp();

        $messages = [];
        foreach ($this->slackChannels as $channel) {
            $messages[$channel['name']] = $this->browseService->getPublicChannel($channel['link'], $timestamp);
        }

        foreach ($messages as $channel => &$section) {
            try {
                if (\count($section) > 0) {
                    $this->storeMessageService->saveChannel($channel, $section);
                    $consoleInteract->success([
                        'Successfully parse channel ' . $channel,
                        count($section) . ' messages saved',
                    ]);
                }
            } catch (\Throwable $throwable) {
                $consoleInteract->error('Unable to save channel ' . $channel . ' : ' . $throwable->getMessage());
            }
        }
    }
}
