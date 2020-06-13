<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ChannelRepository;
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

    private BrowseService $browseService;

    private MessageStorage $storeMessageService;

    private ChannelRepository $channelRepository;

    private int $daysToBrowse;

    public function __construct(
        BrowseService $browseService,
        MessageStorage $storeMessageService,
        ChannelRepository $channelRepository,
        int $daysToBrowse
    ) {
        $this->browseService = $browseService;
        $this->channelRepository = $channelRepository;
        $this->daysToBrowse = $daysToBrowse;
        $this->storeMessageService = $storeMessageService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Browse all slack channels defined in config/channels.json')
            ->setHelp('This command must be launched at regular interval to avoid Slack history limitation.')
            ->addOption(
                'days',
                'd',
                InputOption::VALUE_OPTIONAL,
                'days to browse',
                $this->daysToBrowse
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleInteract = new SymfonyStyle($input, $output);
        /** @var int $daysToBrowse */
        $daysToBrowse = $input->getOption('days');

        $timestamp = Carbon::now()->subDays($daysToBrowse)->getTimestamp();

        $messages = [];
        /** @var \App\Model\Channel $channel */
        foreach ($this->channelRepository->getAll() as $channel) {
            $messages[$channel->getName()] = $this->browseService->getChannelHistory($channel->getLink(), $timestamp);
        }

        foreach ($messages as $channelName => $section) {
            try {
                if (\count($section) > 0) {
                    $this->storeMessageService->saveChannel($channelName, $section);
                    $consoleInteract->success([
                        'Successfully parse channel ' . $channelName,
                        \count($section) . ' messages saved',
                    ]);
                }
            } catch (\Throwable $throwable) {
                $consoleInteract->error('Unable to save channel ' . $channelName . ' : ' . $throwable->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
