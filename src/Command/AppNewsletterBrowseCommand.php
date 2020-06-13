<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Slack\BrowseService;
use App\Storage\MessageStorage;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AppNewsletterBrowseCommand extends Command
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
     * @var MessageStorage
     */
    private $storeMessageService;

    public function __construct(
        BrowseService $browseService,
        MessageStorage $storeMessageService,
        array $slackChannels,
        int $daysToBrowse
    ) {
        $this->browseService = $browseService;
        $this->slackChannels = $slackChannels;
        $this->daysToBrowse = $daysToBrowse;
        $this->storeMessageService = $storeMessageService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Browse all slack channels defined in config/channels.json')
            ->setHelp('This command must be launched at regular interval to avoid Slack history limitation.')
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'days to browse', $this->daysToBrowse)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleInteract = new SymfonyStyle($input, $output);
        /** @var int $daysToBrowse */
        $daysToBrowse = $input->getOption('days');

        $timestamp = Carbon::now()->subDays($daysToBrowse)->getTimestamp();

        $messages = [];
        foreach ($this->slackChannels as $channel) {
            $messages[$channel['name']] = $this->browseService->getChannelHistory($channel['link'], $timestamp);
        }

        foreach ($messages as $channel => &$section) {
            try {
                if (\count($section) > 0) {
                    $this->storeMessageService->saveChannel((string) $channel, $section);
                    $consoleInteract->success([
                        'Successfully parse channel ' . $channel,
                        \count($section) . ' messages saved',
                    ]);
                }
            } catch (\Throwable $throwable) {
                $consoleInteract->error('Unable to save channel ' . $channel . ' : ' . $throwable->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
