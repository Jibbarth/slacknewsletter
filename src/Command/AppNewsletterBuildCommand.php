<?php

declare(strict_types=1);

namespace App\Command;

use App\Builder\NewsletterBuilder;
use App\Storage\NewsletterStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AppNewsletterBuildCommand extends Command
{
    protected static $defaultName = 'app:newsletter:build';

    /**
     * @var NewsletterBuilder
     */
    private $buildService;
    /**
     * @var NewsletterStorage
     */
    private $storeService;

    public function __construct(
        NewsletterStorage $storeService,
        NewsletterBuilder $buildService
    ) {
        $this->storeService = $storeService;
        $this->buildService = $buildService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('no-archive', null, InputOption::VALUE_NONE, 'no archive message after build')
            ->setDescription('Build the newsletter on parsed channels located in public/current')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleInteract = new SymfonyStyle($input, $output);

        try {
            $newsletter = $this->getNewsLetter($input);

            $this->storeService->saveNews($newsletter);
            $consoleInteract->success('News correctly saved');
        } catch (\Throwable $throwable) {
            $consoleInteract->error([
                'Unable to save news',
                $throwable->getMessage(),
            ]);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function getNewsLetter(InputInterface $input): string
    {
        if ($input->getOption('no-archive')) {
            return $this->buildService->build();
        }

        return $this->buildService->buildAndArchive();
    }
}
