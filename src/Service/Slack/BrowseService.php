<?php

declare(strict_types=1);

namespace App\Service\Slack;

use App\Collection\ArticleCollection;
use App\Model\Newsletter\Contributor;
use App\Parser\SlackMessageParser;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Api\Model\ConversationsHistoryGetResponse200;
use JoliCode\Slack\Api\Model\ConversationsRepliesGetResponse200;
use JoliCode\Slack\Api\Model\ObjsMessage;
use JoliCode\Slack\Api\Model\ObjsUserProfile;
use JoliCode\Slack\Api\Model\UsersInfoGetResponse200;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Cache\CacheInterface;

final class BrowseService
{
    private LoggerInterface $logger;

    private SlackMessageParser $messageParser;

    private CacheInterface $cache;

    private Client $client;

    private Serializer $serializer;

    public function __construct(
        LoggerInterface $logger,
        SlackMessageParser $messageParser,
        CacheInterface $cache,
        string $slackToken
    ) {
        $this->client = \JoliCode\Slack\ClientFactory::create($slackToken);
        $this->logger = $logger;
        $this->messageParser = $messageParser;
        $this->cache = $cache;

        $this->serializer = new Serializer([
            new \Symfony\Component\Serializer\Normalizer\ArrayDenormalizer(),
            new \JoliCode\Slack\Api\Normalizer\JaneObjectNormalizer(),
        ]);
    }

    /** @phpstan-ignore-next-line */
    public function getChannelHistory(
        string $channel,
        float $oldest,
        int $max = 1000,
        ?ArticleCollection $messages = null,
        ?int $latest = null
    ): ArticleCollection {
        if (null === $messages) {
            $messages = new ArticleCollection([]);
        }

        $parameters = [
            'channel' => $channel,
            //'count' => $max,
            'inclusive' => true,
            'oldest' => $oldest,
        ];

        if (null !== $latest) {
            $parameters['latest'] = $latest;
        }

        $response = $this->client->conversationsHistory($parameters);
        if (!$response instanceof ConversationsHistoryGetResponse200) {
            throw new \LogicException('Problem while retrieving conversation for channel ' . $channel);
        }

        $hasMore = $response->getHasMore() ?? false;
        $this->processArrayOfMessages($response->getMessages() ?? [], $channel, $messages);
        if ($hasMore) {
            $lastTimeStamp = (int) ($response->getMessages() ?? [])[-1]->getTs();
            $messages = $this->getChannelHistory($channel, $oldest, $max, $messages, $lastTimeStamp);
        }

        return $messages;
    }

    /**
     * @param array<\JoliCode\Slack\Api\Model\ObjsMessage> $messages
     */
    private function processArrayOfMessages(array $messages, string $channel, ArticleCollection $articleCollection): void
    {
        foreach ($messages as $message) {
            try {
                $article = $this->messageParser->getArticle($message);
                $article = $article->withContributor($this->getContributor($message->getUser() ?? ''));
                $articleCollection->add($article);
            } catch (\Throwable $throwable) {
                $this->logger->notice($throwable->getMessage());
            }

            if (null !== $message->getThreadTs()) {
                $response = $this->client->conversationsReplies([
                    'channel' => $channel,
                    'inclusive' => false,
                    'ts' => $message->getThreadTs(),
                ]);

                if (!$response instanceof ConversationsRepliesGetResponse200) {
                    continue;
                }
                // Currently, conversationsReplies don't return array of ObjsMessage, just an old plain array
                $threadMessages = $this->serializer->denormalize($response->getMessages(), ObjsMessage::class . '[]');
                // Remove first message, already treated
                $threadMessages = \array_slice($threadMessages, 1);
                $threadMessages = \array_map(static function (ObjsMessage $currentMessage): ObjsMessage {
                    // Disable thread to avoid infinite loop
                    $currentMessage->setThreadTs(null);

                    return $currentMessage;
                }, $threadMessages);
                $this->processArrayOfMessages($threadMessages, $channel, $articleCollection);
            }
        }
    }

    private function getContributor(string $user): Contributor
    {
        return $this->cache->get('user-' . $user, function () use ($user): Contributor {
            $response = $this->client->usersInfo(['user' => $user]);
            $profile = new ObjsUserProfile();

            if ($response instanceof UsersInfoGetResponse200 && null !== $response->getUser()) {
                $profile = $response->getUser()->getProfile() ?? $profile;
            }

            return new Contributor(
                $profile->getRealName() ?? 'Undefined',
                $profile->getImage72() ?? ''
            );
        });
    }
}
