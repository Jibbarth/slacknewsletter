<?php

declare(strict_types=1);

namespace App\Service\Slack;

use App\Constant\SlackCommand;
use App\Parser\SlackMessageParser;
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BrowseService
{
    /**
     * @var Commander
     */
    private $commander;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var \App\Parser\SlackMessageParser
     */
    private $messageParser;

    public function __construct(
        LoggerInterface $logger,
        SlackMessageParser $messageParser,
        string $slackToken
    ) {
        $interactor = new CurlInteractor();
        $interactor->setResponseFactory(new SlackResponseFactory());

        $this->commander = new Commander($slackToken, $interactor);
        $this->logger = $logger;
        $this->messageParser = $messageParser;
    }

    public function getChannelHistory(
        string $channel,
        int $oldest,
        int $max = 1000,
        array $messages = [],
        int $latest = null
    ): array {
        $channelCommand = $this->retrieveCommandForChannel($channel);

        $commandOption = [
            'channel' => $channel,
            'count' => $max,
            'inclusive' => true,
            'oldest' => $oldest,
        ];

        if (null !== $latest) {
            $commandOption['latest'] = $latest;
        }

        $response = $this->commander->execute($channelCommand, $commandOption);

        /** @var array $body */
        $body = $response->getBody();

        if (!$body['ok']) {
            throw new NotFoundHttpException($body['error']);
        }

        $lastTimeStamp = $oldest;
        foreach ($body['messages'] as $message) {
            try {
                if ($body['has_more']) {
                    $lastTimeStamp = $message['ts'];
                }

                $newMessage = $this->messageParser->getParsedMessage($message);

                $newMessage['ts'] = $message['ts'];
                $newMessage['author'] = $message['user'];
                $messages[] = $newMessage;
            } catch (\Throwable $throwable) {
                $this->logger->notice($throwable->getMessage());
            }
        }

        if ($body['has_more']) {
            $messages = $this->getChannelHistory($channel, $oldest, $max, $messages, $lastTimeStamp);
        }

        return $messages;
    }

    public function getTopContributors(array $messages, int $max = 5): array
    {
        $authors = [];
        foreach ($messages as $message) {
            $authors[] = $message['author'];
        }

        $contributorList = \array_count_values($authors);
        \arsort($contributorList, SORT_NUMERIC);
        $topContributors = [];

        $count = 0;
        foreach ($contributorList as $contributor => $nbContributions) {
            if ($count >= $max) {
                break;
            }

            $response = $this->commander->execute('users.info', ['user' => $contributor]);
            /** @var array $body */
            $body = $response->getBody();
            $topContributors[] = [
                'author' => $body['user']['profile']['real_name'],
                'avatar' => $body['user']['profile']['image_72'],
                'nbContributions' => $nbContributions,
            ];
            $count++;
        }

        return $topContributors;
    }

    protected function retrieveCommandForChannel(string $channel): string
    {
        $firstChannelLetter = \mb_substr($channel, 0, 1);

        switch ($firstChannelLetter) {
            case 'C':
                return SlackCommand::CHANNEL_HISTORY;
            case 'G':
                return SlackCommand::GROUP_HISTORY;
            default:
                throw new \InvalidArgumentException('Unknow channel type for ' . $channel);
        }
    }
}
