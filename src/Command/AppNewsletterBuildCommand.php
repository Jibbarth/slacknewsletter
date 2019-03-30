<?php

namespace App\Command;

use App\Service\Newsletter\BuildService;
use App\Storage\NewsletterStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppNewsletterBuildCommand extends Command
{
    protected static $defaultName = 'app:newsletter:build';

    /**
     * @var BuildService
     */
    private $buildService;
    /**
     * @var NewsletterStorage
     */
    private $storeService;

    public function __construct(
        NewsletterStorage $storeService,
        BuildService $buildService
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

    protected function execute(InputInterface $input, OutputInterface $output)
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
        }
    }

    private function getNewsLetter(InputInterface $input): string
    {
        if ($input->getOption('no-archive')) {
            return $this->buildService->build();
        }

        return $this->buildService->buildAndArchive();
    }
}
