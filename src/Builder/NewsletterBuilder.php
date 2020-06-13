<?php

declare(strict_types=1);

namespace App\Builder;

use App\Collection\SectionCollection;
use App\Model\Newsletter\Article;
use App\Model\Newsletter\Contributor;
use App\Model\Newsletter\Section;
use App\Render\NewsletterRender;
use App\Repository\ChannelRepository;
use App\Storage\MessageStorage;

final class NewsletterBuilder
{
    private NewsletterRender $renderService;

    private ChannelRepository $channelRepository;

    private MessageStorage $storeMessageService;

    public function __construct(
        NewsletterRender $renderService,
        MessageStorage $storeMessageService,
        ChannelRepository $channelRepository
    ) {
        $this->renderService = $renderService;
        $this->channelRepository = $channelRepository;
        $this->storeMessageService = $storeMessageService;
    }

    public function build(): string
    {
        $messages = $this->getMessagesToDisplay();
        $compresser = \WyriHaximus\HtmlCompress\Factory::construct();

        // TODO : option to disable/enable top contributors
        $messages = $this->addTopContributors($messages);
        if ($messages->isEmpty()) {
            throw new \LogicException('No articles to send. Did you launch app:newsletter:browse command ?');
        }

        $newsletter = $this->renderService->render($messages);

        return $compresser->compress($newsletter);
    }

    public function buildAndArchive(): string
    {
        $newsletter = $this->build();

        /** @var \App\Model\Channel $channel */
        foreach ($this->channelRepository->getAll() as $channel) {
            $this->storeMessageService->archiveChannel($channel->getName());
        }

        return $newsletter;
    }

    private function getMessagesToDisplay(): SectionCollection
    {
        $messages = [];
        /** @var \App\Model\Channel $channel */
        foreach ($this->channelRepository->getAll() as $channel) {
            try {
                $channelMessages = $this->storeMessageService->retrieveMessagesForChannel($channel->getName());

                // TODO : remove duplication
                //$channelMessages = $this->removeDuplicationInMessages($channelMessages);

                if (\count($channelMessages) > 0) {
                    $messages[$channel->getName()] = new Section($channel, $channelMessages);
                }
            } catch (\Throwable $throwable) {
                continue;
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
     * @return array<array<string, \App\Model\Newsletter\Contributor|int>>
     */
    private function getTopContributorsForSection(Section $section, int $max = 5): array
    {
        $contributors = \array_map(
            fn (Article $article): Contributor => $article->getContributor(),
            $section->getArticles()->toArray()
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

        \usort($contributorList, static function (array $x, array $y) {
            // Sort by contributions (higher is first)
            if ($x['contributions'] === $y['contributions']) {
                return 0;
            }
            if ($x['contributions'] < $y['contributions']) {
                return 1;
            }

            return -1;
        });

        return \array_slice($contributorList, 0, $max, true);
    }

    /*private function removeDuplicationInMessages(array $messages): array
    {
        // In case browse method retrieve twice same message
        return \array_unique($messages, SORT_REGULAR);

        // TODO : Filter duplicate link to avoid returning twice in the same part.
        // IE some users like reshare same content -_-'
    }*/
}
