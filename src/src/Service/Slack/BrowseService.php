<?php

namespace App\Service\Slack;

use Carbon\Carbon;
use Embed\Embed;
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BrowseService
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
     * BrowseChannel constructor.
     * @param string $slackToken
     * @param array $blacklistUrls
     */
    public function __construct(
        string $slackToken,
        array $blacklistUrls
    ) {

        $this->slackToken = $slackToken;

        $this->interactor = new CurlInteractor();
        $this->interactor->setResponseFactory(new SlackResponseFactory());

        $this->commander = new Commander($this->slackToken, $this->interactor);
        $this->blacklistUrls = $blacklistUrls;
    }

    /**
     * @param string $channel
     * @param int $from nb days to retrieve
     * @param int $max
     * @return array
     */
    public function getPublicChannel(string $channel, int $from = 1, $max = 100) : array
    {
        $commandOption = [
            'channel' => $channel,
            'count' => $max,
            'inclusive' => true,
        ];

        if (!is_null($from)) {
            $commandOption['oldest'] = Carbon::now()->subDays($from)->getTimestamp();
        }

        $response = $this->commander->execute('channels.history', $commandOption);

        /** @var array $body */
        $body = $response->getBody();

        if (!$body['ok']) {
            throw new NotFoundHttpException($body['error']);
        }

        $messages = [];
        foreach ($body['messages'] as $message) {
            try {
                if (isset($message['attachments'])) {
                    $messages[] = $this->getAttachmentDetail($message);
                    continue;
                }

                $messages[] = $this->getMessageContent($message);
            } catch (\Throwable $throwable) {
            }
        }

        return $messages;
    }

    /**
     * @param array $message
     * @return array
     * @SuppressWarnings(PHPMD.StaticAccess)
s     */
    protected function getMessageContent(array $message) : array
    {
        // The Regular Expression filter
        $regexUrl = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

        if (!preg_match($regexUrl, $message['text'], $url)) {
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

    protected function isLinkBlackListed($link)
    {
        foreach ($this->blacklistUrls as $blacklistUrl) {
            if (\strpos($link, $blacklistUrl) > -1) {
                return true;
            }
        }
    }

    /**
     * @param array $message
     * @return array
     */
    private function getAttachmentDetail(array $message) : array
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
