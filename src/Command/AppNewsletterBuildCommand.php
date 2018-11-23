<?php

namespace App\Command;

use App\Service\Newsletter\BuildService;
use App\Service\Newsletter\StoreService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     * @var BuildService
     */
    private $buildService;
    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * AppNewsletterBuildCommand constructor.
     *
     * @param StoreService $storeService
     * @param BuildService $buildService
     */
    public function __construct(
        StoreService $storeService,
        BuildService $buildService
    ) {
        $this->storeService = $storeService;
        $this->buildService = $buildService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('no-archive', null, InputOption::VALUE_NONE, 'no archive message after build')
            ->setDescription('Build the newsletter on parsed channels located in public/current')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
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

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @return string
     */
    private function getNewsLetter(InputInterface $input)
    {
        if ($input->getOption('no-archive')) {
            return $this->buildService->build();
        }

        return $this->buildService->buildAndArchive();
    }
}
