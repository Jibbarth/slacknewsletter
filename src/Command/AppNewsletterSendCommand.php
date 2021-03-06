<?php

declare(strict_types=1);

namespace App\Command;

use App\Storage\NewsletterStorage;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class AppNewsletterSendCommand extends Command
{
    protected static $defaultName = 'app:newsletter:send';

    private NewsletterStorage $newsStoreService;
    /**
     * @var array<string>
     */
    private array $newsReceivers;

    private string $mailSender;

    private MailerInterface $mailer;

    /**
     * @param array<string> $newsReceivers
     */
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
            ->addOption(
                'no-archive',
                null,
                InputOption::VALUE_NONE,
                'no archive news after send'
            )
            ->setDescription('Send a mail with the generated news')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleInteract = new SymfonyStyle($input, $output);
        $subject = 'Newsletter ' . Carbon::now()->format('#W // Y');
        $sender = new Address($this->mailSender, 'NewsLetters');
        $message = (new Email())
            ->from($sender)
            ->subject($subject)
            ->html($this->newsStoreService->getNewsContent());
        $message->getHeaders()->addMailboxListHeader('To', $this->newsReceivers);

        $this->mailer->send($message);

        if (false === $input->getOption('no-archive')) {
            $this->newsStoreService->archiveNews();
        }
        $consoleInteract->success('Newsletter sent to ' . \implode(',', $this->newsReceivers));

        return self::SUCCESS;
    }
}
