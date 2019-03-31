<?php

namespace App\Command;

use App\Storage\NewsletterStorage;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\NamedAddress;

class AppNewsletterSendCommand extends Command
{
    protected static $defaultName = 'app:newsletter:send';
    /**
     * @var NewsletterStorage
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
     * @var \Symfony\Component\Mailer\MailerInterface
     */
    private $mailer;

    public function __construct(
        MailerInterface $mailer,
        NewsletterStorage $newsStoreService,
        array $newsReceivers,
        string $mailSender
    ) {
        $this->mailer = $mailer;
        $this->newsStoreService = $newsStoreService;
        $this->newsReceivers = $newsReceivers;
        $this->mailSender = $mailSender;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('no-archive', null, InputOption::VALUE_NONE, 'no archive news after send')
            ->setDescription('Send a mail with the generated news')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleInteract = new SymfonyStyle($input, $output);
        $subject = 'Newsletter ' . Carbon::now()->format('#W // Y');
        $sender = new NamedAddress($this->mailSender, 'NewsLetters');
        $message = (new Email())
            ->from($sender)
            ->subject($subject)
            ->html($this->newsStoreService->getNewsContent());
        $message->getHeaders()->addMailboxListHeader('To', $this->newsReceivers);

        $this->mailer->send($message);

        if (!$input->getOption('no-archive')) {
            $this->newsStoreService->archiveNews();
        }
        $consoleInteract->success('Message sended to ' . \implode(',', $this->newsReceivers));
    }
}
