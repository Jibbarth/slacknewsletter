<?php

namespace App\Command;

use App\Service\Newsletter\StoreService;
use Carbon\Carbon;
use Swift_Mailer;
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
class AppNewsletterSendCommand extends Command
{
    protected static $defaultName = 'app:newsletter:send';
    /**
     * @var StoreService
     */
    private $newsStoreService;
    /**
     * @var Swift_Mailer
     */
    private $mailer;
    /**
     * @var array
     */
    private $newsReceivers;
    /**
     * @var string
     */
    private $mailSender;

    /**
     * AppNewsletterBuildCommand constructor.
     *
     * @param StoreService $newsStoreService
     * @param Swift_Mailer $mailer
     * @param array $newsReceivers
     * @param string $mailSender
     */
    public function __construct(
        StoreService $newsStoreService,
        Swift_Mailer $mailer,
        array $newsReceivers,
        string $mailSender
    ) {
        $this->newsStoreService = $newsStoreService;
        $this->mailer = $mailer;
        $this->newsReceivers = $newsReceivers;
        $this->mailSender = $mailSender;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('no-archive', null, InputOption::VALUE_NONE, 'no archive news after send')
            ->setDescription('Send a mail with the generated news')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \League\Flysystem\FileNotFoundException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleInteract = new SymfonyStyle($input, $output);
        $subject = 'Newsletter ' . Carbon::now()->format('#W // Y');
        $message = (new \Swift_Message($subject))
            ->setFrom($this->mailSender, 'NewsLetters')
            ->setTo($this->newsReceivers);

        $message->setBody($this->newsStoreService->getNewsContent(), 'text/html');
        $this->mailer->send($message);

        if (!$input->getOption('no-archive')) {
            $this->newsStoreService->archiveNews();
        }
        $consoleInteract->success('Message sended to ' . \implode(',', $this->newsReceivers));
    }
}
