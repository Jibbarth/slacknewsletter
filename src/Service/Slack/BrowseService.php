<?php

declare(strict_types=1);

namespace App\Service\Slack;

use App\Collection\ArticleCollection;
use App\Constant\SlackCommand;
use App\Model\Newsletter\Contributor;
use App\Parser\SlackMessageParser;
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\CacheInterface;

final class BrowseService
{
    private Commander $commander;

    private LoggerInterface $logger;

    private SlackMessageParser $messageParser;

    private CacheInterface $cache;

    public function __construct(
        LoggerInterface $logger,
        SlackMessageParser $messageParser,
        CacheInterface $cache,
        string $slackToken
    ) {
        $interactor = new CurlInteractor();
        $interactor->setResponseFactory(new SlackResponseFactory());

        $this->commander = new Commander($slackToken, $interactor);
        $this->logger = $logger;
        $this->messageParser = $messageParser;
        $this->cache = $cache;
    }

    /** @phpstan-ignore-next-line */
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

        /** @var array<mixed> $body */
        $body = $response->getBody();

        if (!\array_key_exists('ok', $body)) {
            throw new NotFoundHttpException($body['error']);
        }
        $hasMore = \array_key_exists('has_more', $body) && false !== $body['has_more'];

        $lastTimeStamp = $oldest;
        foreach ($body['messages'] as $message) {
            try {
                if ($hasMore) {
                    $lastTimeStamp = (int) $message['ts'];
                }

                $article = $this->messageParser->getArticle($message);
                $article = $article->withContributor($this->getContributor($message['user'] ?? ''));
                $messages->add($article);
            } catch (\Throwable $throwable) {
                $this->logger->notice($throwable->getMessage());
            }
        }

        if ($hasMore) {
            $messages = $this->getChannelHistory($channel, $oldest, $max, $messages, $lastTimeStamp);
        }

        return $messages;
    }

    private function getContributor(string $user): Contributor
    {
        return $this->cache->get('user-' . $user, function () use ($user): Contributor {
            $response = $this->commander->execute('users.info', ['user' => $user]);
            /** @var array<mixed> $body */
            $body = $response->getBody();

            return new Contributor(
                $body['user']['profile']['real_name'] ?? 'Undefined',
                $body['user']['profile']['image_72'] ?? ''
            );
        });
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
