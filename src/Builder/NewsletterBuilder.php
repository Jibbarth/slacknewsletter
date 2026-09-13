<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\Builder;

use Barth\SlackNewsletterBundle\Collection\SectionCollection;
use Barth\SlackNewsletterBundle\Model\Newsletter\Article;
use Barth\SlackNewsletterBundle\Model\Newsletter\Contributor;
use Barth\SlackNewsletterBundle\Model\Newsletter\Section;
use Barth\SlackNewsletterBundle\Render\NewsletterRender;
use Barth\SlackNewsletterBundle\Repository\ChannelRepository;
use Barth\SlackNewsletterBundle\Storage\MessageStorage;

final class NewsletterBuilder
{
    private NewsletterRender $renderService;

    private ChannelRepository $channelRepository;

    private MessageStorage $storeMessageService;

    public function __construct(
        NewsletterRender $renderService,
        MessageStorage $storeMessageService,
        ChannelRepository $channelRepository,
    ) {
        $this->renderService = $renderService;
        $this->channelRepository = $channelRepository;
        $this->storeMessageService = $storeMessageService;
    }

    public function build(): string
    {
        $messages = $this->getMessagesToDisplay();

        // TODO : option to disable/enable top contributors
        $messages = $this->addTopContributors($messages);
        if ($messages->isEmpty()) {
            throw new \LogicException('No articles to send. Did you launch app:newsletter:browse command ?');
        }

        $newsletter = $this->renderService->render($messages);

        return preg_replace('/\s+/', ' ', $newsletter);
    }

    public function buildAndArchive(): string
    {
        $newsletter = $this->build();

        /** @var \Barth\SlackNewsletterBundle\Model\Channel $channel */
        foreach ($this->channelRepository->getAll() as $channel) {
            $this->storeMessageService->archiveChannel($channel->getName());
        }

        return $newsletter;
    }

    private function getMessagesToDisplay(): SectionCollection
    {
        $messages = [];
        /** @var \Barth\SlackNewsletterBundle\Model\Channel $channel */
        foreach ($this->channelRepository->getAll() as $channel) {
            $channelMessages = $this->storeMessageService->retrieveMessagesForChannel($channel->getName());

            if (false === $channelMessages->isEmpty()) {
                $messages[$channel->getName()] = new Section($channel, $channelMessages);
            }
        }

        return new SectionCollection($messages);
    }

    private function addTopContributors(SectionCollection $messages): SectionCollection
    {
        $newCollection = new SectionCollection();
        /** @var Section $section */
        foreach ($messages as $section) {
            $newCollection->add($section->withTopContributors($this->getTopContributorsForSection($section)));
        }

        return $newCollection;
    }

    /**
     * @return array<array<string, \Barth\SlackNewsletterBundle\Model\Newsletter\Contributor|int>>
     */
    private function getTopContributorsForSection(Section $section, int $max = 5): array
    {
        $contributors = \array_map(
            static function (Article $article): Contributor {
                return $article->getContributor();
            },
            $section->getArticles()->toArray(),
        );

        $contributorList = [];
        foreach ($contributors as $contributor) {
            if (!\array_key_exists($contributor->getName(), $contributorList)) {
                $contributorList[$contributor->getName()] = [
                    'contributor' => $contributor,
                    'contributions' => 0,
                ];
            }
            $contributorList[$contributor->getName()]['contributions']++;
        }

        \usort($contributorList, static function (array $current, array $next) {
            // Sort by contributions (higher is first)
            if ($current['contributions'] === $next['contributions']) {
                return 0;
            }
            if ($current['contributions'] < $next['contributions']) {
                return 1;
            }

            return -1;
        });

        return \array_slice($contributorList, 0, $max, true);
    }

}
