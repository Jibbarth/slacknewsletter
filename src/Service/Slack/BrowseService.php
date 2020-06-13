<?php

declare(strict_types=1);

namespace App\Service\Slack;

use App\Collection\ArticleCollection;
use App\Constant\SlackCommand;
use App\Parser\SlackMessageParser;
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BrowseService
{
    private Commander $commander;

    private LoggerInterface $logger;

    private SlackMessageParser $messageParser;

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
        ArticleCollection $messages = null,
        int $latest = null
    ): ArticleCollection {
        $channelCommand = $this->retrieveCommandForChannel($channel);

        if (null === $messages) {
            $messages = new ArticleCollection([]);
        }

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

                $article = $this->messageParser->getArticle($message);
                $article = $article->withSharedBy($message['user'] ?? '');
                $messages->add($article);
            } catch (\Throwable $throwable) {
                $this->logger->notice($throwable->getMessage());
            }
        }

        if ($body['has_more']) {
            $messages = $this->getChannelHistory($channel, $oldest, $max, $messages, $lastTimeStamp);
        }

        return $messages;
    }

    public function getTopContributors(ArticleCollection $articleCollection, int $max = 5): array
    {
        $authors = [];
        /** @var \App\Model\Newsletter\Article $article */
        foreach ($articleCollection as $article) {
            $authors[] = $article->getSharedBy();
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

    private function retrieveCommandForChannel(string $channel): string
    {
        $firstChannelLetter = \mb_substr($channel, 0, 1);

        if ('C' === $firstChannelLetter) {
            return SlackCommand::CHANNEL_HISTORY;
        }
        if ('G' === $firstChannelLetter) {
            return SlackCommand::GROUP_HISTORY;
        }

        throw new \InvalidArgumentException('Unknow channel type for ' . $channel);
    }
}
