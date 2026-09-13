<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('slack_newsletter');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('slack_token')->isRequired()->end()
                ->arrayNode('blocklist_urls')
                    ->scalarPrototype()->end()
                ->end()
                ->integerNode('days_to_browse')->defaultValue(1)->end()
                ->scalarNode('mail_sender')->isRequired()->end()
                ->arrayNode('news_receivers')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('mail_template')
                    ->children()
                        ->scalarNode('main_color')->defaultValue('#333')->end()
                        ->scalarNode('background_color')->defaultValue('#f7f7f7')->end()
                        ->scalarNode('section_title_color')->defaultValue('#FFF')->end()
                        ->scalarNode('logo')->end()
                        ->scalarNode('company_name')->end()
                        ->scalarNode('base_slack_url')->end()
                        ->scalarNode('summary')->end()
                    ->end()
                ->end()
                ->arrayNode('channels')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('link')->isRequired()->end()
                            ->scalarNode('description')->isRequired()->end()
                            ->scalarNode('image')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
