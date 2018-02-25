<?php

namespace App\Service\Slack;

use Embed\Embed;
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
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
     * BrowseChannel constructor.
     * @param string $slackToken
     */
    public function __construct(string $slackToken)
    {
        $this->slackToken = $slackToken;

        $this->interactor = new CurlInteractor();
        $this->interactor->setResponseFactory(new SlackResponseFactory());

        $this->commander = new Commander($this->slackToken, $this->interactor);
    }

    /**
     * @param string $channel
     * @param string|null $from
     * @param int $max
     * @return array
     */
    public function getPublicChannel(string $channel, string $from = null, $max = 100) : array
    {
        $commandOption = [
            'channel' => $channel,
            'count' => $max,
            'inclusive' => true,
        ];

        if (!is_null($from)) {
            $commandOption['oldest'] = $from;
        }

        $response = $this->commander->execute('channels.history', $commandOption);

        if (!$response->getBody()['ok']) {
            throw new NotFoundHttpException($response->getBody()['error']);
        }

        $body = $response->getBody();
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
    private function getMessageContent(array $message) : array
    {
        // The Regular Expression filter
        $regexUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        if (!preg_match($regexUrl, $message['text'], $url)) {
            throw new NotFoundHttpException('No link found in "' . $message['text'] . '"');
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
     * @param array $message
     * @return array
     */
    private function getAttachmentDetail(array $message) : array
    {
        $attachment = $message['attachments'][0];
        if (!isset($attachment['title'], $attachment['title_link'])) {
            throw  new NotFoundHttpException('No link found in "' . $message['text'] . '"');
        }

        return $attachment;
    }
}
