<?php

namespace App\Command;

use App\Service\Newsletter\StoreService;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\NamedAddress;

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
     * @param array $newsReceivers
     * @param string $mailSender
     */
    public function __construct(
        StoreService $newsStoreService,
        array $newsReceivers,
        string $mailSender
    ) {
        $this->newsStoreService = $newsStoreService;
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
     * @throws \League\Flysystem\Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleInteract = new SymfonyStyle($input, $output);
        $subject = 'Newsletter ' . Carbon::now()->format('#W // Y');
        $sender = new NamedAddress($this->mailSender, 'NewsLetters');

        $message = (new Email())
            ->subject($subject)
            ->addFrom($sender)
            ->html($this->newsStoreService->getNewsContent());
        foreach ($this->newsReceivers as $receiver) {
            $message->addTo($receiver);
        }

        // TODO : Use a transport to send mail
        // dump($message->toString());

        if (!$input->getOption('no-archive')) {
            $this->newsStoreService->archiveNews();
        }
        $consoleInteract->success('Message sended to ' . \implode(',', $this->newsReceivers));
    }
}
