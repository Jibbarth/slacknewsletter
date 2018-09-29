<?php

namespace App\Service\Slack;

use Embed\Embed;
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BrowseService
 *
 * @package App\Service\Slack
 */
class BrowseService
{
    /**
     * @var string
     */
    private $slackToken;

    /**
     * @var CurlInteractor
     */
    private $interactor;

    /**
     * @var Commander
     */
    private $commander;
    /**
     * @var array
     */
    private $blacklistUrls;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BrowseChannel constructor.
     *
     * @param string $slackToken
     * @param array $blacklistUrls
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $slackToken,
        array $blacklistUrls,
        LoggerInterface $logger
    ) {
        $this->slackToken = $slackToken;

        $this->interactor = new CurlInteractor();
        $this->interactor->setResponseFactory(new SlackResponseFactory());

        $this->commander = new Commander($this->slackToken, $this->interactor);
        $this->blacklistUrls = $blacklistUrls;
        $this->logger = $logger;
    }

    /**
     * @param string $channel
     * @param int $oldest timestamp to begin retrieve
     * @param int $max
     * @param array $messages
     * @param int|null $latest
     *
     * @return array
     */
    public function getPublicChannel(
        string $channel,
        int $oldest,
        int $max = 1000,
        array $messages = [],
        int $latest = null
    ): array {
        $commandOption = [
            'channel' => $channel,
            'count' => $max,
            'inclusive' => true,
        ];

        if (!\is_null($oldest)) {
            $commandOption['oldest'] = $oldest;
        }
        if (!\is_null($latest)) {
            $commandOption['latest'] = $latest;
        }

        $response = $this->commander->execute('channels.history', $commandOption);

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

                $newMessage = $this->getParsedMessage($message);

                $newMessage['ts'] = $message['ts'];
                $newMessage['author'] = $message['user'];
                $messages[] = $newMessage;
            } catch (\Throwable $throwable) {
                $this->logger->notice($throwable->getMessage());
            }
        }

        if ($body['has_more']) {
            $messages = $this->getPublicChannel($channel, $oldest, $max, $messages, $lastTimeStamp);
        }

        return $messages;
    }

    /**
     * @param array $messages
     * @param int $max
     *
     * @return array
     */
    public function getTopContributors(array $messages, $max = 5)
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

    /**
     * @param array $message
     *
     * @return array
     */
    protected function getParsedMessage(array $message): array
    {
        if (isset($message['attachments'])) {
            return $this->getAttachmentDetail($message);
        }

        return $this->getMessageContent($message);
    }

    /**
     * @param array $message
     *
     * @return array
     * @SuppressWarnings(PHPMD.StaticAccess)
s     */
    protected function getMessageContent(array $message): array
    {
        // The Regular Expression filter
        $regexUrl = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

        if (!\preg_match($regexUrl, $message['text'], $url)) {
            throw new NotFoundHttpException('No link found in "' . $message['text'] . '"');
        }

        if ($this->isLinkBlackListed($url[0])) {
            throw new BadRequestHttpException('Unauthorized url');
        }

        $info = Embed::create($url[0]);

        $content = [
            'title' => $info->getTitle(),
            'title_link' => $info->getUrl(),
            'text' => $info->getDescription(),
            'thumb_url' => $info->getImage(),
        ];

        return $content;
    }

    /**
     * @param $link
     *
     * @return bool
     */
    protected function isLinkBlackListed($link)
    {
        foreach ($this->blacklistUrls as $blacklistUrl) {
            if (\strpos($link, $blacklistUrl) > -1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $message
     *
     * @return array
     */
    private function getAttachmentDetail(array $message): array
    {
        $attachment = $message['attachments'][0];
        if (!isset($attachment['title'], $attachment['title_link'])) {
            throw  new NotFoundHttpException('No link found in "' . $message['text'] . '"');
        }
        if ($this->isLinkBlackListed($attachment['title_link'])) {
            throw new BadRequestHttpException('Unauthorized url');
        }

        return $attachment;
    }
}